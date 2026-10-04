/**
 * Thay khoi .nx-advisors trong CSS da bien dich (public/build) bang ban 2 cot
 * tren dien thoai. Chay: node scratch/sua-css-build.cjs
 */
const fs = require('fs');

const F = 'D:/sandbox/noxh/public/build/assets/app-fb4cf9c4.css';
let css = fs.readFileSync(F, 'utf8');

/* ---- 1. Anh banner: neo vao mep TREN thay vi center right / center 18% ---- */
const truoc1 = '.nx-hero__bg img{width:100%;height:100%;object-fit:cover;object-position:center right}';
const sau1 = '.nx-hero__bg img{width:100%;height:100%;object-fit:cover;object-position:center top}';

if (css.indexOf(truoc1) !== -1) {
  css = css.replace(truoc1, sau1);
  console.log('da doi object-position (mac dinh) -> center top');
} else {
  console.log('KHONG thay quy tac object-position mac dinh - kiem tra lai');
}

const truoc2 = '.nx-hero__bg img{object-position:center 18%}';
const sau2 = '.nx-hero__bg img{object-position:center top}';

if (css.indexOf(truoc2) !== -1) {
  css = css.replace(truoc2, sau2);
  console.log('da doi object-position (dien thoai) -> center top');
} else {
  console.log('KHONG thay quy tac object-position dien thoai - kiem tra lai');
}

fs.writeFileSync(F, css);
