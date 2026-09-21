@extends('frontend.noxh.layout')

@section('content')
@include('frontend.noxh.component.page-head', [
    'crumbs' => ['Hồ sơ' => url('/ho-so'), $tieuDe => ''],
    'tieuDe' => $tieuDe,
    'moTa' => $intro['dossier_description'] ?? 'Danh sách giấy tờ cần chuẩn bị khi mua nhà ở xã hội, theo từng nhóm đối tượng.',
])

<div class="nx-listing nx-listing--left">
    <aside>
        <div class="nx-panel">
            <h2 class="nx-panel__title">Cách xem</h2>
            <div class="nx-filter__group">
                @foreach([
                    ['prepare', 'Hồ sơ cần chuẩn bị', '/ho-so/can-chuan-bi'],
                    ['templates', 'Mẫu đơn tải về', '/ho-so/mau-don'],
                    ['checklist', 'Checklist tích chọn', '/ho-so/checklist'],
                ] as $o)
                    <a href="{{ url($o[2]) }}" class="nx-filter__option"
                       style="text-decoration:none;{{ $kieu === $o[0] ? 'color:#1668e3;font-weight:600' : '' }}">
                        <span>{{ $o[1] }}</span>
                    </a>
                @endforeach
            </div>
        </div>

        @if($tatCaBo->count() > 1)
            <div class="nx-panel">
                <h2 class="nx-panel__title">Nhóm đối tượng</h2>
                <div class="nx-filter__group">
                    <a href="{{ url()->current() }}" class="nx-filter__option" style="text-decoration:none">
                        <span>Tất cả</span>
                    </a>
                    @foreach($tatCaBo as $bo)
                        <a href="{{ url()->current() . '?bo=' . ($bo->canonical ?: $bo->id) }}" class="nx-filter__option"
                           style="text-decoration:none;{{ $boChon == ($bo->canonical ?: $bo->id) ? 'color:#1668e3;font-weight:600' : '' }}">
                            <span>{{ $bo->name }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        @include('frontend.noxh.component.expert-box')
    </aside>

    <div>
        @if($kieu === 'checklist')
            <div class="nx-alert nx-alert--info">
                Tích vào từng giấy tờ đã chuẩn bị xong để theo dõi tiến độ.
                Đánh dấu chỉ lưu trên trình duyệt của bạn, không gửi đi đâu cả.
            </div>
        @endif

        @forelse($boHoSo as $bo)
            <div class="nx-panel">
                <h2 class="nx-panel__title">
                    {{ $bo->name }}
                    <span style="color:#8695aa;font-size:12.5px;font-weight:500;text-transform:none">
                        {{ $bo->items->count() }} giấy tờ
                    </span>
                </h2>

                @if($bo->description)
                    <p class="nx__subheading">{{ strip_tags($bo->description) }}</p>
                @endif

                @foreach($bo->items as $gt)
                    <div class="nx-dossier-item">
                        @if($kieu === 'checklist')
                            <input type="checkbox" data-nx-check="ho-so-{{ $gt->id }}">
                        @else
                            <span style="color:#1668e3;margin-top:2px">
                                @include('frontend.noxh.component.icon', ['name' => 'file', 'size' => 18])
                            </span>
                        @endif

                        <div class="nx-dossier-item__body">
                            <strong>{{ $gt->title }}</strong>
                            @if($gt->description)
                                <p>{{ strip_tags($gt->description) }}</p>
                            @endif

                            <div class="nx-dossier-item__tags">
                                @if($gt->issued_by)
                                    <span>@include('frontend.noxh.component.icon', ['name' => 'building', 'size' => 13]) {{ $gt->issued_by }}</span>
                                @endif
                                <span>@include('frontend.noxh.component.icon', ['name' => 'layers', 'size' => 13]) {{ $gt->copies }} bản</span>
                                @if($gt->is_required)
                                    <span style="color:#dc2626">Bắt buộc</span>
                                @endif
                            </div>
                        </div>

                        @if($gt->template_file)
                            <a href="{{ $gt->template_file }}" class="nx-btn nx-btn--ghost nx-btn--sm" target="_blank" rel="noopener">
                                @include('frontend.noxh.component.icon', ['name' => 'download', 'size' => 15])
                                Tải mẫu
                            </a>
                        @endif
                    </div>
                @endforeach
            </div>
        @empty
            <div class="nx-empty">
                @if($kieu === 'templates')
                    Chưa có mẫu đơn nào được đăng tải.
                @else
                    Chưa có bộ hồ sơ nào được đăng.
                @endif
            </div>
        @endforelse
    </div>
</div>

@if($kieu === 'checklist')
@push('script')
<script>
    // Nho trang thai tich tren chinh may cua nguoi dung. Khong gui len may
    // chu: day la ghi chu ca nhan, khong phai du lieu cua website.
    (function () {
        var o = document.querySelectorAll('[data-nx-check]');
        o.forEach(function (c) {
            var khoa = 'nx-' + c.getAttribute('data-nx-check');
            try {
                c.checked = localStorage.getItem(khoa) === '1';
            } catch (e) {
                // Trinh duyet chan luu tru (che do rieng tu) - bo qua, o tich
                // van dung duoc trong phien nay.
            }
            c.addEventListener('change', function () {
                try { localStorage.setItem(khoa, c.checked ? '1' : '0'); } catch (e) {}
            });
        });
    })();
</script>
@endpush
@endif
@endsection
