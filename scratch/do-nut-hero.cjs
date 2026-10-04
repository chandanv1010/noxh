/**
 * Do hai nut banner trang chu o nhieu be rong man hinh.
 *
 * Chay: node scratch/do-nut-hero.cjs
 *
 * Cau hoi: hai nut co nam CUNG MOT HANG khong, va co bi tran ra ngoai khung khong.
 * Cach do: so sanh `top` cua hai nut (lech > 2px nghia la khac hang) va so be
 * rong cong lai voi khung chua.
 */

const { spawn } = require('child_process');
const fs = require('fs');
const os = require('os');
const path = require('path');

const CHROME = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const PORT = 9362;
const UDD = path.join(os.tmpdir(), 'noxh-nut-' + Date.now());
const HOST = 'noxh.test';
const sleep = (ms) => new Promise((r) => setTimeout(r, ms));

const DS = process.argv.slice(2).map(Number).filter(Boolean);
const RONG = DS.length ? DS : [320, 360, 390, 430, 480, 600, 768, 1024, 1280];

(async () => {
  const chrome = spawn(CHROME, [
    '--headless=new', '--disable-gpu', '--disable-extensions', '--no-sandbox',
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

    const chay = async (expr) => {
      const r = await gui('Runtime.evaluate', {
        expression: `(function(){ try { return String(${expr}); } catch(e){ return 'LOI: ' + e.message; } })()`,
        returnByValue: true, awaitPromise: true,
      }, sid);
      if (r.exceptionDetails) return 'LOI CDP';
      return r.result.value;
    };

    console.log('rong | 1 hang? | dong A/B | co chu | rong chu A/B | rong nut A+B | tran?');
    console.log('-----+---------+----------+--------+--------------+--------------+------');

    for (const w of RONG) {
      await gui('Emulation.setDeviceMetricsOverride',
        { width: w, height: 900, deviceScaleFactor: 1, mobile: w <= 768 }, sid);
      await gui('Page.navigate', { url: `http://${HOST}/` }, sid);
      await sleep(2600);

      const kq = await chay(`(function(){
        var hop = document.querySelector('.nx-hero__nut');
        if (!hop) return 'KHONG THAY .nx-hero__nut';
        var nut = [].slice.call(hop.querySelectorAll('.nx-btn'));
        if (nut.length !== 2) return 'so nut = ' + nut.length;
        var a = nut[0].getBoundingClientRect(), b = nut[1].getBoundingClientRect();
        var h = hop.getBoundingClientRect();
        var chu = document.querySelector('.nx-hero__chu').getBoundingClientRect();

        // Dem so DONG chu thuc te trong mot nut: boc doan chu bang Range roi dem
        // so hinh chu nhat ma no chiem. 1 = mot dong, 2 = da xuong dong.
        var demDong = function(btn){
          var kq = 0;
          [].slice.call(btn.childNodes).forEach(function(n){
            if (n.nodeType !== 3 || !n.textContent.trim()) return;
            var r = document.createRange();
            r.selectNodeContents(n);
            kq = Math.max(kq, r.getClientRects().length);
          });
          return kq;
        };

        // Be rong phan chu (khong ke hinh va padding) de biet con thieu bao nhieu.
        var rongChu = function(btn){
          var w = 0;
          [].slice.call(btn.childNodes).forEach(function(n){
            if (n.nodeType !== 3 || !n.textContent.trim()) return;
            var r = document.createRange();
            r.selectNodeContents(n);
            var ds = r.getClientRects();
            for (var i = 0; i < ds.length; i++) w = Math.max(w, ds[i].width);
          });
          return Math.round(w);
        };

        var co = parseFloat(getComputedStyle(nut[1]).fontSize);
        return JSON.stringify({
          cungHang: Math.abs(a.top - b.top) < 2,
          rongA: Math.round(a.width), rongB: Math.round(b.width),
          traiA: Math.round(a.left), phaiB: Math.round(b.right),
          rongHop: Math.round(h.width), traiHop: Math.round(h.left), phaiHop: Math.round(h.right),
          caoNut: Math.round(a.height), caoNutB: Math.round(b.height),
          chuRong: Math.round(chu.width),
          caoHero: Math.round(document.querySelector('.nx-hero').getBoundingClientRect().height),
          dongA: demDong(nut[0]), dongB: demDong(nut[1]),
          coChu: co, rongChuA: rongChu(nut[0]), rongChuB: rongChu(nut[1]),
        });
      })()`);

      if (kq.startsWith('KHONG') || kq.startsWith('so nut')) { console.log(w + ' | ' + kq); continue; }
      const d = JSON.parse(kq);
      const tran = (d.traiA < d.traiHop - 0.5 || d.phaiB > d.phaiHop + 0.5) ? 'CO TRAN' : 'khong';
      const motDong = d.dongA <= 1 && d.dongB <= 1;
      console.log(
        String(w).padEnd(4) + ' | ' +
        (d.cungHang ? 'co      ' : 'KHONG   ') + ' | ' +
        (d.dongA + '/' + d.dongB).padEnd(8) + ' | ' +
        (d.coChu + 'px').padEnd(6) + ' | ' +
        (d.rongChuA + '/' + d.rongChuB).padEnd(12) + ' | ' +
        (d.rongA + '+' + d.rongB).padEnd(12) + ' | ' +
        tran + (motDong ? '' : '   <-- CHU CON XUONG DONG')
      );
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
