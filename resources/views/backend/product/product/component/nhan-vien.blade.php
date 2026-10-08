{{--
    Gan nhan vien kinh doanh phu trach du an.

    Chi co o form cua quan tri. Bang dieu khien /sale KHONG co khoi nay: nhan
    vien tu them minh vao du an nguoi khac thi phan quyen thanh vo nghia.

    O an co_gan_nhan_vien la dau hieu "form nay co quyen dong vao danh sach".
    Khong co no thi khong phan biet duoc "bo het nguoi ra khoi du an" voi "form
    khong gui o nay len" - trinh duyet khong gui o chon nhieu khi khong chon gi.

    NGUON DU LIEU CUA O CHON NAY (truoc day khong ghi ro nen rat de hieu nham la
    danh sach cung):
      ProductController::nhanVienKinhDoanh()
        = thanh vien dang hoat dong (users.publish = 2)
          thuoc mot nhom co bat co "La nhom nhan vien kinh doanh"
          (user_catalogues.is_sale = 1).
    Them mot nhom nua co bat co do, hoac them nguoi vao nhom do, la danh sach
    nay dai ra ngay - khong phai sua ma nguon.
--}}
@php
    $dangPhuTrach = [];
    if (isset($product) && $product) {
        foreach ($product->nhanVienKinhDoanh as $nv) {
            $dangPhuTrach[] = $nv->id;
        }
    }
    $dangPhuTrach = old('nhan_vien_kinh_doanh', $dangPhuTrach);
@endphp

<div class="ibox w" id="khoi-nhan-vien-phu-trach">
    <div class="ibox-title">
        <h5>Nhân viên kinh doanh phụ trách</h5>
    </div>
    <div class="ibox-content">
        <input type="hidden" name="co_gan_nhan_vien" value="1">

        @if(count($nhanVienKinhDoanh ?? []))
            <div class="form-row">
                <select multiple name="nhan_vien_kinh_doanh[]" class="form-control setupSelect2">
                    @foreach($nhanVienKinhDoanh as $nv)
                        <option value="{{ $nv->id }}"
                            {{ in_array($nv->id, $dangPhuTrach) ? 'selected' : '' }}>
                            {{ $nv->name }}@if($nv->title) — {{ $nv->title }}@endif
                        </option>
                    @endforeach
                </select>
                <small class="text-muted">
                    Những người được chọn sẽ hiện ở cuối trang dự án ngoài website,
                    và thấy được dự án này trong bảng điều khiển riêng của họ.
                </small>
            </div>
        @else
            <p class="text-muted" style="margin:0;font-size:13px">
                Chưa có nhân viên kinh doanh nào.
                <br>
                Vào <strong>QL Thành viên → Nhóm thành viên</strong>, bật ô
                <em>Là nhóm nhân viên kinh doanh</em> cho một nhóm, rồi thêm thành viên vào nhóm đó.
            </p>
        @endif

        {{-- Noi ro du lieu o tren den tu dau. Khong co dong nay thi rat de tuong
             danh sach la co dinh trong ma nguon, nhat la khi CSDL dang co san vai
             nhan vien mau do seeder tao ra. --}}
        @php
            $soHienRa = count($nhanVienKinhDoanh ?? []);
            $soTrongNhom = $nhomKinhDoanh->tong_thanh_vien ?? null;
            $soBiAn = ($soTrongNhom !== null) ? max(0, $soTrongNhom - $soHienRa) : 0;
        @endphp
        <div style="margin-top:9px;padding-top:9px;border-top:1px dashed #e3e7ec;font-size:12.5px;color:#7b8794;line-height:1.6">
            Danh sách trên lấy tự động từ <strong>QL Thành viên</strong>:
            những người <strong>đang hoạt động</strong> và thuộc một nhóm có bật cờ
            <em>Là nhóm nhân viên kinh doanh</em>.

            @if(!empty($nhomKinhDoanh))
                Nhóm đang bật cờ đó là <strong>{{ $nhomKinhDoanh->name }}</strong>, có
                <strong>{{ $soTrongNhom }}</strong> thành viên —
                <a href="{{ route('user.index', ['user_catalogue_id' => $nhomKinhDoanh->id]) }}" target="_blank">xem danh sách thành viên của nhóm này</a>.

                @if($soBiAn > 0)
                    {{-- Noi thang ra. Neu chi in "hiện có 6 người" thi quan tri se
                         thac mac vi sao nhom co 7 nguoi ma o chon chi co 6 - dung
                         kieu nham lan da gap vai lan trong chinh man hinh nay. --}}
                    <br>
                    Ô chọn ở trên hiện <strong>{{ $soHienRa }}</strong> người, tức còn
                    <strong>{{ $soBiAn }}</strong> người trong nhóm đang ở trạng thái
                    <em>tắt</em> nên bị ẩn. Vào
                    <a href="{{ route('user.index', ['user_catalogue_id' => $nhomKinhDoanh->id]) }}" target="_blank">danh sách thành viên</a>
                    bật công tắc <em>Tình Trạng</em> cho họ nếu muốn họ nhận khách.
                @endif
                <br>
            @endif

            Muốn thêm người vào ô chọn này: vào
            <a href="{{ route('user.index') }}" target="_blank">QL Thành viên</a>, thêm người vào nhóm
            đó — hoặc vào
            <a href="{{ route('user.catalogue.index') }}" target="_blank">Nhóm thành viên</a>
            bật cờ <em>Là nhóm nhân viên kinh doanh</em> cho một nhóm khác.
            Không phải sửa mã nguồn.
        </div>
    </div>
</div>
