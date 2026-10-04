/**
 * Tren FORM THAT: kiem tra 3 cau hinh matcher khac nhau.
 * Chay: node scratch/thu-matcher-amd.cjs <cookiePhien>
 */

const { spawn } = require('child_process');
const fs = require('fs');
const os = require('os');
const path = require('path');

const COOKIE = process.argv[2];
const CHROME = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const PORT = 9345;
const UDD = path.join(os.tmpdir(), 'noxh-m3-' + Date.now());
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

    await gui('Page.navigate', { url: `http://${HOST}/product/76/edit` }, sid);
    await sleep(5000);

    const chay = async (expr) => {
      const r = await gui('Runtime.evaluate', {
        expression: `(function(){ try { return String(${expr}); } catch (e) { return 'LOI TRONG TRANG: ' + e.message; } })()`,
        returnByValue: true, awaitPromise: true,
      }, sid);
      if (r.exceptionDetails) return 'LOI CDP: ' + (r.exceptionDetails.exception?.description || r.exceptionDetails.text);
      return r.result.value;
    };

    /* Tim trong dropdown cua rieng o Phuong/Xa, khong dinh dropdown khac:
       id cua dropdown = id cua the select + '-results' */
    const tim = async (tuKhoa) => {
      await chay(`jQuery('#nx-xa').select2('close')`);
      await sleep(250);
      // Lay DUNG o tim kiem cua o Phuong/Xa qua instance Select2, khong dung
      // document.querySelector vi trang co nhieu o Select2.
      const dat = await chay(`(function(){
        jQuery('#nx-xa').select2('open');
        var d = jQuery('#nx-xa').data('select2');
        var o = d && d.dropdown && d.dropdown.$search && d.dropdown.$search[0];
        if (!o) return 'khong lay duoc o tim kiem cua Phuong/Xa';
        window.__oTim = o;
        o.value = '';
        o.dispatchEvent(new Event('input', { bubbles: true }));
        o.value = ${JSON.stringify(tuKhoa)};
        o.dispatchEvent(new Event('input', { bubbles: true }));
        return 'da go vao o tim kiem cua Phuong/Xa';
      })()`);
      await sleep(900);
      const kq = await chay(`(function(){
        // Khung ket qua nam ngay sau o tim kiem trong cung .select2-dropdown
        var khoi = window.__oTim.closest('.select2-dropdown');
        var ds = Array.from(khoi.querySelectorAll('.select2-results__option')).map(function(e){ return e.textContent; });
        return 'o tim kiem: ' + JSON.stringify(window.__oTim.value) + ' | ' + ds.length + ' ket qua [' + ds.slice(0,3).join(', ') + ']';
      })()`);
      return { dat, kq };
    };

    const xemTatCa = async (nhan, tuKhoa) => {
      const r = await tim(tuKhoa);
      console.log('  ' + nhan.padEnd(22) + ' go ' + JSON.stringify(tuKhoa).padEnd(12) + ' -> ' + r.kq);
      if (String(r.dat).indexOf('khong lay duoc') !== -1) { console.log('     !! ' + r.dat); }
    };

    console.log('=== 1. matcher HIEN TAI cua form (khong dat gi) ===');
    await xemTatCa('form nhu dang chay', 'da mai');
    await xemTatCa('form nhu dang chay', 'zzzqqq');

    console.log('\n=== 2. BO HAN matcher (de Select2 tu loc) ===');
    console.log('   ' + await chay(`(function(){
      try {
        var $xa = jQuery('#nx-xa');
        var mau = $xa.data('select2').options.get('data');
        var hienTai = $xa.val();
        $xa.select2('destroy');
        $xa.empty();
        $xa.select2({ placeholder: '[Chọn Phường/Xã]', allowClear: true, width: '100%', data: mau });
        if (hienTai) { $xa.val(hienTai).trigger('change'); }
        return 'xong, so option = ' + $xa.find('option').length;
      } catch (e) { return 'LOI: ' + e.message; }
    })()`));
    await xemTatCa('bo matcher', 'da mai');
    await xemTatCa('bo matcher', 'Da Mai');
    await xemTatCa('bo matcher', 'zzzqqq');

    console.log('\n=== 3. matcher tu bo dau (ham thuong) ===');
    console.log('   ' + await chay(`(function(){
      try {
        var $xa = jQuery('#nx-xa');
        function boDau(s){ return String(s).normalize('NFD').replace(/[\\u0300-\\u036f]/g,'').replace(/đ/g,'d').replace(/Đ/g,'D').toLowerCase().trim(); }
        var mau = $xa.data('select2').options.get('data');
        var hienTai = $xa.val();
        $xa.select2('destroy');
        $xa.empty();
        $xa.select2({
          placeholder: '[Chọn Phường/Xã]', allowClear: true, width: '100%', data: mau,
          matcher: function (p, d) { return boDau(d.text).indexOf(boDau(p.term)) !== -1 ? d : null; },
        });
        if (hienTai) { $xa.val(hienTai).trigger('change'); }
        return 'xong, so option = ' + $xa.find('option').length;
      } catch (e) { return 'LOI: ' + e.message; }
    })()`));
    await xemTatCa('matcher bo dau', 'da mai');
    await xemTatCa('matcher bo dau', 'Da Mai');
    await xemTatCa('matcher bo dau', 'zzzqqq');

  } catch (e) {
    console.error('LOI: ' + e.message);
  } finally {
    try { if (ws) ws.close(); } catch (e) {}
    chrome.kill();
    await sleep(300);
    try { fs.rmSync(UDD, { recursive: true, force: true }); } catch (e) {}
  }
})();
