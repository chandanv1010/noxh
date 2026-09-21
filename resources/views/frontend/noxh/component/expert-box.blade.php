{{-- Khoi chuyen gia o cot phai. Du lieu do NoxhComposer cap. --}}
@if($chuyenGia)
    <div class="nx-expert" style="margin-bottom:18px">
        <h2 class="nx-expert__title">{{ $chuyenGia->name }}</h2>
        @if($chuyenGia->title)
            <p class="nx-expert__name">{{ $chuyenGia->title }}</p>
        @endif

        @if($chuyenGia->commitments)
            <ul class="nx-expert__list">
                @foreach(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $chuyenGia->commitments))) as $ck)
                    <li>
                        @include('frontend.noxh.component.icon', ['name' => 'check-circle', 'size' => 16])
                        {{ $ck }}
                    </li>
                @endforeach
            </ul>
        @endif

        @if($chuyenGia->phone)
            <a href="tel:{{ preg_replace('/[^0-9+]/', '', $chuyenGia->phone) }}" class="nx-btn nx-btn--sm">
                @include('frontend.noxh.component.icon', ['name' => 'phone', 'size' => 15])
                {{ $chuyenGia->phone }}
            </a>
        @endif

        @if($chuyenGia->image)
            <div class="nx-expert__photo">
                <img src="{{ $chuyenGia->image }}" alt="{{ $chuyenGia->name }}" loading="lazy">
            </div>
        @endif
    </div>
@endif
