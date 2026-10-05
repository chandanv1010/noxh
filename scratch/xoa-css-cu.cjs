/**
 * Gỡ luật CSS cũ còn sót trong bản đã biên dịch.
 *
 * Chạy: node scratch/xoa-css-cu.cjs
 *
 * Vì sao cần: public/build/assets/app-fb4cf9c4.css là tệp ĐƯỢC COMMIT SẴN và
 * còn được vá tay (xem them-css-hidden.cjs, them-css-nut-hero.cjs). Nó đã bị
 * lệch khỏi nguồn SCSS: luật `.nx-advisors--mot` không còn trong
 * resources/css/components/_noxh-advisor.scss nữa, nhưng vẫn nằm trong bản đã
 * biên dịch và vì thế VẪN CÓ TÁC DỤNG khi chạy.
 *
 * Hậu quả thật: lọc tư vấn viên còn một người thì thẻ đó bị kéo rộng hết hàng,
 * trong khi ý muốn là giữ đúng bề ngang một cột.
 *
 * Script này chỉ xoá, không thêm. Chạy lại nhiều lần vô hại.
 */

const fs = require('fs');
const path = require('path');

const TEP = path.join(__dirname, '..', 'public', 'build', 'assets', 'app-fb4cf9c4.css');

// Các luật cũ cần gỡ. Ghi nguyên văn để không xoá nhầm luật khác.
const CAN_XOA = [
    '.nx-advisors--mot{grid-template-columns:minmax(0,1fr)}',
];

if (!fs.existsSync(TEP)) {
    console.error('Khong thay ' + TEP);
    process.exit(1);
}

let css = fs.readFileSync(TEP, 'utf8');
let tongXoa = 0;

for (const luat of CAN_XOA) {
    const soLan = css.split(luat).length - 1;
    if (!soLan) {
        console.log('  (khong con) ' + luat);
        continue;
    }
    css = css.split(luat).join('');
    console.log('  da xoa ' + soLan + ' lan: ' + luat);
    tongXoa += soLan;
}

if (tongXoa) {
    fs.writeFileSync(TEP, css, 'utf8');
    console.log('\nDa xoa ' + tongXoa + ' luat. Kich thuoc moi: ' +
        Math.round(fs.statSync(TEP).size / 1024) + ' KB');
    console.log('Kiem tra lai: node scratch/kiem-tra-loc-khu-vuc.cjs "Bắc Ninh"');
} else {
    console.log('\nKhong co gi de xoa.');
}
