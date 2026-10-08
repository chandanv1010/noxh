{{--
    Ban in cua mot bo ho so — duoc nhet vao file ZIP cua "Download tron bo".
    KHONG dung layout cua web: tep nay mo bang Word hoac trinh duyet doc lap, nen
    phai tu mang day du <html>, bang ma va kieu chu.
--}}
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="utf-8">
<title>Hồ sơ {{ $bo->name }}</title>
<style>
    body { font: 14px/1.6 "Times New Roman", serif; color: #111; margin: 28px 34px; }
    h1 { font-size: 20px; margin: 0 0 4px; }
    .nguon { color: #555; font-size: 12.5px; margin: 0 0 18px; }
    .mota { margin: 0 0 18px; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th, td { border: 1px solid #bbb; padding: 7px 9px; vertical-align: top; text-align: left; }
    th { background: #eee; font-size: 13px; }
    td.stt { width: 34px; text-align: center; }
    .bat-buoc { color: #b00; font-weight: 700; }
    .ghi-chu { color: #444; font-size: 13px; }
    .link { color: #0645ad; word-break: break-all; font-size: 12.5px; }
    .chan { margin-top: 26px; padding-top: 10px; border-top: 1px solid #bbb; color: #555; font-size: 12.5px; }
</style>
</head>
<body>

<h1>HỒ SƠ: {{ $bo->name }}</h1>
<p class="nguon">
    Danh sách giấy tờ cần chuẩn bị — in ra {{ now()->format('d/m/Y') }} từ {{ config('app.url') }}
</p>

@if($bo->description)
    <p class="mota">{{ nx_chu_thuan($bo->description) }}</p>
@endif

<table>
    <thead>
        <tr>
            <th class="stt">#</th>
            <th>Giấy tờ</th>
            <th>Nơi cấp / ghi chú</th>
            <th style="width:70px">Số bản</th>
        </tr>
    </thead>
    <tbody>
        @foreach($bo->items as $i => $gt)
            <tr>
                <td class="stt">{{ $i + 1 }}</td>
                <td>
                    <strong>{{ $gt->title }}</strong>
                    @if($gt->is_required)
                        <span class="bat-buoc">(bắt buộc)</span>
                    @endif
                    @if($gt->description)
                        <div class="ghi-chu">{{ nx_chu_thuan($gt->description) }}</div>
                    @endif
                    @if($gt->template_file)
                        <div class="link">Mẫu đơn: {{ $gt->template_file }}</div>
                    @endif
                </td>
                <td>{{ $gt->issued_by ?: '—' }}</td>
                <td style="text-align:center">{{ $gt->copies }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<p class="chan">
    Cần hỗ trợ kiểm tra điều kiện và kê khai hồ sơ, gọi hotline
    <strong>{{ cai_dat('hotline', '') ?: 'trên website' }}</strong>.
</p>

</body>
</html>
