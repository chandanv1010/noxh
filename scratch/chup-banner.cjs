/**
 * Chup khoi banner trang chu o ca dien thoai va may tinh.
 * Chay: node scratch/chup-banner.cjs [rong...]
 */
const { spawn } = require('child_process');
const fs = require('fs');
const os = require('os');
const path = require('path');

const CHROME = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const PORT = 9350;
const UDD = path.join(os.tmpdir(), 'noxh-chup2-' + Date.now());
const HOST = 'noxh.test';
const sleep = (ms) => new Promise((r) => setTimeout(r, ms));
const RONG = process.argv.slice(2).map(Number).filter(Boolean);
const DS = RONG.length ? RONG : [390, 1280];

(async () => {
  const chrome = spawn(CHROME, [
    '--headless=new', '--disable-gpu', '--no-sandbox',
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
    await gui('Runtime.enable', {}, sid);
    await gui('Network.enable', {}, sid);

    const chay = async (expr) => {
      const r = await gui('Runtime.evaluate', {
        expression: `(function(){ try { return String(${expr}); } catch(e){ return 'LOI: ' + e.message; } })()`,
        returnByValue: true, awaitPromise: true,
      }, sid);
      if (r.exceptionDetails) return 'LOI CDP';
      return r.result.value;
    };

    for (const w of DS) {
      await gui('Emulation.setDeviceMetricsOverride', { width: w, height: 1000, deviceScaleFactor: 2, mobile: w <= 768 }, sid);
      await gui('Page.navigate', { url: `http://${HOST}/` }, sid);
      await sleep(3200);

      const tt = await chay(`(function(){
        var h = document.querySelector('.nx-hero');
        var r = h.getBoundingClientRect();
        var img = document.querySelector('.nx-hero__bg img');
        return 'rong=' + Math.round(r.width) + ' cao=' + Math.round(r.height) +
               ' | anh=' + (img && img.currentSrc ? img.currentSrc.split('/').pop() : '?') +
               ' | khoi so lieu: ' + (document.querySelector('.nx-hero__so') ? 'CON' : 'da an');
      })()`);
      console.log('  ' + w + 'px : ' + tt);

      const clip = await chay(`(function(){
        var h = document.querySelector('.nx-hero');
        var r = h.getBoundingClientRect();
        return JSON.stringify({ x: 0, y: Math.round(r.top + window.scrollY),
                                width: Math.round(r.width), height: Math.round(Math.min(r.height, 1300)) });
      })()`);
      const shot = await gui('Page.captureScreenshot', {
        format: 'png', clip: { ...JSON.parse(clip), scale: 1 }, captureBeyondViewport: true,
      }, sid);
      const ra = `D:\\sandbox\\banner-${w}.png`;
      fs.writeFileSync(ra, Buffer.from(shot.data, 'base64'));
      console.log('      -> ' + ra + ' (' + Math.round(fs.statSync(ra).size / 1024) + ' KB)');
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
