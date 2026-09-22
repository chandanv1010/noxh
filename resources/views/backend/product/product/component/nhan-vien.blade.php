{{--
    Gan nhan vien kinh doanh phu trach du an.

    Chi co o form cua quan tri. Bang dieu khien /sale KHONG co khoi nay: nhan
    vien tu them minh vao du an nguoi khac thi phan quyen thanh vo nghia.

    O an co_gan_nhan_vien la dau hieu "form nay co quyen dong vao danh sach".
    Khong co no thi khong phan biet duoc "bo het nguoi ra khoi du an" voi "form
    khong gui o nay len" - trinh duyet khong gui o chon nhieu khi khong chon gi.
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

<div class="ibox w">
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
    </div>
</div>
