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
    var cacNut = document.querySelectorAll('[data-nx-video]');

    if (!cacNut.length) return;

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

    function mo(url) {
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
    }

    cacNut.forEach(function (n) {
        n.addEventListener('click', function () {
            mo(n.getAttribute('data-nx-video'));
        });
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') dong();
    });
})();

// --- Thanh tab: DOI NOI DUNG trong khung ------------------------------------
// Ban thiet ke lam thanh tab kieu switcher: bam mot muc thi khung ben duoi
// doi noi dung tai cho, khong truot xuong. Trang van gui ra day du moi khoi
// va chi giau bot bang class is-tab, nen khong co JS thi doc duoc het.
(function () {
    var khung = document.getElementById('khung-tab');
    var muc = document.querySelectorAll('[data-nx-tab]');

    if (!khung || !muc.length) return;

    var o = {};

    khung.querySelectorAll('[data-nx-pane]').forEach(function (k) {
        o[k.getAttribute('data-nx-pane')] = k;
    });

    if (!Object.keys(o).length) return;

    khung.classList.add('is-tab');

    function chon(ma, keoLen) {
        if (!o[ma]) return;

        Object.keys(o).forEach(function (k) {
            o[k].classList.toggle('is-hien', k === ma);
        });

        muc.forEach(function (m) {
            var la = m.getAttribute('data-nx-tab') === ma;
            m.classList.toggle('is-chon', la);
            m.setAttribute('aria-selected', la ? 'true' : 'false');
        });

        // Chi keo man hinh khi khung dang nam KHUAT TREN dinh - bam mot tab
        // ma trang tu nhay mot doan la kho chiu.
        if (keoLen) {
            var tren = khung.getBoundingClientRect().top;
            if (tren < 120) {
                window.scrollTo({ top: window.scrollY + tren - 130, behavior: 'smooth' });
            }
        }

        if (window.history && history.replaceState) {
            history.replaceState(null, '', '#tab-' + ma);
        }
    }

    muc.forEach(function (m) {
        m.addEventListener('click', function (e) {
            e.preventDefault();
            chon(m.getAttribute('data-nx-tab'), true);
        });
    });

    // Cac nut o khoi khac tro sang mot tab: "Xem anh thuc te", "Xem tat ca".
    document.querySelectorAll('[data-nx-mo-tab]').forEach(function (n) {
        n.addEventListener('click', function () {
            var ma = n.getAttribute('data-nx-mo-tab');
            if (!o[ma]) return;
            chon(ma, false);
            khung.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });

    // Vao thang bang duong dan #tab-xxx thi mo dung tab do.
    var neo = (location.hash || '').replace('#tab-', '');
    if (neo && o[neo]) chon(neo, false);
})();

// --- Hop bat len dung chung (tien do day du, danh sach tu van vien) ---------
// Mot bo xu ly cho moi hop: nut mo mang data-nx-mo="<id cua hop>", con nut
// dong va lop nen mang data-nx-dong.
(function () {
    var dangMo = null;

    function dong() {
        if (!dangMo) return;
        dangMo.hidden = true;
        dangMo = null;
        document.body.style.overflow = '';
    }

    function mo(hop) {
        dong();
        hop.hidden = false;
        dangMo = hop;
        document.body.style.overflow = 'hidden';

        var nut = hop.querySelector('.nx-hop__dong');
        if (nut) nut.focus();
    }

    document.querySelectorAll('[data-nx-mo]').forEach(function (n) {
        n.addEventListener('click', function () {
            var hop = document.getElementById(n.getAttribute('data-nx-mo'));
            if (hop) mo(hop);
        });
    });

    document.querySelectorAll('.nx-hop').forEach(function (hop) {
        hop.addEventListener('click', function (e) {
            if (e.target.closest('[data-nx-dong]')) dong();
        });
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') dong();
    });
})();

// --- Trang chi tiet tin: nut "Xem them noi dung" -----------------------------
//
// Nut do JS TU GAN chu khong viet san trong Blade: bai ngan thi khong can cat,
// va neu trinh duyet khong chay JS thi bai viet phai hien nguyen ven - viet
// san the cat trong HTML la bai ngan cung bi cut mat.
(function () {
    var CAO = 520;   // chieu cao toi da khi con thu gon, don vi diem anh

    document.querySelectorAll('[data-nx-mo-rong]').forEach(function (hop) {
        if (hop.scrollHeight <= CAO + 80) return;

        var nut = document.createElement('button');
        nut.type = 'button';
        nut.className = 'nx-tin-them';
        nut.setAttribute('aria-expanded', 'false');

        var chuMo = hop.getAttribute('data-nx-chu-mo') || 'Xem thêm nội dung';
        var chuThu = hop.getAttribute('data-nx-chu-thu') || 'Thu gọn nội dung';
        var mui = '<svg class="nx-ico" width="18" height="18" viewBox="0 -960 960 960"'
            + ' fill="currentColor" aria-hidden="true"><path d="M469-358q-5-2-10-7L261-563q-9-9-8.5-21.5T262-606q9-9 21.5-9t21.5 9l175 176 176-176q9-9 21-8.5t21 9.5q9 9 9 21.5t-9 21.5L501-365q-5 5-10 7t-11 2q-6 0-11-2Z"/></svg>';

        var ve = function (mo) {
            nut.innerHTML = '<span>' + (mo ? chuThu : chuMo) + '</span>' + mui;
            nut.setAttribute('aria-expanded', mo ? 'true' : 'false');
        };

        hop.classList.add('dang-thu');
        hop.style.maxHeight = CAO + 'px';
        ve(false);

        nut.addEventListener('click', function () {
            var mo = hop.classList.toggle('dang-thu') === false;
            hop.style.maxHeight = mo ? '' : CAO + 'px';
            ve(mo);

            if (!mo) hop.scrollIntoView({ block: 'nearest' });
        });

        hop.parentNode.insertBefore(nut, hop.nextSibling);
    });
})();

// --- Nut chep duong dan bai viet ---------------------------------------------
(function () {
    document.querySelectorAll('[data-nx-chep]').forEach(function (nut) {
        nut.addEventListener('click', function () {
            var duong = nut.getAttribute('data-nx-chep');

            var xong = function () {
                nut.classList.add('da-chep');
                nut.setAttribute('title', nut.getAttribute('data-nx-chep-xong') || '');
                setTimeout(function () { nut.classList.remove('da-chep'); }, 1800);
            };

            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(duong).then(xong, function () {});
                return;
            }

            // Trinh duyet cu khong co clipboard API: muon tam mot o nhap.
            var o = document.createElement('textarea');
            o.value = duong;
            o.setAttribute('readonly', '');
            o.style.position = 'fixed';
            o.style.opacity = '0';
            document.body.appendChild(o);
            o.select();

            try { document.execCommand('copy'); xong(); } catch (e) {}

            document.body.removeChild(o);
        });
    });
})();

</script>

{{-- Chuyen buoc wizard cho muot: bam nut la phu lop mo kem vong xoay.
     Moi buoc la mot lan tai trang that nen khong co lop nay thi man hinh
     dung yen mot nhip roi nhay cai - nguoi dung tuong may treo. --}}
<script>
    (function () {
        var form = document.querySelector('form.nx-wz-the');

        if (!form) {
            return;
        }

        var dangGui = false;

        form.addEventListener('submit', function () {
            // Trinh duyet chan vi o bat buoc con trong thi khong tinh la gui.
            if (dangGui || (form.checkValidity && !form.checkValidity())) {
                return;
            }

            dangGui = true;

            var lop = document.createElement('div');
            lop.className = 'nx-wz-cho';
            lop.setAttribute('aria-live', 'polite');
            lop.innerHTML = '<span class="nx-wz-cho__vong"></span>';
            document.body.appendChild(lop);
        });

        // Bam nut lui o dinh the cung phu lop cho.
        var lui = document.querySelector('.nx-wz-the__lui');

        if (lui) {
            lui.addEventListener('click', function () {
                var lop = document.createElement('div');
                lop.className = 'nx-wz-cho';
                lop.innerHTML = '<span class="nx-wz-cho__vong"></span>';
                document.body.appendChild(lop);
            });
        }
    })();
</script>

@stack('script')
