/**
 * So sanh A/B: matcher mac dinh cua Select2 vs matcher tu bo dau.
 * Chay: node scratch/so-sanh-matcher.cjs
 */

const { spawn } = require('child_process');
const fs = require('fs');
const os = require('os');
const path = require('path');

const CHROME = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const PORT = 9340;
const UDD = path.join(os.tmpdir(), 'noxh-ab-' + Date.now());
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
    await gui('Runtime.enable', {}, sid);
    await gui('Network.enable', {}, sid);

    await gui('Page.navigate', { url: `http://${HOST}/_tn-sel3.html` }, sid);
    await sleep(2500);

    const chay = async (expr) => {
      const r = await gui('Runtime.evaluate', { expression: expr, returnByValue: true, awaitPromise: true }, sid);
      if (r.exceptionDetails) return 'LOI: ' + (r.exceptionDetails.exception?.description || r.exceptionDetails.text);
      return r.result.value;
    };

    // Nap du lieu va dinh nghia ham bo dau
    console.log(await chay(`(function(){
      window.__kq = {};
      window.__boDau = function (s) {
        return String(s).normalize('NFD').replace(/[\\u0300-\\u036f]/g, '')
          .replace(/đ/g, 'd').replace(/Đ/g, 'D').toLowerCase().trim();
      };
      return 'da dinh nghia boDau';
    })()`));

    const chayTruongHop = async (nhan, cauHinhJs, tuKhoa) => {
      const kq = await chay(`(function(){
        var $xa = jQuery('#xa');
        if ($xa.data('select2')) { $xa.select2('destroy'); }
        $xa.empty();
        var cauHinh = ${cauHinhJs};
        fetch('/dia-gioi/phuong-xa/19', { headers: { Accept: 'application/json' } })
          .then(function(r){ return r.json(); })
          .then(function(ds){
            window.__ds = ds;
            $xa.select2(Object.assign({
              placeholder: '[Chọn Phường/Xã]',
              width: '100%',
              data: ds.map(function(x){ return { id: x.code, text: x.name }; })
            }, cauHinh));
            window.__xong = true;
          });
        return 'dang nap';
      })()`);
      for (let i = 0; i < 25 && !(await chay('window.__xong === true')); i++) { await sleep(200); }
      await chay('window.__xong = false');

      await chay(`(function(){
        jQuery('#xa').select2('open');
        var o = document.querySelector('.select2-search__field');
        o.value = ${JSON.stringify(tuKhoa)};
        o.dispatchEvent(new Event('input', { bubbles: true }));
      })()`);
      await sleep(900);

      const hien = await chay(`
        Array.from(document.querySelectorAll('.select2-results__option')).map(function(e){return e.textContent;}).slice(0,3).join(' | ')
      `);
      const so = await chay(`document.querySelectorAll('.select2-results__option').length`);
      console.log('  ' + nhan.padEnd(38) + ' go "' + tuKhoa + '" -> ' + so + ' ket qua: ' + JSON.stringify(hien));
    };

    console.log('\n=== SO SANH (du lieu: phuong/xa cua Thai Nguyen, ma 19) ===\n');

    // Truoc het: trong du lieu co ten nao de thu?
    console.log(await chay(`(function(){
      var ds = window.__ds || [];
      var co = ds.filter(function(x){ return x.name.indexOf('Phường') === 0; }).slice(0,3).map(function(x){return x.name;});
      return 'vi du ten trong du lieu: ' + co.join(' | ');
    })()`));
    console.log('');

    await chayTruongHop('A1. mac dinh, go "Phường"', '{}', 'Phường');
    await chayTruongHop('A2. mac dinh, go "Duc"', '{}', 'Duc');
    await chayTruongHop('A3. mac dinh, go "Đức" (co dau)', '{}', 'Đức');
    await chayTruongHop('A4. mac dinh, go "Duc Xuan"', '{}', 'Duc Xuan');
    await chayTruongHop('B1. matcher bo dau, "Duc Xuan"', `{ matcher: function(p, d){ return window.__boDau(d.text).indexOf(window.__boDau(p.term)) !== -1 ? d : null; } }`, 'Duc Xuan');
    await chayTruongHop('B2. matcher bo dau, "duc xuan"', `{ matcher: function(p, d){ return window.__boDau(d.text).indexOf(window.__boDau(p.term)) !== -1 ? d : null; } }`, 'duc xuan');
    await chayTruongHop('B3. matcher bo dau, "Đức Xuân"', `{ matcher: function(p, d){ return window.__boDau(d.text).indexOf(window.__boDau(p.term)) !== -1 ? d : null; } }`, 'Đức Xuân');

    console.log('\n=== matcher tu tra ve gi? ===');
    console.log(await chay(`(function(){
      var ds = window.__ds || [];
      var vd = ds.filter(function(x){ return x.name.indexOf('Đức Xuân') !== -1; })[0];
      if (!vd) return 'khong thay "Đức Xuân" trong du lieu';
      var p = { term: 'duc xuan' };
      var d = { id: vd.code, text: vd.name };
      var kq = window.__boDau(d.text).indexOf(window.__boDau(p.term)) !== -1 ? d : null;
      return 'd=' + JSON.stringify(d) + '  boDau="' + window.__boDau(d.text) + '"  => ' + (kq ? 'KHOP' : 'khong khop');
    })()`));

    console.log('\n=== exception ===');
    for (const l of log.slice(0, 6)) { console.log('  ' + l); }
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
