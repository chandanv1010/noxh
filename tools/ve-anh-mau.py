# -*- coding: utf-8 -*-
"""Ve cac anh minh hoa mau cua NOXH vao public/uploads/noxh/.

    python tools/ve-anh-mau.py

Vi sao ve chu khong tai anh tren mang: /public/uploads nam trong .gitignore
nen anh khong di theo ma nguon, may nao dung du an cung phai co lai bo anh
do. Ve bang script thi chay mot lenh la co, net o moi kich co, va khong
vuong ban quyen cua ai.

Day chi la anh DUNG DE XEM THU. Co anh that thi quan tri tai len de tay,
seeder NoxhProjectDetailDemoSeeder chi gan anh cho du an con trong.

Can: pip install pillow
"""
import os
import random

from PIL import Image, ImageDraw, ImageFont, ImageFilter

DICH = os.path.join(os.path.dirname(os.path.dirname(os.path.abspath(__file__))),
                    'public', 'uploads', 'noxh')

TUONG = (26, 26, 28)
NEN_P = (236, 226, 211)      # phong ngu / khach - be
NEN_WC = (214, 223, 231)     # ve sinh - xam xanh
NEN_BEP = (226, 232, 226)
NEN_BC = (222, 232, 238)
DO_GO = (150, 120, 92)
VAI = (168, 180, 194)


def font(sz, dam=True):
    ten = 'C:/Windows/Fonts/segoeui' + ('b' if dam else '') + '.ttf'
    for f in (ten, '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf'):
        try:
            return ImageFont.truetype(f, sz)
        except OSError:
            continue
    return ImageFont.load_default()


def luu(im, ten, co=None, q=92):
    os.makedirs(DICH, exist_ok=True)
    if co:
        im = im.resize(co, Image.LANCZOS)
    im.save(os.path.join(DICH, ten), quality=q, subsampling=0)
    print(ten, im.size)


# =============================================================================
# MAT BANG CAN HO
# =============================================================================
W, H = 1400, 1050


def phong(d, x0, y0, x1, y1, nen, ten=None, day=9):
    d.rectangle([x0, y0, x1, y1], fill=nen, outline=TUONG, width=day)
    if ten:
        f = font(30)
        w = d.textlength(ten, font=f)
        d.text(((x0 + x1 - w) / 2, y1 - 52), ten, font=f, fill=(70, 66, 60))


def giuong(d, x0, y0, x1, y1, doi=True):
    d.rounded_rectangle([x0, y0, x1, y1], 10, fill=(206, 214, 226),
                        outline=(120, 132, 148), width=4)
    g = (y1 - y0) * 0.26
    d.rounded_rectangle([x0 + 8, y0 + 8, x1 - 8, y0 + g], 8, fill=(238, 242, 248),
                        outline=(150, 162, 178), width=3)
    if doi:
        d.line([(x0 + x1) // 2, y0 + 8, (x0 + x1) // 2, y0 + g], fill=(150, 162, 178), width=3)


def tu(d, x0, y0, x1, y1):
    d.rectangle([x0, y0, x1, y1], fill=(222, 206, 186), outline=DO_GO, width=4)
    n = max(2, int((x1 - x0) / 60))
    for i in range(1, n):
        x = x0 + (x1 - x0) * i / n
        d.line([x, y0, x, y1], fill=DO_GO, width=3)


def sofa(d, x0, y0, x1, y1):
    d.rounded_rectangle([x0, y0, x1, y1], 12, fill=VAI, outline=(120, 132, 148), width=4)
    d.rounded_rectangle([x0 + 10, y0 + 10, x1 - 10, y0 + (y1 - y0) * 0.45], 8, fill=(196, 206, 219))


def ban(d, cx, cy, r):
    d.ellipse([cx - r, cy - r * 0.72, cx + r, cy + r * 0.72], fill=(226, 214, 196),
              outline=DO_GO, width=4)


def bep(d, x0, y0, x1, y1):
    d.rectangle([x0, y0, x1, y1], fill=(206, 214, 210), outline=(110, 120, 116), width=4)
    d.ellipse([x0 + 14, y0 + 14, x0 + 54, y0 + 54], outline=(110, 120, 116), width=4)
    d.ellipse([x0 + 66, y0 + 14, x0 + 106, y0 + 54], outline=(110, 120, 116), width=4)


def wc(d, x0, y0, x1, y1):
    d.rounded_rectangle([x0 + 14, y0 + 16, x0 + 62, y0 + 86], 14, fill=(250, 252, 254),
                        outline=(130, 144, 158), width=4)
    d.ellipse([x1 - 76, y0 + 16, x1 - 18, y0 + 66], fill=(250, 252, 254),
              outline=(130, 144, 158), width=4)
    d.rectangle([x0 + 14, y1 - 96, x1 - 14, y1 - 16], fill=(238, 244, 250),
                outline=(130, 144, 158), width=4)


def khung():
    im = Image.new('RGB', (W, H), 'white')
    return im, ImageDraw.Draw(im)


def mat_bang():
    M = 60

    # --- 1PN - 1WC
    im, d = khung()
    phong(d, M, M, W - M, H - M, (252, 252, 252))
    phong(d, M, M, W - M, M + 150, NEN_BC, 'BAN CÔNG')
    phong(d, M, M + 150, 800, H - M, NEN_P, 'PHÒNG KHÁCH - BẾP')
    sofa(d, 140, 560, 470, 700)
    ban(d, 610, 640, 110)
    bep(d, 120, M + 190, 420, M + 300)
    phong(d, 800, M + 150, W - M, 660, NEN_P, 'PHÒNG NGỦ')
    giuong(d, 880, 300, 1200, 600)
    tu(d, 1230, 290, 1300, 600)
    phong(d, 800, 660, W - M, H - M, NEN_WC, 'WC')
    wc(d, 830, 700, 1310, 930)
    luu(im, 'mat-bang-1pn-1wc.jpg', (W // 2, H // 2))

    # --- 2PN - 1WC
    im, d = khung()
    phong(d, M, M, W - M, H - M, (252, 252, 252))
    phong(d, M, M, W - M, M + 140, NEN_BC, 'BAN CÔNG')
    phong(d, M, M + 140, 560, 640, NEN_P, 'PHÒNG NGỦ 1')
    giuong(d, 130, 300, 420, 560)
    phong(d, M, 640, 560, H - M, NEN_P, 'PHÒNG NGỦ 2')
    giuong(d, 130, 700, 420, 930, doi=False)
    phong(d, 560, M + 140, 1020, H - M, NEN_BEP, 'KHÁCH - BẾP')
    sofa(d, 640, 520, 940, 660)
    ban(d, 790, 790, 120)
    bep(d, 620, M + 180, 900, M + 290)
    phong(d, 1020, M + 140, W - M, 620, NEN_WC, 'WC')
    wc(d, 1050, 250, 1310, 560)
    phong(d, 1020, 620, W - M, H - M, NEN_P, 'KHO')
    tu(d, 1060, 700, 1300, 800)
    luu(im, 'mat-bang-2pn-1wc.jpg', (W // 2, H // 2))

    # --- 2PN - 2WC
    im, d = khung()
    phong(d, M, M, W - M, H - M, (252, 252, 252))
    phong(d, M, M, W - M, M + 130, NEN_BC, 'BAN CÔNG')
    phong(d, M, M + 130, 520, 600, NEN_P, 'PHÒNG NGỦ 1')
    giuong(d, 120, 280, 400, 520)
    phong(d, M, 600, 520, H - M, NEN_P, 'PHÒNG NGỦ 2')
    giuong(d, 120, 670, 400, 910, doi=False)
    phong(d, 520, M + 130, 1000, 600, NEN_WC, 'WC 1')
    wc(d, 560, 250, 950, 540)
    phong(d, 520, 600, 1000, H - M, NEN_WC, 'WC 2')
    wc(d, 560, 660, 950, 930)
    phong(d, 1000, M + 130, W - M, H - M, NEN_BEP, 'KHÁCH - BẾP')
    sofa(d, 1050, 420, 1300, 560)
    ban(d, 1175, 720, 110)
    bep(d, 1040, M + 170, 1290, M + 280)
    luu(im, 'mat-bang-2pn-2wc.jpg', (W // 2, H // 2))


# =============================================================================
# MAT BANG TONG THE (nhin tu tren xuong)
# =============================================================================
def tong_the():
    W2, H2 = 1600, 1200
    im = Image.new('RGB', (W2, H2), (206, 226, 196))
    d = ImageDraw.Draw(im)
    random.seed(7)

    for _ in range(600):
        x, y = random.randrange(W2), random.randrange(H2)
        r = random.randrange(6, 26)
        d.ellipse([x, y, x + r, y + r], fill=(196, 218, 186))

    DUONG = (86, 90, 96)
    VACH = (244, 246, 240)

    def duong(x0, y0, x1, y1):
        d.rectangle([x0, y0, x1, y1], fill=DUONG)
        if x1 - x0 > y1 - y0:
            y = (y0 + y1) / 2
            for x in range(int(x0) + 20, int(x1) - 20, 70):
                d.line([x, y, x + 36, y], fill=VACH, width=5)
        else:
            x = (x0 + x1) / 2
            for y in range(int(y0) + 20, int(y1) - 20, 70):
                d.line([x, y, x, y + 36], fill=VACH, width=5)

    duong(0, 60, W2, 146)
    duong(0, H2 - 150, W2, H2 - 60)
    duong(70, 0, 156, H2)
    duong(W2 - 160, 0, W2 - 70, H2)

    d.rectangle([250, 300, W2 - 250, 340], fill=(150, 154, 158))
    d.rectangle([250, 300, 292, H2 - 320], fill=(150, 154, 158))
    d.rectangle([250, H2 - 360, W2 - 250, H2 - 320], fill=(150, 154, 158))
    d.rectangle([W2 - 292, 300, W2 - 250, H2 - 320], fill=(150, 154, 158))

    d.ellipse([700, 520, 950, 700], fill=(150, 196, 226), outline=(120, 170, 204), width=6)
    d.rounded_rectangle([380, 780, 620, 900], 18, fill=(224, 176, 140),
                        outline=(196, 150, 116), width=5)

    def toa(x0, y0, x1, y1, ten):
        d.rounded_rectangle([x0 + 14, y0 + 16, x1 + 14, y1 + 16], 10, fill=(150, 160, 150))
        d.rounded_rectangle([x0, y0, x1, y1], 10, fill=(238, 238, 236),
                            outline=(120, 122, 124), width=5)
        for gx in range(int(x0) + 26, int(x1) - 26, 46):
            for gy in range(int(y0) + 26, int(y1) - 26, 46):
                d.rectangle([gx, gy, gx + 28, gy + 28], fill=(206, 212, 218),
                            outline=(170, 176, 182), width=2)
        f = font(46)
        w = d.textlength(ten, font=f)
        cx, cy = (x0 + x1) / 2, (y0 + y1) / 2
        d.rounded_rectangle([cx - w / 2 - 20, cy - 36, cx + w / 2 + 20, cy + 36], 14, fill='white')
        d.text((cx - w / 2, cy - 28), ten, font=f, fill=(24, 82, 168))

    toa(360, 400, 660, 720, 'CT1')
    toa(1000, 400, 1300, 720, 'CT2')
    toa(360, 940, 660, 1060, 'CT3')
    toa(1000, 780, 1300, 1060, 'CT4')

    f = font(34)
    t = 'ĐƯỜNG QUY HOẠCH'
    w = d.textlength(t, font=f)
    d.rectangle([W2 / 2 - w / 2 - 22, 68, W2 / 2 + w / 2 + 22, 120], fill=DUONG)
    d.text((W2 / 2 - w / 2, 76), t, font=f, fill=(238, 240, 236))

    luu(im, 'mat-bang-tong-the.jpg', (1200, 900))


# =============================================================================
# BAN DO (khong ve ghim - trang tu dat nhan ten du an de len)
# =============================================================================
def ban_do():
    W3, H3 = 1200, 900
    im = Image.new('RGB', (W3, H3), (238, 240, 234))
    d = ImageDraw.Draw(im)
    random.seed(11)

    for _ in range(9):
        x, y = random.randrange(W3), random.randrange(H3)
        d.rounded_rectangle([x, y, x + random.randrange(90, 260), y + random.randrange(70, 190)],
                            26, fill=(216, 232, 208))

    for _ in range(3):
        x, y = random.randrange(W3 - 200), random.randrange(H3 - 140)
        d.ellipse([x, y, x + random.randrange(110, 200), y + random.randrange(70, 130)],
                  fill=(186, 214, 236))

    d.line([(-20, 240), (210, 330), (430, 300), (620, 430), (840, 470), (1220, 400)],
           fill=(170, 206, 232), width=22, joint='curve')

    d.line([(0, 560), (300, 540), (640, 600), (1200, 560)], fill=(250, 226, 160),
           width=26, joint='curve')
    d.line([(520, 0), (560, 300), (500, 620), (540, 900)], fill=(250, 226, 160),
           width=22, joint='curve')

    for _ in range(16):
        x0, y0 = random.randrange(W3), random.randrange(H3)
        d.line([(x0, y0), (x0 + random.randrange(-320, 320), y0 + random.randrange(-260, 260))],
               fill='white', width=random.choice([7, 9, 12]), joint='curve')

    luu(im, 'ban-do-mac-dinh.jpg', q=90)


# =============================================================================
# ANH NEN DAI DAU TRANG CHI TIET (bau troi)
# =============================================================================
def nen_dau_trang():
    W4, H4 = 2400, 760
    im = Image.new('RGB', (W4, H4))
    d = ImageDraw.Draw(im)

    for y in range(H4):
        t = y / H4
        d.line([0, y, W4, y], fill=(int(176 + 71 * t), int(214 + 37 * t), int(246 + 9 * t)))

    may = Image.new('RGB', (W4, H4), (0, 0, 0))
    dm = ImageDraw.Draw(may)
    random.seed(3)

    for _ in range(26):
        cx = random.randrange(W4)
        cy = random.randrange(40, int(H4 * 0.58))
        for _ in range(14):
            rx = random.randrange(70, 210)
            ry = int(rx * random.uniform(0.32, 0.5))
            ox = random.randrange(-190, 190)
            oy = random.randrange(-34, 34)
            dm.ellipse([cx + ox - rx, cy + oy - ry, cx + ox + rx, cy + oy + ry], fill='white')

    may = may.filter(ImageFilter.GaussianBlur(38))
    im = Image.composite(Image.new('RGB', (W4, H4), 'white'), im,
                         may.convert('L').point(lambda v: min(255, int(v * 0.72))))

    luu(im, 'nen-dau-trang.jpg', q=88)


# =============================================================================
# FILE TAI LIEU MAU (.pdf)
# =============================================================================
# The giay to o tab "Phap ly" / "Tai lieu" mo file trong tab moi. Khong co
# file thi bam vao khong ra gi, nen du lieu mau can vai file that.
TAI_LIEU = [
    ('van-ban-mau.pdf', 'VĂN BẢN PHÁP LÝ (BẢN MẪU)', [
        'Đây là văn bản mẫu dùng để xem thử giao diện.',
        'Tải văn bản thật lên ở màn hình',
        'QL Dự án NOXH → Hồ sơ pháp lý.',
    ]),
    ('tai-lieu-bang-gia.pdf', 'BẢNG GIÁ BÁN DỰ KIẾN (BẢN MẪU)', [
        'Căn 1PN - 1WC     19,55 – 21 m²      1,075 – 1,180 tỷ',
        'Căn 2PN - 1WC     25 – 28 m²         1,180 – 1,260 tỷ',
        'Căn 2PN - 2WC     32 – 36 m²         1,280 – 1,420 tỷ',
        '',
        'Giá chưa bao gồm phí bảo trì và các khoản phí khác.',
    ]),
    ('tai-lieu-mau-don.pdf', 'ĐƠN ĐĂNG KÝ MUA NHÀ Ở XÃ HỘI (BẢN MẪU)', [
        'Họ và tên: ..........................................',
        'Số CCCD: ...........................................',
        'Nơi thường trú: ..................................',
        'Thu nhập bình quân: ...........................',
        'Loại căn hộ đăng ký: ...........................',
    ]),
    ('tai-lieu-huong-dan.pdf', 'HƯỚNG DẪN HỒ SƠ VAY GÓI ƯU ĐÃI (BẢN MẪU)', [
        '1. Đơn đề nghị vay vốn theo mẫu của ngân hàng.',
        '2. Giấy tờ tùy thân và giấy tờ cư trú.',
        '3. Giấy tờ chứng minh thu nhập.',
        '4. Hợp đồng mua bán căn hộ.',
        '5. Giấy tờ chứng minh đã nộp phần vốn tự có.',
    ]),
]


def tai_lieu():
    W5, H5 = 1240, 1754      # A4 o 150 dpi

    for ten, tieuDe, dong in TAI_LIEU:
        im = Image.new('RGB', (W5, H5), 'white')
        d = ImageDraw.Draw(im)

        d.rectangle([0, 0, W5, 120], fill=(21, 101, 216))
        d.text((90, 44), 'NOXH.vn', font=font(40), fill='white')

        d.text((90, 210), tieuDe, font=font(34), fill=(16, 42, 90))
        d.line([90, 272, W5 - 90, 272], fill=(200, 212, 228), width=3)

        y = 330
        for t in dong:
            d.text((90, y), t, font=font(26, dam=False), fill=(40, 46, 58))
            y += 58

        d.text((90, H5 - 90), 'Bản mẫu do tools/ve-anh-mau.py sinh ra.',
               font=font(20, dam=False), fill=(130, 138, 150))

        os.makedirs(DICH, exist_ok=True)
        im.save(os.path.join(DICH, ten), 'PDF', resolution=150)
        print(ten)


def van_ban_word():
    """
    Mot ban Word mau, de xem duoc the giay to mau XANH (DOC) ben canh cac the
    mau do (PDF) - ban thiet ke co ca hai loai.

    Tu dong goi file .docx chu khong dung thu vien ngoai: mot .docx chi la
    mot file zip chua vai file XML.
    """
    import zipfile

    ten = 'van-ban-mau.docx'
    dong = [
        'VĂN BẢN HƯỚNG DẪN (BẢN MẪU)',
        '',
        'Đây là bản mẫu do tools/ve-anh-mau.py sinh ra để chạy thử giao diện.',
        'Quản trị tải văn bản thật lên để thay thế.',
    ]

    doan = ''.join(
        '<w:p><w:r><w:t xml:space="preserve">%s</w:t></w:r></w:p>' % t.replace('&', '&amp;')
        for t in dong
    )

    os.makedirs(DICH, exist_ok=True)

    with zipfile.ZipFile(os.path.join(DICH, ten), 'w', zipfile.ZIP_DEFLATED) as z:
        z.writestr('[Content_Types].xml',
                   '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                   '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
                   '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
                   '<Default Extension="xml" ContentType="application/xml"/>'
                   '<Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>'
                   '</Types>')
        z.writestr('_rels/.rels',
                   '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                   '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
                   '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>'
                   '</Relationships>')
        z.writestr('word/document.xml',
                   '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                   '<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'
                   '<w:body>' + doan + '</w:body></w:document>')

    print(ten)


if __name__ == '__main__':
    mat_bang()
    tong_the()
    ban_do()
    nen_dau_trang()
    tai_lieu()
    van_ban_word()
