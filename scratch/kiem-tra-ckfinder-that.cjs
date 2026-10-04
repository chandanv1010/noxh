/**
 * Kiem tra THAT cua so chon anh cua CKFinder co nap duoc khong.
 *
 * Chay: node scratch/kiem-tra-ckfinder-that.cjs <cookiePhien>
 *
 * Vi sao phai viet lai: lan truoc toi ket luan "popup ket o about:blank => hong".
 * Doc ma CKFinder moi thay about:blank la CHU Y:
 *
 *   p = a.env.webkit ? 'about:blank' : '';        // Chrome thi mo about:blank
 *   q = window.open(p, 'CKFinderpopup', o, true);
 *   ... q.document.write('<iframe id="ckfinder" ...>')   // giao dien that nam trong IFRAME
 *
 * Nen URL cua cua so luon la about:blank va body rong - khong suy ra duoc gi.
 *
 * Cach do dung: giu lay doi tuong cua so ma window.open tra ve, roi doc thang
 * <iframe> ben trong no (cung ten mien nen doc duoc). Khong phai mo mo tim target.
 */

const { spawn } = require('child_process');
const fs = require('fs');
const os = require('os');
const path = require('path');

const COOKIE = process.argv[2];
if (!COOKIE) { console.error('thieu cookie'); process.exit(1); }

const CHROME = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const PORT = 9366;
const UDD = path.join(os.tmpdir(), 'noxh-ckf2-' + Date.now());
const HOST = 'noxh.test';
const sleep = (ms) => new Promise((r) => setTimeout(r, ms));

(async () => {
  const chrome = spawn(CHROME, [
    '--headless=new', '--disable-gpu', '--disable-extensions', '--no-sandbox',
    '--window-size=1500,1200', '--remote-debugging-port=' + PORT, '--user-data-dir=' + UDD,
    'about:blank',
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
    const loi = [];
    ws.addEventListener('message', (ev) => {
      const m = JSON.parse(ev.data);
      if (m.id && cho.has(m.id)) { cho.get(m.id)(m); cho.delete(m.id); }
      if (m.method === 'Runtime.exceptionThrown') {
        const d = m.params.exceptionDetails;
        loi.push((d.exception && d.exception.description || d.text || '?').split('\n')[0]);
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
    await gui('Network.setCookie', { name: 'noxhvn_session', value: COOKIE.trim(), domain: HOST, path: '/' }, sid);

    const chay = async (expr) => {
      const r = await gui('Runtime.evaluate', {
        expression: `(function(){ try { return String(${expr}); } catch(e){ return 'LOI: ' + e.message; } })()`,
        returnByValue: true, awaitPromise: true,
      }, sid);
      if (r.exceptionDetails) return 'LOI CDP';
      return r.result.value;
    };

    await gui('Page.navigate', { url: `http://${HOST}/introduce/index` }, sid);
    await sleep(3500);

    // Giu lay doi tuong cua so ma window.open tra ve.
    await chay(`(function(){
      var goc = window.open;
      window.__popup = null;
      window.open = function(u, n, f){ window.__popup = goc.call(window, u, n, f); return window.__popup; };
      return 'da boc window.open';
    })()`);

    const o = JSON.parse(await chay(`(function(){
      var e = document.querySelector('.upload-image');
      e.scrollIntoView({block:'center'});
      var r = e.getBoundingClientRect();
      return JSON.stringify({ x: Math.round(r.left + r.width/2), y: Math.round(r.top + r.height/2) });
    })()`));
    await sleep(400);

    loi.length = 0;
    for (const kieu of ['mousePressed', 'mouseReleased']) {
      await gui('Input.dispatchMouseEvent', { type: kieu, x: o.x, y: o.y, button: 'left', clickCount: 1 }, sid);
      await sleep(120);
    }
    console.log('da bam vao o anh');
    await sleep(6000);

    console.log('co doi tuong cua so khong : ' + await chay('String(!!window.__popup)'));
    console.log('cua so co bi dong khong    : ' + await chay('String(window.__popup ? window.__popup.closed : "?")'));
    console.log('so <iframe> trong cua so   : ' + await chay(
      'String(window.__popup ? window.__popup.document.querySelectorAll("iframe").length : -1)'));
    console.log('src cua iframe             : ' + await chay(
      'String(window.__popup ? (window.__popup.document.querySelector("iframe")||{}).src : "")'));

    // Doc noi dung THAT ben trong iframe (cung ten mien).
    console.log('doc duoc ruot iframe khong : ' + await chay(`(function(){
      var w = window.__popup;
      if (!w) return 'khong co cua so';
      var f = w.document.querySelector('iframe');
      if (!f) return 'khong co iframe';
      try {
        var d = f.contentDocument;
        if (!d) return 'contentDocument null (khac ten mien?)';
        return 'tieu de: ' + d.title + ' || so o file: ' + d.querySelectorAll('.ckf-file, .ckf-files-list tr, [data-ckf-file]').length +
               ' || chu: ' + (d.body ? d.body.innerText.replace(/\\s+/g,' ').slice(0, 260) : '(khong co body)');
      } catch(e){ return 'LOI doc iframe: ' + e.message; }
    })()`));

    // Anh trong thu muc /userfiles/image - danh sach that ma trinh chon se hien.
    console.log('');
    console.log('anh co san trong /userfiles/image:');
    console.log('  ' + await chay(`(function(){
      var w = window.__popup;
      if (!w) return 'khong co cua so';
      var f = w.document.querySelector('iframe');
      if (!f || !f.contentDocument) return 'khong doc duoc';
      var d = f.contentDocument;
      var ds = [].slice.call(d.querySelectorAll('img')).map(function(i){ return i.getAttribute('src') || ''; })
                 .filter(function(s){ return s.indexOf('userfiles') >= 0 || s.indexOf('thumb') >= 0; });
      return ds.length ? ds.slice(0, 6).join(' , ') : '(khong thay anh nao)';
    })()`));

    console.log('');
    console.log('loi JS: ' + (loi.length ? loi.join(' ;; ').slice(0, 400) : '(khong co)'));

  } catch (e) {
    console.error('LOI: ' + e.message);
  } finally {
    try { if (ws) ws.close(); } catch (e) {}
    chrome.kill();
    await sleep(300);
    try { fs.rmSync(UDD, { recursive: true, force: true }); } catch (e) {}
  }
})();
