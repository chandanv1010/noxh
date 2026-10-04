/**
 * Kiem tra dut khoat: o tim kiem cua Select2 tren form THAT co loc khong?
 * Dung tu khoa chac chan khong ton tai ("zzzqqq").
 * Chay: node scratch/kiem-tra-loc-select2.cjs <cookiePhien>
 */

const { spawn } = require('child_process');
const fs = require('fs');
const os = require('os');
const path = require('path');

const COOKIE = process.argv[2];
const CHROME = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const PORT = 9342;
const UDD = path.join(os.tmpdir(), 'noxh-loc-' + Date.now());
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
      const r = await gui('Runtime.evaluate', { expression: expr, returnByValue: true, awaitPromise: true }, sid);
      if (r.exceptionDetails) return 'LOI: ' + (r.exceptionDetails.exception?.description || r.exceptionDetails.text);
      return r.result.value;
    };

    console.log('so option trong the goc: ' + await chay(`document.getElementById('nx-xa').options.length`));

    const thu = async (tuKhoa) => {
      await chay(`(function(){
        var $x = jQuery('#nx-xa');
        $x.select2('close');
      })()`);
      await sleep(250);
      await chay(`(function(){
        jQuery('#nx-xa').select2('open');
        var o = document.querySelector('.select2-search__field');
        o.value = '';
        o.dispatchEvent(new Event('input', { bubbles: true }));
        o.value = ${JSON.stringify(tuKhoa)};
        o.dispatchEvent(new Event('input', { bubbles: true }));
      })()`);
      await sleep(800);
      const kq = await chay(`(function(){
        var ds = Array.from(document.querySelectorAll('.select2-results__option'))
          .map(function(e){ return e.textContent; });
        return { tong: ds.length, dau: ds.slice(0, 4), coNoResults: ds.indexOf('No results found') !== -1 };
      })()`);
      return kq;
    };

    console.log('\n=== thu voi cac tu khoa ===');
    for (const tk of ['zzzqqq', 'Đa Mai', 'da mai', 'Đức Xuân', '']) {
      const kq = await thu(tk);
      console.log('  ' + JSON.stringify(tk).padEnd(14) + ' -> ' + kq.tong + ' option' +
                  (kq.coNoResults ? ' (co dong "No results found")' : '') +
                  ' | dau: ' + kq.dau.slice(0, 2).join(' / '));
    }

    console.log('\n=== thong tin o tim kiem ===');
    console.log(await chay(`(function(){
      var o = document.querySelector('.select2-search__field');
      if (!o) return 'khong thay o tim kiem';
      return 'value hien tai = ' + JSON.stringify(o.value) +
             ' | o hien? ' + (o.offsetParent !== null) +
             ' | readOnly? ' + o.readOnly;
    })()`));

  } catch (e) {
    console.error('LOI: ' + e.message);
  } finally {
    try { if (ws) ws.close(); } catch (e) {}
    chrome.kill();
    await sleep(300);
    try { fs.rmSync(UDD, { recursive: true, force: true }); } catch (e) {}
  }
})();
