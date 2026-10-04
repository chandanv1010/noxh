/**
 * Chup anh khoi "Thong tin du an nha o xa hoi" tren form that.
 * Chay: node scratch/chup-anh-form.cjs <cookiePhien> [duongDan] [tenAnh]
 */

const { spawn } = require('child_process');
const fs = require('fs');
const os = require('os');
const path = require('path');

const COOKIE = process.argv[2];
const DUONG_DAN = process.argv[3] || '/product/76/edit';
const TEN_ANH = process.argv[4] || 'khoi-du-an';
if (!COOKIE) { console.error('thieu cookie'); process.exit(1); }

const CHROME = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const PORT = 9335;
const UDD = path.join(os.tmpdir(), 'noxh-anh-' + Date.now());
const HOST = 'noxh.test';
const RA = 'D:\\sandbox\\' + TEN_ANH + '.png';
const sleep = (ms) => new Promise((r) => setTimeout(r, ms));

(async () => {
  const chrome = spawn(CHROME, [
    '--headless=new', '--disable-gpu', '--no-sandbox', '--window-size=1500,2400',
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
    ws.addEventListener('message', (ev) => {
      const m = JSON.parse(ev.data);
      if (m.id && cho.has(m.id)) { cho.get(m.id)(m); cho.delete(m.id); }
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
    await gui('Network.enable', {}, sid);
    await gui('Runtime.enable', {}, sid);

    const [phien, xsrf] = fs.readFileSync(path.join(os.tmpdir(), 'noxh-cookie.txt'), 'utf8').trim().split('\n');
    await gui('Network.setCookie', { name: 'noxhvn_session', value: phien, domain: HOST, path: '/' }, sid);
    if (xsrf) await gui('Network.setCookie', { name: 'XSRF-TOKEN', value: xsrf, domain: HOST, path: '/' }, sid);

    await gui('Page.navigate', { url: `http://${HOST}${DUONG_DAN}` }, sid);
    await sleep(4500);

    const chay = async (expr) => {
      const r = await gui('Runtime.evaluate', { expression: expr, returnByValue: true, awaitPromise: true }, sid);
      if (r.exceptionDetails) return 'LOI: ' + (r.exceptionDetails.exception?.description || r.exceptionDetails.text);
      return r.result.value;
    };

    console.log('tieu de: ' + (await chay('document.title')));

    // Cuon khoi du an len dau khung nhin, roi moi do (getBoundingClientRect
    // tra toa do so voi khung nhin, khong phai so voi trang).
    const cuon = await chay(`(function(){
      var tieuDe = Array.from(document.querySelectorAll('.ibox-title h5'))
        .find(function(h){ return h.textContent.indexOf('Thông tin dự án') !== -1; });
      if (!tieuDe) return null;
      var ibox = tieuDe.closest('.ibox');
      ibox.scrollIntoView({ block: 'start' });
      return 'da cuon';
    })()`);
    console.log('cuon: ' + cuon);
    if (!cuon || cuon === 'null') { throw new Error('khong thay khoi du an'); }

    await sleep(1200);

    const rect = await chay(`(function(){
      var tieuDe = Array.from(document.querySelectorAll('.ibox-title h5'))
        .find(function(h){ return h.textContent.indexOf('Thông tin dự án') !== -1; });
      var r = tieuDe.closest('.ibox').getBoundingClientRect();
      // captureBeyondViewport chup theo toa do TRANG, nen phai cong scrollY.
      var cao = Math.min(Math.round(r.height), 900);
      return JSON.stringify({ x: Math.max(0, Math.round(r.left)),
                              y: Math.max(0, Math.round(r.top + window.scrollY)),
                              width: Math.round(r.width), height: cao });
    })()`);
    console.log('vung chup (toa do trang): ' + rect);

    const clip = JSON.parse(rect);

    const shot = await gui('Page.captureScreenshot', {
      format: 'png', clip: { ...clip, scale: 1 }, captureBeyondViewport: true,
    }, sid);

    fs.writeFileSync(RA, Buffer.from(shot.data, 'base64'));
    console.log('da luu anh: ' + RA + ' (' + Math.round(fs.statSync(RA).size / 1024) + ' KB)');

  } catch (e) {
    console.error('LOI: ' + e.message);
  } finally {
    try { if (ws) ws.close(); } catch (e) {}
    chrome.kill();
    await sleep(300);
    try { fs.rmSync(UDD, { recursive: true, force: true }); } catch (e) {}
  }
})();
