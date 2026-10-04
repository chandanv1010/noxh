/**
 * Dieu khien Chrome that qua DevTools Protocol de kiem tra cascade tren form admin.
 * Chay: node scratch/kiem-tra-cascade-browser.js <cookiePhien> [cookieXsrf]
 */

const { spawn } = require('child_process');
const fs = require('fs');
const os = require('os');
const path = require('path');

const COOKIE_PHien = process.argv[2];
const COOKIE_XSRF = process.argv[3] || '';
if (!COOKIE_PHien) { console.error('thieu cookie phien'); process.exit(1); }

const CHROME = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const PORT = 9333;
const UDD = path.join(os.tmpdir(), 'noxh-cdp-' + Date.now());
const HOST = 'noxh.test';

const sleep = (ms) => new Promise((r) => setTimeout(r, ms));

(async () => {
  const chrome = spawn(CHROME, [
    '--headless=new', '--disable-gpu', '--no-sandbox',
    '--remote-debugging-port=' + PORT,
    '--user-data-dir=' + UDD,
    'about:blank',
  ], { stdio: 'ignore' });

  let ws = null;
  try {
    /* --- doi cong debug mo --- */
    let version = null;
    for (let i = 0; i < 40; i++) {
      try {
        const r = await fetch('http://127.0.0.1:' + PORT + '/json/version');
        version = await r.json();
        break;
      } catch (e) { await sleep(250); }
    }
    if (!version) { throw new Error('khong mo duoc cong debug'); }
    console.log('trinh duyet: ' + version.Browser);

    /* --- mo WebSocket toi trinh duyet --- */
    ws = new WebSocket(version.webSocketDebuggerUrl);
    let id = 0;
    const cho = new Map();
    ws.addEventListener('message', (ev) => {
      const msg = JSON.parse(ev.data);
      if (msg.id && cho.has(msg.id)) { cho.get(msg.id)(msg); cho.delete(msg.id); }
    });
    await new Promise((res, rej) => {
      ws.addEventListener('open', res);
      ws.addEventListener('error', rej);
    });

    const gui = (method, params, sessionId) => new Promise((res, rej) => {
      const myId = ++id;
      cho.set(myId, (m) => (m.error ? rej(new Error(JSON.stringify(m.error))) : res(m.result)));
      ws.send(JSON.stringify({ id: myId, method, params: params || {}, sessionId }));
    });

    /* --- tao target va attach --- */
    const target = await gui('Target.createTarget', { url: 'about:blank' });
    const att = await gui('Target.attachToTarget', { targetId: target.targetId, flatten: true });
    const sid = att.sessionId;

    await gui('Page.enable', {}, sid);
    await gui('Network.enable', {}, sid);
    await gui('Runtime.enable', {}, sid);

    /* --- dat cookie phien nhu the da dang nhap --- */
    await gui('Network.setCookie', {
      name: 'noxhvn_session', value: COOKIE_PHien, domain: HOST, path: '/',
    }, sid);
    if (COOKIE_XSRF) {
      await gui('Network.setCookie', {
        name: 'XSRF-TOKEN', value: COOKIE_XSRF, domain: HOST, path: '/',
      }, sid);
    }

    /* --- mo form sua du an 76 --- */
    const url = 'http://' + HOST + '/product/76/edit';
    await gui('Page.navigate', { url }, sid);
    await sleep(3500);

    const chay = async (bieuThuc) => {
      const r = await gui('Runtime.evaluate', {
        expression: bieuThuc, returnByValue: true, awaitPromise: true,
      }, sid);
      if (r.exceptionDetails) {
        return 'LOI: ' + (r.exceptionDetails.exception?.description || r.exceptionDetails.text);
      }
      return r.result.value;
    };

    const trang = await chay('document.title');
    console.log('tieu de trang : ' + trang);
    if (String(trang).includes('Đăng nhập') || String(trang).includes('Login')) {
      console.log('=> BI DAY VE TRANG DANG NHAP, khong kiem tra duoc');
      return;
    }

    console.log('\n=== TRANG THAI BAN DAU ===');
    console.log(await chay(`(function(){
      var t = document.getElementById('nx-tinh');
      var x = document.getElementById('nx-xa');
      return [
        'o tinh: value="' + t.value + '"  (Select2 boc: ' + !!t.nextElementSibling?.classList.contains('select2-container') + ')',
        'Select2 hien: ' + JSON.stringify((document.querySelector('.select2-selection__rendered')||{}).textContent),
        'o xa  : disabled=' + x.disabled + ' so option=' + x.options.length + '  (Select2 boc: ' + !!x.nextElementSibling?.classList.contains('select2-container') + ')'
      ].join('\\n');
    })()`));

    console.log('\n=== DOI SANG TINH KHAC (Ha Noi, ma 01) nhu nguoi dung bam ===');
    await chay(`jQuery('#nx-tinh').val('01').trigger('change'); 'da chon'`);
    await sleep(2500);
    console.log(await chay(`(function(){
      var t = document.getElementById('nx-tinh');
      var x = document.getElementById('nx-xa');
      return [
        'o tinh: value="' + t.value + '"',
        'o xa  : disabled=' + x.disabled + '  so option=' + x.options.length,
        'o xa  : 4 lua chon dau:',
        Array.from(x.options).slice(0,4).map(function(o){ return '        ' + o.value + ' = ' + o.text; }).join('\\n'),
        'o loc nhanh: ' + (function(){ var l=document.getElementById('nx-xa-loc'); return l ? 'hien=' + (l.style.display !== 'none') : 'khong co'; })()
      ].join('\\n');
    })()`));

    console.log('\n=== THU LOC NHANH ===');
    console.log(await chay(`(function(){
      var l = document.getElementById('nx-xa-loc');
      if (!l) return 'khong co o loc';
      l.value = 'ba dinh';
      l.dispatchEvent(new Event('input', { bubbles: true }));
      var x = document.getElementById('nx-xa');
      return 'sau khi go "ba dinh": so option=' + x.options.length + ' -> ' + Array.from(x.options).map(function(o){return o.text;}).join(' | ');
    })()`));

    console.log('\n=== DOI LAI VE TINH CUA DU AN (Bac Ninh, ma 24) ===');
    await chay(`jQuery('#nx-tinh').val('24').trigger('change'); 'da chon'`);
    await sleep(2500);
    console.log(await chay(`(function(){
      var x = document.getElementById('nx-xa');
      return [
        'o xa: disabled=' + x.disabled + ' so option=' + x.options.length + ' value="' + x.value + '"',
        'dang chon: ' + (x.selectedOptions[0] ? x.selectedOptions[0].text : '(khong)')
      ].join('\\n');
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
