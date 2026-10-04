/**
 * Do chieu cao that cua tung o trong khoi "Thong tin du an" bang Chrome that.
 * Chay: node scratch/do-chieu-cao-o.cjs <cookiePhien>
 */

const { spawn } = require('child_process');
const fs = require('fs');
const os = require('os');
const path = require('path');

const COOKIE = process.argv[2];
const DUONG_DAN = process.argv[3] || '/product/76/edit';
if (!COOKIE) { console.error('thieu cookie'); process.exit(1); }

const CHROME = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const PORT = 9334;
const UDD = path.join(os.tmpdir(), 'noxh-do-' + Date.now());
const HOST = 'noxh.test';
const sleep = (ms) => new Promise((r) => setTimeout(r, ms));

// Cac o can do: nhan -> selector
const O = [
  ['Chu dau tu   ', '#nx-do-investor'],
  ['Trang thai   ', '#nx-do-status'],
  ['Hinh thuc SH ', '#nx-do-owner'],
  ['Tinh/Thanh   ', '#nx-do-tinh'],
  ['Phuong/Xa    ', '#nx-do-xa'],
  ['Dia chi      ', '#nx-do-address'],
];

(async () => {
  const chrome = spawn(CHROME, [
    '--headless=new', '--disable-gpu', '--no-sandbox', '--window-size=1400,3000',
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

    // Cookie phien (giong script kia)
    const [phien, xsrf] = fs.readFileSync(path.join(os.tmpdir(), 'noxh-cookie.txt'), 'utf8').trim().split('\n');
    await gui('Network.setCookie', { name: 'noxhvn_session', value: phien, domain: HOST, path: '/' }, sid);
    if (xsrf) await gui('Network.setCookie', { name: 'XSRF-TOKEN', value: xsrf, domain: HOST, path: '/' }, sid);

    await gui('Page.navigate', { url: `http://${HOST}${DUONG_DAN}` }, sid);
    await sleep(4000);

    const chay = async (expr) => {
      const r = await gui('Runtime.evaluate', { expression: expr, returnByValue: true, awaitPromise: true }, sid);
      if (r.exceptionDetails) return 'LOI: ' + (r.exceptionDetails.exception?.description || r.exceptionDetails.text);
      return r.result.value;
    };

    // Gan id tam cho cac o can do (theo thu tu xuat hien trong khoi du an)
    const gan = await chay(`(function(){
      var khoi = document.querySelector('.ibox-content');
      function oTheo(sel, id){ var e = document.querySelector(sel); if(e) e.id = id; return !!e; }
      return [
        oTheo('select[name="investor_id"]', 'nx-do-investor'),
        oTheo('select[name="status"]', 'nx-do-status'),
        oTheo('input[name="ownership_type"]', 'nx-do-owner'),
        oTheo('select[name="province_code"]', 'nx-do-tinh'),
        oTheo('select[name="ward_code"]', 'nx-do-xa'),
        oTheo('input[name="address"]', 'nx-do-address')
      ].join(',');
    })()`);
    console.log('gan id: ' + gan);

    const kq = await chay(`(function(){
      var ra = [];
      var ds = ${JSON.stringify(O.map((o) => o[1]))};
      ds.forEach(function(sel){
        var e = document.querySelector(sel);
        if (!e) { ra.push([sel, 'khong thay', '', '', '']); return; }
        var r = e.getBoundingClientRect();
        // Voi Select2, do ca khung bao ngoai
        var bao = e.nextElementSibling && e.nextElementSibling.classList.contains('select2-container')
          ? e.nextElementSibling : e;
        var rb = bao.getBoundingClientRect();
        var cs = getComputedStyle(e);
        ra.push([sel, Math.round(r.height*10)/10, Math.round(rb.height*10)/10,
                 Math.round(rb.top*10)/10, cs.paddingTop + '/' + cs.paddingBottom + ' fs=' + cs.fontSize + ' lh=' + cs.lineHeight]);
      });
      return JSON.stringify(ra);
    })()`);

    console.log('\n  o            | cao the goc | cao khung |  top  | padding / font / line-height');
    console.log('  -------------|-------------|-----------|-------|------------------------------');
    for (const [nhan, sel] of O) {
      const row = JSON.parse(kq).find((r) => r[0] === sel);
      if (!row) { console.log('  ' + nhan + ' | (khong thay)'); continue; }
      console.log('  ' + nhan + ' |   ' + String(row[1]).padStart(6) + 'px   |  ' + String(row[2]).padStart(5) + 'px  | ' + String(row[3]).padStart(5) + ' | ' + row[4]);
    }

    // Do rieng nhan label
    const nhan = await chay(`(function(){
      var ra = [];
      document.querySelectorAll('.ibox-content .form-row').forEach(function(fr){
        var l = fr.querySelector('label');
        if (!l) return;
        var r = l.getBoundingClientRect();
        ra.push(l.textContent.trim() + ' top=' + Math.round(r.top) + ' h=' + Math.round(r.height));
      });
      return ra.slice(0, 8).join('\\n');
    })()`);
    console.log('\n  vi tri nhan label:\n' + nhan.split('\n').map((s) => '    ' + s).join('\n'));

  } catch (e) {
    console.error('LOI: ' + e.message);
  } finally {
    try { if (ws) ws.close(); } catch (e) {}
    chrome.kill();
    await sleep(300);
    try { fs.rmSync(UDD, { recursive: true, force: true }); } catch (e) {}
  }
})();
