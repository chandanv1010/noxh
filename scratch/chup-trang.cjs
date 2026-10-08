/**
 * Chụp ảnh trang web (công khai hoặc trong khu quản trị).
 *
 * Chạy:
 *   node scratch/chup-trang.cjs /ho-so
 *   node scratch/chup-trang.cjs "/ho-so|#nx-nhan-ho-so"          <- chỉ chụp một khối
 *   node scratch/chup-trang.cjs /ho-so --rong=390 --ca-trang     <- khổ điện thoại, cả trang
 *
 * Đăng nhập chỉ chạy khi CÓ đặt NOXH_ADMIN_EMAIL và NOXH_ADMIN_PASS. Trang công
 * khai thì không cần, và cũng không nên đăng nhập làm gì cho thừa.
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
const PORT = 9438;
const UDD = path.join(os.tmpdir(), 'chup-trang-' + Date.now());
const sleep = (ms) => new Promise((r) => setTimeout(r, ms));

const THAM_SO = process.argv.slice(2);
const RONG = Number((THAM_SO.find((a) => a.startsWith('--rong=')) || '').slice(7)) || 1440;
const CA_TRANG = THAM_SO.includes('--ca-trang');
const MUC = THAM_SO.filter((a) => !a.startsWith('--'));

if (!MUC.length) {
  console.error('Chưa cho đường dẫn nào. Ví dụ: node scratch/chup-trang.cjs /ho-so');
  process.exit(2);
}

/** "ho-so" hoặc "ho-so#nx-nhan-ho-so" -> tên tệp png an toàn. */
function tenAnh(chuoi) {
  return chuoi.replace(/^\//, '').replace(/[^a-z0-9]+/gi, '-').replace(/^-+|-+$/g, '') + '.png';
}

(async () => {
  const chrome = spawn(CHROME, [
    '--headless=new', '--disable-gpu', '--no-sandbox', '--no-first-run',
    '--no-default-browser-check', `--window-size=${RONG},1000`,
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

    // Khổ điện thoại: Chrome headless mặc định coi bề rộng cửa sổ là bề rộng
    // trang, nhưng vẫn cần khai device metrics để ảnh chụp đúng tỉ lệ và để
    // trang áp đúng breakpoint di động.
    await gui('Emulation.setDeviceMetricsOverride', {
      width: RONG, height: 1000, deviceScaleFactor: 1, mobile: RONG < 700,
    }, a.sessionId);

    if (EMAIL && MAT_KHAU) {
      await gui('Page.navigate', { url: GOC + '/admin' }, a.sessionId);
      await sleep(2500);
      const dangNhap = await doc(`(function () {
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
      console.log('đăng nhập: ' + dangNhap + '  ->  ' + (await doc('location.href')));
    }

    const thuMuc = path.join(__dirname, 'anh-chup');
    fs.mkdirSync(thuMuc, { recursive: true });

    for (const muc of MUC) {
      const [duongDan, selector] = muc.split('|');

      await gui('Page.navigate', { url: GOC + duongDan }, a.sessionId);
      await sleep(3000);

      let clip = null;

      if (selector) {
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
          return JSON.stringify({ x: r.left, y: r.top + window.scrollY, width: r.width, height: r.height });
        })()`));

        // Clip của CDP dùng toạ độ TRANG (đã cộng scrollY).
        const le = 14;
        clip = {
          x: Math.max(0, o.x - le),
          y: Math.max(0, o.y - le),
          width: o.width + le * 2,
          height: o.height + le * 2,
          scale: 1,
        };
      } else if (CA_TRANG) {
        const cao = await doc('Math.ceil(document.documentElement.scrollHeight)');
        await gui('Emulation.setDeviceMetricsOverride', {
          width: RONG, height: Math.min(cao, 12000), deviceScaleFactor: 1, mobile: RONG < 700,
        }, a.sessionId);
        await sleep(400);
      }

      const duong = path.join(thuMuc, tenAnh(duongDan + (selector ? '-' + selector : '') + (RONG !== 1440 ? '-' + RONG : '')));
      const r = await gui('Page.captureScreenshot', clip ? { format: 'png', clip } : { format: 'png' }, a.sessionId);
      fs.writeFileSync(duong, Buffer.from(r.data, 'base64'));
      console.log('  ' + muc + (RONG !== 1440 ? ' (' + RONG + 'px)' : '') + '  ->  ' + duong);
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
