/**
 * Mo mot trang roi hoi truc tiep: console co loi gi, jQuery co nap khong.
 * Chay: node scratch/hoi-trang.cjs <duongDan> [cookiePhien]
 */

const { spawn } = require('child_process');
const fs = require('fs');
const os = require('os');
const path = require('path');

const DUONG_DAN = process.argv[2] || '/_tn3.html';
const COOKIE = process.argv[3] || null;
const CHROME = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const PORT = 9336;
const UDD = path.join(os.tmpdir(), 'noxh-hoi-' + Date.now());
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
        log.push('CONSOLE.' + m.params.type + ': ' + m.params.args.map((a) => a.value ?? a.description).join(' '));
      }
      if (m.method === 'Runtime.exceptionThrown') {
        const d = m.params.exceptionDetails;
        log.push('EXCEPTION: ' + (d.exception?.description || d.text));
      }
      if (m.method === 'Log.entryAdded') {
        log.push('LOG.' + m.params.entry.level + ': ' + m.params.entry.text);
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
    await gui('Log.enable', {}, sid);
    await gui('Network.enable', {}, sid);

    if (COOKIE) {
      await gui('Network.setCookie', { name: 'noxhvn_session', value: COOKIE, domain: HOST, path: '/' }, sid);
    }

    await gui('Page.navigate', { url: `http://${HOST}${DUONG_DAN}` }, sid);
    await sleep(5000);

    const chay = async (expr) => {
      const r = await gui('Runtime.evaluate', { expression: expr, returnByValue: true, awaitPromise: true }, sid);
      if (r.exceptionDetails) return 'LOI: ' + (r.exceptionDetails.exception?.description || r.exceptionDetails.text);
      return r.result.value;
    };

    console.log('=== ' + DUONG_DAN + ' ===');
    console.log('tieu de : ' + (await chay('document.title')));
    console.log('jQuery  : ' + (await chay('typeof jQuery')));
    console.log('jQuery version: ' + (await chay('window.jQuery ? jQuery.fn.jquery : "(khong co)"')));
    console.log('select2 : ' + (await chay('window.jQuery && jQuery.fn.select2 ? "co" : "KHONG"')));
    console.log('so option #xa: ' + (await chay('document.getElementById("xa") ? document.getElementById("xa").options.length : "khong co #xa"')));
    console.log('noi dung .kq:\n  ' + String(await chay('(document.getElementById("kq")||{}).textContent || "(khong co)"')).replace(/\n/g, '\n  '));
    console.log('\n=== console/exception bat duoc ===');
    if (log.length === 0) { console.log('  (khong co)'); }
    for (const l of log.slice(0, 20)) { console.log('  ' + l); }

    /* Neu trang thí nghiệm chua chay thi thu goi tay */
    if (String(await chay('document.title')).indexOf('Thu Select2') !== -1) {
      console.log('\n=== THU GOI TAY ===');
      console.log('document.readyState = ' + (await chay('document.readyState')));
      console.log('so handler ready cua jQuery: ' + (await chay('(function(){ try { return jQuery._data(document, "events") ? Object.keys(jQuery._data(document, "events")).join(",") : "khong co"; } catch(e){ return "loi: " + e.message; } })()')));
      console.log('\n-- khoi tao select2 bang tay --');
      console.log(await chay(`(function(){
        try {
          var $xa = jQuery('#xa');
          $xa.select2({
            placeholder: '[Chọn Phường/Xã]',
            allowClear: true,
            width: '100%',
            ajax: {
              url: '/dia-gioi/phuong-xa/24',
              dataType: 'json',
              delay: 0,
              processResults: function (data) {
                return { results: data.map(function (x) { return { id: x.code, text: x.name }; }) };
              }
            }
          });
          return 'khoi tao duoc select2, so option = ' + $xa.find('option').length;
        } catch (e) { return 'LOI khoi tao: ' + e.message; }
      })()`));

      console.log('-- doi 1.5s roi xem --');
      await sleep(1500);
      console.log(await chay(`(function(){
        var $xa = jQuery('#xa');
        return 'hien="' + (jQuery('.select2-selection__rendered').text() || '') + '"  so option=' + $xa.find('option').length;
      })()`));

      console.log('-- dat value 07210 --');
      console.log(await chay(`(function(){
        var $xa = jQuery('#xa');
        $xa.val('07210').trigger('change');
        return 'value sau khi dat = "' + $xa.val() + '"';
      })()`));
      await sleep(1200);
      console.log(await chay(`(function(){
        var $xa = jQuery('#xa');
        return 'value="' + $xa.val() + '"  hien="' + (jQuery('.select2-selection__rendered').text() || '') + '"  so option=' + $xa.find('option').length;
      })()`));
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
