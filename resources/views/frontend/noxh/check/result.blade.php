@extends('frontend.noxh.layout')

@php
    // Ban ve: noxh_image/thanh-cong.jpg, luu y.jpg, that bai.jpg.
    //
    // Ba ban ve la MOT trang, chi khac mau va khac vai khoi o cot phai, nen
    // o day dung chung mot khung; phan nao khac nhau doc tu bang introduces
    // theo muc ket qua (high / medium / low).
    $muc = in_array($luot->result_level, ['high', 'medium', 'low'], true) ? $luot->result_level : 'low';
    $o = fn ($ten, $du = '') => trim((string) ($intro['result_' . $muc . '_' . $ten] ?? '')) ?: $du;
    $chung = fn ($ten, $du = '') => trim((string) ($intro['result_' . $ten] ?? '')) ?: $du;

    $tieuChi = $luot->tieuChi();
    $tong = (int) ($luot->criteria_total ?: count($tieuChi));
    $dat = (int) $luot->criteria_passed;
    $phanTram = $tong > 0 ? round($dat / $tong * 100) : 0;

    $nhan = [
        'pass' => $chung('badge_pass', 'Phù hợp'),
        'unclear' => $chung('badge_unclear', 'Cần xác minh'),
        'fail' => $chung('badge_fail', 'Chưa đáp ứng'),
    ];

    $hinh = ['pass' => 'check', 'unclear' => 'question', 'fail' => 'close'];

    $luuY = array_values(array_filter(array_map('trim',
        preg_split('/\r\n|\r|\n/', (string) $chung('notice_lines'))
    )));

    $ngay = \Illuminate\Support\Carbon::parse($luot->created_at);
@endphp

@section('content')
<div class="nx-kq nx-kq--{{ $muc }}">
    <div class="nx__container">
        <div class="nx-kq__dinh">
            <a href="{{ url('/') }}" class="nx-kq__ve">
                @include('frontend.noxh.component.icon', ['name' => 'arrow-left', 'size' => 17])
                {{ $chung('back_text', 'Quay về trang chủ') }}
            </a>

            <span class="nx-kq__luc">
                @include('frontend.noxh.component.icon', ['name' => 'clock-line', 'size' => 16])
                {{ strtr($chung('time_text', 'Kết quả được tạo lúc {gio} - {ngay}'), [
                    '{gio}' => $ngay->format('H:i'),
                    '{ngay}' => $ngay->format('d/m/Y'),
                ]) }}
            </span>
        </div>

        <div class="nx-kq__dau">
            @if($o('image'))
                <img class="nx-kq__hinh" src="{{ $o('image') }}" alt="" width="112" height="112">
            @endif

            <p class="nx-kq__nhan">{{ $chung('label', 'Kết quả kiểm tra sơ bộ') }}</p>
            <h1 class="nx-kq__ten">{{ $o('heading') }}</h1>

            @if($o('description'))
                <p class="nx-kq__mo">{{ $o('description') }}</p>
            @endif
        </div>

        <div class="nx-kq__luoi">
            <div class="nx-kq__trai">
                <div class="nx-panel nx-kq-diem">
                    <div class="nx-kq-diem__hang">
                        <span>{{ $chung('score_label', 'Điểm đánh giá') }}</span>
                        <strong>{{ $dat }} / {{ $tong }}</strong>
                    </div>
                    <div class="nx-kq-diem__thanh">
                        <span style="width: {{ $phanTram }}%"></span>
                    </div>
                </div>

                <div class="nx-panel nx-kq-bang">
                    <h2>{{ $chung('detail_heading', 'Chi tiết kết quả theo từng tiêu chí') }}</h2>

                    @forelse($tieuChi as $tc)
                        @php $kl = in_array($tc['verdict'] ?? '', ['pass', 'unclear', 'fail'], true) ? $tc['verdict'] : 'unclear'; @endphp
                        <div class="nx-kq-bang__hang la-{{ $kl }}">
                            <span class="nx-kq-bang__dau">
                                @include('frontend.noxh.component.icon', ['name' => $hinh[$kl], 'size' => 18])
                            </span>

                            <span class="nx-kq-bang__chu">
                                <strong>{{ $tc['label'] ?? '' }}</strong>
                                @if(!empty($tc['text']))
                                    <small>{{ $tc['text'] }}</small>
                                @endif
                            </span>

                            <span class="nx-kq-bang__nhan">{{ $nhan[$kl] }}</span>
                        </div>
                    @empty
                        <p class="nx-kq-bang__trong">{{ $chung('empty_text', 'Chưa chấm được tiêu chí nào.') }}</p>
                    @endforelse
                </div>

                @if(count($luuY))
                    <div class="nx-kq-luuy">
                        <span class="nx-kq-luuy__hinh">
                            @include('frontend.noxh.component.icon', ['name' => 'info', 'size' => 18])
                        </span>
                        <div>
                            <strong>{{ $chung('notice_heading', 'Lưu ý quan trọng') }}</strong>
                            <ul>
                                @foreach($luuY as $dong)
                                    <li>{{ $dong }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <div class="nx-kq-nut">
                    <a href="{{ route('noxh.check.index') }}" class="nx-kq-nut__phu">
                        @include('frontend.noxh.component.icon', ['name' => 'update', 'size' => 18])
                        {{ $chung('again_text', 'Kiểm tra lại') }}
                    </a>

                    @if($o('guide_text'))
                        <a href="{{ url($o('guide_link', '/ho-so')) }}" class="nx-kq-nut__phu">
                            @include('frontend.noxh.component.icon', ['name' => 'doc-line', 'size' => 18])
                            {{ $o('guide_text') }}
                        </a>
                    @endif

                    <a href="{{ url($chung('expert_link', '/cong-hoa/tu-van')) }}" class="nx-kq-nut__chinh">
                        @include('frontend.noxh.component.icon', ['name' => 'phone', 'size' => 18])
                        {{ $chung('expert_text', 'Tư vấn với chuyên gia ngay') }}
                        @include('frontend.noxh.component.icon', ['name' => 'arrow-right', 'size' => 17])
                    </a>
                </div>
            </div>

            <aside class="nx-kq__phai">
                @include('frontend.noxh.check.result-side')
            </aside>
        </div>
    </div>

    @if($muc === 'high' && $chung('banner_image'))
        <div class="nx-kq-dai" style="--nx-anh: url('{{ $chung('banner_image') }}')">
            <div class="nx__container nx-kq-dai__trong">
                @if($chung('banner_quote'))
                    <p class="nx-kq-dai__cau">{{ $chung('banner_quote') }}</p>
                @endif
            </div>
        </div>
    @elseif($goiY->count())
        <div class="nx-kq-goi">
            <div class="nx__container">
                <div class="nx-kq-goi__dau">
                    <div>
                        <h2>{{ $chung('suggest_heading', 'Có thể bạn quan tâm') }}</h2>
                        <p>{{ $chung('suggest_description', 'Một số dự án phù hợp với nhu cầu của bạn') }}</p>
                    </div>
                    <a href="{{ url('/du-an') }}">
                        {{ $chung('suggest_all_text', 'Xem tất cả dự án') }}
                        @include('frontend.noxh.component.icon', ['name' => 'arrow-right', 'size' => 16])
                    </a>
                </div>

                <div class="nx-kq-goi__ds">
                    @foreach($goiY as $d)
                        <a href="{{ url('/du-an/' . $d->canonical) }}" class="nx-kq-goi__o">
                            @if($d->image)
                                <img src="{{ $d->image }}" alt="{{ $d->name }}" loading="lazy">
                            @endif
                            <strong>{{ $d->name }}</strong>
                            @if($d->province_name)
                                <small>{{ $d->province_name }}</small>
                            @endif
                            @if($d->price_from)
                                <span>{{ $chung('suggest_price_text', 'Chỉ từ') }} {{ tien_viet($d->price_from, true) }}/m²</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
