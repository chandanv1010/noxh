{{--
    Khoi thong tin rieng cua du an nha o xa hoi.

    Tach thanh partial rieng de khong lam roi content.blade.php von la phan ke
    thua tu ban clone ban dau. Tat ca cac o o day deu do bo loc va trang chi
    tiet du an ngoai website doc tuc thoi.
--}}
@php $duAn = $product ?? null; @endphp

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
        <div class="row mb15">
            <div class="col-lg-4">
                <div class="form-row">
                    <label class="control-label text-left">Tỉnh/Thành</label>
                    <select name="province_code" class="form-control setupSelect2 province location" data-target="districts">
                        <option value="">[Chọn Tỉnh/Thành]</option>
                        @foreach($tinhThanh ?? [] as $tinh)
                            <option value="{{ $tinh->code }}" {{ old('province_code', ($duAn->province_code) ?? '') == $tinh->code ? 'selected' : '' }}>{{ $tinh->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="form-row">
                    <label class="control-label text-left">Quận/Huyện</label>
                    <select class="form-control districts setupSelect2 location" data-target="wards">
                        <option value="">[Chọn Quận/Huyện]</option>
                    </select>
                    <small class="text-muted">Chỉ dùng để lọc ra phường/xã, không lưu lại.</small>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="form-row">
                    <label class="control-label text-left">Phường/Xã</label>
                    <select name="ward_code" class="form-control wards setupSelect2">
                        <option value="">[Chọn Phường/Xã]</option>
                    </select>
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

        @if(($config['method'] ?? '') === 'edit' && isset($product->id))
            <div class="alert alert-info mt20 mb0">
                <strong>Tiến độ, hồ sơ pháp lý và câu hỏi thường gặp</strong> của dự án này nhập ở màn hình riêng:
                <a href="{{ route('project.milestone.index', ['product_id' => $product->id]) }}">Tiến độ</a> ·
                <a href="{{ route('project.document.index', ['product_id' => $product->id]) }}">Hồ sơ pháp lý</a> ·
                <a href="{{ route('project.faq.index', ['product_id' => $product->id]) }}">Câu hỏi thường gặp</a>
            </div>
        @endif

    </div>
</div>

<script>
    // location.js doc ba bien nay de nap lai Quan/Huyen va Phuong/Xa khi mo
    // form sua - hai danh sach do lay bang ajax nen khong render san tu PHP.
    //
    // Du an chi luu tinh va phuong/xa (bo cap huyen theo don vi hanh chinh
    // moi), nhung van phai cho chon huyen de loc ra danh sach phuong/xa.
    var province_id = '{{ old('province_code', ($product->province_code) ?? '') }}'
    var district_id = ''
    var ward_id = '{{ old('ward_code', ($product->ward_code) ?? '') }}'
</script>
