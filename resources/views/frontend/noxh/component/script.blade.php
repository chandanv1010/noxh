<script>
// =============================================================================
// JS dung chung cua NOXH.vn. Viet thuan, khong keo them thu vien nao.
// =============================================================================

// Doi so tien ra chu, doi xung voi ham tien_viet() ben PHP. Hai ban phai cho
// ra cung mot chuoi: cung mot con tien co the duoc in san tu may chu (bang
// gia du an) hoac tinh ngay tren trinh duyet (may tinh khoan vay).
window.NX = window.NX || {};

window.NX.tienViet = function (dong) {
    if (dong === null || dong === undefined || !isFinite(dong)) return '—';

    var am = dong < 0;
    dong = Math.abs(dong);

    var chia = 1, donVi = 'đồng', soLe = 0;

    if (dong >= 1e9) { chia = 1e9; donVi = 'tỷ'; soLe = 2; }
    else if (dong >= 1e6) { chia = 1e6; donVi = 'triệu'; soLe = 1; }
    else if (dong >= 1e3) { chia = 1e3; donVi = 'nghìn'; soLe = 0; }

    var so = new Intl.NumberFormat('vi-VN', {
        minimumFractionDigits: soLe,
        maximumFractionDigits: soLe,
    }).format(dong / chia);

    // Bo phan thap phan bang 0. Chi lam khi thuc su co dau phay, khong thi
    // 850.000 se thanh "85 nghin".
    if (so.indexOf(',') >= 0) {
        so = so.replace(/0+$/, '').replace(/,$/, '');
    }

    return (am ? '-' : '') + so + ' ' + donVi;
};

// Nhieu o nhap tren trang tai chinh dung don vi TRIEU cho de go.
window.NX.tienTuTrieu = function (trieu) {
    return window.NX.tienViet(trieu * 1e6);
};

(function () {
    // --- Menu tren man hinh hep ---------------------------------------------
    var nut = document.querySelector('[data-nx-nav-toggle]');
    var nav = document.querySelector('[data-nx-nav]');

    if (nut && nav) {
        nut.addEventListener('click', function () {
            var mo = nav.classList.toggle('is-open');
            nut.setAttribute('aria-expanded', mo ? 'true' : 'false');
        });
    }

    // --- Dem so tu 0 len gia tri that ---------------------------------------
    //
    // Gia tri quan tri nhap co the kem ky tu: "120+", "15.250+", "100%". Tach
    // rieng phan so de dem, khung cuoi tra ve DUNG chuoi ban dau nen khong tu
    // dinh dang lai.
    var cacO = document.querySelectorAll('[data-nx-dem]');

    if (cacO.length && !(window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches)) {
        var tach = function (chuoi) {
            var khop = String(chuoi).match(/^(\D*)([\d.,]+)(.*)$/);
            if (!khop) return null;
            var so = parseFloat(khop[2].replace(/[.,]/g, ''));
            return isNaN(so) ? null : { truoc: khop[1], so: so, sau: khop[3] };
        };

        var dem = function (o) {
            var goc = o.getAttribute('data-nx-dem');
            var phan = tach(goc);
            if (!phan) return;

            var batDau = null;

            var buoc = function (nhip) {
                if (batDau === null) batDau = nhip;
                var tien = Math.min((nhip - batDau) / 1400, 1);

                if (tien >= 1) {
                    o.textContent = goc;
                    return;
                }

                var muot = 1 - Math.pow(1 - tien, 3);
                o.textContent = phan.truoc + Math.round(phan.so * muot).toLocaleString('vi-VN') + phan.sau;
                requestAnimationFrame(buoc);
            };

            o.textContent = phan.truoc + '0' + phan.sau;
            requestAnimationFrame(buoc);
        };

        if ('IntersectionObserver' in window) {
            var theoDoi = new IntersectionObserver(function (ds) {
                ds.forEach(function (muc) {
                    if (!muc.isIntersecting) return;
                    theoDoi.unobserve(muc.target);
                    dem(muc.target);
                });
            }, { threshold: 0.4 });

            cacO.forEach(function (o) { theoDoi.observe(o); });
        } else {
            cacO.forEach(dem);
        }
    }

    // --- Bo loc du an: tich vao la gui form luon, khong phai bam nut --------
    document.querySelectorAll('[data-nx-auto-submit]').forEach(function (o) {
        o.addEventListener('change', function () {
            o.closest('form').submit();
        });
    });

    // =====================================================================
    // O CHON TU VE
    // =====================================================================
    //
    // Danh sach option cua the <select> do HE DIEU HANH ve, khong phai trinh
    // duyet - CSS khong voi toi duoc. Tren Windows no ra mot khung xam vuong
    // chu nho, lech han voi phan con lai cua trang.
    //
    // Cach lam o day: GIU NGUYEN the <select> that (an di) de form van gui
    // dung va trang khong co JS van dung duoc, roi dung them mot nut va mot
    // bang danh sach ben canh. Moi thao tac tren bang deu ghi nguoc lai vao
    // <select> that roi phat su kien change, nen doan ma nao dang nghe change
    // (vi du o phuong/xa doi theo tinh) khong phai sua gi.
    //
    // Danh sach tu 8 muc tro len thi co them o go de loc - 34 tinh hay hon
    // tram phuong/xa ma phai cuon tay thi rat cuc.
    var NGUONG_TIM = 8;

    // Bo dau tieng Viet de go "thai nguyen" van ra "Thai Nguyen".
    // Hau het nguoi dung go khong dau, so sanh nguyen van thi tim khong ra.
    // NFD tach dau thanh ky tu rieng roi cat di; rieng chu d gach ngang
    // khong phai la "d + dau" nen phai doi tay.
    function boDau(chuoi) {
        return String(chuoi)
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/\u0111/g, 'd')
            .replace(/\u0110/g, 'D')
            .toLowerCase();
    }

    // Dong moi o chon dang mo. Goi truoc khi mo mot o khac.
    function dongHetOChon() {
        document.querySelectorAll('.nx-chon.is-mo').forEach(function (b) {
            b.classList.remove('is-mo');
            b.querySelector('.nx-chon__bang').hidden = true;
            b.querySelector('.nx-chon__nut').setAttribute('aria-expanded', 'false');
        });
    }

    function taoOChon(select) {
        if (select.dataset.nxDaVe === '1') return;
        select.dataset.nxDaVe = '1';

        var boc = document.createElement('div');
        boc.className = 'nx-chon';

        var nut = document.createElement('button');
        nut.type = 'button';
        nut.className = 'nx-chon__nut';
        nut.setAttribute('aria-haspopup', 'listbox');
        nut.setAttribute('aria-expanded', 'false');

        var nhan = document.createElement('span');
        nhan.className = 'nx-chon__nhan';
        nut.appendChild(nhan);

        var bang = document.createElement('div');
        bang.className = 'nx-chon__bang';
        bang.hidden = true;

        var oTim = document.createElement('input');
        oTim.type = 'text';
        oTim.className = 'nx-chon__tim';
        oTim.placeholder = 'Gõ để tìm…';
        oTim.setAttribute('aria-label', 'Tìm trong danh sách');

        var ds = document.createElement('ul');
        ds.className = 'nx-chon__ds';
        ds.setAttribute('role', 'listbox');

        bang.appendChild(oTim);
        bang.appendChild(ds);

        select.parentNode.insertBefore(boc, select);
        boc.appendChild(select);
        boc.appendChild(nut);
        boc.appendChild(bang);
        select.classList.add('nx-chon__that');

        var muc = [];       // cac <li> dang hien
        var dangSang = -1;  // vi tri dang duoc to sang bang ban phim

        function dongBoNhan() {
            var chon = select.options[select.selectedIndex];
            nhan.textContent = chon ? chon.textContent.trim() : '';
            // Muc dau cua cac o loc thuong la "Chon..." - to nhat di cho biet
            // nguoi dung chua chon gi.
            boc.classList.toggle('is-trong', !select.value);
            nut.disabled = select.disabled;
            boc.classList.toggle('is-khoa', select.disabled);
        }

        function veDanhSach(loc) {
            ds.innerHTML = '';
            muc = [];
            loc = boDau((loc || '').trim());

            Array.prototype.forEach.call(select.options, function (o, i) {
                var ten = o.textContent.trim();
                if (loc && boDau(ten).indexOf(loc) === -1) return;

                var li = document.createElement('li');
                li.className = 'nx-chon__muc';
                li.setAttribute('role', 'option');
                li.textContent = ten;
                li.dataset.vt = String(i);

                if (i === select.selectedIndex) {
                    li.classList.add('is-chon');
                    li.setAttribute('aria-selected', 'true');
                }

                ds.appendChild(li);
                muc.push(li);
            });

            if (!muc.length) {
                var trong = document.createElement('li');
                trong.className = 'nx-chon__trong';
                trong.textContent = 'Không có mục nào khớp';
                ds.appendChild(trong);
            }

            dangSang = -1;
            muc.forEach(function (li, k) {
                if (li.classList.contains('is-chon')) dangSang = k;
            });
        }

        function toSang(i) {
            if (!muc.length) return;
            // Chay vong: tu cuoi xuong thi quay ve dau.
            dangSang = (i + muc.length) % muc.length;
            muc.forEach(function (li, k) { li.classList.toggle('is-sang', k === dangSang); });
            muc[dangSang].scrollIntoView({ block: 'nearest' });
        }

        function mo() {
            if (select.disabled) return;

            dongHetOChon();
            veDanhSach('');
            oTim.value = '';
            bang.hidden = false;
            boc.classList.add('is-mo');
            nut.setAttribute('aria-expanded', 'true');

            var coTim = select.options.length >= NGUONG_TIM;
            oTim.hidden = !coTim;
            if (coTim) oTim.focus();

            if (dangSang >= 0) toSang(dangSang);
        }

        function dongMinh() {
            bang.hidden = true;
            boc.classList.remove('is-mo');
            nut.setAttribute('aria-expanded', 'false');
        }

        function chonMuc(li) {
            if (!li || li.dataset.vt === undefined) return;

            select.selectedIndex = parseInt(li.dataset.vt, 10);
            // Phat change de doan ma khac (o phuong/xa, bo loc tu van) chay
            // theo. bubbles de listener gan tren form cung nhan duoc.
            select.dispatchEvent(new Event('change', { bubbles: true }));
            dongBoNhan();
            dongMinh();
            nut.focus();
        }

        nut.addEventListener('click', function () {
            if (bang.hidden) { mo(); } else { dongMinh(); }
        });

        ds.addEventListener('click', function (e) {
            chonMuc(e.target.closest('.nx-chon__muc'));
        });

        ds.addEventListener('mousemove', function (e) {
            var li = e.target.closest('.nx-chon__muc');
            if (li) toSang(muc.indexOf(li));
        });

        oTim.addEventListener('input', function () {
            veDanhSach(oTim.value);
            toSang(0);
        });

        boc.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                dongMinh();
                nut.focus();
                return;
            }

            if (bang.hidden) {
                if (e.key === 'Enter' || e.key === ' ' || e.key === 'ArrowDown') {
                    e.preventDefault();
                    mo();
                }
                return;
            }

            if (e.key === 'ArrowDown') { e.preventDefault(); toSang(dangSang + 1); }
            else if (e.key === 'ArrowUp') { e.preventDefault(); toSang(dangSang - 1); }
            else if (e.key === 'Home') { e.preventDefault(); toSang(0); }
            else if (e.key === 'End') { e.preventDefault(); toSang(muc.length - 1); }
            else if (e.key === 'Enter') { e.preventDefault(); chonMuc(muc[dangSang]); }
            else if (e.key === 'Tab') { dongMinh(); }
        });

        // Bam ra ngoai thi dong.
        document.addEventListener('click', function (e) {
            if (!boc.contains(e.target)) dongMinh();
        });

        // Danh sach option co the bi doan ma khac thay het (o phuong/xa nap
        // lai moi khi doi tinh). Theo doi de nhan hien ra khong lech voi the
        // <select> that.
        new MutationObserver(function () {
            dongBoNhan();
            if (!bang.hidden) veDanhSach(oTim.value);
        }).observe(select, { childList: true, attributes: true, attributeFilter: ['disabled'] });

        select.addEventListener('change', dongBoNhan);

        dongBoNhan();
    }

    document.querySelectorAll('select[data-nx-chon]').forEach(taoOChon);
})();

// =============================================================================
// TRANG CHI TIET DU AN
// =============================================================================

// --- Dai anh nho: bam mot o thi doi anh lon ---------------------------------
// Moi o van la mot the <a> tro thang den file anh, nen khong co JS thi bam
// vao van mo duoc anh - chi mat phan doi anh tai cho.
(function () {
    var chinh = document.getElementById('nx-pd-anh-chinh');
    var oAnh = document.querySelectorAll('[data-nx-anh]');

    if (!chinh || !oAnh.length) return;

    var goc = chinh.getAttribute('src');

    oAnh.forEach(function (o) {
        o.addEventListener('click', function (e) {
            e.preventDefault();

            var duong = o.getAttribute('data-nx-anh');

            // Bam lai dung o dang xem thi tra ve anh dai dien.
            if (chinh.getAttribute('src') === duong) {
                chinh.setAttribute('src', goc);
                o.classList.remove('is-chon');
                return;
            }

            chinh.setAttribute('src', duong);
            oAnh.forEach(function (k) { k.classList.remove('is-chon'); });
            o.classList.add('is-chon');
        });
    });
})();

// --- Nut "Xem video du an" --------------------------------------------------
// Chi dung mot lop phu duy nhat, tao luc bam lan dau. Dong lai thi GO HAN
// the iframe chu khong chi an di - de an thi YouTube van chay tieng ngam.
(function () {
    var nut = document.querySelector('[data-nx-video]');

    if (!nut) return;

    var lop = null;

    // Doi link YouTube/Vimeo thuong thanh dang nhung duoc. Link khac thi tra
    // ve null de mo sang tab moi, khong nhoi bua vao iframe.
    function duongNhung(url) {
        var m = url.match(/(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([\w-]{6,})/);
        if (m) return 'https://www.youtube.com/embed/' + m[1] + '?autoplay=1';

        m = url.match(/vimeo\.com\/(?:video\/)?(\d+)/);
        if (m) return 'https://player.vimeo.com/video/' + m[1] + '?autoplay=1';

        return null;
    }

    function dong() {
        if (!lop) return;
        lop.remove();
        lop = null;
        document.body.style.overflow = '';
    }

    nut.addEventListener('click', function () {
        var url = nut.getAttribute('data-nx-video');
        var nhung = duongNhung(url);

        if (!nhung) {
            window.open(url, '_blank', 'noopener');
            return;
        }

        lop = document.createElement('div');
        lop.className = 'nx-pd-video';
        lop.innerHTML =
            '<div class="nx-pd-video__hop">' +
            '<button type="button" class="nx-pd-video__dong" aria-label="Đóng">&times;</button>' +
            '<iframe allow="autoplay; fullscreen" allowfullscreen></iframe>' +
            '</div>';
        lop.querySelector('iframe').setAttribute('src', nhung);

        lop.addEventListener('click', function (e) {
            if (e.target === lop || e.target.closest('.nx-pd-video__dong')) dong();
        });

        document.body.appendChild(lop);
        document.body.style.overflow = 'hidden';
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') dong();
    });
})();

// --- Thanh tab: to sang muc dang xem ----------------------------------------
// Dung IntersectionObserver chu khong nghe su kien cuon: trinh duyet tu bao
// khi mot khoi vao vung nhin, khong phai tinh lai vi tri moi khung hinh.
(function () {
    var muc = document.querySelectorAll('[data-nx-tab]');

    if (!muc.length || !('IntersectionObserver' in window)) return;

    var theo = {};
    var khoi = [];

    muc.forEach(function (m) {
        var id = m.getAttribute('data-nx-tab');
        var k = document.getElementById(id);

        if (!k) return;

        theo[id] = m;
        khoi.push(k);
    });

    if (!khoi.length) return;

    // Dang thay: khoi nao co phan nam trong dai giua man hinh thi tinh la
    // dang xem. Lay khoi TREN CUNG trong so do de khi hai khoi cung lot vao
    // thi tab khong nhay qua lai.
    var dangThay = {};

    var nguoiXem = new IntersectionObserver(function (ds) {
        ds.forEach(function (d) {
            dangThay[d.target.id] = d.isIntersecting;
        });

        var chon = null;

        for (var i = 0; i < khoi.length; i++) {
            if (dangThay[khoi[i].id]) { chon = khoi[i].id; break; }
        }

        Object.keys(theo).forEach(function (id) {
            theo[id].classList.toggle('is-chon', id === chon);
        });
    }, { rootMargin: '-72px 0px -55% 0px' });

    khoi.forEach(function (k) { nguoiXem.observe(k); });
})();
</script>
@stack('script')
