# -*- coding: utf-8 -*-
"""
Tach anh minh hoa cua trang Phong phap ly ra khoi ban thiet ke.

Ban ve la tai san cua du an (noxh_image/plxh fix.jpg) nen lay anh thang tu do
chinh xac hon la di tim mot buc anh khac tuong tu - va khong vuong ban quyen.

Sinh ra:
    public/uploads/noxh/dai-phap-ly.png   dai anh toa nha + can cong ly o dau
                                          trang (cat phan KHONG co chu)
    public/uploads/noxh/tu-van-vien.png   nguoi tu van o the "Can ho tro phap
                                          ly", da xoa nen thanh trong suot

Chay:
    python tools/tach-anh-ban-ve.py

Thu muc public/uploads nam trong .gitignore nen may khac phai chay lai lenh
nay (hoac quan tri up anh that de len trong man hinh Gioi thieu).
"""
import os
from collections import deque

from PIL import Image, ImageFilter

GOC = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
BAN_VE = os.path.join(os.path.dirname(GOC), 'noxh_image', 'plxh fix.jpg')
DICH = os.path.join(GOC, 'public', 'uploads', 'noxh')


def dai_dau_trang(im):
    """
    Dai anh o dau trang: toa nha, cay va can cong ly.

    Chi lay phan ban ve KHONG co chu: tu x=615 sang phai (het dong "...An tam
    mua Nha o xa hoi") va tren y=288 (tren bon o chip trang). Vung duoi va
    trai duoc lam mo dan ve trong suot de anh tan vao nen xanh cua dai.
    """
    o = im.crop((615, 80, 1090, 288)).convert('RGBA')
    o = o.resize((o.width * 2, o.height * 2), Image.LANCZOS)
    w, h = o.size

    mo = Image.new('L', (w, h), 255)
    px = mo.load()

    tan_trai = int(w * 0.26)
    tan_duoi = int(h * 0.3)

    for x in range(w):
        for y in range(h):
            a = 255
            if x < tan_trai:
                a = min(a, int(255 * x / tan_trai))
            if y > h - tan_duoi:
                a = min(a, int(255 * (h - y) / tan_duoi))
            px[x, y] = a

    o.putalpha(mo)

    return o


def nguoi_tu_van(im):
    """
    Nguoi tu van o the "Can ho tro phap ly", xoa nen cho trong suot.

    Nen quanh nguoi gan nhu trang nen loang tu bon canh vao - loang chu khong
    phai "cu trang la bo", vi ao so mi ben trong cung trang ma no bi ao vest
    vay kin nen khong bi loang toi.
    """
    o = im.crop((1325, 120, 1512, 400)).convert('RGBA')
    o = o.resize((o.width * 3, o.height * 3), Image.LANCZOS)
    w, h = o.size
    px = o.load()

    def la_nen(p):
        return min(p[0], p[1], p[2]) > 224 and max(p) - min(p) < 26

    xong = [[False] * h for _ in range(w)]
    hang = deque()

    for x in range(w):
        for y in (0, h - 1):
            if la_nen(px[x, y]) and not xong[x][y]:
                xong[x][y] = True
                hang.append((x, y))

    for y in range(h):
        for x in (0, w - 1):
            if la_nen(px[x, y]) and not xong[x][y]:
                xong[x][y] = True
                hang.append((x, y))

    while hang:
        x, y = hang.popleft()
        px[x, y] = (255, 255, 255, 0)

        for dx, dy in ((1, 0), (-1, 0), (0, 1), (0, -1)):
            a, b = x + dx, y + dy

            if 0 <= a < w and 0 <= b < h and not xong[a][b] and la_nen(px[a, b]):
                xong[a][b] = True
                hang.append((a, b))

    # Lam mem vien rang cua do cat theo nguong.
    mo = o.getchannel('A').filter(ImageFilter.GaussianBlur(1.2))
    o.putalpha(mo)

    return o


def main():
    if not os.path.exists(BAN_VE):
        raise SystemExit(f'Khong thay ban ve: {BAN_VE}')

    os.makedirs(DICH, exist_ok=True)
    im = Image.open(BAN_VE).convert('RGB')

    for ten, ham in (('dai-phap-ly.png', dai_dau_trang), ('tu-van-vien.png', nguoi_tu_van)):
        anh = ham(im)
        duong = os.path.join(DICH, ten)
        anh.save(duong)
        print(f'{duong}  {anh.size[0]}x{anh.size[1]}')


if __name__ == '__main__':
    main()
