"""
Ve ba anh co nguon ngon ngu ma bang `languages` dang tro toi.

Chay: python tools/lam-anh-co.py

Vi sao can: `languages.image` tro toi /public/userfiles/image/language/*.png
nhung ban clone khong kem theo cac tep do, nen moi trang quan tri deu bao 404
mot anh co o bo chuyen ngon ngu.

Kich thuoc nho (60x40) vi chi hien o thanh doi ngon ngu phia tren form.
Ve bang hinh hoc co ban, khong can tai mang.
"""

import os

from PIL import Image, ImageDraw

RA = r'D:\sandbox\noxh\public\userfiles\image\language'
RONG, CAO = 60, 40


def nen(mau):
    a = Image.new('RGB', (RONG, CAO), mau)
    return a, ImageDraw.Draw(a)


def ngoi_sao(d, cx, cy, r_ngoai, r_trong, mau, so_canh=5):
    """Ngoi sao nam canh: hai ban kinh xen ke, tinh bang toa do cuc."""
    import math
    diem = []
    for i in range(so_canh * 2):
        goc = -math.pi / 2 + i * math.pi / so_canh
        r = r_ngoai if i % 2 == 0 else r_trong
        diem.append((cx + r * math.cos(goc), cy + r * math.sin(goc)))
    d.polygon(diem, fill=mau)


def co_viet_nam():
    a, d = nen((218, 37, 29))            # nen do
    ngoi_sao(d, RONG / 2, CAO / 2, 11.5, 4.6, (255, 255, 0))
    return a


def co_anh():
    """Union Jack: chu thap do vien trang, cheo trang do, lop xanh."""
    a, d = nen((1, 33, 105))             # nen xanh dam
    # Cheo trang (rong)
    d.line([(0, 0), (RONG, CAO)], fill=(255, 255, 255), width=8)
    d.line([(RONG, 0), (0, CAO)], fill=(255, 255, 255), width=8)
    # Cheo do (hep)
    d.line([(0, 0), (RONG, CAO)], fill=(200, 16, 46), width=3)
    d.line([(RONG, 0), (0, CAO)], fill=(200, 16, 46), width=3)
    # Chu thap trang
    d.rectangle([RONG / 2 - 6, 0, RONG / 2 + 6, CAO], fill=(255, 255, 255))
    d.rectangle([0, CAO / 2 - 4, RONG, CAO / 2 + 4], fill=(255, 255, 255))
    # Chu thap do
    d.rectangle([RONG / 2 - 3.5, 0, RONG / 2 + 3.5, CAO], fill=(200, 16, 46))
    d.rectangle([0, CAO / 2 - 2.5, RONG, CAO / 2 + 2.5], fill=(200, 16, 46))
    return a


def co_trung_quoc():
    a, d = nen((222, 41, 16))            # nen do
    vang = (255, 222, 0)
    ngoi_sao(d, 12, 11, 6.5, 2.6, vang)  # sao lon
    # Bon sao nho quanh sao lon
    for cx, cy in ((23, 5), (27, 12), (27, 20), (23, 27)):
        ngoi_sao(d, cx, cy, 2.6, 1.05, vang)
    return a


if __name__ == '__main__':
    os.makedirs(RA, exist_ok=True)
    for ten, ham in [
        ('Flag_of_Vietnam_svg.png', co_viet_nam),
        ('en.png', co_anh),
        ('cn.png', co_trung_quoc),
    ]:
        duong = os.path.join(RA, ten)
        ham().save(duong, 'PNG', optimize=True)
        print(f'{ten:<28} {RONG}x{CAO}  {os.path.getsize(duong) / 1024:.1f} KB  -> {duong}')
