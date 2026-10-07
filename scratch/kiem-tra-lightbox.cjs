/**
 * Kiểm tra thật lightbox album ảnh trên trang chi tiết dự án.
 *
 * Chạy: node scratch/kiem-tra-lightbox.cjs [url]
 *
 * Đo cái gì, và vì sao:
 *   - Bấm ảnh trong album -> lớp phủ HIỆN RA (không phải mở tab mới). Đây là
 *     yêu cầu chính, nên phải kiểm tra cả hai vế: lớp phủ có, và địa chỉ trang
 *     KHÔNG đổi, và không có tab nào mới sinh ra.
 *   - Bấm mũi tên / phím -> đổi ảnh, bộ đếm nhảy theo.
 *   - Escape -> đóng, và trang phải cuộn lại được (không bị khoá cửa sổ).
 *   - Nút phóng to trên ảnh lớn -> mở lớp phủ đúng ảnh đó.
 *   - Kiểm tra cả CSS: lớp phủ phải thật sự phủ kín màn hình, không phải
 *     display:none hay cao 0px. Thiếu bước này thì "có phần tử" vẫn có thể là
 *     "không nhìn thấy gì".
 */

const { spawn } = require('child_process');
const fs = require('fs');
const os = require('os');
const path = require('path');

const CHROME = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
// Tham so dau tien KHONG bat dau bang "--" moi la dia chi trang.
const THAM_SO_URL = process.argv.slice(2).find((a) => !a.startsWith('--'));
const URL = THAM_SO_URL || 'http://noxh.test/du-an/noxh-tuc-duyen';
const PORT = 9434;
const UDD = path.join(os.tmpdir(), 'kt-lightbox-' + Date.now());
const sleep = (ms) => new Promise((r) => setTimeout(r, ms));

const ketQua = [];
function ghi(ten, dat, ghiChu) {
  ketQua.push({ ten, dat: !!dat });
  console.log('  ' + (dat ? 'ĐẠT  ' : 'HỎNG ') + ten + (ghiChu ? '\n         ' + ghiChu : ''));
}

(async () => {
  const chrome = spawn(CHROME, [
    '--headless=new', '--disable-gpu', '--no-sandbox', '--no-first-run',
    '--no-default-browser-check', '--window-size=1440,900',
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
    await gui('Page.navigate', { url: URL }, a.sessionId);
    await sleep(3500);

    const doc = async (bieuThuc) => {
      const r = await gui('Runtime.evaluate', {
        expression: `JSON.stringify((function(){ ${bieuThuc} })())`,
        returnByValue: true, awaitPromise: true,
      }, a.sessionId);
      if (r.exceptionDetails) return { loi: r.exceptionDetails.text + ' ' + ((r.exceptionDetails.exception || {}).description || '') };
      return JSON.parse(r.result.value);
    };
    const bamPhim = async (key) => {
      await gui('Input.dispatchKeyEvent', { type: 'keyDown', key, windowsVirtualKeyCode: key === 'Escape' ? 27 : (key === 'ArrowRight' ? 39 : 37) }, a.sessionId);
      await gui('Input.dispatchKeyEvent', { type: 'keyUp', key, windowsVirtualKeyCode: key === 'Escape' ? 27 : (key === 'ArrowRight' ? 39 : 37) }, a.sessionId);
    };

    console.log('══════════════════════════════════════════════════════════');
    console.log(' Kiểm tra lightbox album — ' + URL);
    console.log('══════════════════════════════════════════════════════════');
    console.log('\n[1] Trang và album');
    const dau = await doc(`return {
      url: location.href,
      soAnhAlbum: document.querySelectorAll('.nx-pd-album a[data-nx-lb]').length,
      soNutPhongTo: document.querySelectorAll('.nx-pd-anh__to[data-nx-lb]').length,
      coCss: !!Array.from(document.styleSheets).find(function (s) {
        try { return Array.from(s.cssRules).some(function (r) { return (r.selectorText || '').indexOf('.nx-lb') === 0; }); }
        catch (e) { return false; }
      }),
      // Lightbox GOP TRUNG theo duong dan anh: du lieu mau dung lai cung mot tep
      // cho nhieu o, khong gop thi nguoi dung bam "toi" se gap lai dung tam anh
      // do may lan lien tiec. Con so dung de doi chieu la so anh KHAC NHAU
      // trong CA NHOM dang xem (anh lon + album) - dung bang so tren bo dem.
      soAnhKhacNhau: (function () {
        var goc = document.querySelector('.nx-pd-album a[data-nx-lb]');
        var nhom = goc ? goc.getAttribute('data-nx-nhom') : null;
        var thay = {}, dem = 0;
        document.querySelectorAll('[data-nx-lb]').forEach(function (e) {
          if (nhom && e.getAttribute('data-nx-nhom') !== nhom) return;
          var u = e.getAttribute('data-nx-lb');
          if (u && !thay[u]) { thay[u] = 1; dem++; }
        });
        return dem;
      })(),
    };`);
    console.log('         ' + JSON.stringify(dau));
    ghi('Album có ảnh gắn data-nx-lb', dau.soAnhAlbum > 0, 'số ảnh: ' + dau.soAnhAlbum);
    ghi('Có nút phóng to trên ảnh lớn', dau.soNutPhongTo === 1);
    ghi('CSS của lớp phủ đã được nạp', dau.coCss === true,
      'kiểm tra bằng cách duyệt cssRules tìm selector bắt đầu bằng .nx-lb');

    console.log('\n[2] Bấm một ảnh trong album');
    const bam = await doc(`
      var o = document.querySelectorAll('.nx-pd-album a[data-nx-lb]');
      o[2].click();
      return { daBam: o.length > 2, urlAnh: o[2].getAttribute('data-nx-lb') };
    `);
    await sleep(900);
    const sauBam = await doc(`return {
      url: location.href,
      coLop: !!document.querySelector('.nx-lb'),
      hien: document.querySelector('.nx-lb') ? !document.querySelector('.nx-lb').hidden : false,
      dem: document.querySelector('.nx-lb__dem') ? document.querySelector('.nx-lb__dem').textContent : null,
      src: document.querySelector('.nx-lb__anh') ? document.querySelector('.nx-lb__anh').getAttribute('src') : null,
      cao: document.querySelector('.nx-lb') ? Math.round(document.querySelector('.nx-lb').getBoundingClientRect().height) : 0,
      rong: document.querySelector('.nx-lb') ? Math.round(document.querySelector('.nx-lb').getBoundingClientRect().width) : 0,
      nen: document.querySelector('.nx-lb') ? getComputedStyle(document.querySelector('.nx-lb')).backgroundColor : null,
      cuonBiKhoa: document.documentElement.style.overflow === 'hidden',
      soNutHien: document.querySelectorAll('.nx-lb__nut:not([hidden])').length,
    };`);
    console.log('         ' + JSON.stringify(sauBam));
    ghi('Lớp phủ hiện ra', sauBam.coLop && sauBam.hien);
    ghi('Lớp phủ phủ kín màn hình', sauBam.cao >= 600 && sauBam.rong >= 1000,
      sauBam.rong + 'x' + sauBam.cao + '  nền: ' + sauBam.nen);
    ghi('Không rời khỏi trang (không mở tab mới)', sauBam.url === dau.url, 'url: ' + sauBam.url);
    ghi('Hiện đúng ảnh vừa bấm', sauBam.src === bam.urlAnh, 'ảnh: ' + sauBam.src);
    ghi('Bộ đếm đúng vị trí', sauBam.dem === '2 / ' + dau.soAnhKhacNhau, 'bộ đếm: ' + sauBam.dem + '  (số ảnh khác nhau: ' + dau.soAnhKhacNhau + ')');
    ghi('Khoá cuộn trang nền', sauBam.cuonBiKhoa === true);
    ghi('Hiện đủ 3 nút (đóng, lùi, tới)', sauBam.soNutHien === 3, 'số nút: ' + sauBam.soNutHien);

    if (process.argv.includes('--anh')) {
      const thuMuc = path.join(__dirname, 'anh-chup');
      fs.mkdirSync(thuMuc, { recursive: true });
      const r = await gui('Page.captureScreenshot', { format: 'png' }, a.sessionId);
      const duong = path.join(thuMuc, 'lightbox.png');
      fs.writeFileSync(duong, Buffer.from(r.data, 'base64'));
      console.log('         ảnh: ' + duong);
    }

    console.log('\n[3] Phím mũi tên và Escape');
    await bamPhim('ArrowRight');
    await sleep(500);
    const sauPhai = await doc(`return { dem: document.querySelector('.nx-lb__dem').textContent };`);
    ghi('Mũi tên phải sang ảnh kế', sauPhai.dem === '3 / ' + dau.soAnhKhacNhau, 'bộ đếm: ' + sauPhai.dem);

    await bamPhim('ArrowRight');
    await bamPhim('ArrowRight');
    await sleep(500);
    const sauVong = await doc(`return { dem: document.querySelector('.nx-lb__dem').textContent };`);
    ghi('Hết bộ thì quay vòng về đầu', sauVong.dem === '2 / ' + dau.soAnhKhacNhau, 'bộ đếm: ' + sauVong.dem + '  (bấm phải 3 lần từ vị trí 2, có 3 ảnh)');

    await bamPhim('Escape');
    await sleep(500);
    const sauDong = await doc(`return {
      hien: !document.querySelector('.nx-lb').hidden,
      cuon: document.documentElement.style.overflow,
    };`);
    ghi('Escape đóng lớp phủ', sauDong.hien === false);
    ghi('Mở lại được cuộn trang', sauDong.cuon === '', 'overflow: "' + sauDong.cuon + '"');

    console.log('\n[4] Nút phóng to trên ảnh lớn + bấm ra nền để đóng');
    const bamTo = await doc(`
      var n = document.querySelector('.nx-pd-anh__to');
      n.click();
      return { urlAnh: n.getAttribute('data-nx-lb') };
    `);
    await sleep(800);
    const sauTo = await doc(`return {
      hien: !document.querySelector('.nx-lb').hidden,
      src: document.querySelector('.nx-lb__anh').getAttribute('src'),
      dem: document.querySelector('.nx-lb__dem').textContent,
    };`);
    console.log('         ' + JSON.stringify(sauTo));
    ghi('Nút phóng to mở lớp phủ', sauTo.hien === true);
    ghi('Mở đúng ảnh lớn', sauTo.src === bamTo.urlAnh, 'ảnh: ' + sauTo.src);

    // Bấm ra nền (góc trên trái, chắc chắn không trúng ảnh).
    await gui('Input.dispatchMouseEvent', { type: 'mousePressed', x: 8, y: 8, button: 'left', clickCount: 1 }, a.sessionId);
    await gui('Input.dispatchMouseEvent', { type: 'mouseReleased', x: 8, y: 8, button: 'left', clickCount: 1 }, a.sessionId);
    await sleep(500);
    const sauNen = await doc(`return { hien: !document.querySelector('.nx-lb').hidden };`);
    ghi('Bấm ra nền thì đóng', sauNen.hien === false);

    console.log('\n[5] Không còn thẻ mở tab mới trong album');
    const conTab = await doc(`return {
      conTargetBlank: document.querySelectorAll('.nx-pd-album a[target="_blank"]').length,
      soAnh: document.querySelectorAll('.nx-pd-album a').length,
    };`);
    ghi('Album không còn target="_blank"', conTab.conTargetBlank === 0,
      'số ảnh: ' + conTab.soAnh + '  còn target trống: ' + conTab.conTargetBlank);

    // ── Dải ảnh nhỏ ở đầu trang phải giữ nguyên hành vi CŨ ──────────────────
    // Hai bộ xử lý click nằm sát nhau (đổi ảnh lớn và mở lightbox), nên rất dễ
    // gắn nhầm thuộc tính khiến bấm ảnh nhỏ lại mở lightbox. Kiểm tra để chốt.
    console.log('\n[6] Dải ảnh nhỏ vẫn đổi ảnh lớn (không mở lightbox)');
    const doiAnh = await doc(`
      var truoc = document.getElementById('nx-pd-anh-chinh').getAttribute('src');
      var o = document.querySelectorAll('.nx-pd-anh__nho [data-nx-anh]');
      var o2 = o[o.length - 1];
      o2.click();
      return { truoc: truoc, urlAnh: o2.getAttribute('data-nx-anh'), soODai: o.length };
    `);
    await sleep(700);
    const sauDoi = await doc(`return {
      src: document.getElementById('nx-pd-anh-chinh').getAttribute('src'),
      lbHien: document.querySelector('.nx-lb') ? !document.querySelector('.nx-lb').hidden : false,
    };`);
    ghi('Bấm ảnh nhỏ thì đổi ảnh lớn', sauDoi.src === doiAnh.urlAnh,
      'ảnh lớn: ' + sauDoi.src);
    ghi('Bấm ảnh nhỏ KHÔNG mở lightbox', sauDoi.lbHien === false);

    // Không có tab nào mới ngoài about:blank ban đầu và trang này.
    const ds = await gui('Target.getTargets');
    const trang = ds.targetInfos.filter((x) => x.type === 'page');
    ghi('Không sinh tab mới', trang.length === 2,
      'các tab: ' + trang.map((x) => String(x.url).slice(0, 60)).join(' | '));

  } catch (e) {
    ghi('Chạy trọn bộ kiểm tra', false, e.message);
  } finally {
    try { if (ws) ws.close(); } catch (e) {}
    chrome.kill();
    await sleep(400);
    try { fs.rmSync(UDD, { recursive: true, force: true }); } catch (e) {}
  }

  const dat = ketQua.filter((k) => k.dat).length;
  console.log('\n══════════════════════════════════════════════════════════');
  console.log(' KẾT QUẢ: ' + dat + '/' + ketQua.length + ' đạt');
  ketQua.filter((k) => !k.dat).forEach((k) => console.log('   HỎNG: ' + k.ten));
  console.log('══════════════════════════════════════════════════════════');
  process.exitCode = dat === ketQua.length ? 0 : 1;
})();
