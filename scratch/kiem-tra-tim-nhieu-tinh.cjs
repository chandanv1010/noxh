/**
 * Kiem tra tim kiem khong dau tren FORM THAT voi nhieu tinh khac nhau.
 * Chay: node scratch/kiem-tra-tim-nhieu-tinh.cjs <cookiePhien>
 */

const { spawn } = require('child_process');
const fs = require('fs');
const os = require('os');
const path = require('path');

const COOKIE = process.argv[2];
const CHROME = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const PORT = 9346;
const UDD = path.join(os.tmpdir(), 'noxh-nhieu-' + Date.now());
const HOST = 'noxh.test';
const sleep = (ms) => new Promise((r) => setTimeout(r, ms));

// [ma tinh, ten tinh, tu khoa go (khong dau), ten phuong/xa mong doi]
const CA = [
  ['24', 'Bắc Ninh',   'da mai',    'Phường Đa Mai'],
  ['19', 'Thái Nguyên','duc xuan',  'Phường Đức Xuân'],
  ['01', 'Hà Nội',     'ba dinh',   'Phường Ba Đình'],
  ['79', 'TP HCM',     'ben thanh', null],
  ['48', 'Đà Nẵng',    'hai chau',  null],
];

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
    await gui('Network.enable', {}, sid);
    await gui('Runtime.enable', {}, sid);
    await gui('Network.setCookie', { name: 'noxhvn_session', value: COOKIE, domain: HOST, path: '/' }, sid);

    await gui('Page.navigate', { url: `http://${HOST}/product/76/edit` }, sid);
    await sleep(5000);

    const chay = async (expr) => {
      const r = await gui('Runtime.evaluate', {
        expression: `(function(){ try { return String(${expr}); } catch (e) { return 'LOI TRANG: ' + e.message; } })()`,
        returnByValue: true, awaitPromise: true,
      }, sid);
      if (r.exceptionDetails) return 'LOI CDP';
      return r.result.value;
    };

    for (const [ma, tenTinh, tuKhoa, mongDoi] of CA) {
      await chay(`jQuery('#nx-tinh').val(${JSON.stringify(ma)}).trigger('change')`);
      await sleep(2500);
      const soXa = await chay(`document.getElementById('nx-xa').options.length`);

      const kq = await chay(`(function(){
        jQuery('#nx-xa').select2('open');
        var d = jQuery('#nx-xa').data('select2');
        var o = d && d.dropdown && d.dropdown.$search && d.dropdown.$search[0];
        if (!o) return 'khong co o tim kiem';
        window.__o = o;
        o.value = ${JSON.stringify(tuKhoa)};
        o.dispatchEvent(new Event('input', { bubbles: true }));
        return 'go xong';
      })()`);
      await sleep(900);
      const hien = await chay(`(function(){
        var khoi = window.__o.closest('.select2-dropdown');
        var ds = Array.from(khoi.querySelectorAll('.select2-results__option')).map(function(e){ return e.textContent; });
        return ds.slice(0, 3).join(' | ') + (ds.length > 3 ? ' …(' + ds.length + ')' : '');
      })()`);

      const dat = mongDoi ? (String(hien).indexOf(mongDoi) !== -1 ? 'DUNG' : 'SAI (mong doi ' + mongDoi + ')') : '(khong kiem tra)';
      console.log(`  ${tenTinh.padEnd(13)} (${ma}, ${soXa} xa)  go "${tuKhoa}"  ->  ${hien}`);
      console.log(`  ${''.padEnd(13)} ${dat}`);
      await chay(`jQuery('#nx-xa').select2('close')`);
      await sleep(200);
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
