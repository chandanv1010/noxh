/**
 * Doc thang cau hinh Select2 cua o Phuong/Xa tren form that.
 * Chay: node scratch/doc-cau-hinh-select2.cjs <cookiePhien>
 */

const { spawn } = require('child_process');
const fs = require('fs');
const os = require('os');
const path = require('path');

const COOKIE = process.argv[2];
const CHROME = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const PORT = 9343;
const UDD = path.join(os.tmpdir(), 'noxh-cfg-' + Date.now());
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

    console.log('=== CAU HINH SELECT2 CUA O PHUONG/XA ===');
    console.log(await chay(`(function(){
      var $x = jQuery('#nx-xa');
      var d = $x.data('select2');
      if (!d) return 'chua khoi tao Select2';
      var o = d.options;
      var ra = [];
      ra.push('phien ban Select2 : ' + (jQuery.fn.select2.amd && jQuery.fn.select2.amd.require ? 'amd co' : 'amd khong'));
      ra.push('matcher           : ' + (typeof o.get('matcher')));
      ra.push('sorter            : ' + (typeof o.get('sorter')));
      ra.push('dataAdapter       : ' + (o.get('dataAdapter') ? o.get('dataAdapter').name || 'co' : 'khong'));
      ra.push('allowClear        : ' + o.get('allowClear'));
      ra.push('placeholder       : ' + JSON.stringify(o.get('placeholder')));
      ra.push('so option the goc : ' + $x.find('option').length);
      ra.push('data la mang?     : ' + (Array.isArray(o.get('data')) ? 'co, ' + o.get('data').length + ' phan tu' : typeof o.get('data')));
      var mau = o.get('data') && o.get('data')[1];
      ra.push('vi du 1 phan tu   : ' + JSON.stringify(mau));
      // thu goi matcher hien tai
      try {
        var m = o.get('matcher');
        var thu = m({ term: 'da mai' }, { id: '1', text: 'Phường Đa Mai' });
        ra.push('matcher("da mai","Phường Đa Mai") -> ' + (thu ? 'KHOP tra ve ' + JSON.stringify(thu) : 'null (khong khop)'));
      } catch (e) { ra.push('loi goi matcher: ' + e.message); }
      return ra.join('\\n');
    })()`));

    console.log('\n=== SO SANH: matcher mac dinh tren cung du lieu ===');
    console.log(await chay(`(function(){
      var $x = jQuery('#nx-xa');
      var o = $x.data('select2').options;
      var matcherMacDinh = null;
      try {
        var amd = jQuery.fn.select2.amd;
        var DefaultMatcher = amd.require('select2/compat/matcher');
        matcherMacDinh = new DefaultMatcher(function (term, text, option) {
          return text.toUpperCase().indexOf(term.toUpperCase()) !== -1;
        });
      } catch (e) { return 'khong lay duoc matcher mac dinh: ' + e.message; }
      var d = { id: '1', text: 'Phường Đa Mai' };
      var kq1 = matcherMacDinh({ term: 'da mai' }, d);
      var kq2 = matcherMacDinh({ term: 'Da Mai' }, d);
      var kq3 = matcherMacDinh({ term: 'Đa Mai' }, d);
      return '"da mai" -> ' + (kq1 ? 'KHOP' : 'khong') +
             '  |  "Da Mai" -> ' + (kq2 ? 'KHOP' : 'khong') +
             '  |  "Đa Mai" -> ' + (kq3 ? 'KHOP' : 'khong');
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
