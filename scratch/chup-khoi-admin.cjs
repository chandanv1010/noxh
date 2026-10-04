/**
 * Chup mot khoi (theo chu tieu de) tren trang quan tri.
 *
 * Chay: node scratch/chup-khoi-admin.cjs <cookiePhien> <duongDan> "<chu tieu de>" <tenAnh>
 * Vi du: node scratch/chup-khoi-admin.cjs abc123 /introduce/index "Khối 1: Banner trang chủ" admin-banner
 *
 * Script cung in ra danh sach <img> trong khoi do de biet dang dung anh nao.
 */

const { spawn } = require('child_process');
const fs = require('fs');
const os = require('os');
const path = require('path');

const COOKIE = process.argv[2];
const DUONG_DAN = process.argv[3] || '/introduce/index';
const TIEU_DE = process.argv[4] || 'Khối 1: Banner trang chủ';
const TEN_ANH = process.argv[5] || 'khoi-admin';
if (!COOKIE) { console.error('thieu cookie'); process.exit(1); }

const CHROME = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const PORT = 9341;
const UDD = path.join(os.tmpdir(), 'noxh-khoi-' + Date.now());
const HOST = 'noxh.test';
const RA = 'D:\\sandbox\\' + TEN_ANH + '.png';
const sleep = (ms) => new Promise((r) => setTimeout(r, ms));

(async () => {
  const chrome = spawn(CHROME, [
    '--headless=new', '--disable-gpu', '--no-sandbox', '--window-size=1500,1400',
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
    await gui('Emulation.setDeviceMetricsOverride',
      { width: 1440, height: 1200, deviceScaleFactor: 1, mobile: false }, sid);

    // Cookie phien dang nhap do scratch/lay-cookie-phien.php ghi ra.
    const phien = COOKIE.trim();
    await gui('Network.setCookie', { name: 'noxhvn_session', value: phien, domain: HOST, path: '/' }, sid);

    await gui('Page.navigate', { url: `http://${HOST}${DUONG_DAN}` }, sid);
    await sleep(3500);

    const chay = async (expr) => {
      const r = await gui('Runtime.evaluate', {
        expression: `(function(){ try { return ${expr}; } catch(e){ return 'LOI: ' + e.message; } })()`,
        returnByValue: true, awaitPromise: true,
      }, sid);
      if (r.exceptionDetails) return 'LOI CDP: ' + JSON.stringify(r.exceptionDetails).slice(0, 200);
      return r.result.value;
    };

    console.log('tieu de trang: ' + await chay('document.title'));
    console.log('duong dan   : ' + await chay('location.pathname'));

    const thongTin = await chay(`(function(){
      var tds = [].slice.call(document.querySelectorAll('.panel-title'));
      var td = tds.filter(function(e){ return e.textContent.trim().indexOf(${JSON.stringify(TIEU_DE)}) === 0; })[0];
      if (!td) return JSON.stringify({ loi: 'khong thay khoi: ' + ${JSON.stringify(TIEU_DE)},
                                       co: tds.map(function(e){ return e.textContent.trim(); }) });
      var khoi = td.closest('.row');
      var r = khoi.getBoundingClientRect();
      var imgs = [].slice.call(khoi.querySelectorAll('img')).map(function(i){ return i.getAttribute('src') || ''; });
      var nhan = [].slice.call(khoi.querySelectorAll('.form-row label span')).map(function(e){ return e.textContent.trim(); });
      return JSON.stringify({
        y: Math.round(r.top + window.scrollY), x: Math.round(r.left),
        w: Math.round(r.width), h: Math.round(r.height),
        anh: imgs, nhan: nhan,
      });
    })()`);

    const tt = JSON.parse(thongTin);
    if (tt.loi) { console.log(tt.loi); console.log('cac khoi co tren trang: ' + JSON.stringify(tt.co)); }
    else {
      console.log('khoi        : ' + tt.w + 'x' + tt.h + ' tai y=' + tt.y);
      console.log('nhan o      : ' + JSON.stringify(tt.nhan));
      console.log('anh dang dung: ' + JSON.stringify(tt.anh));

      // Thu xem anh xem truoc co cap nhat khi gia tri o nhap doi khong.
      const thuXemTruoc = await chay(`(function(){
        var o = document.querySelector('.o-anh');
        if (!o) return 'KHONG co .o-anh (chua them anh xem truoc)';
        var inp = o.querySelector('.upload-image');
        var khung = o.querySelector('.o-anh__xem');
        var anh = khung.querySelector('img');
        var truoc = { src: anh.getAttribute('src'), an: khung.classList.contains('trong') };

        inp.value = '/uploads/noxh/banner-pc.jpg';
        inp.dispatchEvent(new Event('input', { bubbles: true }));
        var sauGo = { src: anh.getAttribute('src'), an: khung.classList.contains('trong') };

        inp.value = '';
        inp.dispatchEvent(new Event('input', { bubbles: true }));
        var sauXoa = { src: anh.getAttribute('src'), an: khung.classList.contains('trong') };

        inp.value = truoc.src || '';
        inp.dispatchEvent(new Event('input', { bubbles: true }));
        return JSON.stringify({ truoc: truoc, sauGo: sauGo, sauXoa: sauXoa });
      })()`);
      console.log('thu anh xem truoc: ' + thuXemTruoc);

      const shot = await gui('Page.captureScreenshot', {
        format: 'png',
        clip: { x: 0, y: tt.y, width: 1440, height: Math.min(tt.h, 2000), scale: 1 },
        captureBeyondViewport: true,
      }, sid);
      fs.writeFileSync(RA, Buffer.from(shot.data, 'base64'));
      console.log('-> ' + RA + ' (' + Math.round(fs.statSync(RA).size / 1024) + ' KB)');
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
