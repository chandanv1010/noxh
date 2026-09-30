{{--
    Khoi dia chi nha o / noi lam viec va danh sach du an quan tam
    (nua duoi ban ve noxh_image/w-4.jpg).

    Danh sach phuong/xa va danh sach du an nap theo tinh dang chon, lay qua
    hai duong dan JSON - nap san 3.321 phuong/xa vao trang thi nang trang vo
    ich, ma nguoi dung chi dung toi vai chuc dong.
--}}
@php
    $maxDuAn = (int) ($intro['wizard_project_max'] ?? 5) ?: 5;
@endphp

<div class="nx-wz-dc"
     data-nx-dia-chi
     data-nx-duong-xa="{{ url('/kiem-tra-dieu-kien/phuong-xa') }}"
     data-nx-duong-du-an="{{ url('/kiem-tra-dieu-kien/du-an') }}"
     data-nx-toi-da="{{ $maxDuAn }}"
     data-nx-chu-dem="{{ $intro['wizard_project_count_text'] ?? 'Đã chọn: {so}/{max}' }}"
     data-nx-chu-mo="{{ $intro['wizard_project_hint'] ?? '' }}"
     data-nx-chu-trong="{{ $intro['wizard_project_empty_text'] ?? 'Chưa có dự án nào trong khu vực này.' }}"
     data-nx-chu-xem="{{ $intro['wizard_project_link_text'] ?? 'Xem thông tin' }}"
     data-nx-chon="{{ implode(',', $duAnDaChon) }}">

    <div class="nx-wz-dc__hai">
        @foreach([
            ['nha', 'home', $intro['wizard_addr_home_heading'] ?? 'Địa chỉ nhà ở hiện tại', $intro['wizard_addr_home_hint'] ?? '', $diaChi['province_code'] ?? '', $diaChi['ward_code'] ?? ''],
            ['viec', 'clipboard', $intro['wizard_addr_work_heading'] ?? 'Nơi làm việc hiện tại', $intro['wizard_addr_work_hint'] ?? '', $diaChi['work_province_code'] ?? '', $diaChi['work_ward_code'] ?? ''],
        ] as [$ma, $hinh, $dau, $mo, $tinhDaChon, $xaDaChon])
            <div class="nx-wz-dc__khoi">
                <h3>
                    <span class="nx-wz-dc__hinh">
                        @include('frontend.noxh.component.icon', ['name' => $hinh, 'size' => 20])
                    </span>
                    {{ $dau }}
                </h3>

                @if(trim((string) $mo) !== '')
                    <p>{{ $mo }}</p>
                @endif

                <label for="nx-tinh-{{ $ma }}">
                    {{ $intro['wizard_addr_province_label'] ?? 'Tỉnh/Thành phố' }} <i>*</i>
                </label>
                <span class="nx-wz-dc__o">
                    @include('frontend.noxh.component.icon', ['name' => 'pin', 'size' => 18])
                    <select id="nx-tinh-{{ $ma }}"
                            name="{{ $ma === 'nha' ? 'province_code' : 'work_province_code' }}"
                            data-nx-tinh="{{ $ma }}">
                        <option value="">{{ $intro['wizard_addr_province_hint'] ?? '— Chọn tỉnh/thành phố —' }}</option>
                        @foreach($tinhThanh as $t)
                            <option value="{{ $t->code }}" {{ (string) $tinhDaChon === (string) $t->code ? 'selected' : '' }}>{{ $t->name }}</option>
                        @endforeach
                    </select>
                </span>

                <label for="nx-xa-{{ $ma }}">
                    {{ $intro['wizard_addr_ward_label'] ?? 'Phường/Xã' }} <i>*</i>
                </label>
                <span class="nx-wz-dc__o">
                    @include('frontend.noxh.component.icon', ['name' => 'pin', 'size' => 18])
                    <select id="nx-xa-{{ $ma }}"
                            name="{{ $ma === 'nha' ? 'ward_code' : 'work_ward_code' }}"
                            data-nx-xa="{{ $ma }}"
                            data-nx-da-chon="{{ $xaDaChon }}">
                        <option value="">{{ $intro['wizard_addr_ward_hint'] ?? '— Chọn phường/xã —' }}</option>
                    </select>
                </span>
            </div>
        @endforeach
    </div>

    <div class="nx-wz-da" data-nx-du-an hidden>
        <div class="nx-wz-da__dau">
            <h3>
                <span class="nx-wz-dc__hinh">
                    @include('frontend.noxh.component.icon', ['name' => 'building', 'size' => 20])
                </span>
                {{ $intro['wizard_project_heading'] ?? 'Các dự án NOXH trên địa bàn nơi bạn làm việc' }}
            </h3>
            <span class="nx-wz-da__dem" data-nx-dem></span>
        </div>

        <p class="nx-wz-da__mo" data-nx-mo></p>

        <div class="nx-wz-da__ds" data-nx-ds></div>
    </div>
</div>

@push('script')
<script>
// =============================================================================
// Khoi dia chi + danh sach du an cua buoc "Nha o"
// (nua duoi ban ve noxh_image/w-4.jpg)
//
// Danh sach phuong/xa va du an nap theo tinh dang chon qua hai duong dan
// JSON: nap san 3.321 phuong/xa vao trang thi nang trang vo ich, ma nguoi
// dung chi dung toi vai chuc dong.
//
// Tat JS thi hai o tinh/thanh van chon duoc va gui len binh thuong - chi
// mat phan phuong/xa va danh sach du an, khong chan duoc nguoi dung di tiep.
// =============================================================================
(function () {
    var goc = document.querySelector('[data-nx-dia-chi]');

    if (!goc) {
        return;
    }

    var duongXa = goc.dataset.nxDuongXa;
    var duongDuAn = goc.dataset.nxDuongDuAn;
    var toiDa = parseInt(goc.dataset.nxToiDa, 10) || 5;

    var khoiDuAn = goc.querySelector('[data-nx-du-an]');
    var oDem = goc.querySelector('[data-nx-dem]');
    var oMo = goc.querySelector('[data-nx-mo]');
    var oDs = goc.querySelector('[data-nx-ds]');

    var daChon = (goc.dataset.nxChon || '').split(',').filter(Boolean);

    function lay(duong) {
        return fetch(duong, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.ok ? r.json() : null; })
            .catch(function () { return null; });
    }

    // --- phuong / xa ---------------------------------------------------------
    function napXa(ma) {
        var oTinh = goc.querySelector('[data-nx-tinh="' + ma + '"]');
        var oXa = goc.querySelector('[data-nx-xa="' + ma + '"]');

        if (!oTinh || !oXa) {
            return Promise.resolve();
        }

        var dauTien = oXa.options[0];

        while (oXa.options.length > 1) {
            oXa.remove(1);
        }

        if (!oTinh.value) {
            oXa.disabled = true;
            return Promise.resolve();
        }

        oXa.disabled = true;
        dauTien.textContent = 'Đang tải…';

        return lay(duongXa + '/' + encodeURIComponent(oTinh.value)).then(function (ds) {
            dauTien.textContent = oXa.dataset.nxChuMo || dauTien.dataset.goc || dauTien.textContent;
            oXa.disabled = false;

            if (!ds) {
                return;
            }

            var can = oXa.dataset.nxDaChon || '';

            ds.forEach(function (x) {
                var o = document.createElement('option');
                o.value = x.code;
                o.textContent = x.name;
                if (String(can) === String(x.code)) {
                    o.selected = true;
                }
                oXa.appendChild(o);
            });

            // Chi giu lai lua chon cu mot lan: doi tinh la phai chon lai xa.
            oXa.dataset.nxDaChon = '';
        });
    }

    // --- danh sach du an -----------------------------------------------------
    function veDem() {
        if (!oDem) {
            return;
        }

        oDem.textContent = (goc.dataset.nxChuDem || '')
            .replace('{so}', daChon.length)
            .replace('{max}', toiDa);
    }

    function napDuAn() {
        var oTinh = goc.querySelector('[data-nx-tinh="viec"]');
        var oXa = goc.querySelector('[data-nx-xa="viec"]');

        if (!oTinh || !khoiDuAn) {
            return;
        }

        if (!oTinh.value) {
            khoiDuAn.hidden = true;
            return;
        }

        var duong = duongDuAn + '/' + encodeURIComponent(oTinh.value);

        if (oXa && oXa.value) {
            duong += '?xa=' + encodeURIComponent(oXa.value);
        }

        lay(duong).then(function (ra) {
            if (!ra) {
                khoiDuAn.hidden = true;
                return;
            }

            khoiDuAn.hidden = false;
            oDs.innerHTML = '';

            if (oMo) {
                oMo.textContent = (goc.dataset.nxChuMo || '')
                    .replace('{noi}', ra.noi || '')
                    .replace('{max}', toiDa);
            }

            if (!ra.duAn || !ra.duAn.length) {
                var trong = document.createElement('p');
                trong.className = 'nx-wz-da__trong';
                trong.textContent = goc.dataset.nxChuTrong || '';
                oDs.appendChild(trong);
                veDem();
                return;
            }

            ra.duAn.forEach(function (d) {
                oDs.appendChild(veThe(d));
            });

            veDem();
        });
    }

    function veThe(d) {
        var the = document.createElement('label');
        the.className = 'nx-wz-da__o';

        var tich = document.createElement('input');
        tich.type = 'checkbox';
        tich.name = 'project_ids[]';
        tich.value = d.id;
        tich.checked = daChon.indexOf(String(d.id)) !== -1;

        tich.addEventListener('change', function () {
            var i = daChon.indexOf(String(d.id));

            if (tich.checked) {
                if (i === -1) {
                    // Qua so luong cho phep thi bo tich lai, khong am tham
                    // nuot lua chon cua nguoi dung.
                    if (daChon.length >= toiDa) {
                        tich.checked = false;
                        return;
                    }
                    daChon.push(String(d.id));
                }
            } else if (i !== -1) {
                daChon.splice(i, 1);
            }

            the.classList.toggle('da-chon', tich.checked);
            veDem();
        });

        the.classList.toggle('da-chon', tich.checked);
        the.appendChild(tich);

        var dau = document.createElement('span');
        dau.className = 'nx-wz-da__tich';
        the.appendChild(dau);

        if (d.image) {
            var anh = document.createElement('img');
            anh.src = d.image;
            anh.alt = '';
            anh.loading = 'lazy';
            the.appendChild(anh);
        }

        var ten = document.createElement('strong');
        ten.textContent = d.name;
        the.appendChild(ten);

        if (d.place) {
            var noi = document.createElement('small');
            noi.textContent = d.place;
            the.appendChild(noi);
        }

        if (d.url) {
            var xem = document.createElement('a');
            xem.href = d.url;
            xem.target = '_blank';
            xem.rel = 'noopener';
            xem.textContent = goc.dataset.nxChuXem || '';
            xem.addEventListener('click', function (e) { e.stopPropagation(); });
            the.appendChild(xem);
        }

        return the;
    }

    // --- noi day ------------------------------------------------------------
    ['nha', 'viec'].forEach(function (ma) {
        var oTinh = goc.querySelector('[data-nx-tinh="' + ma + '"]');
        var oXa = goc.querySelector('[data-nx-xa="' + ma + '"]');

        if (!oTinh) {
            return;
        }

        if (oXa && oXa.options[0]) {
            oXa.dataset.nxChuMo = oXa.options[0].textContent;
        }

        oTinh.addEventListener('change', function () {
            napXa(ma).then(function () {
                if (ma === 'viec') {
                    napDuAn();
                }
            });
        });

        if (oXa && ma === 'viec') {
            oXa.addEventListener('change', napDuAn);
        }

        // Quay lai buoc nay: dung lai dung tinh/xa da chon lan truoc.
        if (oTinh.value) {
            napXa(ma).then(function () {
                if (ma === 'viec') {
                    napDuAn();
                }
            });
        } else if (oXa) {
            oXa.disabled = true;
        }
    });

    veDem();
})();
</script>
@endpush
