/**
 * Kiểm tra bộ lọc tư vấn viên theo khu vực ở trang chủ.
 *
 * Chạy: node scratch/kiem-tra-loc-khu-vuc.cjs
 *
 * Câu hỏi: chọn một khu vực trong dropdown thì các thẻ tư vấn viên có bị ẩn đi
 * không, và nếu không thì do đâu - logic JS sai, hay JS đúng mà CSS đè?
 *
 * Đo cả HAI đường:
 *   A. bấm thật vào dropdown tự vẽ (.nx-chon)  - đường người dùng thật đi
 *   B. đặt select.value rồi tự phát 'change'   - đường logic thuần
 * Nếu B lọc được mà A không, lỗi ở dropdown. Nếu cả hai đều "không ẩn được thẻ"
 * thì lỗi ở CSS.
 */

const { spawn } = require('child_process');
const fs = require('fs');
const os = require('os');
const path = require('path');

const CHROME = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const PORT = 9411;
const UDD = path.join(os.tmpdir(), 'loc-kv-' + Date.now());
const TRANG = 'http://noxh.test/';
const KHU = process.argv[2] || 'Thái Nguyên';
// Bề rộng màn hình giả lập; có thể truyền qua --rong 390. Mặc định để rộng.
const iRong = process.argv.indexOf('--rong');
const RONG_MH = iRong >= 0 ? Number(process.argv[iRong + 1]) : 0;
const sleep = (ms) => new Promise((r) => setTimeout(r, ms));

/** "Thái Nguyên" -> "thai-nguyen", để đặt tên tệp ảnh. */
const boDauTen = (s) => s.normalize('NFD')
  .replace(/[\u0300-\u036f]/g, '')
  .replace(/\u0111/g, 'd').replace(/\u0110/g, 'D')
  .replace(/[^a-zA-Z0-9]+/g, '-').replace(/^-|-$/g, '').toLowerCase();

(async () => {
  const chrome = spawn(CHROME, [
    '--headless=new', '--disable-gpu', '--disable-extensions', '--no-sandbox',
    '--window-size=1500,1200', '--remote-debugging-port=' + PORT, '--user-data-dir=' + UDD,
    'about:blank',
  ], { stdio: 'ignore' });

  let ws = null;
  try {
    let version = null;
    for (let i = 0; i < 60; i++) {
      try { version = await (await fetch(`http://127.0.0.1:${PORT}/json/version`)).json(); break; }
      catch (e) { await sleep(250); }
    }
    if (!version) throw new Error('không mở được cổng debug');

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
    const sid = a.sessionId;
    await gui('Page.enable', {}, sid);
    await gui('Runtime.enable', {}, sid);
    if (RONG_MH) {
      await gui('Emulation.setDeviceMetricsOverride',
        { width: RONG_MH, height: 1000, deviceScaleFactor: 1, mobile: RONG_MH <= 768 }, sid);
    }
    await gui('Page.navigate', { url: TRANG }, sid);
    await sleep(4500);

    if (RONG_MH) console.log('màn hình giả lập: ' + RONG_MH + 'px');

    const chay = async (expr) => {
      const r = await gui('Runtime.evaluate', {
        expression: `(function(){ try { return JSON.stringify(${expr}); } catch(e){ return JSON.stringify({loi:e.message}); } })()`,
        returnByValue: true, awaitPromise: true,
      }, sid);
      if (r.exceptionDetails) return { loi: 'CDP' };
      return JSON.parse(r.result.value);
    };

    // Trạng thái: mỗi thẻ có bị ẩn thật không (theo cả thuộc tính hidden LẪN
    // kiểu display đang áp dụng LẪN chiều cao thực tế).
    const DO = `(function(){
      var ds = document.querySelector('[data-nx-danh-sach-tu-van] .nx-advisors');
      var sel = document.querySelector('[data-nx-loc-khu-vuc]');
      var the = [].slice.call(ds.children);
      return {
        coSelect: !!sel,
        giaTriSelect: sel ? sel.value : null,
        coDropdownTuVe: !!(sel && sel.closest('.nx-chon')),
        // Khi chi con MOT the thi JS them class nay de the chiem ca be ngang.
        coMot: ds.classList.contains('nx-advisors--mot'),
        soThe: the.length,
        the: the.map(function (x) {
          var k = x.querySelector('.nx-advisor__khu');
          var r = x.getBoundingClientRect();
          return {
            khu: k ? k.textContent.trim() : '(không có ô khu vực)',
            thuocTinhHidden: x.hasAttribute('hidden'),
            display: getComputedStyle(x).display,
            cao: Math.round(r.height),
            // Bề ngang: thẻ lọc còn một người phải RỘNG BẰNG một cột bình
            // thường, không được kéo dài hết hàng.
            rong: Math.round(r.width),
          };
        }),
      };
    })()`;

    const truoc = await chay(DO);
    console.log('=== TRƯỚC KHI LỌC ===');
    console.log('có ô chọn khu vực   : ' + truoc.coSelect);
    console.log('dropdown tự vẽ      : ' + truoc.coDropdownTuVe);
    console.log('số thẻ tư vấn       : ' + truoc.soThe);
    truoc.the.forEach((x, i) => console.log('   [' + i + '] ' + x.khu.padEnd(22) +
      ' hidden=' + x.thuocTinhHidden + '  display=' + x.display + '  cao=' + x.cao + '  rong=' + x.rong));

    const demHien = (tt) => tt.the.filter((x) => x.cao > 0).length;

    // ---- Cách A: bấm thật vào dropdown tự vẽ ----
    console.log('');
    console.log('=== A. BẤM THẬT VÀO DROPDOWN: "' + KHU + '" ===');
    const toaDoNut = await chay(`(function(){
      var nut = document.querySelector('.nx-chon-khu-vuc .nx-chon__nut');
      if (!nut) return { loi: 'không thấy nút dropdown' };
      nut.scrollIntoView({block:'center'});
      var r = nut.getBoundingClientRect();
      return { x: Math.round(r.left + r.width/2), y: Math.round(r.top + r.height/2) };
    })()`);

    if (toaDoNut.loi) { console.log('  ' + toaDoNut.loi); }
    else {
      for (const kieu of ['mousePressed', 'mouseReleased']) {
        await gui('Input.dispatchMouseEvent', { type: kieu, x: toaDoNut.x, y: toaDoNut.y, button: 'left', clickCount: 1 }, sid);
        await sleep(120);
      }
      await sleep(700);

      const soMuc = await chay(`(function(){
        var ds = document.querySelector('.nx-chon-khu-vuc .nx-chon__ds');
        var muc = ds ? [].slice.call(ds.querySelectorAll('.nx-chon__muc')) : [];
        return { so: muc.length, ten: muc.map(function (li) { return li.textContent.trim(); }) };
      })()`);
      console.log('  mục trong dropdown: ' + soMuc.so + ' -> ' + JSON.stringify(soMuc.ten));

      const oMuc = await chay(`(function(){
        var ds = document.querySelector('.nx-chon-khu-vuc .nx-chon__ds');
        if (!ds) return { loi: 'không thấy bảng' };
        var muc = [].slice.call(ds.querySelectorAll('.nx-chon__muc'));
        var can = muc.filter(function (li) { return li.textContent.trim() === ${JSON.stringify(KHU)}; })[0];
        if (!can) return { loi: 'không có mục "' + ${JSON.stringify(KHU)} + '"' };
        can.scrollIntoView({block:'center'});
        var r = can.getBoundingClientRect();
        return { x: Math.round(r.left + r.width/2), y: Math.round(r.top + r.height/2) };
      })()`);

      if (oMuc.loi) { console.log('  ' + oMuc.loi); }
      else {
        for (const kieu of ['mousePressed', 'mouseReleased']) {
          await gui('Input.dispatchMouseEvent', { type: kieu, x: oMuc.x, y: oMuc.y, button: 'left', clickCount: 1 }, sid);
          await sleep(120);
        }
        await sleep(800);

        const sauA = await chay(DO);
        console.log('  giá trị select sau khi bấm: ' + JSON.stringify(sauA.giaTriSelect));
        sauA.the.forEach((x, i) => console.log('   [' + i + '] ' + x.khu.padEnd(22) +
          ' hidden=' + x.thuocTinhHidden + '  display=' + x.display + '  cao=' + x.cao + '  rong=' + x.rong));
        console.log('  => số thẻ CÒN HIỆN: ' + demHien(sauA) + ' / ' + sauA.soThe +
                    '  (mong đợi: đúng số thẻ thuộc "' + KHU + '")');
        console.log('  class nx-advisors--mot (1 thẻ chiếm cả hàng): ' + sauA.coMot);

        // Ảnh chụp để đối chiếu bằng mắt.
        const vung = await chay(`(function(){
          var khoi = document.querySelector('.nx-doi-tu-van');
          if (!khoi) return { loi: 'không thấy khối tư vấn' };
          var r = khoi.getBoundingClientRect();
          return { x: 0, y: Math.round(r.top + window.scrollY),
                   width: Math.round(r.width), height: Math.round(r.height) };
        })()`);
        if (!vung.loi) {
          const shot = await gui('Page.captureScreenshot', {
            format: 'png',
            clip: { x: vung.x, y: vung.y, width: vung.width, height: Math.min(vung.height, 1400), scale: 1 },
            captureBeyondViewport: true,
          }, sid);
          const ra = path.join(__dirname, 'anh-chup', 'loc-khu-vuc-' +
            boDauTen(KHU) + '.png');
          fs.mkdirSync(path.dirname(ra), { recursive: true });
          fs.writeFileSync(ra, Buffer.from(shot.data, 'base64'));
          console.log('  -> ảnh: ' + ra);
        }
      }
    }

    // ---- Cách B: đặt giá trị rồi tự phát change ----
    console.log('');
    console.log('=== B. ĐẶT select.value RỒI PHÁT change (đường logic thuần) ===');
    await chay(`(function(){
      var sel = document.querySelector('[data-nx-loc-khu-vuc]');
      sel.value = ${JSON.stringify(KHU)};
      sel.dispatchEvent(new Event('change', { bubbles: true }));
      return 'ok';
    })()`);
    await sleep(600);
    const sauB = await chay(DO);
    console.log('  giá trị select: ' + JSON.stringify(sauB.giaTriSelect));
    sauB.the.forEach((x, i) => console.log('   [' + i + '] ' + x.khu.padEnd(22) +
      ' hidden=' + x.thuocTinhHidden + '  display=' + x.display + '  cao=' + x.cao + '  rong=' + x.rong));
    console.log('  => số thẻ CÒN HIỆN: ' + demHien(sauB) + ' / ' + sauB.soThe);

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
