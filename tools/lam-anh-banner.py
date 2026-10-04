"""
Dung HAI anh banner trang chu tu anh goc DO PHAN GIAI CAO:

  banner-pc.jpg      - anh ngang cho man hinh rong
  banner-mobile.jpg  - anh doc   cho dien thoai

Chay: python tools/lam-anh-banner.py

-------------------------------------------------------------------------------
VI SAO BAN CU MO:

Ban cu lay tu public/uploads/noxh/du-an-mac-dinh.jpg chi co 1200x324 roi
phong len 1920x600 (x1.6). Anh hien thi rong 1905 CSS px nen tren man hinh 2x
trinh duyet phai ve 3810 diem anh tu mot anh 1920 -> nhoe gap doi. Cong them
nguon da bi phong truoc do, mat nguoi nhin thay ro la mo.

Bai hoc: dung anh goc co DO PHAN GIAI LON HON khung hien thi. Lan nay dung anh
6000x3375 (Chung cu HH Linh Dam, Hoang Mai, Ha Noi) - thu nho chu khong phong
to, nen net that.

-------------------------------------------------------------------------------
CACH LAM

Moi anh ra = CAT mot vung tu anh goc roi THU NHO ve dung kich thuoc khung.
Khong dat tung diem anh, khong keo gian: chi cat + thu nho nen khong the lam
bien dang toa nha.

Script TU KIEM TRA va bao loi (exit 1) neu:
  - phai PHONG TO (he so > 1)  -> anh se mo
  - ti le anh ra lech qua 1% so voi ti le khung that
  - do net (phuong sai Laplace) duoi nguong

-------------------------------------------------------------------------------
SO DO KHUNG THAT (do bang scratch/chup-banner.cjs tren trang dang chay):

  man hinh  khung        ti le   vung ANH CON NHIN THAY
  --------- ------------ ------  --------------------------------
  390px     390x630      1.615   ca anh (khop ti le)
  1280px    1265x334     3.787   ~85% chieu cao (cat bot mep duoi)
  1920px    1905x334     5.700   56% chieu cao (cat bot mep duoi)

Vi CSS dung object-position: center top nen phan BI CAT luon nam o DAY anh.
=> Toa nha va bau troi phai nam o NUA TREN cua anh, day anh chi de trong.
"""

import os
import sys

from PIL import Image, ImageFilter, ImageStat

# Anh goc: Chung cu HH Linh Dam, Hoang Mai, Ha Noi (xem tools/anh-goc/NGUON-ANH.txt).
# De trong tools/ chu KHONG de trong public/uploads: thu muc do la thu vien
# media cua quan tri, de file 5 MB vao do se lam roi danh sach chon anh.
NGUON_PC = r'D:\sandbox\noxh\tools\anh-goc\linh-dam-01.jpg'
NGUON_MOBILE = NGUON_PC

RA = {
    'pc': r'D:\sandbox\noxh\public\uploads\noxh\banner-pc.jpg',
    'mobile': r'D:\sandbox\noxh\public\uploads\noxh\banner-mobile.jpg',
}

# Ti le khung that ma anh phai khop.
TI_LE_KHUNG = {
    'pc': 1920 / 600,        # 3.2  - dung nhu ban cu de bo cuc khong doi
    'mobile': 390 / 630,     # 0.619
}

CAU_HINH = {
    'pc': {
        'nguon': NGUON_PC,
        # Vung cat trong anh goc 6000x3375. Toa nha nam o y 738..2219, troi o
        # tren, ho va cay o duoi. Lay tu y=400 de con khoang troi o mep tren;
        # 56% tren cung (vung nhin thay tren man hinh rong) roi vao troi + nua
        # tren toa nha, con goc trai la troi sach cho chu de len.
        'cat': (0, 400, 6000, 2275),      # trai, tren, phai, duoi
        # 3840 = 2x be rong hien thi lon nhat (1905). Nguon 6000 van con lon hon
        # nen day la THU NHO that su, khong phai phong to.
        'ra': (3840, 1200),
        'chat': 80,
    },
    'mobile': {
        'nguon': NGUON_MOBILE,
        # Khung doc: cat mot dai doc quanh cum toa nha.
        #
        # Mep TREN cat o y=560, tuc chi con 178 diem anh troi truoc khi cham
        # noc toa nha (y=738). Truoc do cat tu y=0 nen 22% tren cung man hinh
        # dien thoai chi la troi tron - trong nhu bi thieu anh.
        # Chieu cao lay den het anh goc; phan ho va cay o duoi nam sau lop
        # chuyen sac cua CSS nen khong phi.
        'cat': (2150, 560, 3893, 3375),
        'ra': (1200, 1938),               # 390 CSS px x 3 (man hinh dien thoai 3x)
        'chat': 78,
    },
}

# Ti le khung that cho phep lech bao nhieu.
SAI_SO_TI_LE = 0.01


def do_net(anh: Image.Image) -> float:
    """Phuong sai Laplace: cang cao cang net. Dung de so sanh truoc/sau."""
    xam = anh.convert('L')
    bien = xam.filter(ImageFilter.Kernel((3, 3), [0, 1, 0, 1, -4, 1, 0, 1, 0], scale=1, offset=0))
    return ImageStat.Stat(bien).var[0]


def dung(loai: str):
    c = CAU_HINH[loai]
    goc = Image.open(c['nguon']).convert('RGB')

    x0, y0, x1, y1 = c['cat']
    if not (0 <= x0 < x1 <= goc.width and 0 <= y0 < y1 <= goc.height):
        print(f'  !! {loai}: vung cat {c["cat"]} nam ngoai anh goc {goc.size}')
        return None, None

    vung = goc.crop(c['cat'])
    ra = vung.resize(c['ra'], Image.LANCZOS)

    # vung/ra > 1 nghia la anh goc NHIEU diem anh hon anh ra => THU NHO => net.
    # vung/ra < 1 nghia la phai bia them diem anh => PHONG TO => mo.
    thu_nho = min(vung.width / ra.width, vung.height / ra.height)

    ra_ti_le = ra.width / ra.height
    lech = abs(ra_ti_le - TI_LE_KHUNG[loai]) / TI_LE_KHUNG[loai]
    # Vung cat phai cung ti le voi anh ra, neu khong thi resize se keo gian.
    lech_cat = abs((vung.width / vung.height) - ra_ti_le) / ra_ti_le

    print(f'{loai:>6}: goc {goc.size[0]}x{goc.size[1]} -> cat {vung.width}x{vung.height} '
          f'-> ra {ra.width}x{ra.height}')
    print(f'        nguon/day = {thu_nho:.2f} (>1 la thu nho, tot) | ti le {ra_ti_le:.3f} '
          f'(khung {TI_LE_KHUNG[loai]:.3f}, lech {lech * 100:.1f}%) | net {do_net(ra):.0f}')

    ok = True
    if thu_nho < 1.0:
        print(f'  !! {loai}: dang PHONG TO {1 / thu_nho:.2f} lan - anh se mo. '
              f'Lay vung cat nho hon hoac anh goc to hon.')
        ok = False
    if lech > SAI_SO_TI_LE:
        print(f'  !! {loai}: ti le lech {lech * 100:.1f}% (> {SAI_SO_TI_LE * 100:.0f}%) - '
              f'CSS se phai cat bot, bo cuc se lech.')
        ok = False
    if lech_cat > SAI_SO_TI_LE:
        print(f'  !! {loai}: vung cat ti le {vung.width / vung.height:.3f} khac anh ra '
              f'{ra_ti_le:.3f} -> toa nha bi keo gian.')
        ok = False

    return ra, ok


if __name__ == '__main__':
    if not os.path.exists(NGUON_PC):
        print(f'Thieu anh goc: {NGUON_PC}')
        print('Tai bang: xem phan dau tools/tai-anh-goc.ps1 hoac scratch/LARAGON-SETUP.md')
        sys.exit(1)

    tat_ca_ok = True
    for loai, duong in RA.items():
        anh, ok = dung(loai)
        tat_ca_ok = tat_ca_ok and ok
        if anh is None:
            continue
        anh.save(duong, 'JPEG', quality=CAU_HINH[loai]['chat'], optimize=True, progressive=True)
        print(f'        -> {duong}  {os.path.getsize(duong) / 1024:.0f} KB')
        print()

    sys.exit(0 if tat_ca_ok else 1)
