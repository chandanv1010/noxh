/**
 * Kiem tra o Phuong/Xa (Select2) tren form du an THAT bang Chrome.
 * Chay: node scratch/kiem-tra-select2-form.cjs <cookiePhien> [duongDan]
 */

const { spawn } = require('child_process');
const fs = require('fs');
const os = require('os');
const path = require('path');

const COOKIE = process.argv[2];
const DUONG_DAN = process.argv[3] || '/product/76/edit';
if (!COOKIE) { console.error('thieu cookie'); process.exit(1); }

const CHROME = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const PORT = 9338;
const UDD = path.join(os.tmpdir(), 'noxh-sel2form-' + Date.now());
const HOST = 'noxh.test';
const sleep = (ms) => new Promise((r) => setTimeout(r, ms));

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
    let id = 0; const cho = new Map(); const log = [];
    ws.addEventListener('message', (ev) => {
      const m = JSON.parse(ev.data);
      if (m.method === 'Runtime.consoleAPICalled') {
        log.push('CONSOLE: ' + m.params.args.map((a) => a.value ?? a.description).join(' '));
      }
      if (m.method === 'Runtime.exceptionThrown') {
        log.push('EXCEPTION: ' + (m.params.exceptionDetails.exception?.description || m.params.exceptionDetails.text));
      }
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

    await gui('Page.navigate', { url: `http://${HOST}${DUONG_DAN}` }, sid);
    await sleep(5000);

    const chay = async (expr) => {
      const r = await gui('Runtime.evaluate', { expression: expr, returnByValue: true, awaitPromise: true }, sid);
      if (r.exceptionDetails) return 'LOI: ' + (r.exceptionDetails.exception?.description || r.exceptionDetails.text);
      return r.result.value;
    };

    const KQ = (nhan, gt) => console.log('  ' + nhan.padEnd(34) + ': ' + gt);

    console.log('=== ' + DUONG_DAN + ' ===');
    KQ('tieu de trang', await chay('document.title'));
    KQ('Select2 da boc o Phuong/Xa?', await chay(`(function(){
      var x = document.getElementById('nx-xa');
      return x && x.nextElementSibling && x.nextElementSibling.classList.contains('select2-container') ? 'CO' : 'khong';
    })()`));
    KQ('o Tinh/Thanh dang chon', await chay(`document.getElementById('nx-tinh').value`));
    KQ('Select2 Phuong/Xa hien', JSON.stringify(await chay(`(function(){
      var x = document.getElementById('nx-xa');
      var khung = x.nextElementSibling;
      var r = khung && khung.querySelector('.select2-selection__rendered');
      return r ? r.textContent : '(khong co khung)';
    })()`)));
    KQ('so option that trong the goc', await chay(`document.getElementById('nx-xa').options.length`));
    KQ('gia tri o Phuong/Xa', JSON.stringify(await chay(`document.getElementById('nx-xa').value`)));
    KQ('chieu cao khung Select2', await chay(`(function(){
      var x = document.getElementById('nx-xa');
      var k = x.nextElementSibling;
      return k ? Math.round(k.getBoundingClientRect().height) + 'px' : '?';
    })()`));

    console.log('\n=== MO O TIM KIEM VA CHON PHUONG KHAC (nhu nguoi dung) ===');
    console.log('  ' + (await chay(`(function(){
      jQuery('#nx-xa').select2('open');
      var o = document.querySelector('.select2-search__field');
      if (!o) return 'khong thay o tim kiem';
      o.value = 'da mai';
      o.dispatchEvent(new Event('input', { bubbles: true }));
      return 'da go "da mai" (khong dau)';
    })()`)));
    await sleep(900);
    KQ('ket qua loc duoc', JSON.stringify(await chay(`
      Array.from(document.querySelectorAll('.select2-results__option')).map(function(e){return e.textContent;}).join(' | ')
    `)));
    console.log('  ' + (await chay(`(function(){
      var ds = document.querySelectorAll('.select2-results__option');
      if (!ds.length) return 'khong co ket qua';
      ds[0].dispatchEvent(new MouseEvent('mouseup', { bubbles: true }));
      return 'da bam "' + ds[0].textContent + '"';
    })()`)));
    await sleep(900);
    KQ('gia tri sau khi bam', JSON.stringify(await chay(`document.getElementById('nx-xa').value`)));
    KQ('khung hien', JSON.stringify(await chay(`(function(){
      var k = document.getElementById('nx-xa').nextElementSibling;
      var r = k && k.querySelector('.select2-selection__rendered');
      return r ? r.textContent : '?';
    })()`)));

    console.log('\n=== DOI TINH SANG HA NOI ===');
    console.log('  ' + (await chay(`jQuery('#nx-tinh').val('01').trigger('change'); 'da chon Ha Noi'`)));
    await sleep(2500);
    KQ('so option moi', await chay(`document.getElementById('nx-xa').options.length`));
    KQ('gia tri (phai rong)', JSON.stringify(await chay(`document.getElementById('nx-xa').value`)));
    KQ('khung hien', JSON.stringify(await chay(`(function(){
      var k = document.getElementById('nx-xa').nextElementSibling;
      var r = k && k.querySelector('.select2-selection__rendered');
      return r ? r.textContent : '?';
    })()`)));
    KQ('chieu cao khung', await chay(`(function(){
      var k = document.getElementById('nx-xa').nextElementSibling;
      return k ? Math.round(k.getBoundingClientRect().height) + 'px' : '?';
    })()`));

    console.log('\n=== console/exception ===');
    for (const l of log.slice(0, 10)) { console.log('  ' + l); }
    if (!log.length) { console.log('  (khong co)'); }

  } catch (e) {
    console.error('LOI: ' + e.message);
  } finally {
    try { if (ws) ws.close(); } catch (e) {}
    chrome.kill();
    await sleep(300);
    try { fs.rmSync(UDD, { recursive: true, force: true }); } catch (e) {}
  }
})();
