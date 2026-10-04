/**
 * Do chieu cao that cua khoi banner tren vai be rong, de biet ti le anh doc
 * can dung.
 * Chay: node scratch/do-banner.cjs
 */
const { spawn } = require('child_process');
const fs = require('fs');
const os = require('os');
const path = require('path');

const CHROME = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const PORT = 9348;
const UDD = path.join(os.tmpdir(), 'noxh-hero-' + Date.now());
const HOST = 'noxh.test';
const sleep = (ms) => new Promise((r) => setTimeout(r, ms));
const RONG = [320, 360, 390, 414, 480, 768, 1024, 1280];

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

    console.log('  be rong | cao banner | ti le (cao/rong) | anh dang dung | hien thi');
    console.log('  --------+------------+------------------+---------------+---------');

    for (const w of RONG) {
      await gui('Emulation.setDeviceMetricsOverride', { width: w, height: 900, deviceScaleFactor: 1, mobile: w <= 768 }, sid);
      await gui('Page.navigate', { url: `http://${HOST}/` }, sid);
      await sleep(2600);

      const kq = await chay(`(function(){
        var hero = document.querySelector('.nx-hero');
        if (!hero) return 'khong thay .nx-hero';
        var r = hero.getBoundingClientRect();
        var img = document.querySelector('.nx-hero__bg img');
        var dangDung = img && img.currentSrc ? img.currentSrc.split('/').pop() : '(khong co)';
        return [
          Math.round(r.width),
          Math.round(r.height),
          (r.height / r.width).toFixed(2),
          dangDung,
          img ? Math.round(img.getBoundingClientRect().width) + 'x' + Math.round(img.getBoundingClientRect().height) : '?'
        ].join(' | ');
      })()`);
      console.log('  ' + kq);
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
