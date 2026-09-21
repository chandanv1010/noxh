@extends('frontend.noxh.layout')

@section('content')
@include('frontend.noxh.component.page-head', [
    'crumbs' => ['Hỏi đáp' => ''],
    'tieuDe' => $intro['qa_heading'] ?? 'Hỏi đáp về nhà ở xã hội',
    'moTa' => $intro['qa_description'] ?? 'Đặt câu hỏi và nhận giải đáp từ chuyên gia pháp lý NOXH.',
])

<div class="nx-listing nx-listing--right">
    <div>
        <form method="GET" class="nx-searchbar">
            <input type="text" name="tu-khoa" value="{{ request('tu-khoa') }}" placeholder="Tìm câu hỏi...">
            <button type="submit" class="nx-btn">Tìm kiếm</button>
        </form>

        <div class="nx-panel">
            @forelse($cauHoi as $ch)
                <div class="nx-qa-item">
                    <span class="nx-qa-item__avatar">{{ mb_substr($ch->asker_name ?: 'K', 0, 1) }}</span>
                    <div style="flex:1;min-width:0">
                        <h2 class="nx-qa-item__title" style="font-size:15px">
                            <a href="{{ url('/hoi-dap/' . $ch->id) }}">{{ $ch->title }}</a>
                            @if($ch->is_featured)
                                <span class="nx-badge nx-badge--upcoming" style="margin-left:6px">Nổi bật</span>
                            @endif
                        </h2>

                        @if($ch->content)
                            <p style="margin:0 0 6px;color:#4a5a70;font-size:13.5px">
                                {{ \Illuminate\Support\Str::words(strip_tags($ch->content), 30, '…') }}
                            </p>
                        @endif

                        <div class="nx-qa-item__meta">
                            <span>{{ $ch->asker_name ?: 'Bạn đọc' }}</span>
                            <span>{{ $ch->created_at?->diffForHumans() }}</span>
                            <span>@include('frontend.noxh.component.icon', ['name' => 'eye', 'size' => 13]) {{ number_format($ch->view_count, 0, ',', '.') }}</span>
                            <span>@include('frontend.noxh.component.icon', ['name' => 'chat', 'size' => 13]) {{ $ch->answers_count }} trả lời</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="nx-empty">Chưa có câu hỏi nào được đăng.</div>
            @endforelse
        </div>

        @include('frontend.noxh.component.pagination', ['model' => $cauHoi])

        <div class="nx-panel">
            <h2 class="nx-panel__title">Gửi câu hỏi của bạn</h2>
            <p class="nx__subheading">Chuyên gia sẽ trả lời và đăng lên sau khi duyệt nội dung.</p>

            @include('frontend.noxh.component.alert')

            <form method="POST" action="{{ route('noxh.qa.store') }}">
                @csrf
                <div class="nx-field">
                    <label for="ch-title">Câu hỏi <span style="color:#dc2626">*</span></label>
                    <input type="text" id="ch-title" name="title" value="{{ old('title') }}"
                           placeholder="Ví dụ: Tôi có hộ khẩu tỉnh khác thì mua NOXH được không?" required>
                </div>
                <div class="nx-field">
                    <label for="ch-content">Diễn giải thêm</label>
                    <textarea id="ch-content" name="content" rows="4">{{ old('content') }}</textarea>
                </div>
                <div class="nx-grid-2">
                    <div class="nx-field">
                        <label for="ch-name">Họ và tên <span style="color:#dc2626">*</span></label>
                        <input type="text" id="ch-name" name="asker_name" value="{{ old('asker_name') }}" required>
                    </div>
                    <div class="nx-field">
                        <label for="ch-phone">Số điện thoại</label>
                        <input type="tel" id="ch-phone" name="asker_phone" value="{{ old('asker_phone') }}">
                    </div>
                </div>
                <button type="submit" class="nx-btn">GỬI CÂU HỎI</button>
            </form>
        </div>

    </div>

    <aside>
        @include('frontend.noxh.component.expert-box')

        @if($noiBat->count())
            <div class="nx-panel">
                <h2 class="nx-panel__title">Câu hỏi nổi bật</h2>
                @foreach($noiBat as $ch)
                    <div class="nx-qa-item">
                        <div>
                            <h3 class="nx-qa-item__title">
                                <a href="{{ url('/hoi-dap/' . $ch->id) }}">{{ $ch->title }}</a>
                            </h3>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </aside>
</div>
@endsection
