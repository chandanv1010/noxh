<script>
// =============================================================================
// JS dung chung cua NOXH.vn. Viet thuan, khong keo them thu vien nao: toan bo
// tuong tac o day chi la mo/dong menu va dem so.
// =============================================================================
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
