# Tai anh goc do phan giai cao ve tools/anh-goc/
#
# Anh lay tu Wikimedia Commons (giay phep tu do, xem anh-goc/NGUON-ANH.txt).
# Commons chan tan suat nen script nay nghi giua cac lan tai va thu lai.
#
# Chay: powershell -File tools/tai-anh-goc.ps1
#
# LUU Y VE TIENG VIET: ten tep tren Commons co dau. Tep .ps1 nay duoc luu
# khong co BOM, ma Windows PowerShell 5.1 doc .ps1 theo bang ma ANSI, nen chu
# co dau trong tep se bi doc sai. Vi vay ten tep o day duoc viet san duoi dang
# da ma hoa %.. (ASCII) - dung dan nguyen ca chuoi vao URL.

$ErrorActionPreference = 'Stop'
$ProgressPreference = 'SilentlyContinue'

$dir = Join-Path $PSScriptRoot 'anh-goc'
New-Item -ItemType Directory -Force -Path $dir | Out-Null

$h = @{ 'User-Agent' = 'NOXH-demo/1.0 (local demo)' }

# ten da ma hoa tren Commons  ->  ten luu trong tools/anh-goc
$danhSach = @(
    @{
        ma  = 'File%3AChung%20c%C6%B0%20HH%20Linh%20%C4%90%C3%A0m%2C%20Ho%C3%A0ng%20Mai%2C%20H%C3%A0%20N%E1%BB%99i%20001.jpg'
        luu = 'linh-dam-01.jpg'
    }
)

foreach ($a in $danhSach) {
    $ra = Join-Path $dir $a.luu
    if (Test-Path $ra) { "Da co: $ra"; continue }

    for ($lan = 1; $lan -le 4; $lan++) {
        Start-Sleep -Seconds 4
        try {
            $u = 'https://commons.wikimedia.org/w/api.php?action=query&format=json&titles=' +
                 $a.ma + '&prop=imageinfo&iiprop=url|size|extmetadata'
            $r = Invoke-RestMethod -Uri $u -TimeoutSec 40 -Headers $h
            $trang = $r.query.pages.PSObject.Properties.Value | Select-Object -First 1
            $ii = $trang.imageinfo[0]

            Invoke-WebRequest -Uri $ii.url -OutFile $ra -TimeoutSec 300 -Headers $h -UseBasicParsing

            $kb = [math]::Round((Get-Item $ra).Length / 1KB)
            "OK $($a.luu)  $($ii.width)x$($ii.height)  $kb KB  giay phep: $($ii.extmetadata.LicenseShortName.value)"
            "   tac gia: $($ii.extmetadata.Artist.value -replace '<[^>]+>', '')"
            "   nguon  : $($ii.descriptionurl)"
            break
        }
        catch {
            if ($lan -eq 4) { throw "Khong tai duoc $($a.luu): $($_.Exception.Message)" }
            Start-Sleep -Seconds 10
        }
    }
}
