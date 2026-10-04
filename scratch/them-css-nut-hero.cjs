/**
 * Vá CSS đã biên dịch: hai nút banner nằm chung một hàng trên điện thoại.
 *
 * Chạy: node scratch/them-css-nut-hero.cjs
 *
 * Vì sao phải vá tay: public/build/assets/app-*.css được commit sẵn trong dự án
 * (host không cần Node), mà máy này không có node_modules nên không chạy được
 * `npm run build`. Luật ở đây phải KHỚP với resources/css/components/_noxh-home.scss;
 * sửa file SCSS thì phải chạy lại script này.
 *
 * Ghi thêm vào CUỐI tệp: các luật gốc của .nx-hero__nut đứng trước nên luật thêm
 * sau thắng khi độ ưu tiên bằng nhau. Chạy lại nhiều lần không nhân đôi nhờ dấu DSH-THEM.
 */

const fs = require('fs');
const path = require('path');

const TEP = path.join(__dirname, '..', 'public', 'build', 'assets', 'app-fb4cf9c4.css');
const DAU = '/* DSH-THEM: nut banner 1 hang tren dien thoai */';

const THEM = `
${DAU}
@media (max-width: 768px){
.nx-hero__nut{flex-wrap:nowrap;gap:10px}
.nx-hero__nut .nx-btn{flex:1 1 0;min-width:0;padding:12px 10px;font-size:13px;gap:7px;line-height:1.25;text-align:center}
}
@media (max-width: 480px){
.nx-hero__nut{gap:8px}
.nx-hero__nut .nx-btn{padding:11px 7px;font-size:clamp(10px,3.1vw,12px);gap:5px;letter-spacing:0}
.nx-hero__nut .nx-btn svg{width:14px;height:14px}
.nx-hero__nut .nx-btn:first-child svg:last-child{display:none}
}
`;

if (!fs.existsSync(TEP)) {
  console.error('Khong thay ' + TEP);
  process.exit(1);
}

let css = fs.readFileSync(TEP, 'utf8');
if (css.includes(DAU)) {
  // Ghi de khoi cu bang khoi moi de sua luat ma khong phai xoa tay.
  const i = css.indexOf(DAU);
  css = css.slice(0, i).replace(/\s*$/, '\n');
  console.log('da go khoi cu');
}
fs.writeFileSync(TEP, css + THEM, 'utf8');
console.log('da them ' + THEM.trim().split('\n').length + ' dong vao ' + path.basename(TEP));
console.log('kich thuoc moi: ' + Math.round(fs.statSync(TEP).size / 1024) + ' KB');
