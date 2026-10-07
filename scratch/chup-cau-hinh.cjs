/**
 * Chụp ảnh trang cấu hình hệ thống trong khu quản trị.
 *
 * Chạy: node scratch/chup-cau-hinh.cjs
 *
 * Dùng để nhìn tận mắt khối kiểm tra Telegram mới thêm, thay vì chỉ tin vào
 * việc "bài kiểm tra chạy qua".
 */

const { spawn } = require('child_process');
const fs = require('fs');
const os = require('os');
const path = require('path');

const CHROME = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const GOC = process.env.NOXH_URL || 'http://noxh.test';
const EMAIL = process.env.NOXH_ADMIN_EMAIL || '';
const MAT_KHAU = process.env.NOXH_ADMIN_PASS || '';
const PORT = 9436;
const UDD = path.join(os.tmpdir(), 'chup-ch-' + Date.now());
const sleep = (ms) => new Promise((r) => setTimeout(r, ms));

if (!EMAIL || !MAT_KHAU) {
  console.error('Thiếu tài khoản quản trị.');
  console.error('Đặt biến môi trường NOXH_ADMIN_EMAIL và NOXH_ADMIN_PASS rồi chạy lại.');
  console.error('Không ghi mật khẩu vào tệp này: tệp nằm trong kho mã.');
  process.exit(2);
}

(async () => {
  const chrome = spawn(CHROME, [
    '--headless=new', '--disable-gpu', '--no-sandbox', '--no-first-run',
    '--no-default-browser-check', '--window-size=1440,1200',
    '--user-data-dir=' + UDD, '--remote-debugging-port=' + PORT, 'about:blank',
  ], { stdio: ['ignore', 'ignore', 'pipe'] });

  let ws = null;
  try {
    let version = null;
    for (let i = 0; i < 60; i++) {
      try { version = await (await fetch(`http://127.0.0.1:${PORT}/json/version`)).json(); break; }
      catch (e) { await sleep(250); }
    }
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
    const a = await gui('Target.attachToTarget', { targetId: t.targetId, flatten: true });
    await gui('Page.enable', {}, a.sessionId);
    await gui('Runtime.enable', {}, a.sessionId);
    const doc = (e) => gui('Runtime.evaluate', { expression: e, returnByValue: true, awaitPromise: true }, a.sessionId)
      .then((r) => (r.exceptionDetails ? 'LỖI: ' + r.exceptionDetails.text : r.result.value));

    // 1. Đăng nhập
    await gui('Page.navigate', { url: GOC + '/admin' }, a.sessionId);
    await sleep(2500);
    await doc(`(function () {
      var e = document.querySelector('input[name="email"]');
      var p = document.querySelector('input[name="password"]');
      if (!e || !p) return 'khong thay o dang nhap';
      e.value = ${JSON.stringify(EMAIL)};
      p.value = ${JSON.stringify(MAT_KHAU)};
      var f = e.closest('form');
      if (f) { f.submit(); return 'da gui form'; }
      return 'khong thay form';
    })()`);
    await sleep(4000);
    console.log('sau khi dang nhap: ' + (await doc('location.href')));

    // 2. Mở trang cấu hình
    await gui('Page.navigate', { url: GOC + '/system/index' }, a.sessionId);
    await sleep(4000);
    console.log('trang cau hinh: ' + (await doc('location.href')));

    const kt = await doc(`(function () {
      var k = document.querySelector('.telegram-kiem-tra');
      return JSON.stringify({
        coKhoi: !!k,
        soNut: k ? k.querySelectorAll('[data-tg]').length : 0,
        coOToken: !!document.querySelector('input[name="config[telegram_bot_token]"]'),
        coOChatId: !!document.querySelector('input[name="config[telegram_chat_id]"]'),
      });
    })()`);
    console.log('khoi kiem tra: ' + kt);

    // 3. Cuon toi khoi Telegram roi chup
    await doc(`(function () {
      var k = document.querySelector('.telegram-kiem-tra');
      if (k) k.scrollIntoView({ block: 'center' });
      return 1;
    })()`);
    await sleep(700);

    const r = await gui('Page.captureScreenshot', { format: 'png' }, a.sessionId);
    const thuMuc = path.join(__dirname, 'anh-chup');
    fs.mkdirSync(thuMuc, { recursive: true });
    const duong = path.join(thuMuc, 'cau-hinh-telegram.png');
    fs.writeFileSync(duong, Buffer.from(r.data, 'base64'));
    console.log('ảnh: ' + duong);
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
