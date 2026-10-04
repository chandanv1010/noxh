/**
 * Kiem tra o Phuong/Xa (Select2) tren FORM THAT: tim khong dau / co dau.
 * Chay: node scratch/kiem-tra-tim-khong-dau.cjs <cookiePhien>
 */

const { spawn } = require('child_process');
const fs = require('fs');
const os = require('os');
const path = require('path');

const COOKIE = process.argv[2];
if (!COOKIE) { console.error('thieu cookie'); process.exit(1); }

const CHROME = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const PORT = 9341;
const UDD = path.join(os.tmpdir(), 'noxh-tim-' + Date.now());
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

    // Mo form sua du an 76 (Bac Ninh - co Phuong Da Mai)
    await gui('Page.navigate', { url: `http://${HOST}/product/76/edit` }, sid);
    await sleep(5000);

    const chay = async (expr) => {
      const r = await gui('Runtime.evaluate', { expression: expr, returnByValue: true, awaitPromise: true }, sid);
      if (r.exceptionDetails) return 'LOI: ' + (r.exceptionDetails.exception?.description || r.exceptionDetails.text);
      return r.result.value;
    };

    console.log('=== FORM THAT /product/76/edit ===');
    console.log('  tinh dang chon      : ' + await chay(`document.getElementById('nx-tinh').value`));
    console.log('  so option Phuong/Xa : ' + await chay(`document.getElementById('nx-xa').options.length`));

    const thu = async (tuKhoa) => {
      // dong roi mo lai de o tim kiem sach
      await chay(`(function(){
        var $x = jQuery('#nx-xa');
        if ($x.data('select2')) { $x.select2('close'); }
      })()`);
      await sleep(200);
      await chay(`(function(){
        jQuery('#nx-xa').select2('open');
        var o = document.querySelector('.select2-search__field');
        o.value = '';
        o.dispatchEvent(new Event('input', { bubbles: true }));
        o.value = ${JSON.stringify(tuKhoa)};
        o.dispatchEvent(new Event('input', { bubbles: true }));
      })()`);
      await sleep(800);
      const ds = await chay(`
        Array.from(document.querySelectorAll('.select2-results__option')).map(function(e){return e.textContent;})
          .filter(function(t){ return t !== 'No results found'; })
      `);
      console.log('  go ' + JSON.stringify(tuKhoa).padEnd(16) + ' -> ' + (ds && ds.length ? ds.length + ' ket qua: ' + ds.slice(0, 3).join(' | ') : 'KHONG CO KET QUA'));
    };

    console.log('\n=== TIM KIEM (du lieu Bac Ninh, co "Phường Đa Mai") ===');
    await thu('Đa Mai');
    await thu('da mai');
    await thu('Da Mai');
    await thu('Đa');
    await thu('da');
    await thu('Phường');
    await thu('phuong');

    console.log('\n=== CHON BANG KET QUA TIM KHONG DAU ===');
    await chay(`(function(){
      jQuery('#nx-xa').select2('open');
      var o = document.querySelector('.select2-search__field');
      o.value = 'da mai';
      o.dispatchEvent(new Event('input', { bubbles: true }));
    })()`);
    await sleep(800);
    console.log('  ' + await chay(`(function(){
      var ds = document.querySelectorAll('.select2-results__option');
      if (!ds.length) return 'khong co ket qua de bam';
      ds[0].dispatchEvent(new MouseEvent('mouseup', { bubbles: true }));
      return 'da bam "' + ds[0].textContent + '"';
    })()`));
    await sleep(700);
    console.log('  value trong form   : ' + JSON.stringify(await chay(`document.getElementById('nx-xa').value`)));
    console.log('  khung hien         : ' + JSON.stringify(await chay(`(function(){
      var k = document.getElementById('nx-xa').nextElementSibling;
      var r = k && k.querySelector('.select2-selection__rendered');
      return r ? r.textContent : '?';
    })()`)));

  } catch (e) {
    console.error('LOI: ' + e.message);
  } finally {
    try { if (ws) ws.close(); } catch (e) {}
    chrome.kill();
    await sleep(300);
    try { fs.rmSync(UDD, { recursive: true, force: true }); } catch (e) {}
  }
})();
