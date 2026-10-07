@include('backend.dashboard.component.breadcrumb', ['title' => $config['seo']['create']['title']])
@php
    $url = (isset($config['method']) && $config['method'] == 'translate' ) ? route('system.save.translate', ['languageId' => $languageId]) : route('system.store')
@endphp
<form action="{{ $url }}" method="post" class="box">
    @csrf
    <div class="wrapper wrapper-content animated fadeInRight">
       
        <div class="uk-flex uk-flex-middle">
            @foreach($languages as $language)
            @if(count($languages) == 0) @continue @endif
            <a 
                class="image img-cover system-flag"
                href="{{ route('system.translate', ['languageId' => $language->id] ) }}"><img src="{{ image($language->image) }}" alt=""></a>
            @endforeach
        </div>
      
        @foreach($systemConfig as $key => $val)
        <div class="row">
            <div class="col-lg-5">
                <div class="panel-head">
                    <div class="panel-title">{{ $val['label'] }}</div>
                    <div class="panel-description">
                        {{ $val['description'] }}
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="ibox">
                    @if(count($val['value']))
                    <div class="ibox-content">
                        @foreach($val['value'] as $keyVal => $item)
                        @php
                            $name = $key.'_'.$keyVal;
                        @endphp
                        <div class="row mb15">
                            <div class="col-lg-12">
                                <div class="form-row">
                                    <label for="" class="uk-flex uk-flex-space-between" >
                                        <span>{{ $item['label'] }}</span> 
                                        {!! renderSystemLink($item) !!}
                                        {!! renderSystemTitle($item) !!}
                                    </label>

                                    @switch($item['type'])
                                        @case('text')
                                            {!! renderSystemInput($name, $systems) !!}
                                            @break
                                        @case('images')
                                            {!! renderSystemImages($name, $systems) !!}
                                            @break
                                        @case('textarea')
                                            {!! renderSystemTextarea($name, $systems) !!}
                                            @break
                                        @case('select')
                                            {!! renderSystemSelect($item, $name, $systems) !!}
                                            @break
                                        @case('editor')
                                            {!! renderSystemEditor($name, $systems) !!}
                                            @break

                                    @endswitch


                                </div>
                            </div>
                        </div>
                        @endforeach

                        {{--
                            Khối kiểm tra riêng cho Telegram.

                            Cấu hình Telegram sai thì biểu hiện duy nhất là "không
                            thấy thông báo nào về máy", mà có ít nhất bốn nguyên
                            nhân khác nhau: token sai, token dán thiếu ký tự, chat
                            id sai, hoặc bot chưa từng được nhắn. Ba nút dưới đây
                            trả lời dứt khoát từng cái.
                        --}}
                        @if($key === 'telegram')
                            <div class="telegram-kiem-tra" style="margin-top:14px;padding-top:14px;border-top:1px dashed #d9dee5">
                                <div class="uk-flex uk-flex-middle" style="gap:8px;flex-wrap:wrap">
                                    <button type="button" class="btn btn-default btn-sm" data-tg="token">Kiểm tra bot token</button>
                                    <button type="button" class="btn btn-default btn-sm" data-tg="chat-id">Tìm chat ID</button>
                                    <button type="button" class="btn btn-primary btn-sm" data-tg="gui-thu">Gửi tin thử</button>
                                </div>

                                <p class="text-muted" style="margin:8px 0 0;font-size:12.5px">
                                    Ba nút này dùng giá trị <strong>đã lưu</strong>. Vừa sửa ô bên trên thì bấm
                                    <strong>Lưu lại</strong> trước đã, rồi hãy kiểm tra.
                                </p>

                                <div class="telegram-ket-qua" hidden
                                     style="margin-top:10px;padding:10px 12px;border-radius:6px;font-size:13px;line-height:1.6"></div>
                            </div>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>
        <hr>
        @endforeach
       
        <div class="text-right mb15">
            <button class="btn btn-primary" type="submit" name="send" value="send">Lưu lại</button>
        </div>
    </div>
</form>

<script>
// Ba nút kiểm tra Telegram. Viết thẳng ở đây chứ không nhét vào gói JS đã build:
// trang quản trị này ít khi đổi, mà sửa gói JS thì phải build lại rồi mới thấy.
(function () {
    var khung = document.querySelector('.telegram-kiem-tra');
    if (!khung) return;

    var oKetQua = khung.querySelector('.telegram-ket-qua');
    var nut = khung.querySelectorAll('[data-tg]');

    function hien(chu, mau) {
        oKetQua.hidden = false;
        oKetQua.style.background = mau === 'loi' ? '#fdeceb' : (mau === 'canh-bao' ? '#fdf1e0' : '#e6f4ec');
        oKetQua.style.color = mau === 'loi' ? '#b3261e' : (mau === 'canh-bao' ? '#b45f06' : '#147a4b');
        oKetQua.innerHTML = chu;
    }

    function them(dong) {
        return dong ? '<div style="margin-top:6px">' + dong + '</div>' : '';
    }

    nut.forEach(function (b) {
        b.addEventListener('click', function () {
            var viec = b.getAttribute('data-tg');
            var token = document.querySelector('input[name="_token"]');

            nut.forEach(function (x) { x.disabled = true; });
            hien('Đang kiểm tra…', 'tin');

            fetch(@json(route('system.telegram.kiem-tra')), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token ? token.value : '',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ viec: viec })
            })
                .then(function (r) { return r.json(); })
                .then(function (kq) {
                    var chu = '';

                    if (kq.ok) {
                        if (viec === 'token') chu = 'Token hợp lệ. Bot: <b>' + (kq.bot || '') + '</b>';
                        else if (viec === 'gui-thu') chu = 'Đã gửi tin thử tới <b>' + (kq.den || '') + '</b>. Mở Telegram xem có tin chưa.';
                        else chu = 'Token dùng được.';
                    } else {
                        chu = 'Chưa được: <b>' + (kq.loi || 'không rõ') + '</b>';
                    }

                    if (kq.canhBao) chu += them('<b>Lưu ý:</b> ' + kq.canhBao);

                    if (kq.goiY) chu += them(kq.goiY);

                    if (kq.ds) {
                        if (!kq.ds.length) {
                            chu += them(kq.goiY || 'Chưa thấy chat nào đã nhắn cho bot.');
                        } else {
                            chu += them('Các chat đã nhắn cho bot — bấm vào để điền vào ô Chat ID ở trên:');
                            kq.ds.forEach(function (c) {
                                chu += them('<a href="#" data-chat="' + c.id + '"><code>' + c.id + '</code></a> — ' +
                                    (c.ten || '(không tên)') + ' <span style="opacity:.7">(' + c.loai + ')</span>');
                            });
                        }
                    }

                    hien(chu, kq.ok ? 'tot' : 'loi');

                    // Chat id rat dai va de chep sai, nen cho bam de dien thang.
                    oKetQua.querySelectorAll('[data-chat]').forEach(function (a) {
                        a.addEventListener('click', function (e) {
                            e.preventDefault();
                            var o = document.querySelector('input[name="config[telegram_chat_id]"]');
                            if (o) { o.value = a.getAttribute('data-chat'); o.focus(); }
                        });
                    });
                })
                .catch(function (e) { hien('Không gọi được máy chủ: ' + e.message, 'loi'); })
                .then(function () { nut.forEach(function (x) { x.disabled = false; }); });
        });
    });
})();
</script>
