// =============================================================================
// Ban do du an - /du-an/ban-do.
//
// File nay CHI duoc nap o trang ban do (@vite trong map.blade.php), khong di
// theo moi trang: rieng Leaflet da khoang 140KB, keo vao trang chu la phi.
//
// Hai nen ban do dung chung mot ban ve:
//   - OpenStreetMap qua Leaflet: mien phi, khong can khai bao gi.
//   - Google Maps: quen mat nguoi dung hon, nhung phai co API key va tinh
//     tien theo luot mo sau khi het muc mien phi hang thang.
//
// Quan tri chon o Cau hinh he thong -> Ban do du an. Chon Google ma key sai
// hoac tai khong duoc thi trang tu quay ve OpenStreetMap - tha hien ban do
// mien phi con hon hien mot o xam bao loi.
//
// Du lieu ghim do PHP giao ra san (the <script type="application/json">),
// ke ca chu gia va dien tich: dinh dang tien da co ham PHP lo, viet lai mot
// ban nua bang JS thi hai cho lech nhau ngay lan sua dau tien.
// =============================================================================
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

(function () {
    var khung = document.querySelector('[data-nx-bando]');
    var oDuLieu = document.querySelector('[data-nx-bando-diem]');

    if (!khung || !oDuLieu) return;

    var diem = [];

    try {
        diem = JSON.parse(oDuLieu.textContent || '[]');
    } catch (e) {
        diem = [];
    }

    var chu = {
        chiTiet: khung.dataset.chiTiet || 'Xem chi tiết',
        donVi: khung.dataset.donVi || '',
        nhanDt: khung.dataset.nhanDt || '',
        nhanCan: khung.dataset.nhanCan || '',
        nhom: khung.dataset.nhom || '{so} dự án',
        nguon: khung.dataset.nguon || '',
    };

    var giua = {
        lat: parseFloat(khung.dataset.lat) || 0,
        lng: parseFloat(khung.dataset.lng) || 0,
        zoom: parseInt(khung.dataset.zoom, 10) || 5,
    };

    var nhomDiem = gomGhim(diem);

    // -------------------------------------------------------------------------
    // Nhieu du an co the dung chung MOT toa do - hay gap khi du an chua duoc
    // nhap vi do/kinh do rieng nen cung lay tam tinh/thanh. Day chung ra moi
    // cai mot ti thi ban do chi ro vi tri sai, nen gom lai thanh MOT ghim ghi
    // "3 du an", bam vao thi hien ca ba de chon.
    // -------------------------------------------------------------------------
    function gomGhim(ds) {
        var theoKhoa = {};
        var thuTu = [];

        ds.forEach(function (d) {
            var khoa = d.lat.toFixed(5) + ',' + d.lng.toFixed(5);

            if (!theoKhoa[khoa]) {
                theoKhoa[khoa] = { lat: d.lat, lng: d.lng, ds: [] };
                thuTu.push(khoa);
            }

            theoKhoa[khoa].ds.push(d);
        });

        return thuTu.map(function (khoa) { return theoKhoa[khoa]; });
    }

    function thoat(s) {
        return String(s === null || s === undefined ? '' : s)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    /** Pill tren ban do: gia cua du an, hoac so luong neu nhieu du an mot cho. */
    function hinhGhim(nhom) {
        var trong = nhom.ds.length > 1
            ? chu.nhom.replace('{so}', nhom.ds.length)
            : (nhom.ds[0].giaNgan || '•');

        return '<span class="nx-bd-ghim' + (nhom.ds.length > 1 ? ' la-nhieu' : '') + '">'
            + '<b>' + thoat(trong) + '</b></span>';
    }

    /** The thong tin hien khi bam vao ghim. */
    function theGhim(nhom) {
        if (nhom.ds.length === 1) return theMot(nhom.ds[0]);

        var h = '<div class="nx-bd-pop nx-bd-pop--nhieu">';
        h += '<strong class="nx-bd-pop__dau">' + thoat(chu.nhom.replace('{so}', nhom.ds.length)) + '</strong>';

        nhom.ds.forEach(function (d) {
            h += '<a class="nx-bd-pop__dong" href="' + thoat(d.url) + '">'
                + '<img src="' + thoat(d.anh) + '" alt="" loading="lazy">'
                + '<span><b>' + thoat(d.ten) + '</b>'
                + '<i>' + thoat(d.gia) + ' ' + thoat(chu.donVi) + '</i></span></a>';
        });

        return h + '</div>';
    }

    function theMot(d) {
        var h = '<a class="nx-bd-pop" href="' + thoat(d.url) + '">';

        h += '<span class="nx-bd-pop__anh"><img src="' + thoat(d.anh) + '" alt="" loading="lazy">';
        if (d.nhanTrangThai) {
            h += '<span class="nx-badge nx-badge--' + thoat(d.trangThai) + '">' + thoat(d.nhanTrangThai) + '</span>';
        }
        h += '</span>';

        h += '<span class="nx-bd-pop__than">';
        h += '<strong>' + thoat(d.ten) + '</strong>';

        if (d.noi) {
            h += '<span class="nx-bd-pop__noi">' + thoat(d.noi) + '</span>';
        }

        h += '<span class="nx-bd-pop__gia">' + thoat(d.gia) + ' <small>' + thoat(chu.donVi) + '</small></span>';

        h += '<span class="nx-bd-pop__so">';
        h += '<span>' + thoat(chu.nhanDt) + ': <b>' + thoat(d.dienTich) + '</b></span>';
        if (d.soCan) {
            h += '<span>' + thoat(chu.nhanCan) + ': <b>' + thoat(d.soCan) + '</b></span>';
        }
        h += '</span>';

        h += '<span class="nx-bd-pop__nut">' + thoat(chu.chiTiet) + ' →</span>';
        h += '</span></a>';

        return h;
    }

    // -------------------------------------------------------------------------
    // Noi the ben trai voi ghim ben phai: tro chuot vao the thi ghim tuong ung
    // noi len, va nguoc lai. Mot ghim co the ung voi nhieu the (nhieu du an
    // cung mot toa do) nen phai tra cuu ca hai chieu.
    // -------------------------------------------------------------------------
    var cacThe = {};
    var nhomCuaThe = {};

    document.querySelectorAll('[data-nx-bando-the]').forEach(function (el) {
        cacThe[el.dataset.nxBandoThe] = el;
    });

    nhomDiem.forEach(function (nhom, i) {
        nhom.ds.forEach(function (d) { nhomCuaThe[d.id] = i; });
    });

    function keoTheVaoTam(nhom) {
        Object.keys(cacThe).forEach(function (k) { cacThe[k].classList.remove('la-sang'); });

        nhom.ds.forEach(function (d, i) {
            var el = cacThe[d.id];
            if (!el) return;

            el.classList.add('la-sang');
            if (i === 0) el.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
        });
    }

    function sangThe(nhom, bat) {
        nhom.ds.forEach(function (d) {
            var el = cacThe[d.id];
            if (el) el.classList.toggle('la-sang', !!bat);
        });
    }

    /** Gan su kien cho cac the ben trai. Tham so la CHI SO NHOM, khong phai id. */
    function noiThe(moNhom, toSang) {
        Object.keys(cacThe).forEach(function (id) {
            var el = cacThe[id];
            var i = nhomCuaThe[id];

            if (i === undefined) return;

            el.addEventListener('mouseenter', function () { toSang(i, true); });
            el.addEventListener('mouseleave', function () { toSang(i, false); });

            // Nut "xem tren ban do" chi hien ra khi co JS (xem map-card).
            var nut = el.querySelector('[data-nx-bando-xem]');
            if (nut) {
                nut.hidden = false;
                nut.addEventListener('click', function () { moNhom(i); });
            }
        });
    }

    // -------------------------------------------------------------------------
    // Ban ve bang Leaflet + OpenStreetMap.
    // -------------------------------------------------------------------------
    function veOsm() {
        var map = L.map(khung, {
            center: [giua.lat, giua.lng],
            zoom: giua.zoom,
            scrollWheelZoom: false,
        });

        // Bam vao ban do moi cho cuon de phong, roi tat lai khi tro chuot ra
        // khoi khung - cuon trang qua ban do ma bi hut vao rat kho chiu.
        map.on('click', function () { map.scrollWheelZoom.enable(); });
        khung.addEventListener('mouseleave', function () { map.scrollWheelZoom.disable(); });

        L.tileLayer(khung.dataset.anhNen, {
            maxZoom: 19,
            // Giay phep OpenStreetMap BAT BUOC ghi nguon, khong phai dong chu
            // trang tri - bo di la dung sai giay phep.
            attribution: chu.nguon,
        }).addTo(map);

        var ghim = [];

        nhomDiem.forEach(function (nhom) {
            var m = L.marker([nhom.lat, nhom.lng], {
                icon: L.divIcon({
                    className: 'nx-bd-ghim-goc',
                    html: hinhGhim(nhom),
                    iconSize: [0, 0],
                }),
                title: nhom.ds.map(function (d) { return d.ten; }).join(' · '),
                riseOnHover: true,
            }).addTo(map);

            m.bindPopup(theGhim(nhom), {
                className: 'nx-bd-pop-khung',
                minWidth: 268,
                maxWidth: 268,
                closeButton: true,
                autoPanPadding: [24, 24],
            });

            m.on('popupopen', function () { keoTheVaoTam(nhom); });
            m.on('mouseover', function () { sangThe(nhom, true); });
            m.on('mouseout', function () { sangThe(nhom, false); });

            ghim.push(m);
        });

        if (nhomDiem.length) {
            map.fitBounds(L.latLngBounds(nhomDiem.map(function (n) { return [n.lat, n.lng]; })), {
                padding: [48, 48],
                maxZoom: 15,
            });
        }

        noiThe(function (i) {
            var m = ghim[i];
            if (!m) return;
            map.setView(m.getLatLng(), Math.max(map.getZoom(), 13));
            m.openPopup();
        }, function (i, bat) {
            var m = ghim[i];
            if (m && m._icon) m._icon.classList.toggle('la-sang', !!bat);
        });
    }

    // -------------------------------------------------------------------------
    // Ban ve bang Google Maps.
    //
    // Ghim dung OverlayView chu khong dung google.maps.Marker: pill gia la mot
    // the HTML, ve bang Marker thi phai dung lai mot anh SVG khac han ban
    // OpenStreetMap va hai nen se trong khac han nhau.
    // -------------------------------------------------------------------------
    function veGoogle(key) {
        return taiGoogle(key).then(function (g) {
            var map = new g.maps.Map(khung, {
                center: { lat: giua.lat, lng: giua.lng },
                zoom: giua.zoom,
                mapTypeControl: false,
                streetViewControl: false,
                fullscreenControl: true,
                gestureHandling: 'cooperative',
            });

            var hopThe = new g.maps.InfoWindow({ maxWidth: 288 });

            function Ghim(viTri, el) {
                this.viTri = viTri;
                this.el = el;
            }

            Ghim.prototype = Object.create(g.maps.OverlayView.prototype);

            Ghim.prototype.onAdd = function () {
                // overlayMouseTarget la lop DUY NHAT nhan duoc su kien chuot;
                // dat o lop khac thi ghim hien ra nhung bam khong an.
                this.getPanes().overlayMouseTarget.appendChild(this.el);
            };

            Ghim.prototype.draw = function () {
                var p = this.getProjection().fromLatLngToDivPixel(this.viTri);
                if (!p) return;
                this.el.style.left = p.x + 'px';
                this.el.style.top = p.y + 'px';
            };

            Ghim.prototype.onRemove = function () {
                if (this.el.parentNode) this.el.parentNode.removeChild(this.el);
            };

            var ghim = [];
            var khungNhin = new g.maps.LatLngBounds();

            nhomDiem.forEach(function (nhom) {
                var el = document.createElement('div');
                el.className = 'nx-bd-ghim-goc';
                el.innerHTML = hinhGhim(nhom);

                var viTri = new g.maps.LatLng(nhom.lat, nhom.lng);
                var o = new Ghim(viTri, el);
                o.setMap(map);

                el.addEventListener('click', function () {
                    hopThe.setContent(theGhim(nhom));
                    hopThe.setPosition(viTri);
                    hopThe.open(map);
                    keoTheVaoTam(nhom);
                });

                el.addEventListener('mouseenter', function () { sangThe(nhom, true); });
                el.addEventListener('mouseleave', function () { sangThe(nhom, false); });

                khungNhin.extend(viTri);
                ghim.push({ el: el, viTri: viTri, nhom: nhom });
            });

            if (nhomDiem.length) {
                map.fitBounds(khungNhin, 48);

                // Mot ghim duy nhat thi fitBounds phong sat mat dat, nhin
                // khong ra dang khu vuc nua.
                g.maps.event.addListenerOnce(map, 'idle', function () {
                    if (map.getZoom() > 15) map.setZoom(15);
                });
            }

            noiThe(function (i) {
                var m = ghim[i];
                if (!m) return;
                map.setCenter(m.viTri);
                if (map.getZoom() < 13) map.setZoom(13);
                hopThe.setContent(theGhim(m.nhom));
                hopThe.setPosition(m.viTri);
                hopThe.open(map);
            }, function (i, bat) {
                var m = ghim[i];
                if (m) m.el.classList.toggle('la-sang', !!bat);
            });
        });
    }

    /** Tai thu vien Google Maps mot lan, tra ve Promise. */
    function taiGoogle(key) {
        return new Promise(function (nhan, loi) {
            if (window.google && window.google.maps) return nhan(window.google);

            var ten = 'nxBanDoSan';
            var s = document.createElement('script');

            window[ten] = function () { nhan(window.google); };

            s.src = 'https://maps.googleapis.com/maps/api/js?key=' + encodeURIComponent(key)
                + '&language=vi&region=VN&loading=async&callback=' + ten;
            s.async = true;
            s.onerror = function () { loi(new Error('Khong tai duoc Google Maps')); };

            document.head.appendChild(s);

            // Key sai thi Google van tra ve file JS nhung khong goi callback,
            // nen phai tu dat han gio, neu khong trang se cho mai.
            setTimeout(function () {
                if (!(window.google && window.google.maps)) loi(new Error('Google Maps qua han'));
            }, 8000);
        });
    }

    if (khung.dataset.nen === 'google' && khung.dataset.key) {
        veGoogle(khung.dataset.key).catch(function () {
            khung.innerHTML = '';
            veOsm();
        });
    } else {
        veOsm();
    }

    // -------------------------------------------------------------------------
    // Doi tinh/thanh thi gui lai bo loc ngay: danh sach phuong/xa phai do may
    // chu dung lai (chi do ra phuong/xa CO du an, kem so luong), khong the
    // doan o trinh duyet.
    // -------------------------------------------------------------------------
    var oTinh = document.querySelector('[data-nx-bando-tinh]');
    var formLoc = document.querySelector('[data-nx-bando-loc]');

    if (oTinh && formLoc) {
        oTinh.addEventListener('change', function () {
            var oXa = formLoc.querySelector('[name="ward_code"]');
            if (oXa) oXa.value = '';
            formLoc.submit();
        });
    }
})();
