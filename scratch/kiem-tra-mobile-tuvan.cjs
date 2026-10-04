/**
 * Gia lap dien thoai, mo trang chu, do luoi tu van va chup anh.
 * Chay: node scratch/kiem-tra-mobile-tuvan.cjs [chieuRong]
 */
const { spawn } = require('child_process');
const fs = require('fs');
const os = require('os');
const path = require('path');

const RONG = parseInt(process.argv[2] || '390', 10);
const CAO = 844;
const CHROME = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const PORT = 9347;
const UDD = path.join(os.tmpdir(), 'noxh-mb-' + Date.now());
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
    await gui('Runtime.enable', {}, sid);
    await gui('Emulation.setDeviceMetricsOverride', {
      width: RONG, height: CAO, deviceScaleFactor: 2, mobile: true,
    }, sid);
    await gui('Emulation.setTouchEmulationEnabled', { enabled: true }, sid);

    await gui('Page.navigate', { url: `http://${HOST}/` }, sid);
    await sleep(5000);

    const chay = async (expr) => {
      const r = await gui('Runtime.evaluate', {
        expression: `(function(){ try { return String(${expr}); } catch(e){ return 'LOI: ' + e.message; } })()`,
        returnByValue: true, awaitPromise: true,
      }, sid);
      if (r.exceptionDetails) return 'LOI CDP';
      return r.result.value;
    };

    console.log('=== DIEN THOAI ' + RONG + 'x' + CAO + ' ===');
    console.log('  be ngang khung nhin : ' + await chay('window.innerWidth') + 'px');
    console.log('  so the tu van        : ' + await chay(`document.querySelectorAll('.nx-advisors .nx-advisor').length`));

    console.log(await chay(`(function(){
      var l = document.querySelector('.nx-advisors');
      if (!l) return 'khong thay .nx-advisors';
      var cs = getComputedStyle(l);
      var r = l.getBoundingClientRect();
      var the = l.querySelector('.nx-advisor');
      var rt = the.getBoundingClientRect();
      // Dem so the tren hang dau: cung toa do top
      var tops = {};
      l.querySelectorAll('.nx-advisor').forEach(function(e){
        var y = Math.round(e.getBoundingClientRect().top);
        tops[y] = (tops[y] || 0) + 1;
      });
      var hang = Object.keys(tops).sort(function(a,b){return a-b;}).map(function(y){ return tops[y]; });
      // Chieu cao thuc cua noi dung trong the (de biet co bi tran khong)
      var ten = the.querySelector('.nx-advisor__ten');
      var nut = the.querySelector('.nx-advisor__nut');
      return [
        '  cot (grid-template-columns): ' + cs.gridTemplateColumns,
        '  be ngang luoi / the        : ' + Math.round(r.width) + 'px / ' + Math.round(rt.width) + 'px',
        '  so the moi hang            : ' + hang.join(' - '),
        '  tran ngang?                : ' + (l.scrollWidth > l.clientWidth + 1 ? 'CO (scrollWidth=' + l.scrollWidth + ')' : 'khong'),
        '  ten dai nhat tran ra ngoai? : ' + (function(){
            var xau = null;
            l.querySelectorAll('.nx-advisor__ten').forEach(function(e){
              if (e.scrollWidth > e.clientWidth + 1) { xau = e.textContent.trim(); }
            });
            return xau ? 'CO: ' + xau : 'khong';
        })(),
        '  cao the / cao ten / cao nut : ' + Math.round(rt.height) + ' / ' + Math.round(ten.getBoundingClientRect().height) + ' / ' + Math.round(nut.getBoundingClientRect().height) + 'px'
      ].join('\\n');
    })()`));

    /* Cuon toi khoi tu van va chup anh */
    await chay(`(function(){
      var l = document.querySelector('.nx-advisors');
      if (l) l.scrollIntoView({ block: 'center' });
    })()`);
    await sleep(900);

    const vitri = await chay(`(function(){
      var l = document.querySelector('.nx-advisors');
      var r = l.getBoundingClientRect();
      return JSON.stringify({ x: Math.max(0, Math.round(r.left - 8)), y: Math.max(0, Math.round(r.top + window.scrollY - 30)),
                              width: Math.min(${RONG}, Math.round(r.width + 16)), height: Math.min(1400, Math.round(r.height + 40)) });
    })()`);
    console.log('  vung chup: ' + vitri);

    const chup = async (tenAnh) => {
      const v = await chay(`(function(){
        var l = document.querySelector('.nx-advisors');
        var r = l.getBoundingClientRect();
        return JSON.stringify({ x: Math.max(0, Math.round(r.left - 8)), y: Math.max(0, Math.round(r.top + window.scrollY - 30)),
                                width: Math.min(${RONG}, Math.round(r.width + 16)), height: Math.min(1400, Math.round(r.height + 40)) });
      })()`);
      const s = await gui('Page.captureScreenshot', {
        format: 'png', clip: { ...JSON.parse(v), scale: 2 }, captureBeyondViewport: true,
      }, sid);
      const ra = 'D:\\sandbox\\' + tenAnh + '.png';
      fs.writeFileSync(ra, Buffer.from(s.data, 'base64'));
      console.log('  anh: ' + ra + ' (' + Math.round(fs.statSync(ra).size / 1024) + ' KB)');
    };

    // Chup TRUOC khi loc, neu khong thi anh bi ghi de boi trang thai da loc.
    await chup('tu-van-mobile-' + RONG);

    /* Thu loc theo khu vuc: chi con the khop, kiem tra luoi khong vo */
    const coLoc = await chay(`!!document.querySelector('[data-nx-loc-khu-vuc]')`);
    if (coLoc === 'true') {
      console.log('\n=== LOC THEO KHU VUC ===');
      console.log(await chay(`(function(){
        var o = document.querySelector('[data-nx-loc-khu-vuc]');
        var kv = o.options[1] ? o.options[1].textContent.trim() : '';
        o.value = o.options[1] ? o.options[1].value : '';
        o.dispatchEvent(new Event('change', { bubbles: true }));
        var l = document.querySelector('.nx-advisors');
        var hien = 0, an = 0, tops = {};
        l.querySelectorAll('.nx-advisor').forEach(function(e){
          if (e.hidden) { an++; } else {
            hien++;
            var y = Math.round(e.getBoundingClientRect().top);
            tops[y] = (tops[y] || 0) + 1;
          }
        });
        var hang = Object.keys(tops).sort(function(a,b){return a-b;}).map(function(y){ return tops[y]; });
        var cs = getComputedStyle(l);
        var theDau = l.querySelector('.nx-advisor:not([hidden])');
        return '  chon khu vuc "' + kv + '" -> hien ' + hien + ' the, an ' + an +
               ', so the moi hang: ' + hang.join(' - ') +
               ', cot: ' + cs.gridTemplateColumns +
               ', be ngang the: ' + (theDau ? Math.round(theDau.getBoundingClientRect().width) : '?') + 'px' +
               ', tran ngang? ' + (l.scrollWidth > l.clientWidth + 1 ? 'CO' : 'khong');
      })()`));
      await sleep(600);
    }

    /* Chup lai sau khi loc, neu chi con mot nguoi */
    if (coLoc === 'true') {
      await chup('tu-van-mobile-loc-' + RONG);
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
