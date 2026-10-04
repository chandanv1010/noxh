<?php
require_once __DIR__ . '/_khoa.php';
/**
 * Chay dung doan JS lay tu trang da render tren mot DOM gia de kiem tra
 * cascade Tinh -> Phuong/Xa co that su hoat dong khong.
 *
 * Chay: php scratch/kiem-tra-js-cascade.php
 */

$base = 'http://127.0.0.1';
$host = 'noxh.test';
$jar  = sys_get_temp_dir() . '/noxh-js.txt';

function rq(string $m, string $p, array $post = [], bool $follow = true): array
{
    global $base, $host, $jar;
    $ch = curl_init($base . $p);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => 1, CURLOPT_FOLLOWLOCATION => $follow,
        CURLOPT_TIMEOUT => 90, CURLOPT_HTTPHEADER => ['Host: ' . $host],
        CURLOPT_COOKIEJAR => $jar, CURLOPT_COOKIEFILE => $jar, CURLOPT_HEADER => 1,
    ]);
    if ($m === 'POST') {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
    }
    $raw = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $hl = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);
    return ['code' => $code, 'b' => substr((string) $raw, $hl)];
}

function token(string $html): string
{
    preg_match('~name="_token"\s+value="([^"]+)"~', $html, $m);
    return $m[1] ?? '';
}

@unlink($jar);
rq('POST', '/login', ['_token' => token(rq('GET', '/admin')['b']), 'email' => 'tuannc.dev@gmail.com', 'password' => nx_khoa_admin()]);

/* Lay form SUA (co san tinh + phuong/xa) de kiem tra ca truong hop nap san */
$html = rq('GET', '/product/76/edit')['b'];

/* Rut dung doan script chua cascade */
if (! preg_match('~\(function \(\) \{\s*\n\s*var oTinh = document\.getElementById\(.nx-tinh.\).*?\}\)\(\);~s', $html, $m)) {
    echo "KHONG rut duoc doan JS cascade tu trang.\n";
    exit(1);
}
$js = $m[0];
echo "=== da rut duoc " . strlen($js) . " byte JS tu trang da render ===\n\n";

file_put_contents(sys_get_temp_dir() . '/noxh-cascade.js', $js);
$harness = sys_get_temp_dir() . '/noxh-harness.js';
file_put_contents($harness, <<<'JS'
// DOM gia: du cho doan JS tren chay
const nodes = {
  'nx-tinh': { value: '24', disabled: false, innerHTML: '', _h: {}, addEventListener(ev, fn) { this._h[ev] = fn; },
               querySelector() { return null; } },
  'nx-xa':   { value: '', disabled: true, innerHTML: '', _opts: [], _h: {},
               addEventListener(ev, fn) { this._h[ev] = fn; },
               querySelector(sel) {
                 // Do option tu innerHTML nhu DOM that, de kiem tra ca buoc
                 // "chon san phuong/xa cua du an dang sua".
                 this._opts = (this.innerHTML.match(/<option value="([^"]*)"/g) || [])
                   .map(s => s.replace(/.*value="([^"]*)"/, '$1'));
                 const m = sel.match(/option\[value="(.*)"\]/);
                 return (m && this._opts.includes(m[1])) ? {} : null;
               } },
};
global.document = { getElementById: (id) => nodes[id] || null };

let soLanGoi = 0;
const daGoi = [];
global.fetch = (url) => {
  soLanGoi++;
  const ma = url.split('/').pop();
  daGoi.push(ma);
  // Tra ve nhu endpoint that
  const duLieu = { '24': [{ code: '07210', name: 'Phường Bắc Giang' }, { code: '07222', name: 'Phường Đa Mai' }],
                   '01': [{ code: '00004', name: 'Phường Ba Đình' }] }[ma] || [];
  return Promise.resolve({ ok: true, json: () => Promise.resolve(duLieu) });
};

const oXa = nodes['nx-xa'];
function demOption() { return (oXa.innerHTML.match(/<option/g) || []).length; }

/*__JS_TU_TRANG__*/

(async () => {
  // Bat dau: form sua du an 76 -> o tinh da co gia tri '24', JS phai tu nap
  const sauKhiNap = [];
  await new Promise(r => setTimeout(r, 30));
  sauKhiNap.push(['tu nap khi mo form', soLanGoi, daGoi.slice(), demOption(), oXa.disabled, oXa.value]);

  // Doi sang tinh khac
  nodes['nx-tinh'].value = '01';
  nodes['nx-tinh']._h.change();
  await new Promise(r => setTimeout(r, 30));
  sauKhiNap.push(['sau khi doi sang 01', soLanGoi, daGoi.slice(), demOption(), oXa.disabled, oXa.value]);

  // Bo chon tinh
  nodes['nx-tinh'].value = '';
  nodes['nx-tinh']._h.change();
  await new Promise(r => setTimeout(r, 30));
  sauKhiNap.push(['sau khi bo chon tinh', soLanGoi, daGoi.slice(), demOption(), oXa.disabled, oXa.value]);

  // Tinh khong co phuong/xa nao (endpoint tra mang rong)
  nodes['nx-tinh'].value = '99';
  nodes['nx-tinh']._h.change();
  await new Promise(r => setTimeout(r, 30));
  sauKhiNap.push(['tinh khong co phuong/xa', soLanGoi, daGoi.slice(), demOption(), oXa.disabled, oXa.value]);

  console.log(JSON.stringify(sauKhiNap, null, 1));
})();
JS);

echo "=== chay JS that tren DOM gia (can Node) ===\n";
$node = 'C:\laragon\bin\nodejs\node-v18';
$nodeExe = null;
foreach (['C:\Program Files\nodejs\node.exe', "$node\node.exe"] as $p) {
    if (is_file($p)) { $nodeExe = $p; break; }
}
if (! $nodeExe) {
    $which = trim((string) shell_exec('where node 2>NUL'));
    $nodeExe = $which ? trim(explode("\n", $which)[0]) : null;
}
if (! $nodeExe) {
    echo "  khong tim thay node.exe - bo qua buoc nay\n";
    exit(0);
}
echo "  node: $nodeExe\n\n";
// Ghep: DOM gia + JS rut tu trang (JS phai nam SAU phan dung DOM gia)
$full = str_replace('/*__JS_TU_TRANG__*/', $js, file_get_contents($harness));
file_put_contents($harness, $full);

passthru('"' . $nodeExe . '" ' . escapeshellarg($harness));
