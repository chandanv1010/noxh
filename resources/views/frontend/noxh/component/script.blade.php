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
})();
</script>
@stack('script')
