/**
 * Kiem tra o nhap anh trong trang quan tri co THUC SU doi duoc anh khong.
 *
 * Chay: node scratch/kiem-tra-o-anh.cjs <cookiePhien> [duongDan]
 *
 * Cau hoi can tra loi:
 *   1. Trong trang co CKFinder khong (class .upload-image duoc gan vao dau)?
 *   2. Bam vao o nhap anh thi co mo duoc cua so chon anh khong?
 *   3. Cua so do co loi PHP khong (CKFinder 2 viet cho PHP 5, co the chet tren PHP 8.4)?
 */

const { spawn } = require('child_process');
const fs = require('fs');
const os = require('os');
const path = require('path');

const COOKIE = process.argv[2];
const DUONG_DAN = process.argv[3] || '/introduce/index';
if (!COOKIE) { console.error('thieu cookie'); process.exit(1); }

const CHROME = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const PORT = 9347;
const UDD = path.join(os.tmpdir(), 'noxh-oanh-' + Date.now());
const HOST = 'noxh.test';
const sleep = (ms) => new Promise((r) => setTimeout(r, ms));

(async () => {
  const chrome = spawn(CHROME, [
    '--headless=new', '--disable-gpu', '--disable-extensions', '--no-sandbox', '--window-size=1500,1200',
    '--remote-debugging-port=' + PORT, '--user-data-dir=' + UDD, 'about:blank',
  ], { stdio: 'ignore' });

  let ws = null;
  try {
    let version = null;
    for (let i = 0; i < 40; i++) {
      try { version = await (await fetch(`http://127.0.0.1:${PORT}/json/version`)).json(); break; }
      catch (e) { await sleep(250); }
    }
    if (!version) throw new Error('khong mo duoc cong debug');

    ws = new WebSocket(version.webSocketDebuggerUrl);
    let id = 0; const cho = new Map();
    const nhat = [];
    ws.addEventListener('message', (ev) => {
      const m = JSON.parse(ev.data);
      if (m.id && cho.has(m.id)) { cho.get(m.id)(m); cho.delete(m.id); }
      if (m.method === 'Runtime.consoleAPICalled') {
        nhat.push('[console] ' + (m.params.args || []).map((a) => a.value || a.description || '').join(' '));
      }
      if (m.method === 'Runtime.exceptionThrown') {
        nhat.push('[loi js ] ' + (m.params.exceptionDetails.exception || {}).description);
      }
      if (m.method === 'Log.entryAdded' && m.params.entry.level === 'error') {
        nhat.push('[log loi] ' + m.params.entry.text + ' ' + (m.params.entry.url || ''));
      }
    });
    await new Promise((res, rej) => { ws.addEventListener('open', res); ws.addEventListener('error', rej); });

    const gui = (method, params, sid) => new Promise((res, rej) => {
      const myId = ++id;
      cho.set(myId, (m) => (m.error ? rej(new Error(JSON.stringify(m.error))) : res(m.result)));
      ws.send(JSON.stringify({ id: myId, method, params: params || {}, sessionId: sid }));
    });

    const t = await gui('Target.createTarget', { url: 'about:blank' });
    const att = await gui('Target.attachToTarget', { targetId: t.targetId, flatten: true });
    const sid = att.sessionId;
    await gui('Page.enable', {}, sid);
    await gui('Runtime.enable', {}, sid);
    await gui('Log.enable', {}, sid);
    await gui('Network.enable', {}, sid);
    await gui('Network.setCookie', { name: 'noxhvn_session', value: COOKIE.trim(), domain: HOST, path: '/' }, sid);

    await gui('Page.navigate', { url: `http://${HOST}${DUONG_DAN}` }, sid);
    await sleep(3500);

    const chay = async (expr) => {
      const r = await gui('Runtime.evaluate', {
        expression: `(function(){ try { return String(${expr}); } catch(e){ return 'LOI: ' + e.message; } })()`,
        returnByValue: true, awaitPromise: true,
      }, sid);
      if (r.exceptionDetails) return 'LOI CDP';
      return r.result.value;
    };

    console.log('trang       : ' + await chay('location.pathname'));
    console.log('jQuery      : ' + await chay('typeof jQuery'));
    console.log('CKFinder    : ' + await chay('typeof CKFinder'));
    console.log('o .upload-image: ' + await chay('document.querySelectorAll(".upload-image").length'));
    console.log('o dau tien  : ' + await chay('(document.querySelector(".upload-image")||{}).value'));
    console.log('o .o-anh    : ' + await chay('document.querySelectorAll(".o-anh").length') +
                ' | anh xem truoc dang hien: ' + await chay(
                  '[].slice.call(document.querySelectorAll(".o-anh__xem:not(.trong) img"))' +
                  '.map(function(i){return i.getAttribute("src")}).join(" , ")'));

    // CKFinder 2 dung TEN cua selectActionFunction de dung URL:
    //   j.toString().match(/function ([^(]+)/)[1]
    // Ham an danh "function( fileUrl, data )" khong co ten sau chu "function"
    // => match tra ve null => null[1] nem loi => cua so da mo nhung khong bao gio
    // nap duoc ckfinder.html, de lai about:blank.
    console.log('--- chan doan URL cua CKFinder ---');
    console.log('basePath           : ' + await chay('(new CKFinder()).basePath'));
    console.log('CKFINDER_BASEPATH  : ' + await chay('String(window.CKFINDER_BASEPATH)'));
    console.log('DEFAULT_basePath   : ' + await chay('String(CKFinder.DEFAULT_basePath)'));
    console.log('lj khong ham       : ' + await chay(
      '(function(){ var f=new CKFinder(); try { return f.lj(); } catch(e){ return "LOI: "+e.message; } })()'));
    console.log('lj ham AN DANH     : ' + await chay(
      '(function(){ var f=new CKFinder(); f.selectActionFunction=function(a,b){}; ' +
      'try { return f.lj(); } catch(e){ return "LOI: "+e.message; } })()'));
    console.log('lj ham CO TEN      : ' + await chay(
      '(function(){ var f=new CKFinder(); f.selectActionFunction=function ten(a,b){}; ' +
      'try { return f.lj(); } catch(e){ return "LOI: "+e.message; } })()'));

    // /json/list tra ve khoa 'id', KHONG phai 'targetId' (targetId la ten trong
    // giao thuc CDP). Dung sai ten nen moi phan tu deu la undefined va phep so
    // sanh "target moi" luon sai -> da mot lan ket luan nham la khong mo popup.
    const ma = (tg) => tg.id || tg.targetId;

    const truocDs = await (await fetch(`http://127.0.0.1:${PORT}/json/list`)).json();
    const truoc = new Set(truocDs.map(ma));
    console.log('so cua so truoc khi bam: ' + truocDs.length);

    // Phai bam bang SU KIEN THAT (Input.dispatchMouseEvent): window.open() tu mot
    // .click() gia lap bi trinh duyet coi la popup va chan, nen lan truoc khong
    // thay cua so nao mo ra - ket luan "khong mo duoc" la SAI.
    const o = JSON.parse(await chay(`(function(){
      var e = document.querySelector('.upload-image');
      e.scrollIntoView({block:'center'});
      var r = e.getBoundingClientRect();
      return JSON.stringify({ x: Math.round(r.left + r.width / 2), y: Math.round(r.top + r.height / 2) });
    })()`));
    await sleep(600);

    for (const kieu of ['mousePressed', 'mouseReleased']) {
      await gui('Input.dispatchMouseEvent', {
        type: kieu, x: o.x, y: o.y, button: 'left', clickCount: 1,
      }, sid);
      await sleep(120);
    }
    console.log('da bam that vao o tai (' + o.x + ',' + o.y + ')');
    await sleep(5000);

    const ds = await (await fetch(`http://127.0.0.1:${PORT}/json/list`)).json();
    console.log('so cua so sau khi bam : ' + ds.length);
    console.log('--- TOAN BO cua so sau khi bam ---');
    ds.forEach((tg, i) => {
      console.log('   ' + i + ' | ' + tg.type + ' | moi=' + (!truoc.has(ma(tg))) +
                  ' | ' + String(ma(tg)).slice(0, 8) + ' | ' + String(tg.url).slice(0, 110));
    });

    const popup = ds.find((tg) => !truoc.has(ma(tg)) && tg.type === 'page')
               || ds.find((tg) => String(tg.url).indexOf('ckfinder') >= 0);
    if (popup) {
      console.log('cua so chon anh: ' + String(popup.url).slice(0, 130));
      const a2 = await gui('Target.attachToTarget', { targetId: ma(popup), flatten: true });
      await gui('Runtime.enable', {}, a2.sessionId);
      await sleep(6000);
      const ds2 = await (await fetch('http://127.0.0.1:' + PORT + '/json/list')).json();
      const nay = ds2.find((tg) => ma(tg) === ma(popup));
      console.log('url cua so chon anh sau 6s: ' + String(nay && nay.url).slice(0, 140));
      const r2 = await gui('Runtime.evaluate', {
        expression: '(document.body ? document.body.innerText : "(khong co body)")',
        returnByValue: true,
      }, a2.sessionId);
      console.log('--- noi dung cua so chon anh ---');
      console.log(String(r2.result.value).replace(/\s*\n\s*/g, '\n').slice(0, 700));
    } else {
      console.log('KHONG mo duoc cua so chon anh nao');
    }

    if (nhat.length) {
      console.log('--- loi ghi nhan duoc ---');
      nhat.slice(0, 12).forEach((d) => console.log('   ' + d.slice(0, 220)));
    } else {
      console.log('khong co loi js nao');
    }

  } catch (e) {
    console.error('LOI: ' + e.message);
  } finally {
    try { if (ws) ws.close(); } catch (e) {}
    chrome.kill();
    await sleep(300);
    try { fs.rmSync(UDD, { recursive: true, force: true }); } catch (e) {}
  }
})();
