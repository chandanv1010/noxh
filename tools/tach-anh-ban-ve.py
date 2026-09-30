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
    public/uploads/noxh/bang-ron-trang-trong.png
                                          dai anh xanh o dau trang Tin tuc -
                                          dung chung cho ca trang chi tiet du an
    public/uploads/noxh/bai-viet-1..5.jpg anh minh hoa nam bai viet mau
    public/uploads/noxh/bai-viet-lon.jpg  anh dau bai cua bai viet mau

Chay:
    python tools/tach-anh-ban-ve.py

Thu muc public/uploads nam trong .gitignore nen may khac phai chay lai lenh
nay (hoac quan tri up anh that de len trong man hinh Gioi thieu).
"""
import os
from collections import deque

from PIL import Image, ImageFilter

GOC = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
ANH = os.path.join(os.path.dirname(GOC), 'noxh_image')
BAN_VE = os.path.join(ANH, 'plxh fix.jpg')
BAN_VE_TIN = os.path.join(ANH, 'tin-tuc-fix.webp')
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


def bang_ron_trang_trong(im):
    """
    Dai anh xanh o dau trang Tin tuc: troi xanh ben trai, khu chung cu va cay
    ben phai, khau hieu viet tay o goc phai.

    Ban ve in san dong "Tin tuc" va cau mo ta de len nua trai cua dai, ma hai
    dong do la chu dong (quan tri sua duoc) nen khong the giu. Cach lam:

      1. Do mau nen xanh cua TUNG HANG o khoang x 30..205 - khoang duy nhat
         chua co anh chup de len - roi keo mau do ra het be ngang.
      2. Dan de len phan anh KHONG dinh chu (x 660..1135), lap sang trai bang
         cach lat guong xen ke cho phu het.
      3. Giu nguyen goc phai (tu x=1135) vi do la khau hieu viet tay.
      4. Mot mat na mo dan tu trai sang giu cho nua trai van la troi xanh -
         dung cho de dat tieu de trang, dung nhu ban ve.
    """
    TREN, DUOI = 52, 149
    ANH_TRAI, ANH_PHAI = 660, 1135
    CHU_TAY = 1135
    NEN_TRAI, NEN_PHAI = 30, 205

    dai = im.crop((0, TREN, im.width, DUOI))
    w, h = dai.size
    px = dai.load()

    nen = []

    for y in range(h):
        mau = [px[x, y] for x in range(NEN_TRAI, NEN_PHAI)
               if px[x, y][2] > 140 and px[x, y][2] - px[x, y][0] > 55
               and px[x, y][2] - px[x, y][1] > 30]

        if mau:
            mau.sort(key=lambda c: c[2])
            nen.append(mau[len(mau) // 2])
        else:
            nen.append(nen[-1] if nen else (20, 110, 200))

    # Trung vi tung hang nhay vai don vi; de nguyen thi nen bi ke soc ngang.
    nen = [
        tuple(round(sum(c[i] for c in nen[max(0, y - 6):y + 7]) / len(nen[max(0, y - 6):y + 7])) for i in range(3))
        for y in range(h)
    ]

    ket = Image.new('RGB', (w, h))
    kpx = ket.load()

    for y in range(h):
        for x in range(w):
            kpx[x, y] = nen[y]

    anh = dai.crop((ANH_TRAI, 0, ANH_PHAI, h))
    guong = anh.transpose(Image.FLIP_LEFT_RIGHT)

    lop = Image.new('RGB', (w, h))
    x, lan = ANH_TRAI, 0

    while x > -anh.width:
        x -= anh.width
        lan += 1
        lop.paste(guong if lan % 2 else anh, (x, 0))

    lop.paste(anh, (ANH_TRAI, 0))
    lop.paste(dai.crop((CHU_TAY, 0, w, h)), (CHU_TAY, 0))

    na = Image.new('L', (w, h), 255)
    npx = na.load()
    BAT, HET = 60, 340

    for x in range(w):
        a = 255 if x >= HET else (0 if x <= BAT else int(255 * (x - BAT) / (HET - BAT)))

        for y in range(h):
            npx[x, y] = a

    ket.paste(lop, (0, 0), na)

    return ket.resize((1920, round(1920 * h / w)), Image.LANCZOS)


def anh_bai_viet(im):
    """
    Sau buc anh minh hoa bai viet in tren ban ve: nam anh nho o khoi "Bai viet
    lien quan" ben phai va mot anh lon o dau bai giua trang.

    Tra ve mot danh sach (ten tep, anh) chu khong phai mot anh - cac ham khac
    trong tep nay chi sinh mot anh nen main() xu ly rieng truong hop nay.

    Vi sao lay tu ban ve: du an chua co anh chup that, ma tai anh o tren mang
    ve thi vuong ban quyen. Anh trong ban ve la tai san cua du an nen dung
    duoc ngay, va dung y nguyen bo anh ma ban thiet ke da chon.
    """
    # Nam anh nho: cung khung 100x56, cach nhau 63..67 diem anh theo chieu doc
    # (khoang cach le nhau vi tieu de ben canh dai ngan khac nhau).
    NHO_X, NHO_RONG, NHO_CAO = 1006, 100, 56
    NHO_DINH = (221, 288, 355, 419, 483)

    ket = []

    for i, y in enumerate(NHO_DINH, start=1):
        o = im.crop((NHO_X, y, NHO_X + NHO_RONG, y + NHO_CAO))
        o = o.resize((o.width * 3, o.height * 3), Image.LANCZOS)
        ket.append((f'bai-viet-{i}.jpg', o))

    # Anh dau bai: cat den y=458, DUOI do la dong chu thich in san tren ban ve.
    lon = im.crop((370, 313, 962, 458))
    lon = lon.resize((lon.width * 2, lon.height * 2), Image.LANCZOS)
    ket.append(('bai-viet-lon.jpg', lon))

    return ket


def main():
    os.makedirs(DICH, exist_ok=True)

    viec = (
        (BAN_VE, 'dai-phap-ly.png', dai_dau_trang),
        (BAN_VE, 'tu-van-vien.png', nguoi_tu_van),
        (BAN_VE_TIN, 'bang-ron-trang-trong.png', bang_ron_trang_trong),
        (BAN_VE_TIN, '', anh_bai_viet),
    )

    for nguon, ten, ham in viec:
        if not os.path.exists(nguon):
            raise SystemExit(f'Khong thay ban ve: {nguon}')

        ket = ham(Image.open(nguon).convert('RGB'))

        # Ham tra ve mot anh, hoac mot danh sach (ten, anh) khi cat nhieu anh
        # cung mot luot.
        for t, anh in (ket if isinstance(ket, list) else [(ten, ket)]):
            duong = os.path.join(DICH, t)
            anh.save(duong, quality=88)
            print(f'{duong}  {anh.size[0]}x{anh.size[1]}')


if __name__ == '__main__':
    main()
