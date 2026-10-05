/**
 * Vá CSS đã biên dịch: thuộc tính [hidden] phải luôn ẩn được phần tử.
 *
 * Chạy: node scratch/them-css-hidden.cjs
 *
 * Vì sao phải vá tay: public/build/assets/app-*.css được commit sẵn trong dự án
 * (host không cần Node), mà máy này không có node_modules nên không chạy được
 * `npm run build`. Luật ở đây phải KHỚP với resources/css/components/_noxh.scss.
 *
 * CHÈN TRƯỚC khối của scratch/them-css-nut-hero.cjs, không phải sau.
 * Script kia gỡ khối cũ bằng cách cắt tệp tại dấu của chính nó, nên mọi thứ
 * NẰM SAU dấu đó sẽ bị xoá khi chạy lại. Chèn trước thì hai script không giẫm
 * lên nhau. Chạy lại nhiều lần cũng không nhân đôi: script tự gỡ khối của mình
 * trước khi chèn.
 */

const fs = require('fs');
const path = require('path');

const TEP = path.join(__dirname, '..', 'public', 'build', 'assets', 'app-fb4cf9c4.css');
const DAU = '/* DSH-THEM: [hidden] phai luon an duoc */';
const DAU_NUT = '/* DSH-THEM: nut banner 1 hang tren dien thoai */';

if (!fs.existsSync(TEP)) {
  console.error('Khong thay ' + TEP);
  process.exit(1);
}

let css = fs.readFileSync(TEP, 'utf8');

// 1. Gỡ khối cũ của chính mình (nó kết thúc ngay trước dấu của nut-hero, hoặc ở cuối tệp).
const iCu = css.indexOf(DAU);
if (iCu >= 0) {
  const iNut = css.indexOf(DAU_NUT);
  const ketThuc = iNut > iCu ? iNut : css.length;
  css = (css.slice(0, iCu) + css.slice(ketThuc)).replace(/\s*$/, '\n');
  console.log('da go khoi cu');
}

// 2. Chèn ngay trước khối nut-hero, hoặc xuống cuối nếu chưa có khối đó.
const khoi = DAU + '\n[hidden]{display:none!important}\n\n';
const iNut = css.indexOf(DAU_NUT);
if (iNut >= 0) {
  css = css.slice(0, iNut) + khoi + css.slice(iNut);
  console.log('da chen truoc khoi nut-hero');
} else {
  css = css.replace(/\s*$/, '\n') + '\n' + khoi;
  console.log('da them vao cuoi tep');
}

fs.writeFileSync(TEP, css, 'utf8');
console.log('kich thuoc moi: ' + Math.round(fs.statSync(TEP).size / 1024) + ' KB');
