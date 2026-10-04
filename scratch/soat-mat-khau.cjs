/**
 * Soat xem co mat khau nao bi viet thang trong ma nguon khong.
 *
 * Chay: node scratch/soat-mat-khau.cjs
 *
 * Vi sao can: thu muc scratch/ nam trong git va kho ma nguon la cong khai. Da
 * mot lan 12 cho trong 8 tep viet thang mat khau tam (xem scratch/_khoa.php).
 *
 * Script nay KHONG chua mat khau nao: no do mau, khong do gia tri cu the. Nho vay
 * no khong tro thanh cho ro ri moi.
 *
 * Thoat bang ma 1 neu tim thay, de cam vao hook truoc khi commit.
 */

const fs = require('fs');
const path = require('path');

const GOC = path.join(__dirname, '..');
const THU_MUC = ['scratch', 'tools', 'database/seeders'];
const DUOI = ['.php', '.cjs', '.js', '.mjs', '.md', '.ps1', '.py'];

// Cac mau dang nghi ngo. Khong liet ke mat khau cu the.
const MAU = [
  // 'password' => 'chuoi that',  /  "password" => "chuoi that"
  { ten: 'gan chuoi thang cho password', re: /['"](?:password|pass|matKhau|mat_khau|pwd)['"]\s*=>\s*['"][^'"$]{4,}['"]/gi },
  // $password = 'chuoi that';
  { ten: 'gan chuoi thang cho bien mat khau', re: /\$(?:password|pass|matKhau|mat_khau|pwd|newPassword)\s*=\s*['"][^'"$]{4,}['"]/gi },
  // password: 'chuoi that'  (JS)
  { ten: 'gan chuoi thang cho password (JS)', re: /\b(?:password|matKhau)\s*:\s*['"][^'"$]{4,}['"]/gi },
  // Khoa bi mat noi tieng
  { ten: 'khoa bi mat', re: /\b(?:sk-[A-Za-z0-9]{20,}|ghp_[A-Za-z0-9]{20,}|AKIA[0-9A-Z]{12,})\b/g },
];

let soCho = 0;

function quet(duong) {
  const ten = path.relative(GOC, duong).replace(/\\/g, '/');
  if (ten === 'scratch/soat-mat-khau.cjs') return;      // chinh no
  const noiDung = fs.readFileSync(duong, 'utf8');
  const dong = noiDung.split(/\r?\n/);

  dong.forEach((chu, i) => {
    for (const m of MAU) {
      m.re.lastIndex = 0;
      const khop = chu.match(m.re);
      if (!khop) continue;
      // Bo qua dong chi la chu thich giai thich cach dat bien moi truong.
      if (/getenv|process\.env|\$env:/.test(chu)) continue;
      soCho++;
      console.log('  ' + ten + ':' + (i + 1) + '  [' + m.ten + ']');
      console.log('      ' + chu.trim().slice(0, 110));
    }
  });
}

function duyet(thuMuc) {
  for (const e of fs.readdirSync(thuMuc, { withFileTypes: true })) {
    const duong = path.join(thuMuc, e.name);
    if (e.isDirectory()) {
      if (['node_modules', 'vendor', '.git', 'storage'].includes(e.name)) continue;
      duyet(duong);
    } else if (DUOI.includes(path.extname(e.name))) {
      quet(duong);
    }
  }
}

console.log('=== Soat mat khau viet thang trong ma nguon ===');
for (const t of THU_MUC) {
  const duong = path.join(GOC, t);
  if (fs.existsSync(duong)) duyet(duong);
}

console.log('');
if (soCho) {
  console.log('THAY ' + soCho + ' cho nghi ngo. Xem lai truoc khi commit/push.');
  process.exit(1);
}
console.log('Khong thay mat khau nao viet thang.');
