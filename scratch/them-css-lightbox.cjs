/**
 * Chen CSS cua lightbox anh du an vao tep CSS da build, VA doi ten tep de khong
 * bi cache cu.
 *
 * Chay: node scratch/them-css-lightbox.cjs
 *
 * VI SAO PHAI LAM THE NAY:
 * CSS cua frontend duoc Vite build tu resources/css/*.scss ra
 * public/build/assets/app-<hash>.css, va tep da build duoc DUA VAO KHO MA
 * (may chu khong chay `npm run build`). May nay khong co node_modules nen cung
 * khong build lai duoc. Vi vay phai chen tay - giong cac ban va truoc do.
 *
 * VI SAO PHAI DOI TEN TEP:
 * Cloudflare giu ban cache cua /build/* trong 12 gio, ma ten tep thi khong doi
 * khi ta sua noi dung ben trong. Lan truoc, sua CSS xong ma web ngoai van chay
 * ban cu, phai vao Cloudflare xoa cache thu cong moi thay. Doi ten tep lam
 * Cloudflare khong co ban cache nao cho dia chi moi, nen `git pull` la xong.
 * Tep cu duoc GIU LAI chu khong xoa: trinh duyet nao con giu HTML cu (tro vao
 * ten cu) van tai duoc, khong bi trang mat CSS.
 *
 * QUY UOC: khoi chen tay bat dau bang `/* DSH-THEM: ... *\/` va duoc dat NGAY
 * TRUOC khoi cua them-css-nut-hero.cjs. Ly do: script do TRUNCATE tep tai marker
 * cua no roi ghi lai, nen moi thu nam SAU marker do se bi xoa.
 *
 * Chay lai duoc nhieu lan: noi dung khong doi thi ten tep cung khong doi.
 */

const fs = require('fs');
const path = require('path');
const crypto = require('crypto');

const GOC = path.join(__dirname, '..');
const MANIFEST = path.join(GOC, 'public', 'build', 'manifest.json');
const KHOA = 'resources/css/app.scss';
const MARKER = '/* DSH-THEM: lightbox anh du an */';
const MARKER_NEO = '/* DSH-THEM: nut banner 1 hang tren dien thoai */';

// Viet tay tu resources/css/components/_noxh-page.scss. Sua SCSS thi phai sua
// ca o day, VA nho rang ban that su chay tren may chu la tep nay.
const CSS = `${MARKER}
.nx-lb{position:fixed;inset:0;z-index:10050;display:flex;align-items:center;justify-content:center;background:rgba(6,12,28,.93);padding:56px 68px}
.nx-lb[hidden]{display:none}
@media (max-width:768px){.nx-lb{padding:52px 8px 64px}}
.nx-lb__anh{max-width:100%;max-height:100%;display:block;border-radius:6px;background:#0b1226;box-shadow:0 24px 70px rgba(0,0,0,.55);opacity:0;transition:opacity .18s}
.nx-lb__anh.is-xong{opacity:1}
.nx-lb__nut{position:absolute;display:inline-flex;align-items:center;justify-content:center;width:44px;height:44px;border:0;border-radius:50%;background:rgba(255,255,255,.14);color:#fff;cursor:pointer;transition:background .15s}
.nx-lb__nut:hover{background:rgba(255,255,255,.26)}
.nx-lb__nut:focus-visible{outline:2px solid #fff;outline-offset:2px}
.nx-lb__dong{top:14px;right:14px}
.nx-lb__truoc,.nx-lb__sau{top:50%;margin-top:-22px}
.nx-lb__truoc{left:12px}
.nx-lb__sau{right:12px}
.nx-lb__dem{position:absolute;bottom:16px;left:50%;transform:translateX(-50%);color:rgba(255,255,255,.82);font-size:13px;font-weight:600;letter-spacing:.04em;background:rgba(0,0,0,.35);padding:4px 12px;border-radius:999px;white-space:nowrap}
.nx-lb__goi{cursor:zoom-in}
.nx-pd-anh__to{position:absolute;right:12px;bottom:12px;display:inline-flex;align-items:center;justify-content:center;width:40px;height:40px;border:0;border-radius:50%;background:rgba(6,12,28,.62);color:#fff;cursor:zoom-in;transition:background .15s}
.nx-pd-anh__to:hover{background:rgba(6,12,28,.85)}
.nx-pd-anh__to:focus-visible{outline:2px solid #fff;outline-offset:2px}
`;

const manifest = JSON.parse(fs.readFileSync(MANIFEST, 'utf8'));
const dichCu = manifest[KHOA].file;
const tepCu = path.join(GOC, 'public', 'build', dichCu);

let chu = fs.readFileSync(tepCu, 'utf8');
const truoc = chu.length;

// Go khoi cu neu co.
const vtCu = chu.indexOf(MARKER);
if (vtCu >= 0) {
  const vtNeo = chu.indexOf(MARKER_NEO, vtCu);
  chu = chu.slice(0, vtCu) + (vtNeo >= 0 ? chu.slice(vtNeo) : '');
  console.log('  da go khoi cu');
}

// Chen ngay truoc khoi neo, hoac vao cuoi tep neu khong tim thay neo.
const vtNeo = chu.indexOf(MARKER_NEO);
if (vtNeo >= 0) {
  chu = chu.slice(0, vtNeo) + CSS + chu.slice(vtNeo);
  console.log('  chen truoc khoi neo (nut banner)');
} else {
  chu = chu + '\n' + CSS;
  console.log('  KHONG thay khoi neo -> chen vao cuoi tep');
}

// Ten tep moi lay tu chinh noi dung, dung kieu Vite: app-<8 ky tu bam>.css
const bam = crypto.createHash('sha256').update(chu, 'utf8').digest('hex').slice(0, 8);
const dichMoi = `assets/app-${bam}.css`;

fs.writeFileSync(path.join(GOC, 'public', 'build', dichMoi), chu, 'utf8');
manifest[KHOA].file = dichMoi;
fs.writeFileSync(MANIFEST, JSON.stringify(manifest, null, 2) + '\n', 'utf8');

console.log('  noi dung  : ' + truoc + ' -> ' + chu.length + ' ky tu');
console.log('  tep CSS   : ' + dichCu + ' -> ' + dichMoi);
console.log('  manifest  : da cap nhat ' + KHOA);
if (dichCu !== dichMoi) {
  console.log('  (giu lai ' + dichCu + ' cho trinh duyet con cache HTML cu)');
}

// Kiem tra lai cho chac.
const lai = fs.readFileSync(path.join(GOC, 'public', 'build', dichMoi), 'utf8');
const kt = [
  ['co khoi lightbox', lai.includes(MARKER)],
  ['co .nx-lb', lai.includes('.nx-lb{')],
  ['co .nx-pd-anh__to', lai.includes('.nx-pd-anh__to{')],
  ['[hidden] van con', lai.includes('[hidden]{display:none!important}')],
  ['manifest tro dung tep moi', JSON.parse(fs.readFileSync(MANIFEST, 'utf8'))[KHOA].file === dichMoi],
  ['tep moi co that', fs.existsSync(path.join(GOC, 'public', 'build', dichMoi))],
];
kt.forEach(([ten, dat]) => console.log('  ' + (dat ? 'ok   ' : 'HONG ') + ten));
if (kt.some(([, dat]) => !dat)) process.exitCode = 1;
