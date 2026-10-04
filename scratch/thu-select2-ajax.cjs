/**
 * Thu Select2 + AJAX bang cach goi ham truc tiep tu CDP (khong phu thuoc
 * $(document).ready nua), de tach van de "script co chay khong" khoi van de
 * "Select2 co giu lua chon khong".
 */

const { spawn } = require('child_process');
const fs = require('fs');
const os = require('os');
const path = require('path');

const CHROME = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const PORT = 9337;
const UDD = path.join(os.tmpdir(), 'noxh-sel2-' + Date.now());
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
    await gui('Runtime.enable', {}, sid);
    await gui('Network.enable', {}, sid);

    const chay = async (expr) => {
      const r = await gui('Runtime.evaluate', { expression: expr, returnByValue: true, awaitPromise: true }, sid);
      if (r.exceptionDetails) return 'LOI: ' + (r.exceptionDetails.exception?.description || r.exceptionDetails.text);
      return r.result.value;
    };

    /* Trang that co san jQuery + Select2 cua du an (trang thi nghiem _tn2.html
       nap ca hai bang the script thuong). */
    await gui('Page.navigate', { url: `http://${HOST}/_tn-sel3.html` }, sid);
    await sleep(3000);

    console.log('jQuery + Select2 tren trang: ' + (await chay('typeof jQuery + " / " + (window.jQuery && jQuery.fn.select2 ? "select2 co" : "select2 KHONG")')));
    if (String(await chay('typeof jQuery')) !== 'function') {
      throw new Error('trang khong nap duoc jQuery');
    }
    /* Don trang: bo het select cu, tao mot select sach */
    await chay(`jQuery('body').empty().append('<select id="xa" style="width:360px"></select>')`);

    /* Bat request de xem goi AJAX nao duoc gui */
    const req = [];
    ws.addEventListener('message', (ev) => {
      const m = JSON.parse(ev.data);
      if (m.method === 'Network.requestWillBeSent' && /dia-gioi/.test(m.params.request.url)) {
        req.push(m.params.request.url);
      }
    });

    console.log('\n=== 1. Khoi tao Select2 co AJAX ===');
    console.log(await chay(`(function(){
      try {
        window.__soLan = 0;
        jQuery('#xa').select2({
          placeholder: '[Chọn Phường/Xã]',
          allowClear: true,
          width: '100%',
          ajax: {
            url: '/dia-gioi/phuong-xa/24',
            dataType: 'json',
            delay: 0,
            processResults: function (data) {
              window.__soLan++;
              window.__cuoi = data.length;
              return { results: data.map(function (x) { return { id: x.code, text: x.name }; }) };
            }
          }
        });
        return 'khoi tao xong';
      } catch (e) { return 'LOI: ' + e.message; }
    })()`));

    await sleep(2000);
    console.log('   request dia-gioi da gui: ' + JSON.stringify(req));
    console.log('   processResults goi ' + (await chay('window.__soLan')) + ' lan, so ban ghi = ' + (await chay('window.__cuoi')));
    console.log('   so option trong the goc: ' + (await chay('jQuery("#xa").find("option").length')));
    console.log('   giao dien hien: ' + JSON.stringify(await chay('jQuery(".select2-selection__rendered").text() || ""')));

    console.log('\n=== 2. Dat value 07210 (nhu mo form sua) ===');
    console.log('   ' + (await chay(`(function(){
      var v = jQuery('#xa').val('07210').trigger('change').val();
      return 'value sau khi dat = ' + JSON.stringify(v);
    })()`)));
    await sleep(1500);
    console.log('   sau 1.5s: value=' + JSON.stringify(await chay('jQuery("#xa").val()')) +
                '  hien=' + JSON.stringify(await chay('jQuery(".select2-selection__rendered").text() || ""')) +
                '  so option=' + (await chay('jQuery("#xa").find("option").length')));

    console.log('\n=== 3. Xoa lua chon ===');
    console.log('   ' + (await chay(`(function(){
      jQuery('#xa').val(null).trigger('change');
      return 'value sau khi xoa = ' + JSON.stringify(jQuery('#xa').val());
    })()`)));
    await sleep(600);
    console.log('   hien=' + JSON.stringify(await chay('jQuery(".select2-selection__rendered").text() || ""')));

    console.log('\n=== 4. Chon lai bang cach them option (cach form sua se dung) ===');
    console.log('   ' + (await chay(`(function(){
      var o = new Option('Phường Bắc Giang', '07210', true, true);
      jQuery('#xa').append(o).trigger('change');
      return 'value = ' + JSON.stringify(jQuery('#xa').val());
    })()`)));
    await sleep(800);
    console.log('   hien=' + JSON.stringify(await chay('jQuery(".select2-selection__rendered").text() || ""')) +
                '  so option=' + (await chay('jQuery("#xa").find("option").length')));

    /* ================= DUONG SE DUNG TRONG FORM ================= */
    console.log('\n=== 5. Cach se dung: nap san data bang fetch, roi Select2 quan ly ===');
    console.log('   ' + (await chay(`(function(){
      var $xa = jQuery('#xa');
      // Xoa trang: destroy Select2 cu va the select
      if ($xa.data('select2')) { $xa.select2('destroy'); }
      $xa.empty();
      window.__xong = false;
      // Nap danh sach phuong/xa cua Bac Ninh bang fetch nhu code se viet
      fetch('/dia-gioi/phuong-xa/24', { headers: { Accept: 'application/json' } })
        .then(function (r) { return r.json(); })
        .then(function (ds) {
          window.__ds = ds;
          $xa.select2({
            placeholder: '[Chọn Phường/Xã]',
            allowClear: true,
            width: '100%',
            data: ds.map(function (x) { return { id: x.code, text: x.name }; })
          });
          window.__xong = true;
        })
        .catch(function (e) { window.__loi = String(e); window.__xong = true; });
      return 'da bat dau nap';
    })()`)));

    for (let i = 0; i < 20 && !(await chay('window.__xong === true')); i++) { await sleep(300); }
    console.log('   nap xong: ' + (await chay('window.__xong')) + ', so ban ghi = ' + (await chay('(window.__ds||[]).length')) + ', loi = ' + (await chay('window.__loi || "(khong)"')));
    console.log('   giao dien: ' + JSON.stringify(await chay('jQuery(".select2-selection__rendered").text() || ""')));

    console.log('\n   -- 5a. Chon san phuong/xa cua du an (giong mo form sua) --');
    console.log('   ' + (await chay(`(function(){
      var $xa = jQuery('#xa');
      var o = new Option('Phường Bắc Giang', '07210', true, true);
      $xa.append(o).trigger('change');
      return 'value = ' + JSON.stringify($xa.val());
    })()`)));
    await sleep(500);
    console.log('   hien=' + JSON.stringify(await chay('jQuery(".select2-selection__rendered").text() || ""')) +
                '  so option=' + (await chay('jQuery("#xa").find("option").length')));

    console.log('\n   -- 5b. MO O TIM KIEM va bam chon mot phuong khac (giong nguoi dung) --');
    console.log(await chay(`(function(){
      var $xa = jQuery('#xa');
      $xa.select2('open');                       // mo dropdown
      var oTim = document.querySelector('.select2-search__field');
      if (!oTim) return 'khong thay o tim kiem';
      oTim.value = 'Da Mai';                     // go chu
      oTim.dispatchEvent(new Event('input', { bubbles: true }));
      return 'da go "Da Mai" vao o tim kiem';
    })()`));
    await sleep(900);
    console.log('   ket qua hien ra: ' + JSON.stringify(await chay(`Array.from(document.querySelectorAll('.select2-results__option')).map(function(e){return e.textContent;}).join(' | ')`)));

    console.log('   ' + (await chay(`(function(){
      var ds = document.querySelectorAll('.select2-results__option');
      if (!ds.length) return 'khong co ket qua de bam';
      // Bam vao ket qua dau tien nhu nguoi dung
      ds[0].dispatchEvent(new MouseEvent('mouseup', { bubbles: true }));
      return 'da bam vao "' + ds[0].textContent + '"';
    })()`)));
    await sleep(900);
    console.log('   SAU KHI BAM: value=' + JSON.stringify(await chay('jQuery("#xa").val()')) +
                '  hien=' + JSON.stringify(await chay('jQuery(".select2-selection__rendered").text() || ""')) +
                '  so option=' + (await chay('jQuery("#xa").find("option").length')));

    console.log('\n=== 6. Matcher bo dau qua AMD cua Select2 ===');
    console.log('   ' + (await chay(`(function(){
      return 'co $.fn.select2.amd? ' + !!(jQuery.fn.select2 && jQuery.fn.select2.amd) +
             ' | require? ' + !!(jQuery.fn.select2 && jQuery.fn.select2.amd && jQuery.fn.select2.amd.require);
    })()`)));

    console.log('   ' + (await chay(`(function(){
      try {
        var amd = jQuery.fn.select2.amd;
        var Utils = amd.require('select2/utils');
        var Options = amd.require('select2/options');
        var DefaultMatcher = amd.require('select2/compat/matcher');

        function boDau(s) {
          return String(s).normalize('NFD').replace(/[\\u0300-\\u036f]/g, '')
            .replace(/đ/g, 'd').replace(/Đ/g, 'D').toLowerCase().trim();
        }
        function khop(term, text) {
          return boDau(text).indexOf(boDau(term)) !== -1;
        }
        window.__AMD_OK = true;
        window.__Utils = Utils;
        window.__Options = Options;
        window.__DefaultMatcher = DefaultMatcher;
        window.__khop = khop;
        return 'nap duoc AMD module: Utils=' + (typeof Utils) + ' Options=' + (typeof Options) + ' Matcher=' + (typeof DefaultMatcher);
      } catch (e) { return 'LOI AMD: ' + e.message; }
    })()`)));

    /* Khoi tao lai o Phuong/Xa voi matcher bo dau */
    console.log('\n   -- kiem tra ham so khop truoc --');
    console.log('   ' + (await chay(`(function(){
      var ds = window.__ds || [];
      var ten = ds.filter(function(x){ return x.name.indexOf('Túc Duyên') !== -1; }).map(function(x){return x.name;});
      return 'ten co dau trong du lieu: ' + JSON.stringify(ten) +
             ' | boDau("Phường Túc Duyên")="' + window.__khop && window.__khop ? 'co ham' : 'khong co ham';
    })()`)));
    console.log('   ' + (await chay(`(function(){
      return 'khop("tuc duyen", "Phường Túc Duyên") = ' + window.__khop('tuc duyen', 'Phường Túc Duyên');
    })()`)));

    /* Khoi tao lai o Phuong/Xa voi matcher bo dau */
    console.log('\n   -- khoi tao lai voi matcher bo dau --');
    console.log('   ' + (await chay(`(function(){
      try {
        var $xa = jQuery('#xa');
        if ($xa.data('select2')) { $xa.select2('destroy'); }
        $xa.empty();
        var options = new window.__Options({
          matcher: new window.__DefaultMatcher(function (term, text) { return window.__khop(term, text); }, true),
        }, $xa);
        window.__m = options.get('matcher');
        $xa.select2({
          placeholder: '[Chọn Phường/Xã]',
          allowClear: true,
          width: '100%',
          matcher: window.__m,
          data: (window.__ds || []).map(function (x) { return { id: x.code, text: x.name }; }),
        });
        return 'khoi tao xong, so option = ' + $xa.find('option').length + ', matcher=' + (typeof window.__m);
      } catch (e) { return 'LOI: ' + e.message; }
    })()`)));

    await sleep(600);
    console.log('   ' + (await chay(`(function(){
      jQuery('#xa').select2('open');
      var o = document.querySelector('.select2-search__field');
      if (!o) return 'khong thay o tim kiem';
      o.value = 'tuc duyen';
      o.dispatchEvent(new Event('input', { bubbles: true }));
      return 'da go "tuc duyen" (khong dau)';
    })()`)));
    await sleep(900);
    console.log('   ket qua: ' + JSON.stringify(await chay(`
      Array.from(document.querySelectorAll('.select2-results__option')).map(function(e){return e.textContent;}).join(' | ')
    `)));

    /* Thu duong khac: sorter - Select2 4.0.0 dung sorter de vua loc vua xep */
    console.log('\n   -- thu bang sorter --');
    console.log('   ' + (await chay(`(function(){
      try {
        var $xa = jQuery('#xa');
        if ($xa.data('select2')) { $xa.select2('destroy'); }
        $xa.empty();
        window.__sorterGoi = 0;
        $xa.select2({
          placeholder: '[Chọn Phường/Xã]',
          allowClear: true,
          width: '100%',
          sorter: function (data) {
            var term = jQuery('.select2-search__field').val() || '';
            window.__sorterGoi++;
            if (!term) { return data; }
            return data.filter(function (x) { return window.__khop(term, x.text); });
          },
          data: (window.__ds || []).map(function (x) { return { id: x.code, text: x.name }; }),
        });
        return 'khoi tao xong, so option = ' + $xa.find('option').length;
      } catch (e) { return 'LOI: ' + e.message; }
    })()`)));

    await sleep(600);
    console.log('   ' + (await chay(`(function(){
      jQuery('#xa').select2('open');
      var o = document.querySelector('.select2-search__field');
      if (!o) return 'khong thay o tim kiem';
      o.value = 'tuc duyen';
      o.dispatchEvent(new Event('input', { bubbles: true }));
      return 'da go "tuc duyen"';
    })()`)));
    await sleep(900);
    console.log('   sorter duoc goi ' + (await chay('window.__sorterGoi')) + ' lan');
    console.log('   ket qua: ' + JSON.stringify(await chay(`
      Array.from(document.querySelectorAll('.select2-results__option')).map(function(e){return e.textContent;}).join(' | ')
    `)));

    /* Thu: matcher luon dung (de Select2 khong tu loc) + sorter tu loc */
    console.log('\n   -- matcher luon dung + sorter tu loc --');
    console.log('   ' + (await chay(`(function(){
      try {
        var $xa = jQuery('#xa');
        if ($xa.data('select2')) { $xa.select2('destroy'); }
        $xa.empty();
        window.__mGoi = 0; window.__sGoi = 0; window.__term = '';
        $xa.select2({
          placeholder: '[Chọn Phường/Xã]',
          allowClear: true,
          width: '100%',
          matcher: function (params, data) { window.__mGoi++; return data; },
          sorter: function (data) {
            window.__sGoi++;
            var term = (document.querySelector('.select2-search__field') || {}).value || '';
            window.__term = term;
            if (!term) { return data; }
            return data.filter(function (x) { return window.__khop(term, x.text); });
          },
          data: (window.__ds || []).map(function (x) { return { id: x.code, text: x.name }; }),
        });
        return 'khoi tao xong';
      } catch (e) { return 'LOI: ' + e.message; }
    })()`)));

    await sleep(600);
    console.log('   ' + (await chay(`(function(){
      jQuery('#xa').select2('open');
      var o = document.querySelector('.select2-search__field');
      if (!o) return 'khong thay o tim kiem';
      o.value = 'tuc duyen';
      o.dispatchEvent(new Event('input', { bubbles: true }));
      return 'da go "tuc duyen"';
    })()`)));
    await sleep(900);
    console.log('   matcher goi ' + (await chay('window.__mGoi')) + ' lan, sorter goi ' + (await chay('window.__sGoi')) + ' lan, term doc duoc = ' + JSON.stringify(await chay('window.__term')));
    console.log('   ket qua: ' + JSON.stringify(await chay(`
      Array.from(document.querySelectorAll('.select2-results__option')).map(function(e){return e.textContent;}).join(' | ')
    `)));

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
