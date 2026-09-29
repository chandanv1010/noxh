// =============================================================================
// Sinh file resources/views/frontend/noxh/component/icon.blade.php
//
// Bo icon cua giao dien la Material Symbols Rounded ban TO DAC (fill), lay tu
// goi npm @material-symbols/svg-400. Ban thiet ke home-fix.jpg do ChatGPT ve
// ra nen khong co file goc; doi chieu tung hinh thi Material Symbols Rounded
// la bo giong nhat (net day, goc bo tron, luoi 24).
//
// Hinh duoc DAN THANG vao Blade chu khong tai font luc chay trang: chi dung
// khoang 40 hinh, keo ca bo font ve vua nang vua phu thuoc mang, va neu font
// ve cham thi nguoi dung nhin thay chu "location_on" hien ra giua trang.
//
// Chay lai moi khi them icon:
//     node tools/build-icons.mjs
// =============================================================================
import { readFileSync, writeFileSync, existsSync } from 'node:fs';
import { resolve, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';

const goc = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const kho = resolve(goc, 'node_modules/@material-symbols/svg-400/rounded');
const dich = resolve(goc, 'resources/views/frontend/noxh/component/icon.blade.php');

// Ten trong ma nguon => ten icon cua Material Symbols.
// Giu nguyen cac ten cu (pin, phone, clipboard...) de cac trang da viet roi
// khong phai sua theo.
const BANG = {
    // --- dieu huong, thao tac ---
    'search': 'search',
    'menu': 'menu',
    'close': 'close',
    'arrow-right': 'arrow_forward',
    'arrow-left': 'arrow_back',
    'chevron-down': 'keyboard_arrow_down',
    'chevron-right': 'keyboard_arrow_right',
    'download': 'download',
    'eye': 'visibility',

    // --- lien he ---
    'phone': 'call',
    'mail': 'mail',
    'send': 'send',
    'chat': 'chat_bubble',
    'globe': 'public',
    'pin': 'location_on',

    // --- so lieu banner ---
    'building': 'apartment',
    'users': 'groups',
    'user': 'person',
    'shield-check': 'verified_user',
    'shield': 'shield',

    // --- khoi thong tin huu ich ---
    'scale': 'balance',          // can cong ly - Chinh sach & phap luat
    'clipboard': 'assignment',   // bang kep co dong ke - Huong dan ho so
    'coins': 'database',         // chong dong xu - Tai chinh & vay von
    'bulb': 'lightbulb',
    'question': 'help',
    'file-download': 'download',

    // --- tien, ngan hang ---
    'money': 'payments',
    'bank': 'account_balance',
    'calculator': 'calculate',
    'piggy': 'savings',
    'chart': 'bar_chart',

    // --- tai lieu ---
    'file': 'description',
    'file-text': 'description',
    'folder': 'folder',
    'home': 'home',
    'house': 'house',          // nha co cua so - dau hieu thuong hieu NOXH

    // --- trang thai ---
    'check': 'check',
    'check-circle': 'check_circle',
    'clock': 'alarm',            // dong ho bao thuc - dai "Nhanh chong"
    'schedule': 'schedule',
    'lock': 'lock',
    'info': 'info',
    'warning': 'warning',

    // --- the du an ---
    'grid': 'grid_view',         // so can ho
    'calendar': 'event',         // quy ban giao
    'ruler': 'straighten',
    'layers': 'layers',
};

function doc(ten) {
    for (const f of [`${ten}-fill.svg`, `${ten}.svg`]) {
        const p = resolve(kho, f);
        if (existsSync(p)) return readFileSync(p, 'utf8');
    }
    throw new Error(`Khong tim thay icon "${ten}" trong ${kho}`);
}

function ruot(svg) {
    const m = svg.match(/<svg[^>]*>([\s\S]*?)<\/svg>/);
    if (!m) throw new Error('File SVG khong doc duoc');
    // Bo khoang trang thua, giu nguyen du lieu duong ve.
    return m[1].replace(/>\s+</g, '><').trim();
}

const dong = Object.entries(BANG).map(([ta, ho]) => {
    const d = ruot(doc(ho)).replace(/'/g, "\\'");
    return `        '${ta}' => '${d}', // ${ho}`;
});

const noiDung = `{{--
    Bo icon dung chung cua frontend NOXH.

    SINH TU DONG - dung sua tay. Them icon thi mo tools/build-icons.mjs, khai
    bao them mot dong trong bang BANG roi chay:

        node tools/build-icons.mjs

    Nguon: Material Symbols Rounded (ban to dac), goi npm
    @material-symbols/svg-400, giay phep Apache-2.0. Duong ve duoc dan thang
    vao day de trang khong phai tai font icon luc chay.

    Luoi cua Material Symbols la viewBox "0 -960 960 960" chu khong phai
    "0 0 24 24", va hinh la mang TO DAC nen to bang fill chu khong phai stroke.

    Cach dung:
        @include('frontend.noxh.component.icon', ['name' => 'pin'])
        @include('frontend.noxh.component.icon', ['name' => 'pin', 'size' => 20])
--}}
@php
    $size = $size ?? 16;
    $duong = [
${dong.join('\n')}
    ];
@endphp
@if(isset($duong[$name]))
    <svg xmlns="http://www.w3.org/2000/svg" class="nx-ico" width="{{ $size }}" height="{{ $size }}"
         viewBox="0 -960 960 960" fill="currentColor"
         aria-hidden="true" focusable="false">{!! $duong[$name] !!}</svg>
@endif
`;

writeFileSync(dich, noiDung, 'utf8');
console.log(`Da ghi ${Object.keys(BANG).length} icon vao ${dich}`);
