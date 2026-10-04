/**
 * Kiem tra luong tao moi: chon tinh -> chon phuong/xa bang Select2 -> luu.
 * Chay: node scratch/kiem-tra-tao-moi-select2.cjs <cookiePhien>
 */

const { spawn } = require('child_process');
const fs = require('fs');
const os = require('os');
const path = require('path');

const COOKIE = process.argv[2];
if (!COOKIE) { console.error('thieu cookie'); process.exit(1); }

const CHROME = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const PORT = 9339;
const UDD = path.join(os.tmpdir(), 'noxh-tao-' + Date.now());
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

    await gui('Page.navigate', { url: `http://${HOST}/product/create` }, sid);
    await sleep(4500);

    const chay = async (expr) => {
      const r = await gui('Runtime.evaluate', { expression: expr, returnByValue: true, awaitPromise: true }, sid);
      if (r.exceptionDetails) return 'LOI: ' + (r.exceptionDetails.exception?.description || r.exceptionDetails.text);
      return r.result.value;
    };
    const KQ = (n, v) => console.log('  ' + n.padEnd(36) + ': ' + v);

    console.log('=== /product/create ===');
    KQ('tieu de', await chay('document.title'));
    KQ('o Phuong/Xa co option nao', await chay(`document.getElementById('nx-xa').options.length`));
    KQ('Select2 da boc o Phuong/Xa?', await chay(`(function(){
      var x = document.getElementById('nx-xa');
      return x.nextElementSibling && x.nextElementSibling.classList.contains('select2-container') ? 'CO' : 'chua';
    })()`));

    console.log('\n--- chon Tinh/Thanh = Thai Nguyen (19) ---');
    console.log('  ' + (await chay(`jQuery('#nx-tinh').val('19').trigger('change'); 'da chon'`)));
    await sleep(2500);
    KQ('so option Phuong/Xa', await chay(`document.getElementById('nx-xa').options.length`));
    KQ('Select2 da boc?', await chay(`(function(){
      var x = document.getElementById('nx-xa');
      return x.nextElementSibling && x.nextElementSibling.classList.contains('select2-container') ? 'CO' : 'chua';
    })()`));

    console.log('\n--- mo o tim kiem, go "tuc duyen", bam ket qua ---');
    console.log('  ' + (await chay(`(function(){
      jQuery('#nx-xa').select2('open');
      var o = document.querySelector('.select2-search__field');
      if (!o) return 'khong thay o tim kiem';
      o.value = 'tuc duyen';
      o.dispatchEvent(new Event('input', { bubbles: true }));
      return 'da go';
    })()`)));
    await sleep(900);
    KQ('ket qua', JSON.stringify(await chay(`
      Array.from(document.querySelectorAll('.select2-results__option')).map(function(e){return e.textContent;}).join(' | ')
    `)));
    console.log('  ' + (await chay(`(function(){
      var ds = document.querySelectorAll('.select2-results__option');
      if (!ds.length) return 'khong co ket qua';
      ds[0].dispatchEvent(new MouseEvent('mouseup', { bubbles: true }));
      return 'da bam "' + ds[0].textContent + '"';
    })()`)));
    await sleep(700);

    /* Dien cac o bat buoc de luu duoc */
    console.log('\n--- dien cac o bat buoc va luu ---');
    const slug = 'du-an-tao-bang-select2-' + Date.now();
    console.log('  ' + (await chay(`(function(){
      var dat = function(sel, gt){ var e = document.querySelector(sel); if (e) e.value = gt; };
      dat('input[name="name"]', 'DU AN TAO BANG SELECT2');
      dat('input[name="canonical"]', ${JSON.stringify(slug)});
      var dm = document.querySelector('select[name="product_catalogue_id"]');
      if (dm && !dm.value) dm.value = dm.options[1] ? dm.options[1].value : '';
      return 'ward_code trong form = ' + JSON.stringify(jQuery('#nx-xa').val()) +
             ' | tinh = ' + JSON.stringify(jQuery('#nx-tinh').val()) +
             ' | danh muc = ' + JSON.stringify(dm ? dm.value : '(khong co)');
    })()`)));

    /* Gui form that */
    console.log('  ' + (await chay(`(function(){
      var f = document.querySelector('form.nx-form-du-an');
      if (!f) return 'khong thay form';
      f.submit();
      return 'da gui form';
    })()`)));
    await sleep(4000);
    KQ('URL sau khi gui', await chay('location.pathname'));
    KQ('tieu de sau khi gui', await chay('document.title'));
    KQ('co thong bao loi khong', await chay(`document.body.innerHTML.indexOf('invalid-feedback') !== -1 || document.body.innerHTML.indexOf('alert-danger') !== -1 ? 'CO' : 'khong'`));

    console.log('\n=== exception ===');
    for (const l of log.slice(0, 8)) { console.log('  ' + l); }
    if (!log.length) { console.log('  (khong co)'); }
    fs.writeFileSync(path.join(os.tmpdir(), 'noxh-slug-tao.txt'), slug);

  } catch (e) {
    console.error('LOI: ' + e.message);
  } finally {
    try { if (ws) ws.close(); } catch (e) {}
    chrome.kill();
    await sleep(300);
    try { fs.rmSync(UDD, { recursive: true, force: true }); } catch (e) {}
  }
})();
