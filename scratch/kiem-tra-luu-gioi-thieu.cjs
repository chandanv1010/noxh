/**
 * Kiem tra form "Gioi thieu" con LUU duoc khong sau khi doi markup o anh.
 *
 * Chay: node scratch/kiem-tra-luu-gioi-thieu.cjs <cookiePhien>
 *
 * Lo: renderSystemImages() vua duoc boc them <div class="o-anh"> va <span> anh
 * xem truoc. Neu vo tinh lam mat thuoc tinh `name` cua <input> thi form van gui
 * di nhung thieu du lieu, va moi o se bi ghi rong -> trang chu mat het chu.
 *
 * Script nay bam "Luu lai" ma KHONG doi gia tri nao, roi kiem tra:
 *   1. co thong bao luu thanh cong khong,
 *   2. tai lai trang thi cac gia tri con nguyen khong,
 *   3. so o gui di co dung bang so o tren form khong.
 */

const { spawn } = require('child_process');
const fs = require('fs');
const os = require('os');
const path = require('path');

const COOKIE = process.argv[2];
if (!COOKIE) { console.error('thieu cookie'); process.exit(1); }

const CHROME = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const PORT = 9371;
const UDD = path.join(os.tmpdir(), 'noxh-luu-' + Date.now());
const HOST = 'noxh.test';
const sleep = (ms) => new Promise((r) => setTimeout(r, ms));

(async () => {
  const chrome = spawn(CHROME, [
    '--headless=new', '--disable-gpu', '--disable-extensions', '--no-sandbox',
    '--window-size=1500,1200', '--remote-debugging-port=' + PORT, '--user-data-dir=' + UDD,
    'about:blank',
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
    await gui('Runtime.enable', {}, sid);
    await gui('Network.setCookie', { name: 'noxhvn_session', value: COOKIE.trim(), domain: HOST, path: '/' }, sid);

    const chay = async (expr) => {
      const r = await gui('Runtime.evaluate', {
        expression: `(function(){ try { return String(${expr}); } catch(e){ return 'LOI: ' + e.message; } })()`,
        returnByValue: true, awaitPromise: true,
      }, sid);
      if (r.exceptionDetails) return 'LOI CDP';
      return r.result.value;
    };

    const DOC = `(function(){
      var o = {};
      document.querySelectorAll('form input[name^="config["], form textarea[name^="config["], form select[name^="config["]')
        .forEach(function(e){ o[e.getAttribute('name')] = e.value; });
      return JSON.stringify(o);
    })()`;

    await gui('Page.navigate', { url: `http://${HOST}/introduce/index` }, sid);
    await sleep(3500);

    const truoc = JSON.parse(await chay(DOC));
    const soOTruoc = Object.keys(truoc).length;
    console.log('so o cua form truoc khi luu : ' + soOTruoc);

    // Bam nut "Luu lai" that su.
    const o = JSON.parse(await chay(`(function(){
      var b = [].slice.call(document.querySelectorAll('button[type=submit]'))
                .filter(function(e){ return e.textContent.trim().indexOf('Lưu') >= 0; })[0];
      if (!b) return JSON.stringify({ loi: 'khong thay nut Luu lai' });
      b.scrollIntoView({block:'center'});
      var r = b.getBoundingClientRect();
      return JSON.stringify({ x: Math.round(r.left + r.width/2), y: Math.round(r.top + r.height/2) });
    })()`));

    if (o.loi) { console.log(o.loi); }
    else {
      for (const kieu of ['mousePressed', 'mouseReleased']) {
        await gui('Input.dispatchMouseEvent', { type: kieu, x: o.x, y: o.y, button: 'left', clickCount: 1 }, sid);
        await sleep(150);
      }
      await sleep(4500);
      console.log('sau khi bam Luu, duong dan: ' + await chay('location.pathname'));
      console.log('thong bao tren trang        : ' + await chay(
        `(function(){
          var s = document.querySelector('.alert, .toast, .swal2-title, .toast-message, .error, .success');
          return s ? s.textContent.replace(/\\s+/g,' ').trim().slice(0,140) : '(khong thay)';
        })()`));

      await gui('Page.navigate', { url: `http://${HOST}/introduce/index` }, sid);
      await sleep(3200);
      const sau = JSON.parse(await chay(DOC));
      console.log('so o cua form sau khi luu   : ' + Object.keys(sau).length);

      let khac = 0;
      const viDu = [];
      for (const k of Object.keys(truoc)) {
        if (sau[k] === undefined) { khac++; viDu.push(k + ': MAT O'); }
        else if (sau[k] !== truoc[k]) { khac++; if (viDu.length < 5) viDu.push(k + ': "' + truoc[k] + '" -> "' + sau[k] + '"'); }
      }
      for (const k of Object.keys(sau)) if (truoc[k] === undefined) { khac++; viDu.push(k + ': O MOI'); }

      console.log('so o bi doi                 : ' + khac);
      viDu.slice(0, 6).forEach((d) => console.log('   ' + d));
      console.log(khac === 0 ? '=> LUU OK, khong o nao bi mat hay bi ghi rong' : '=> CO VAN DE');
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
