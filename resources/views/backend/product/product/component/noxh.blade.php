{{--
    Khoi thong tin rieng cua du an nha o xa hoi.

    Tach thanh partial rieng de khong lam roi content.blade.php von la phan ke
    thua tu ban clone ban dau. Tat ca cac o o day deu do bo loc va trang chi
    tiet du an ngoai website doc tuc thoi.
--}}
@php $duAn = $product ?? null; @endphp

{{-- In MOT lan du partial duoc nhung nhieu lan. --}}
@once
<style>
    /*
     * Cho cac o trong form du an cao bang nhau.
     *
     * CSS cua theme dat .select2-container .select2-selection--single { height:32px }
     * trong khi .form-control (o select thuong va o nhap chu) cao 40px ke ca
     * padding - lech nhau 8px, nhin so le. Do la ly do khoi "Thong tin du an"
     * trong nhu bi lech hang.
     *
     * Do bang Chrome: o Select2 32px, o thuong 40px. Bon tri so duoi day dua ca
     * hai ve 40px; dung !important vi rule cua theme cung dung !important.
     * Doi 40px thi phai doi CA BON, khong thi lech lai.
     */
    .nx-form-du-an .select2-container .select2-selection--single {
        height: 40px !important;
    }

    .nx-form-du-an .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 28px !important;
        /* 40 - 6 - 6 = 28: khop padding cua .form-control */
        padding: 6px 24px 6px 6px;
        font-size: 14px;
    }

    .nx-form-du-an .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 38px;
    }
</style>
@endonce

<div class="ibox">
    <div class="ibox-title">
        <h5>Thông tin dự án nhà ở xã hội</h5>
    </div>
    <div class="ibox-content">

        <div class="row mb15">
            <div class="col-lg-4">
                <div class="form-row">
                    <label class="control-label text-left">Chủ đầu tư</label>
                    <select name="investor_id" class="form-control setupSelect2">
                        <option value="">[Chưa chọn]</option>
                        @foreach($chuDauTu ?? [] as $cdt)
                            <option value="{{ $cdt->id }}" {{ (int) old('investor_id', ($duAn->investor_id) ?? 0) === $cdt->id ? 'selected' : '' }}>{{ $cdt->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="form-row">
                    <label class="control-label text-left">Trạng thái dự án</label>
                    <select name="status" class="form-control">
                        <option value="">[Chưa chọn]</option>
                        @foreach($trangThaiDuAn ?? [] as $ma => $ten)
                            <option value="{{ $ma }}" {{ old('status', ($duAn->status) ?? '') === $ma ? 'selected' : '' }}>{{ $ten }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">Quyết định nhãn màu trên thẻ dự án và bộ lọc ngoài website.</small>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="form-row">
                    <label class="control-label text-left">Hình thức sở hữu</label>
                    <input type="text" name="ownership_type" value="{{ old('ownership_type', ($duAn->ownership_type) ?? '') }}"
                           class="form-control" placeholder="Ví dụ: Sở hữu 50 năm" autocomplete="off">
                </div>
            </div>
        </div>

        <h4 class="mt20 mb10">Vị trí</h4>
        {{-- Tu 01/07/2025 Viet Nam bo cap quan/huyen: duoi tinh/thanh la thang
             phuong/xa. Khong con o "Quan/Huyen" - no vua khong con don vi de
             chon, vua lam o Phuong/Xa khong nap duoc vi danh sach phuong/xa cu
             duoc loc theo ma huyen. --}}
        <div class="row mb15">
            <div class="col-lg-6">
                <div class="form-row">
                    <label class="control-label text-left">Tỉnh/Thành</label>
                    <select name="province_code" id="nx-tinh" class="form-control setupSelect2">
                        <option value="">[Chọn Tỉnh/Thành]</option>
                        @foreach($tinhThanh ?? [] as $tinh)
                            <option value="{{ $tinh->code }}" {{ (string) old('province_code', ($product->province_code) ?? '') === (string) $tinh->code ? 'selected' : '' }}>{{ $tinh->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="form-row">
                    <label class="control-label text-left">Phường/Xã</label>
                    {{-- O nay KHONG co option render san tu PHP: danh sach phuong/xa
                         tuy tung tinh nen nap bang fetch, roi giao cho Select2 quan
                         ly (nho vay co san o tim kiem cua Select2, go khong dau
                         cung loc duoc).

                         Khac cac o con lai: Select2 o day duoc khoi tao bang JS
                         SAU khi tai xong danh sach, chu khong de library.js khoi
                         tao luc trang san sang - luc do chua co du lieu nao. --}}
                    <select name="ward_code" id="nx-xa" class="form-control setupSelect2">
                        @if(!empty($phuongXa))
                            {{-- Option nay phai co san thi Select2 moi hien duoc
                                 lua chon dang luu khi mo form sua. --}}
                            <option value="{{ $phuongXa->code }}" selected>{{ $phuongXa->name }}</option>
                        @endif
                    </select>
                    <small class="text-muted">Danh sách phường/xã tải theo tỉnh/thành đã chọn. Gõ vào ô để tìm nhanh.</small>
                </div>
            </div>
        </div>

        <div class="row mb15">
            <div class="col-lg-12">
                <div class="form-row">
                    <label class="control-label text-left">Địa chỉ chi tiết</label>
                    <input type="text" name="address" value="{{ old('address', ($duAn->address) ?? '') }}"
                           class="form-control" autocomplete="off">
                </div>
            </div>
        </div>

        <div class="row mb15">
            <div class="col-lg-6">
                <div class="form-row">
                    <label class="control-label text-left">Vĩ độ (latitude)</label>
                    <input type="text" name="latitude" value="{{ old('latitude', ($duAn->latitude) ?? '') }}"
                           class="form-control" placeholder="21.0278" autocomplete="off">
                </div>
            </div>
            <div class="col-lg-6">
                <div class="form-row">
                    <label class="control-label text-left">Kinh độ (longitude)</label>
                    <input type="text" name="longitude" value="{{ old('longitude', ($duAn->longitude) ?? '') }}"
                           class="form-control" placeholder="105.8342" autocomplete="off">
                </div>
            </div>
            <div class="col-lg-12">
                <small class="text-muted">
                    Lấy tọa độ: mở Google Maps, bấm chuột phải vào vị trí dự án, dòng số đầu tiên
                    hiện ra là <strong>vĩ độ, kinh độ</strong>. Bỏ trống thì dự án không có ghim trên bản đồ.
                </small>
            </div>
        </div>

        <h4 class="mt20 mb10">Giá và diện tích</h4>
        <div class="row mb15">
            <div class="col-lg-3">
                <div class="form-row">
                    <label class="control-label text-left">Giá từ (triệu/m²)</label>
                    <input type="number" step="0.01" name="price_from" value="{{ old('price_from', ($duAn->price_from) ?? '') }}" class="form-control" min="0">
                </div>
            </div>
            <div class="col-lg-3">
                <div class="form-row">
                    <label class="control-label text-left">Giá đến (triệu/m²)</label>
                    <input type="number" step="0.01" name="price_to" value="{{ old('price_to', ($duAn->price_to) ?? '') }}" class="form-control" min="0">
                </div>
            </div>
            <div class="col-lg-3">
                <div class="form-row">
                    <label class="control-label text-left">Diện tích từ (m²)</label>
                    <input type="number" step="0.01" name="area_from" value="{{ old('area_from', ($duAn->area_from) ?? '') }}" class="form-control" min="0">
                </div>
            </div>
            <div class="col-lg-3">
                <div class="form-row">
                    <label class="control-label text-left">Diện tích đến (m²)</label>
                    <input type="number" step="0.01" name="area_to" value="{{ old('area_to', ($duAn->area_to) ?? '') }}" class="form-control" min="0">
                </div>
            </div>
            <div class="col-lg-12">
                <small class="text-muted">
                    Mẫu thiết kế hiển thị <strong>khoảng giá</strong> (19,55 – 23,99 triệu/m²) và
                    <strong>khoảng diện tích</strong> (32 – 70 m²). Bộ lọc "dưới 18 / 18–20 / 20–22 / trên 22"
                    so sánh với hai ô giá này, để trống là dự án không lọt vào bất kỳ mức lọc nào.
                </small>
            </div>
        </div>

        <h4 class="mt20 mb10">Quy mô</h4>
        <div class="row mb15">
            <div class="col-lg-3">
                <div class="form-row">
                    <label class="control-label text-left">Tổng số căn</label>
                    <input type="number" name="total_units" value="{{ old('total_units', ($duAn->total_units) ?? '') }}" class="form-control" min="0">
                </div>
            </div>
            <div class="col-lg-3">
                <div class="form-row">
                    <label class="control-label text-left">Tổng diện tích đất (ha)</label>
                    <input type="number" step="0.01" name="total_land_area" value="{{ old('total_land_area', ($duAn->total_land_area) ?? '') }}" class="form-control" min="0">
                </div>
            </div>
            <div class="col-lg-6">
                <div class="form-row">
                    <label class="control-label text-left">Mô tả quy mô</label>
                    <input type="text" name="scale_description" value="{{ old('scale_description', ($duAn->scale_description) ?? '') }}"
                           class="form-control" placeholder="Ví dụ: 4 tòa, 25 tầng" autocomplete="off">
                </div>
            </div>
        </div>

        <div class="row mb15">
            <div class="col-lg-12">
                <div class="form-row">
                    <label class="control-label text-left">Các loại căn hộ</label>
                    <input type="text" name="apartment_types" value="{{ old('apartment_types', ($duAn->apartment_types) ?? '') }}"
                           class="form-control" placeholder="Ví dụ: 1PN-1WC, 2PN-1WC, 2PN-2WC" autocomplete="off">
                    <small class="text-muted">Danh sách ngắn để hiện trên thẻ dự án. Chi tiết từng loại căn nhập ở mục Biến thể phía dưới.</small>
                </div>
            </div>
        </div>

        <h4 class="mt20 mb10">Mốc thời gian</h4>
        <div class="row">
            <div class="col-lg-4">
                <div class="form-row">
                    <label class="control-label text-left">Ngày khởi công</label>
                    <input type="date" name="start_date" value="{{ old('start_date', isset($duAn->start_date) && $duAn->start_date ? \Illuminate\Support\Carbon::parse($duAn->start_date)->format('Y-m-d') : '') }}" class="form-control">
                </div>
            </div>
            <div class="col-lg-4">
                <div class="form-row">
                    <label class="control-label text-left">Dự kiến bàn giao</label>
                    <input type="date" name="handover_date" value="{{ old('handover_date', isset($duAn->handover_date) && $duAn->handover_date ? \Illuminate\Support\Carbon::parse($duAn->handover_date)->format('Y-m-d') : '') }}" class="form-control">
                </div>
            </div>
            <div class="col-lg-4">
                <div class="form-row">
                    <label class="control-label text-left">Nhãn thời gian</label>
                    <input type="text" name="timeline_label" value="{{ old('timeline_label', ($duAn->timeline_label) ?? '') }}"
                           class="form-control" placeholder="Ví dụ: Quý II/2026" autocomplete="off">
                    <small class="text-muted">Dự án thường công bố theo quý chứ không theo ngày cụ thể.</small>
                </div>
            </div>
        </div>

        {{-- Cac o chi phuc vu trang chi tiet du an. Bo trong o nao thi khoi
             tuong ung ngoai trang tu an di, khong de lai cho trong. --}}
        <h4 class="mt20 mb10">Ảnh và liên kết của trang chi tiết</h4>
        <div class="row">
            <div class="col-lg-6">
                <div class="form-row">
                    <label class="control-label text-left">Video giới thiệu</label>
                    <input type="text" name="video_url" value="{{ old('video_url', ($duAn->video_url) ?? '') }}"
                           class="form-control" placeholder="https://www.youtube.com/watch?v=..." autocomplete="off">
                    <small class="text-muted">Có link thì ảnh lớn ở đầu trang hiện nút "Xem video dự án".</small>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="form-row">
                    <label class="control-label text-left">Đường dẫn "Xem cập nhật tiến độ"</label>
                    <input type="text" name="progress_url" value="{{ old('progress_url', ($duAn->progress_url) ?? '') }}"
                           class="form-control" placeholder="Ví dụ: /tin-tuc/tien-do-thang-9" autocomplete="off">
                    <small class="text-muted">Để trống thì khối Tiến độ không có nút này.</small>
                </div>
            </div>
        </div>
        <div class="row mt10">
            <div class="col-lg-6">
                <div class="form-row">
                    <label class="control-label text-left">Đường dẫn Google Maps</label>
                    <input type="text" name="map_url" value="{{ old('map_url', ($duAn->map_url) ?? '') }}"
                           class="form-control" placeholder="https://maps.google.com/..." autocomplete="off">
                    <small class="text-muted">Để trống thì trang tự dựng link từ vĩ độ / kinh độ ở trên.</small>
                </div>
            </div>
        </div>

        <div class="row mt15">
            @foreach([
                'site_plan_image' => ['Ảnh mặt bằng tổng thể', 'Hiện bên phải bảng Tổng quan. Ảnh NGANG.'],
                'map_image' => ['Ảnh bản đồ vị trí', 'Hiện trong khối "Vị trí dự án". Để trống thì khối này không hiện.'],
                'progress_image' => ['Ảnh công trường', 'Hiện bên phải các mốc trong khối "Tiến độ dự án".'],
            ] as $o => $mo)
                <div class="col-lg-4">
                    <div class="form-row">
                        <label class="control-label text-left mb10">{{ $mo[0] }}</label>
                        <span class="image img-cover image-target"
                              style="height:150px;padding:16px;text-align:center;border:1px dashed #b8b2b2;display:flex;align-items:center;justify-content:center;cursor:pointer;">
                            <img src="{{ old($o, ($duAn->$o) ?? '') ?: 'backend/img/image.svg' }}" alt="" style="max-width:100%;max-height:100%;object-fit:contain;">
                        </span>
                        <input type="hidden" name="{{ $o }}" value="{{ old($o, ($duAn->$o) ?? '') }}">
                        <small class="text-muted">{{ $mo[1] }}</small>
                    </div>
                </div>
            @endforeach
        </div>

        @if(($config['method'] ?? '') === 'edit' && isset($product->id))
            <div class="alert alert-info mt20 mb0">
                <strong>Các khối nhiều dòng</strong> của dự án này nhập ở màn hình riêng:
                <a href="{{ route('project.unit.index', ['product_id' => $product->id]) }}">Loại căn hộ</a> ·
                <a href="{{ route('project.highlight.index', ['product_id' => $product->id]) }}">Điểm nhấn</a> ·
                <a href="{{ route('project.milestone.index', ['product_id' => $product->id]) }}">Tiến độ</a> ·
                <a href="{{ route('project.document.index', ['product_id' => $product->id]) }}">Hồ sơ pháp lý</a> ·
                <a href="{{ route('project.faq.index', ['product_id' => $product->id]) }}">Câu hỏi thường gặp</a>
            </div>
        @endif

    </div>
</div>

<script>
    /**
     * O chon Phuong/Xa nap theo Tinh/Thanh dang chon.
     *
     * Truoc day viec nay do location.js lam, nhung no di qua cap Quan/Huyen va
     * doc bang `wards` cu (ba cap) - ma phuong/xa cua du an luu theo ma moi nam
     * chu so trong vn_wards, hai bo ma khong khop nhau. Doan nay goi thang
     * endpoint /dia-gioi/phuong-xa/{maTinh}, cung nguon voi thanh tim o trang
     * chu, nen chi con MOT cho dinh nghia danh sach phuong/xa.
     *
     * Dung Select2 chu khong tu ve mot o loc rieng: Select2 da co san o tim
     * kiem, va no tu bo dau khi loc ("da mai" tim ra "Phường Đa Mai").
     *
     * Ba dieu phai lam dung thu tu, da kiem chung bang Chrome that:
     *   1. Select2 KHONG tu goi ajax khi khoi tao - phai mo dropdown no moi goi.
     *      Nen nap danh sach bang fetch roi dua vao `data`, khong dung `ajax`.
     *   2. `.val(ma)` tra ve null neu trong the <select> khong co option mang ma
     *      do, nen khi mo form sua phai render san option cua phuong/xa dang luu
     *      (lam o PHP trong $phuongXa), roi chi can `trigger('change')`.
     *   3. Doi tinh thi phai `select2('destroy')` roi khoi tao lai voi danh sach
     *      moi, khong thi Select2 giu du lieu cua tinh cu.
     *
     * Nam ngoai $(document).ready de khong phu thuoc thu tu nap.
     *
     * CHUA CO SELECT2 NGAY LUC NAY - phai doi:
     * script nay nam trong than trang, ma Select2 chi duoc nap o cuoi trang
     * (trong ckeditor.js; the script tu CDN dat ngay truoc do, nhung may khong
     * ra duoc Internet thi no khong nap duoc). Neu doi co Select2 moi gan su kien
     * cho o Tinh/Thanh thi su kien KHONG BAO GIO duoc gan - chon tinh xong khong
     * co gi xay ra. Nen: gan su kien NGAY, con khoi tao Select2 thi doi.
     */
    (function () {
        var oTinh = document.getElementById('nx-tinh');
        var oXa = document.getElementById('nx-xa');

        if (!oTinh || !oXa) {
            return;
        }

        var goc = @json(url('/dia-gioi/phuong-xa'));
        var $xa = null;

        /** Cho toi khi Select2 co mat (no nap o cuoi trang), roi moi chay. */
        function khiCoSelect2(chay) {
            if (window.jQuery && jQuery.fn.select2) {
                chay();

                return;
            }

            var soLan = 0;
            var hen = setInterval(function () {
                soLan++;

                if (window.jQuery && jQuery.fn.select2) {
                    clearInterval(hen);
                    chay();
                } else if (soLan > 100) {
                    // 5 giay khong co Select2 thi thoi, khong de interval chay mai.
                    clearInterval(hen);
                }
            }, 50);
        }

        function khoiTao(danhSach) {
            // destroy truoc: Select2 cu con giu du lieu cua tinh truoc do.
            if ($xa.data('select2')) {
                $xa.select2('destroy');
            }

            $xa.empty();

            $xa.select2({
                placeholder: '[Chọn Phường/Xã]',
                allowClear: true,
                width: '100%',
                language: {
                    noResults: function () { return 'Không tìm thấy phường/xã'; },
                },
                data: (danhSach || []).map(function (x) {
                    return { id: x.code, text: x.name };
                }),
            });
        }

        function nap(maTinh) {
            if (!$xa) {
                return;
            }

            if (!maTinh) {
                if ($xa.data('select2')) {
                    $xa.select2('destroy');
                }
                $xa.empty();
                $xa.append(new Option('[Chọn Tỉnh/Thành trước]', ''));

                return;
            }

            fetch(goc + '/' + encodeURIComponent(maTinh), { headers: { Accept: 'application/json' } })
                .then(function (r) { return r.ok ? r.json() : []; })
                .catch(function () { return []; })
                .then(function (ds) { khoiTao(ds); });
        }

        if (window.jQuery) {
            // Gan su kien NGAY, khong doi Select2: o Tinh/Thanh bi Select2 boc
            // nen phai nghe bang jQuery chu KHONG phai addEventListener -
            // jQuery.trigger('change') chi goi handler gan qua jQuery.
            jQuery(oTinh).on('change', function () { nap(oTinh.value); });

            khiCoSelect2(function () {
                $xa = jQuery(oXa);

                if (oTinh.value) {
                    nap(oTinh.value);
                } else {
                    $xa.empty();
                    $xa.append(new Option('[Chọn Tỉnh/Thành trước]', ''));
                }
            });
        } else {
            // Khong co jQuery thi khong the co Select2. Van phai gan de doi tinh
            // thi o Phuong/Xa khong giu lai ma cu cua tinh truoc.
            oTinh.addEventListener('change', function () {
                oXa.innerHTML = '<option value="">[Chọn Phường/Xã]</option>';
            });
        }
    })();
</script>
