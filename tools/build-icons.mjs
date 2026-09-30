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

// Ban NET DAY (weight 700). Ban thiet ke ve mot vai hinh - ro nhat la ong
// nghe dien thoai - day han cac hinh con lai; dung ban 400 vao thi hinh
// mong manh han so voi ban ve.
const khoDay = resolve(goc, 'node_modules/@material-symbols/svg-700/rounded');
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
    'phone': 'call:day',      // ong nghe - ban ve dung net day han mac dinh
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
    'area': 'crop_free:net',     // khung vuong bon goc - dien tich can ho
    'units': 'workspaces:net',   // ba khoi xep hinh thap - so can ho
    'sort': 'swap_vert',

    // --- trang danh sach du an (project-cate-fix.jpg) ---
    'city': 'location_city:net',   // ba toa nha - "Du an toan quoc"
    'map-pins': 'pin_drop:net',    // ghim cam xuong ban do - "Tinh / Thanh pho"
    'group': 'group:net',          // hai nguoi - "Khach hang quan tam"
    'verified': 'verified:net',    // huy hieu co dau tich - "Thong tin kiem chung"
    'filter': 'filter_alt:net',    // pheu loc - nut "Ap dung bo loc"
    'map': 'map:net',              // ban do gap - khoi "Ban do du an"
    'news': 'newspaper:net',       // to bao - khoi "Tin tuc noi bat"
    'bulb-rays': 'emoji_objects',  // bong den co tia - khoi "Co du an phu hop"

    // --- trang chi tiet du an (product-detail-fix.jpg) ---
    'headset': 'support_agent:net',       // tai nghe - khoi "Tu van nhanh"
    'play': 'play_arrow',                 // tam giac phat - nut "Xem video du an"
    'photo': 'add_photo_alternate:net',   // khung anh co dau cong - "Xem anh thuc te"
    'update': 'update:net',               // dong ho co mui ten - "Xem cap nhat tien do"
    'directions': 'directions:net',       // bien chi duong - "Xem tren Google Maps"
    'floor-plan': 'foundation:net',       // mat bang - tab "Mat bang"
    'door': 'meeting_room:net',           // cua mo - the loai can ho

    // --- 10 o tren thanh tab cua trang chi tiet du an ---
    // Doi chieu tung hinh voi product-detail-fix.jpg (phong to 8 lan) roi
    // chon hinh Material trung khop nhat; hinh nao khong co san thi ve tay
    // o bang VE_TAY ben duoi.
    'tab-overview': 'dashboard',           // bon o vuong 2x2
    'tab-location': 'location_on:net',     // ghim ban do ve vien
    'tab-plan': 'apartment',               // toa nha nhieu cua so
    'tab-amenity': 'nx_diamond',           // hai hinh thoi long nhau - ve tay
    'tab-price': 'request_quote',          // to giay bao gia
    'tab-progress': 'autorenew',           // hai mui ten xoay vong
    'tab-legal': 'license:net',            // huy hieu co duoi ruy bang
    'tab-gallery': 'photo_camera_back',    // may anh co phong canh ben trong
    'tab-doc': 'description',              // to tai lieu co dong ke
    'tab-faq': 'help:net',                 // vong tron co dau hoi

    // --- trang Phong phap ly NOXH (plxh fix.jpg) ---
    'book': 'menu_book:net',         // sach mo co dong ke - "Kien thuc phap ly"
    'doc-line': 'description:net',   // to giay gap goc - "Ho tro ho so"
    'people': 'group:net',           // hai nguoi - "Tu van boi ..."
    'home-door': 'house:net',        // nha co cua so va cua ra vao - "Mua ban"
    'doc-pen': 'contract_edit:net',  // to giay co dong ke kem but - "Hop dong"
    'clipboard-check': 'assignment_turned_in:net', // bang kep co dau tich
    'sms': 'sms:net',                // bong chat ba cham - nut "Tu van mien phi"
    // Bon hinh o dai cuoi trang deu la MOT HINH KEM MOT HUY HIEU nho goc duoi
    // phai - ghep o bang GHEP ben duoi vi Material khong co san kieu nay.
    'trust-doc': 'nx_doc_shield',
    'trust-live': 'nx_clock_fast',
    'trust-chat': 'nx_chat_check',
    'trust-lock': 'nx_board_check',

    // --- trang Tin tuc (tin-tuc-fix.webp) ---
    //
    // Cot trai liet ke chuyen muc bang hinh VE VIEN mau xanh tham - de hinh
    // to dac vao thi cot nay nang han han ban ve.
    'news-all': 'nx_tin_tat_ca',      // khung tin co thanh dau va hai o - "Tat ca tin tuc"
    'scale-line': 'balance:net',      // can cong ly - "Chinh sach"
    'trend': 'monitoring:net',        // cot bieu do kem duong di len - "Thi truong"
    'bulb-line': 'nx_bong_den_tia',   // bong den VE VIEN co tia - "Kinh nghiem"
    'pin-line': 'location_on:net',    // ghim ban do - "Tin dia phuong"
    'calendar-line': 'calendar_month:net', // lich - dong ngay dang bai
    'eye-line': 'visibility:net',     // con mat - luot xem
    'link': 'link',                   // mat xich - nut chep duong dan bai viet

    'bullet': 'expand_circle_down',        // tron dac co mui nhon - gach dau dong the can ho
    'gallery': 'photo_library:net',        // chong anh - nut xem album
    'video': 'smart_display',              // man hinh co nut phat - nut xem video
};

// Hinh GHEP: mot hinh nen o goc tren trai kem mot huy hieu nho o goc duoi
// phai, dung nhu bon hinh dai cuoi trang phap ly.
//
// Giua hai hinh co mot KHE TRANG (dia mau trang ve truoc huy hieu) dung nhu
// ban ve, nen chi dat cac hinh nay tren nen trang.
const GHEP = {
    'nx_doc_shield': ['description', 'verified_user'],   // to giay + khien tich
    'nx_clock_fast': ['update', 'nx_vach_toc'],          // dong ho + vach toc do
    'nx_chat_check': ['chat_bubble', 'check_circle'],    // bong chat + tich tron
    'nx_board_check': ['assignment', 'check_circle'],    // bang kep + tich tron
};

// Hinh CHONG: giu NGUYEN mot hinh cua Material roi ve them vai net len tren.
// Khac GHEP o cho khong thu nho hinh nen va khong chen khe trang, nen dung
// duoc tren moi mau nen.
const CHONG = {
    'nx_bong_den_tia': ['emoji_objects:net', 'nx_tia_den'],  // bong den + tia sang
};

// Hinh khong co trong Material Symbols thi ve tay theo dung ban thiet ke.
// Luoi giong Material: viewBox "0 -960 960 960", toa do y am.
const VE_TAY = {
    // Hai hinh thoi long nhau, o tab "Tien ich". Vong ngoai la mot vanh
    // (dung fill-rule evenodd de khoet ruot), giua la mot hinh thoi dac.
    'nx_diamond': '<path fill-rule="evenodd" d="M480-872 872-480 480-88 88-480 480-872Zm0 116L204-480l276 276 276-276-276-276Z"/>'
        + '<path d="M480-616 616-480 480-344 344-480 480-616Z"/>',

    // Khung tin cua muc "Tat ca tin tuc": mot khung bo tron, trong co thanh
    // dau (o vuong dac kem hai dong ke) va hai o noi dung. Material khong co
    // hinh nao dung nhu vay nen ve lai theo ban ve.
    'nx_tin_tat_ca': '<rect x="118" y="-822" width="724" height="684" rx="86"'
        + ' fill="none" stroke="currentColor" stroke-width="62"/>'
        + '<rect x="196" y="-740" width="92" height="92" rx="26"/>'
        + '<rect x="330" y="-730" width="330" height="34" rx="17"/>'
        + '<rect x="330" y="-676" width="230" height="34" rx="17"/>'
        + '<rect x="205" y="-570" width="250" height="340" rx="44"'
        + ' fill="none" stroke="currentColor" stroke-width="54"/>'
        + '<rect x="505" y="-570" width="250" height="340" rx="44"'
        + ' fill="none" stroke="currentColor" stroke-width="54"/>',

    // Nam tia sang toa quanh nua tren bong den, dung cho hinh ghep
    // 'nx_bong_den_tia'. Tam bong den cua emoji_objects nam o (480,-600).
    'nx_tia_den': '<g stroke="currentColor" stroke-width="46" stroke-linecap="round">'
        + '<line x1="742" y1="-600" x2="812" y2="-600"/>'
        + '<line x1="665" y1="-785" x2="714" y2="-834"/>'
        + '<line x1="480" y1="-862" x2="480" y2="-932"/>'
        + '<line x1="295" y1="-785" x2="246" y2="-834"/>'
        + '<line x1="218" y1="-600" x2="148" y2="-600"/></g>',

    // Ba vach toc do ben trai dong ho "Cap nhat lien tuc". Day khong phai huy
    // hieu goc duoi phai nen ham ghep khong thu nho, khong chen khe trang.
    'nx_vach_toc': '<rect x="110" y="-620" width="140" height="54" rx="27"/>'
        + '<rect x="20" y="-507" width="230" height="54" rx="27"/>'
        + '<rect x="110" y="-394" width="140" height="54" rx="27"/>',
};

// Ten co the kem hau to ":day" - lay ban net day (weight 700). Va hau to ":net" - lay ban VE VIEN thay vi ban to dac. Ban
// thiet ke trang danh sach du an dung hinh net manh cho dai so lieu va bo
// loc, de hinh to dac vao thi nang han so voi ban ve.
function doc(ten) {
    if (GHEP[ten]) return ghep(...GHEP[ten]);
    if (CHONG[ten]) return chong(...CHONG[ten]);
    if (VE_TAY[ten]) return `<svg viewBox="0 -960 960 960">${VE_TAY[ten]}</svg>`;

    const day = ten.endsWith(':day');
    if (day) ten = ten.slice(0, -4);

    const net = ten.endsWith(':net');
    const goc = net ? ten.slice(0, -4) : ten;
    const thu = net ? [`${goc}.svg`] : [`${goc}-fill.svg`, `${goc}.svg`];
    const thuMuc = day ? khoDay : kho;

    for (const f of thu) {
        const p = resolve(thuMuc, f);
        if (existsSync(p)) return readFileSync(p, 'utf8');
    }

    throw new Error(`Khong tim thay icon "${goc}" trong ${thuMuc}`);
}

/** Ve `nen` nguyen ban roi dan them cac net cua `them` len tren. */
function chong(nen, them) {
    return `<svg viewBox="0 -960 960 960">${ruot(doc(nen))}${VE_TAY[them]}</svg>`;
}

/**
 * Dat `nen` thu nho o goc tren trai, `huy` thu nho o goc duoi phai, giua hai
 * hinh chen mot dia trang de con thay duong vien - dung nhu ban ve.
 *
 * Rieng vach toc do khong phai huy hieu: no ve san dung cho ben trai nen giu
 * nguyen toa do, chi day hinh nen sang phai cho du cho.
 */
function ghep(nen, huy) {
    const a = ruot(doc(`${nen}:net`));

    if (huy.startsWith('nx_')) {
        return `<svg viewBox="0 -960 960 960">`
            + `<g transform="translate(230,-102) scale(0.79)">${a}</g>`
            + `${ruot(doc(huy))}</svg>`;
    }

    return `<svg viewBox="0 -960 960 960">`
        + `<g transform="translate(0,-150) scale(0.84)">${a}</g>`
        + `<circle cx="700" cy="-262" r="272" fill="#fff"/>`
        + `<g transform="translate(450,-12) scale(0.52)">${ruot(doc(`${huy}:net`))}</g>`
        + `</svg>`;
}

function ruot(svg) {
    const m = svg.match(/<svg[^>]*>([\s\S]*?)<\/svg>/);
    if (!m) throw new Error('File SVG khong doc duoc');
    // Bo khoang trang thua, giu nguyen du lieu duong ve.
    return m[1].replace(/>\s+</g, '><').trim();
}

const dong = Object.entries(BANG).map(([ta, ho]) => {
    const d = ruot(doc(ho)).replace(/'/g, "\\'");
    return `        '${ta}' => '${d}', // ${ho.replace(':net', ' (net)')}`;
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
