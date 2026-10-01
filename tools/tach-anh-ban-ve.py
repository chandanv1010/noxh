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
    public/uploads/noxh/kiem-tra-dau-trang.jpg
                                          day nha o ben phai dai dau trang
                                          "Kiem tra kha nang mua" (w-1.jpg)
    public/uploads/noxh/kt-bang-kep.png   hinh tron "bang kep co dau tich" va
    public/uploads/noxh/kt-khien-khoa.png hinh tron "khien co o khoa" cua
                                          trang mo dau (start-fix.jpg)
    public/uploads/noxh/dt-*.png          12 hinh tron cua tung nhom doi tuong
                                          o buoc 1 (w-1.jpg)
    public/uploads/noxh/kt-tien.png       hinh tron "chong dong xu" tren dau
                                          cau hoi thu nhap (w-2.jpg)
    public/uploads/noxh/th-*.png          tranh cua tung tinh huong o buoc
                                          thu nhap (w-2.jpg)
    public/uploads/noxh/cs-*.png          hinh vuong cua ba dap an buoc Chinh
                                          sach (w-3.jpg)
    public/uploads/noxh/nh-*.png          hinh tron cua ba loai nha o (w-4.jpg)
    public/uploads/noxh/kt-tam-*.png      tranh o cot phai cac buoc 3, 4, 5
    public/uploads/noxh/kq-*.png          hinh tron va tranh cua ba trang ket
                                          qua (thanh-cong / luu y / that bai)
    public/uploads/noxh/tv-*.png          sau anh chan dung tu van vien cua
                                          khoi "Danh sach tu van ho tro"
                                          (product-detail-fix.jpg)
    public/uploads/noxh/kiem-tra-nen.jpg  tranh nen (day nha, hang cay, luoi
                                          cham) cua trang mo dau bo kiem tra
                                          dieu kien (start-fix.jpg)

Chay:
    python tools/tach-anh-ban-ve.py

Thu muc public/uploads nam trong .gitignore nen may khac phai chay lai lenh
nay (hoac quan tri up anh that de len trong man hinh Gioi thieu).
"""
import os
from collections import deque

import numpy as np
from PIL import Image, ImageDraw, ImageFilter

GOC = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
ANH = os.path.join(os.path.dirname(GOC), 'noxh_image')
BAN_VE = os.path.join(ANH, 'plxh fix.jpg')
BAN_VE_TIN = os.path.join(ANH, 'tin-tuc-fix.webp')
BAN_VE_KT = os.path.join(ANH, 'w-1.jpg')
BAN_VE_BD = os.path.join(ANH, 'start-fix.jpg')
BAN_VE_KT2 = os.path.join(ANH, 'w-2.jpg')
BAN_VE_KT3 = os.path.join(ANH, 'w-3.jpg')
BAN_VE_KT4 = os.path.join(ANH, 'w-4.jpg')
BAN_VE_KT5 = os.path.join(ANH, 'w-5.jpg')
BAN_VE_CT = os.path.join(ANH, 'product-detail-fix.jpg')
BAN_VE_KQ = {
    'high': os.path.join(ANH, 'thanh-cong.jpg'),
    'medium': os.path.join(ANH, 'luu y.jpg'),
    'low': os.path.join(ANH, 'that bai.jpg'),
}
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


def anh_dau_kiem_tra(im):
    """
    Day nha o ben phai dai dau trang "Kiem tra kha nang mua" (w-1.jpg).

    Vung nay bi HAI thu de len:

      - net but do nguoi dung danh dau tren ban ve, nam gon trong x 576..687;
      - dong chu viet tay "An cu hom nay / Kien tao tuong lai", x 688..866,
        y 120..222, viet de len khoang troi.

    Net do thi chi can cat tu x=688 tro di la het. Chu viet tay thi khong cat
    bo duoc vi no nam giua khoang troi can giu, nen xoa bang cach: do tim tung
    diem muc (xanh dam tren nen troi nhat), no rong ra vai diem cho het vien
    nhoe, roi to lai bang mau TRUNG VI CUA CHINH HANG DO - troi o day la mot
    dai mau chuyen deu theo chieu doc nen to lai gan nhu khong thay vet.
    """
    TRAI, PHAI = 688, 1154        # het net but do -> mep phai ban ve
    TREN, DUOI = 68, 356          # duoi thanh dau trang -> tren thanh buoc
    CHU_TREN, CHU_DUOI = 112, 228  # khung bao dong chu viet tay
    CHU_PHAI = 872

    im = im.crop((TRAI, TREN, PHAI, DUOI))
    px = im.load()
    rong, cao = im.size

    for y in range(CHU_TREN - TREN, min(CHU_DUOI - TREN, cao)):
        muc = []
        sach = []

        for x in range(0, CHU_PHAI - TRAI):
            r, g, b = px[x, y]
            if b > 110 and b - r > 45 and b - g > 28:
                muc.append(x)
            else:
                sach.append((r, g, b))

        if not muc or len(sach) < 20:
            continue

        sach.sort(key=lambda c: c[0] + c[1] + c[2])
        nen = sach[len(sach) // 2]

        # No rong vung muc ba diem moi ben cho het vien nhoe cua JPEG.
        xoa = set()
        for x in muc:
            for d in range(-3, 4):
                if 0 <= x + d < CHU_PHAI - TRAI:
                    xoa.add(x + d)

        for x in xoa:
            px[x, y] = nen

    # Lam min lai dung o khung chu de khong con vet ngang.
    khung = (0, max(0, CHU_TREN - TREN - 4), CHU_PHAI - TRAI, min(cao, CHU_DUOI - TREN + 4))
    im.paste(im.crop(khung).filter(ImageFilter.GaussianBlur(1.2)), khung)

    # Gap doi kich thuoc cho man hinh mat do cao.
    return im.resize((rong * 2, cao * 2), Image.LANCZOS)


def nen_bat_dau(im):
    """
    Tranh nen cua trang mo dau bo kiem tra dieu kien (start-fix.jpg).

    Tranh la mot day nha mau xanh rat nhat, hang cay o chan trang va mot luoi
    cham o goc tren phai. Chu va cac khoi giao dien nam de len giua tranh nen
    khong the cat mot vung nao ra dung duoc - phai XOA phan giao dien di roi
    va lai cho nen lien.

    Cach xoa chia hai tang:

      - Nua tren (chu tieu de, dong mo ta, the thoi gian): chu la muc DAM
        (do sang < 178) con tranh nen cho nao toi nhat cung con 190, nen chi
        can do do sang la tach duoc, khong dung toi mang tranh nao.

      - Nua duoi (the trang, nut, dai ghi chu): cac khoi nay MAU TRANG, do
        sang khong tach duoc. Nhung o vung do tranh nen chi ve toi x=145 ben
        trai va x=1262 ben phai, giua la khoang trang - nen quet han mot o
        chu nhat.

    Cho da xoa duoc va lai bang cach noi thang mau tu diem sach gan nhat ben
    trai sang diem sach gan nhat ben phai cua chinh hang do: nen la mot dai
    mau chuyen deu theo chieu ngang nen noi thang nhu vay khong thay vet.
    """
    TREN, DUOI = 107, 1122          # duoi thanh dau trang -> het ban ve
    NUA = 615 - TREN                # ranh giua hai tang xoa
    QUET_TRAI, QUET_PHAI = 145, 1262
    NGUONG_MUC = 178
    NO_RONG = 7

    # Hai khoi o nua tren co nen SANG (dia tron sau hinh bang kep, the "Thoi
    # gian thuc hien") nen do sang khong bat duoc - quet han theo o chu nhat.
    O_SANG = (
        (630, 135, 780, 265),
        (450, 530, 955, 605),
    )

    im = im.crop((0, TREN, im.size[0], DUOI))
    m = np.asarray(im).astype(np.int16)
    cao, rong = m.shape[0], m.shape[1]

    sang = m.mean(axis=2)
    xoa = sang < NGUONG_MUC
    xoa[NUA:, :] = False
    xoa[NUA:, QUET_TRAI:QUET_PHAI] = True

    for x0, y0, x1, y1 in O_SANG:
        xoa[y0 - TREN:y1 - TREN, x0:x1] = True

    # No rong vet muc cho het vien nhoe cua JPEG.
    for d in range(1, NO_RONG + 1):
        xoa[:, d:] |= xoa[:, :-d]
        xoa[:, :-d] |= xoa[:, d:]

    for y in range(cao):
        hang = xoa[y]
        if not hang.any():
            continue

        x = 0
        while x < rong:
            if not hang[x]:
                x += 1
                continue

            dau = x
            while x < rong and hang[x]:
                x += 1
            cuoi = x - 1

            trai = m[y, dau - 1] if dau > 0 else m[y, cuoi + 1] if cuoi + 1 < rong else None
            phai = m[y, cuoi + 1] if cuoi + 1 < rong else trai

            if trai is None:
                continue

            n = cuoi - dau + 1
            for i in range(n):
                t = (i + 1) / (n + 1)
                m[y, dau + i] = trai + (phai - trai) * t

    # Noi ngang xong thi tung hang van con lech nhau vai don vi, nhin ra
    # thanh nhung vet ke ngang mo mo cho vua xoa chu. Lam min THEO CHIEU DOC
    # va chi o dung cho da xoa: mang tranh hai ben giu nguyen do net.
    doc = np.copy(m).astype(np.float32)
    cong = np.cumsum(np.vstack([np.zeros((1, rong, 3), np.float32), doc]), axis=0)
    r = 7
    tren = np.clip(np.arange(cao) - r, 0, cao)
    duoi = np.clip(np.arange(cao) + r + 1, 0, cao)
    doc = (cong[duoi] - cong[tren]) / (duoi - tren)[:, None, None]
    m = np.where(xoa[:, :, None], doc, m)

    im = Image.fromarray(np.clip(m, 0, 255).astype(np.uint8))
    im = im.filter(ImageFilter.GaussianBlur(0.7))

    # Phong to cho man hinh rong - tranh nen mem nen phong khong vo hat.
    return im.resize((int(rong * 1.4), int(cao * 1.4)), Image.LANCZOS)


def _cat_tron(im, cx, cy, r, phong=3):
    """
    Cat mot hinh TRON ra khoi ban ve thanh anh PNG co nen trong suot.

    Ban ve ve san ca dia mau nhat lan hinh ben trong; cat ca cum nhu vay thi
    giu dung mau va dung net ma khong phai ve lai. Cat vuong roi dat len trang
    se lo goc vuong nen phai boi mat na tron; mat na ve o kich thuoc lon roi
    thu nho lai de vien tron khong bi rang cua.
    """
    o = im.crop((cx - r, cy - r, cx + r, cy + r)).resize((2 * r * phong, 2 * r * phong), Image.LANCZOS)

    mat = Image.new('L', (o.size[0] * 4, o.size[1] * 4), 0)
    ImageDraw.Draw(mat).ellipse((0, 0, mat.size[0] - 1, mat.size[1] - 1), fill=255)
    mat = mat.resize(o.size, Image.LANCZOS)

    o = o.convert('RGBA')
    o.putalpha(mat)

    return o


def hinh_tron_bat_dau(im):
    """Hai hinh tron lon cua trang mo dau bo kiem tra dieu kien."""
    return [
        ('kt-bang-kep.png', _cat_tron(im, 700, 198, 61)),
        ('kt-khien-khoa.png', _cat_tron(im, 240, 719, 64)),
    ]


def hinh_doi_tuong(im):
    """
    Hinh tron cua tung nhom doi tuong o buoc 1 (w-1.jpg).

    Ten file dat theo gia tri luu cua dap an (cot `value`) chu khong theo so
    thu tu: quan tri doi thu tu cac o thi hinh van di theo dung nhom.

    BAN VE BI DANH DAU BANG BUT DO. Net do de len bon hinh:

      - "ho-ngheo-thien-tai": net chi phu len mang cay ben phai, ma canh phai
        cua tranh gan nhu lap lai canh trai, nen lay guong ben kia dap sang -
        nhin khong ra vet.
      - "luc-luong-vu-trang": ve dung mot nguoi si quan giong het o
        "cach-mang" (chi khac mau dia), nen lay tranh cua o kia rot sang roi
        doi mau dia.
      - "ho-ngheo-nong-thon" va "hai-con": net do cat ngang giua nguoi, khong
        con du tranh de dap lai. HAI HINH NAY KHONG CAT RA DUOC - trang ngoai
        se lui ve dung hinh Material da chon cho dap an do. Muon co dung
        tranh cua ban ve thi phai gui lai file w-1.jpg CHUA danh dau.
    """
    TEN = [
        'cach-mang', 'ho-ngheo-nong-thon', 'ho-ngheo-thien-tai', 'ho-ngheo-do-thi',
        'thu-nhap-thap', 'cong-nhan', 'luc-luong-vu-trang', 'can-bo',
        'hoc-sinh-sinh-vien', 'doanh-nghiep', 'hai-con', 'chua-xac-dinh',
    ]
    BO_QUA = {'ho-ngheo-nong-thon', 'hai-con'}
    LAY_GUONG = {'ho-ngheo-thien-tai'}
    CHEP_TU = {'luc-luong-vu-trang': ('cach-mang', (251, 227, 223), (229, 247, 226))}

    # Tam cua tung hinh tron, DO TAY tung o mot chu khong tinh theo cong
    # thuc: ban ve la anh ve ra chu khong phai anh chup trang that, bon cot
    # khong deu nhau (khoang cach 268 / 258 / 285), tinh theo cong thuc thi
    # hai o ben phai cat vao chu.
    TAM = (
        (109, 707), (378, 710), (634, 707), (915, 707),
        (103, 844), (374, 843), (631, 845), (912, 845),
        (101, 989), (371, 989), (631, 990), (918, 990),
    )
    BAN_KINH = 41

    def tam(i):
        return TAM[i]

    m = np.asarray(im).astype(int)
    r, g, b = m[:, :, 0], m[:, :, 1], m[:, :, 2]
    but = (r > 140) & (g < 130) & (b < 130) & (r - g > 70) & (r - b > 70)

    for _ in range(3):
        but[1:, :] |= but[:-1, :]
        but[:-1, :] |= but[1:, :]
        but[:, 1:] |= but[:, :-1]
        but[:, :-1] |= but[:, 1:]

    def o(i, guong=False):
        cx, cy = tam(i)
        R = BAN_KINH + 3
        con = m[cy - R:cy + R, cx - R:cx + R].copy()

        if guong:
            vet = but[cy - R:cy + R, cx - R:cx + R]
            lat = con[:, ::-1]
            dap = vet & ~vet[:, ::-1]
            con[dap] = lat[dap]

        return Image.fromarray(np.clip(con, 0, 255).astype(np.uint8)), R

    ra = []

    for i, ten in enumerate(TEN):
        if ten in BO_QUA:
            continue

        if ten in CHEP_TU:
            nguon, cu, moi = CHEP_TU[ten]
            anh, R = o(TEN.index(nguon))
            d = np.asarray(anh).astype(int)
            gan = np.abs(d - np.array(cu)).sum(axis=2) < 34
            d[gan] = moi
            anh = Image.fromarray(np.clip(d, 0, 255).astype(np.uint8))
        else:
            anh, R = o(i, guong=ten in LAY_GUONG)

        ra.append(('dt-' + ten + '.png', _cat_tron(anh, R, R, BAN_KINH)))

    return ra


def hinh_thu_nhap(im):
    """
    Hinh tron va tranh cua tung tinh huong o buoc thu nhap (w-2.jpg).

    Ban ve nay cung bi danh dau bang but do: net do khoanh qua tam giua
    ("Doc than nuoi con nho") nen tranh do KHONG cat ra duoc - trang ngoai se
    lui ve ve hinh net trong vong tron mau. Hai tam con lai va hinh tron
    chong dong xu deu sach.
    """
    ra = [('kt-tien.png', _cat_tron(im, 613, 357, 58))]

    # (ten file, khung tranh, mau nen cua tam) - khung do tay tren ban ve.
    TRANH = (
        ('th-doc-than.png', (85, 570, 232, 745), (242, 247, 253)),
        ('th-ket-hon.png', (822, 570, 988, 745), (253, 241, 241)),
    )

    for ten, khung, nen in TRANH:
        o = im.crop(khung).convert('RGBA')
        m = np.asarray(o).astype(int)

        # Nen cua tam bo di cho tranh dat duoc len bat ky mau nao.
        trong = np.abs(m[:, :, :3] - np.array(nen)).sum(axis=2) < 26
        m[:, :, 3] = np.where(trong, 0, 255)

        ra.append((ten, Image.fromarray(m.astype(np.uint8))))

    return ra


def _vuong_trong(im, khung, nen, phong=3):
    """Cat mot o vuong roi bo nen phang di, giu lai hinh ben trong."""
    o = im.crop(khung)
    o = o.resize((o.size[0] * phong, o.size[1] * phong), Image.LANCZOS).convert('RGBA')

    m = np.asarray(o).astype(int)
    trong = np.abs(m[:, :, :3] - np.array(nen)).sum(axis=2) < 30
    m[:, :, 3] = np.where(trong, 0, 255)

    return Image.fromarray(m.astype(np.uint8))


def hinh_chinh_sach(im):
    """
    Ba hinh vuong cua buoc "Chinh sach" va buc tranh o cot phai (w-3.jpg).

    Ban ve nay ve o kich thuoc khac hai ban truoc (rong 1024), toa do do
    rieng chu khong dung chung cong thuc nao.
    """
    TEN = ('cs-chua-ho-tro.png', 'cs-da-ho-tro.png', 'cs-khong-chac.png')
    HANG = (703, 830, 951)
    TRAI, RONG = 124, 74

    ra = []

    for ten, y in zip(TEN, HANG):
        ra.append((ten, _vuong_trong(im, (TRAI, y, TRAI + RONG, y + RONG), (243, 247, 252))))

    ra.append(('kt-tam-gia-dinh.png', im.crop((700, 770, 1010, 1060))))

    return ra


def hinh_nha_o(im):
    """
    Ba hinh tron cua buoc "Nha o" va buc tranh o cot phai (w-4.jpg).

    Net but do de len hinh tron thu hai ("Co dat nhung chua co nha"). Manh
    tranh do la mot dai dat co hang cay, hai nua gan giong nhau nen lay guong
    ben kia dap sang la lien.
    """
    TAM = (('nh-chua-co-nha.png', 174, 386, False),
           ('nh-co-dat.png', 452, 386, True),
           ('nh-co-nha.png', 731, 386, False))
    BAN_KINH = 44

    m = np.asarray(im).astype(int)
    r, g, b = m[:, :, 0], m[:, :, 1], m[:, :, 2]
    but = (r > 150) & (g < 115) & (b < 115) & (r - g > 85) & (r - b > 85)

    for _ in range(3):
        but[1:, :] |= but[:-1, :]
        but[:-1, :] |= but[1:, :]
        but[:, 1:] |= but[:, :-1]
        but[:, :-1] |= but[:, 1:]

    ra = []

    for ten, cx, cy, guong in TAM:
        R = BAN_KINH + 3
        con = m[cy - R:cy + R, cx - R:cx + R].copy()

        if guong:
            vet = but[cy - R:cy + R, cx - R:cx + R]
            lat = con[:, ::-1]
            dap = vet & ~vet[:, ::-1]
            con[dap] = lat[dap]

        anh = Image.fromarray(np.clip(con, 0, 255).astype(np.uint8))
        ra.append((ten, _cat_tron(anh, R, R, BAN_KINH)))

    # Hinh tron dau cau hoi cua buoc Nha o.
    ra.append(('kt-nha.png', _cat_tron(im, 94, 268, 46)))
    ra.append(('kt-tam-toa-nha.png', im.crop((895, 660, 1210, 940))))

    return ra


def hinh_nhap_tin(im):
    """Hinh tron va buc tranh cua buoc cuoi - o nhap thong tin (w-5.jpg)."""
    return [
        ('kt-ho-so.png', _cat_tron(im, 120, 325, 45)),
        ('kt-tam-dien-thoai.png', im.crop((820, 500, 1160, 830))),
    ]


def hinh_ket_qua(im, muc):
    """
    Hinh tron lon va buc tranh cot phai cua mot trang ket qua.

    Ba ban ve thanh-cong.jpg / luu y.jpg / that bai.jpg ve cung mot khung,
    chi khac mau va khac hinh, nen toa do dung chung.
    """
    TAM_X, TAM_Y, BAN_KINH = 512, 158, 56

    # Khung buc tranh o cot phai - moi trang mot cho khac nhau.
    TRANH = {
        'high': (676, 688, 986, 1012),
        'medium': (676, 378, 984, 516),
        'low': (676, 364, 984, 578),
    }

    ra = [('kq-' + muc + '.png', _cat_tron(im, TAM_X, TAM_Y, BAN_KINH))]
    ra.append(('kq-tranh-' + muc + '.png', im.crop(TRANH[muc])))

    # Dai anh cuoi trang chi co o ban thanh cong.
    if muc == 'high':
        ra.append(('kq-dai-duoi.jpg', im.crop((0, 1232, 1024, 1424))))

    return ra


def hinh_tu_van(im):
    """
    Sau anh chan dung cua khoi "DANH SACH TU VAN HO TRO" o trang chi tiet du
    an (product-detail-fix.jpg).

    Ban ve ve san sau nguoi kem ten; truoc day trang chi ve dia tron mang hai
    chu cai dau nen khong doi chieu duoc voi ban ve. Cat thang anh ra dung
    hon la di tim sau buc anh chan dung khac.

    Anh goc chi 44x44 - do la kich thuoc that trong ban ve - nen phong len
    ba lan cho du net o man hinh mat do cao, khong phong hon duoc nua.

    Ten tep dat theo ten nguoi (bo dau) de seeder gan dung nguoi.
    """
    TEN = (
        'tv-nguyen-van-hung.png',
        'tv-tran-thi-mai.png',
        'tv-le-thi-thu.png',
        'tv-pham-minh-duc.png',
        'tv-hoang-thi-lan.png',
        'tv-vu-quang-huy.png',
    )

    # Tam tung o, do bang cach quet cot x=693 tim cac dai hang co muc. Ban ve
    # do AI sinh nen khoang cach giua cac hang khong deu (56 - 60.5), phai
    # ghi tung tam mot chu khong tinh bang mot buoc nhay.
    TAM_Y = (717.5, 773.5, 830.0, 890.5, 948.5, 1008.0)
    TAM_X = 691.5
    NUA = 23
    PHONG = 3

    ra = []

    for ten, y in zip(TEN, TAM_Y):
        o = im.crop((
            int(round(TAM_X - NUA)), int(round(y - NUA)),
            int(round(TAM_X + NUA)), int(round(y + NUA)),
        ))
        o = o.resize((o.size[0] * PHONG, o.size[1] * PHONG), Image.LANCZOS)
        ra.append((ten, o))

    return ra


def main():
    os.makedirs(DICH, exist_ok=True)

    viec = (
        (BAN_VE, 'dai-phap-ly.png', dai_dau_trang),
        (BAN_VE, 'tu-van-vien.png', nguoi_tu_van),
        (BAN_VE_TIN, 'bang-ron-trang-trong.png', bang_ron_trang_trong),
        (BAN_VE_TIN, '', anh_bai_viet),
        (BAN_VE_KT, 'kiem-tra-dau-trang.jpg', anh_dau_kiem_tra),
        (BAN_VE_BD, 'kiem-tra-nen.jpg', nen_bat_dau),
        (BAN_VE_BD, '', hinh_tron_bat_dau),
        (BAN_VE_KT, '', hinh_doi_tuong),
        (BAN_VE_KT2, '', hinh_thu_nhap),
        (BAN_VE_KT3, '', hinh_chinh_sach),
        (BAN_VE_KT4, '', hinh_nha_o),
        (BAN_VE_KT5, '', hinh_nhap_tin),
        (BAN_VE_CT, '', hinh_tu_van),
        (BAN_VE_KQ['high'], '', lambda im: hinh_ket_qua(im, 'high')),
        (BAN_VE_KQ['medium'], '', lambda im: hinh_ket_qua(im, 'medium')),
        (BAN_VE_KQ['low'], '', lambda im: hinh_ket_qua(im, 'low')),
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
