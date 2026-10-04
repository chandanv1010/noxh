/**
 * Ghi lai DUNG URL ma CKFinder dua cho window.open khi bam vao o anh.
 *
 * Chay: node scratch/do-url-popup.cjs <cookiePhien>
 *
 * Cach do truoc do (doc /json/list roi attach vao target moi) cho ket qua mau
 * thuan, khong ket luan duoc. Cach nay khong dung den danh sach target: ta thay
 * chinh window.open bang mot ham ghi lai tham so, nen chac chan biet CKFinder
 * dinh mo dia chi nao - hoac biet no nem loi truoc khi kip goi.
 */

const { spawn } = require('child_process');
const fs = require('fs');
const os = require('os');
const path = require('path');

const COOKIE = process.argv[2];
if (!COOKIE) { console.error('thieu cookie'); process.exit(1); }

const CHROME = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const PORT = 9359;
const UDD = path.join(os.tmpdir(), 'noxh-url-' + Date.now());
const HOST = 'noxh.test';
const DUONG_DAN = process.argv[3] || '/introduce/index';
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
        loi.push((d.exception && d.exception.description || d.text || '?').split('\n').slice(0, 2).join(' | '));
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

    await gui('Page.navigate', { url: `http://${HOST}${DUONG_DAN}` }, sid);
    await sleep(3500);

    console.log('trang: ' + await chay('location.pathname'));
    console.log('CKFinder.DEFAULT_basePath : ' + await chay('String(CKFinder.DEFAULT_basePath)'));
    console.log('');

    // Thay window.open bang ham ghi lai. Tra ve null de khong thuc su mo cua so
    // (khong mo thi khong phai don).
    await chay(`(function(){
      window.__mo = [];
      window.open = function(u, n, f){ window.__mo.push({ url: String(u), name: String(n) }); return null; };
      return 'da thay window.open';
    })()`);

    const o = JSON.parse(await chay(`(function(){
      var e = document.querySelector('.upload-image');
      e.scrollIntoView({block:'center'});
      var r = e.getBoundingClientRect();
      return JSON.stringify({ x: Math.round(r.left + r.width/2), y: Math.round(r.top + r.height/2) });
    })()`));
    await sleep(500);

    loi.length = 0;
    for (const kieu of ['mousePressed', 'mouseReleased']) {
      await gui('Input.dispatchMouseEvent', { type: kieu, x: o.x, y: o.y, button: 'left', clickCount: 1 }, sid);
      await sleep(120);
    }
    await sleep(2500);

    console.log('URL ma window.open nhan duoc:');
    console.log('  ' + await chay('JSON.stringify(window.__mo)'));
    console.log('loi JS khi bam: ' + (loi.length ? loi.join(' ;; ').slice(0, 400) : '(khong co)'));

    // Thu lan hai: tu goi thang ham cua ung dung voi basePath ro rang, xem URL khac gi.
    console.log('');
    console.log('--- neu dat basePath ro rang ---');
    await chay(`(function(){
      window.__mo = [];
      var f = new CKFinder();
      f.basePath = '/vendor/backend/plugins/ckfinder_2/';
      f.selectActionFunction = function tenThu(a, b){};
      try { f.popup(); } catch(e){ window.__mo.push({ url: 'LOI: ' + e.message, name: '' }); }
      return 'xong';
    })()`);
    await sleep(1200);
    console.log('  ' + await chay('JSON.stringify(window.__mo)'));

  } catch (e) {
    console.error('LOI: ' + e.message);
  } finally {
    try { if (ws) ws.close(); } catch (e) {}
    chrome.kill();
    await sleep(300);
    try { fs.rmSync(UDD, { recursive: true, force: true }); } catch (e) {}
  }
})();
