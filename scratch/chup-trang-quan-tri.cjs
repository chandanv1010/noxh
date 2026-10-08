/**
 * Chụp ảnh vài trang trong khu quản trị, sau khi đăng nhập.
 *
 * Chạy:
 *   $env:NOXH_ADMIN_EMAIL="..."; $env:NOXH_ADMIN_PASS="..."
 *   node scratch/chup-trang-quan-tri.cjs /user/catalogue/index
 *   node scratch/chup-trang-quan-tri.cjs "/product/create|.ibox.w"   <- chỉ chụp một khối
 *
 * Cú pháp mỗi tham số: "<đường dẫn>" hoặc "<đường dẫn>|<selector>".
 * Có selector thì cuộn tới phần tử đó rồi chụp ĐÚNG phần tử ấy thôi — form dự án
 * dài hơn màn hình rất nhiều, chụp cả trang thì khối cần xem bé tí.
 *
 * Không ghi tài khoản vào tệp này: tệp nằm trong kho mã.
 */

const { spawn } = require('child_process');
const fs = require('fs');
const os = require('os');
const path = require('path');

const CHROME = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const GOC = process.env.NOXH_URL || 'http://noxh.test';
const EMAIL = process.env.NOXH_ADMIN_EMAIL || '';
const MAT_KHAU = process.env.NOXH_ADMIN_PASS || '';
const PORT = 9437;
const UDD = path.join(os.tmpdir(), 'chup-qt-' + Date.now());
const sleep = (ms) => new Promise((r) => setTimeout(r, ms));

const DUONG_DAN = process.argv.slice(2).filter((a) => a.startsWith('/'));

if (!EMAIL || !MAT_KHAU) {
  console.error('Thiếu tài khoản quản trị: đặt NOXH_ADMIN_EMAIL và NOXH_ADMIN_PASS.');
  process.exit(2);
}

if (!DUONG_DAN.length) {
  console.error('Chưa cho đường dẫn nào. Ví dụ: /user/catalogue/index');
  process.exit(2);
}

/** "user/catalogue/index" -> "user-catalogue-index.png" */
function tenAnh(duongDan) {
  return duongDan.replace(/^\//, '').replace(/[^a-z0-9]+/gi, '-').replace(/-+$/, '') + '.png';
}

(async () => {
  const chrome = spawn(CHROME, [
    '--headless=new', '--disable-gpu', '--no-sandbox', '--no-first-run',
    '--no-default-browser-check', '--window-size=1440,1100',
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

    await gui('Page.navigate', { url: GOC + '/admin' }, a.sessionId);
    await sleep(2500);
    await doc(`(function () {
      var e = document.querySelector('input[name="email"]');
      var p = document.querySelector('input[name="password"]');
      if (!e || !p) return 'khong thay o dang nhap';
      e.value = ${JSON.stringify(EMAIL)};
      p.value = ${JSON.stringify(MAT_KHAU)};
      var f = e.closest('form');
      if (f) { f.submit(); return 'ok'; }
      return 'khong thay form';
    })()`);
    await sleep(4000);
    console.log('sau đăng nhập: ' + (await doc('location.href')));

    const thuMuc = path.join(__dirname, 'anh-chup');
    fs.mkdirSync(thuMuc, { recursive: true });

    for (const muc of DUONG_DAN) {
      const [duongDan, selector] = muc.split('|');

      await gui('Page.navigate', { url: GOC + duongDan }, a.sessionId);
      await sleep(3500);

      let clip = null;

      if (selector) {
        // Cuộn tới khối cần chụp, rồi đo lại. Phải đo SAU khi cuộn: toạ độ đọc
        // trước khi cuộn là toạ độ cũ.
        const co = await doc(`(function () {
          var e = document.querySelector(${JSON.stringify(selector)});
          if (!e) return false;
          e.scrollIntoView({ block: 'start' });
          return true;
        })()`);

        if (!co) {
          console.log('  ' + duongDan + '  ->  KHÔNG thấy ' + selector);
          continue;
        }

        await sleep(400);

        const o = JSON.parse(await doc(`(function () {
          var e = document.querySelector(${JSON.stringify(selector)});
          var r = e.getBoundingClientRect();
          return JSON.stringify({
            x: r.left, y: r.top + window.scrollY, width: r.width, height: r.height
          });
        })()`));

        // Clip của CDP dùng toạ độ TRANG (đã cộng scrollY), không phải toạ độ
        // khung nhìn.
        const le = 14;
        clip = {
          x: Math.max(0, o.x - le),
          y: Math.max(0, o.y - le),
          width: o.width + le * 2,
          height: o.height + le * 2,
          scale: 1,
        };
      }

      const duong = path.join(thuMuc, tenAnh(duongDan + (selector ? '-' + selector : '')));
      const r = await gui('Page.captureScreenshot', clip ? { format: 'png', clip } : { format: 'png' }, a.sessionId);
      fs.writeFileSync(duong, Buffer.from(r.data, 'base64'));
      console.log('  ' + muc + '  ->  ' + duong);
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
