/**
 * Soi thật hành vi album ảnh ở trang chi tiết dự án trên web đang chạy.
 *
 * Chạy: node scratch/soi-album.cjs [url]
 *
 * Câu hỏi cần trả lời, không đoán: bấm vào một ảnh nhỏ thì
 *   (a) ảnh lớn đổi tại chỗ (JS đang chặn mặc định), hay
 *   (b) trình duyệt mở sang tab mới / điều hướng sang tệp ảnh (JS không chạy)
 */

const { spawn } = require('child_process');
const fs = require('fs');
const os = require('os');
const path = require('path');

const CHROME = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const URL = process.argv[2] || 'https://noxh.vn/du-an/noxh-machino-elite-phu-xuan';
const PORT = 9433;
const UDD = path.join(os.tmpdir(), 'soi-album-' + Date.now());
const sleep = (ms) => new Promise((r) => setTimeout(r, ms));

(async () => {
  const chrome = spawn(CHROME, [
    '--headless=new', '--disable-gpu', '--no-sandbox', '--no-first-run',
    '--no-default-browser-check', '--user-data-dir=' + UDD,
    '--remote-debugging-port=' + PORT, 'about:blank',
  ], { stdio: ['ignore', 'ignore', 'pipe'] });

  let ws = null;
  try {
    let version = null;
    for (let i = 0; i < 60; i++) {
      try { version = await (await fetch(`http://127.0.0.1:${PORT}/json/version`)).json(); break; }
      catch (e) { await sleep(250); }
    }
    ws = new WebSocket(version.webSocketDebuggerUrl);
    let id = 0; const cho = new Map(); const nhat = [];
    ws.addEventListener('message', (ev) => {
      const m = JSON.parse(ev.data);
      if (m.id && cho.has(m.id)) { cho.get(m.id)(m); cho.delete(m.id); }
      else if (m.method === 'Runtime.exceptionThrown') {
        nhat.push('NGOẠI LỆ: ' + ((m.params.exceptionDetails.exception || {}).description || m.params.exceptionDetails.text));
      } else if (m.method === 'Runtime.consoleAPICalled') {
        nhat.push('log ' + m.params.type + ': ' + (m.params.args || []).map((a) => a.value || a.description).join(' '));
      } else if (m.method === 'Target.targetCreated') {
        nhat.push('TAB MỚI: ' + m.params.targetInfo.url);
      }
    });
    await new Promise((res, rej) => { ws.addEventListener('open', res); ws.addEventListener('error', rej); });
    const gui = (method, params, sid) => new Promise((res, rej) => {
      const myId = ++id;
      cho.set(myId, (m) => (m.error ? rej(new Error(JSON.stringify(m.error))) : res(m.result)));
      ws.send(JSON.stringify({ id: myId, method, params: params || {}, sessionId: sid }));
    });
    const eval_ = async (sid, e) => {
      const r = await gui('Runtime.evaluate', { expression: e, returnByValue: true, awaitPromise: true }, sid);
      if (r.exceptionDetails) return 'LỖI: ' + r.exceptionDetails.text;
      return r.result.value;
    };

    await gui('Target.setDiscoverTargets', { discover: true });

    const t = await gui('Target.createTarget', { url: 'about:blank' });
    const a = await gui('Target.attachToTarget', { targetId: t.targetId, flatten: true });
    await gui('Page.enable', {}, a.sessionId);
    await gui('Runtime.enable', {}, a.sessionId);
    await gui('Page.navigate', { url: URL }, a.sessionId);
    await sleep(5000);

    console.log('=== trang ===');
    console.log('  url  : ' + (await eval_(a.sessionId, 'location.href')));
    console.log('  tieu de: ' + (await eval_(a.sessionId, 'document.title')));

    const thongTin = await eval_(a.sessionId, `(function () {
      var o = document.querySelectorAll('[data-nx-anh]');
      var chinh = document.getElementById('nx-pd-anh-chinh');
      return JSON.stringify({
        soAnhNho: o.length,
        coAnhChinh: !!chinh,
        srcAnhChinh: chinh ? chinh.getAttribute('src') : null,
        theDauTien: o.length ? o[0].outerHTML.slice(0, 160) : null,
        coHamXuLy: o.length ? (typeof o[0].onclick) : null,
        hrefDauTien: o.length ? o[0].getAttribute('href') : null,
      });
    })()`);
    console.log('=== album ===');
    console.log('  ' + thongTin);

    // Bấm thật vào ảnh nhỏ thứ 2 (nếu có) và xem chuyện gì xảy ra.
    const truoc = await eval_(a.sessionId, `document.getElementById('nx-pd-anh-chinh').getAttribute('src')`);
    const bam = await eval_(a.sessionId, `(function(){
      var o = document.querySelectorAll('[data-nx-anh]');
      if (o.length < 2) return 'chi co ' + o.length + ' anh';
      o[1].scrollIntoView();
      o[1].click();
      return 'da bam';
    })()`);
    console.log('=== sau khi bam anh nho thu 2 ===');
    console.log('  ' + bam);
    await sleep(2500);
    const sau = await eval_(a.sessionId, `JSON.stringify({
      url: location.href,
      srcAnhChinh: document.getElementById('nx-pd-anh-chinh') ? document.getElementById('nx-pd-anh-chinh').getAttribute('src') : null,
    })`);
    console.log('  truoc: ' + truoc);
    console.log('  sau  : ' + sau);

    const ds = await gui('Target.getTargets');
    console.log('');
    console.log('=== cac tab dang mo ===');
    ds.targetInfos.filter((x) => x.type === 'page').forEach((x) => console.log('  ' + String(x.url).slice(0, 120)));

    if (nhat.length) {
      console.log('');
      console.log('=== log / loi cua trang ===');
      nhat.slice(-14).forEach((l) => console.log('  ' + String(l).slice(0, 240)));
    }
  } catch (e) {
    console.error('LỖI: ' + e.message);
    process.exitCode = 1;
  } finally {
    try { if (ws) ws.close(); } catch (e) {}
    chrome.kill();
    await sleep(400);
    try { fs.rmSync(UDD, { recursive: true, force: true }); } catch (e) {}
  }
})();
