<?php

namespace App\Http\Controllers\Frontend\Noxh;

use App\Classes\Introduce;
use App\Classes\NoxhIcon;
use App\Http\Controllers\FrontendController;
use App\Http\ViewComposers\NoxhComposer;
use App\Models\Product;
use App\Models\ProjectDocument;
use App\Models\ProjectFaq;
use App\Models\ProjectHighlight;
use App\Models\ProjectMilestone;
use App\Models\ProjectUnit;
use App\Models\User;
use App\Repositories\Noxh\PostQuery;
use App\Repositories\Noxh\ProjectQuery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProjectController extends FrontendController
{
    /** Cac muc cua bo loc gia va dien tich - dung chung cho ca loc lan dem. */
    public const KHOANG_GIA = [
        'duoi-18' => ['nhan' => 'Dưới 18', 'tu' => null, 'den' => 18],
        '18-20' => ['nhan' => '18 – 20', 'tu' => 18, 'den' => 20],
        '20-22' => ['nhan' => '20 – 22', 'tu' => 20, 'den' => 22],
        'tren-22' => ['nhan' => 'Trên 22', 'tu' => 22, 'den' => null],
    ];

    public const KHOANG_DIEN_TICH = [
        'duoi-50' => ['nhan' => 'Dưới 50', 'tu' => null, 'den' => 50],
        '50-60' => ['nhan' => '50 – 60', 'tu' => 50, 'den' => 60],
        '60-70' => ['nhan' => '60 – 70', 'tu' => 60, 'den' => 70],
        'tren-70' => ['nhan' => 'Trên 70', 'tu' => 70, 'den' => null],
    ];

    /** So the tinh/thanh trong khoi ban do o cot phai (ban thiet ke ve 6). */
    public const SO_TINH_COT_PHAI = 6;

    /** So tin trong khoi "Tin tuc noi bat" o cot phai. */
    public const SO_TIN_COT_PHAI = 3;

    protected $projectQuery;
    protected $postQuery;

    /** Cac o chu quan tri sua duoc - cung nguon voi bien $intro cua view. */
    protected array $intro = [];

    public function __construct(ProjectQuery $projectQuery, PostQuery $postQuery)
    {
        $this->projectQuery = $projectQuery;
        $this->postQuery = $postQuery;
        $this->intro = NoxhComposer::intro();
        parent::__construct();
    }

    public function index(Request $request)
    {
        $loc = $this->docBoLoc($request);

        $duAn = $this->projectQuery->danhSach($loc, $request->input('sap-xep', 'moi-nhat'), 10);

        $tongDuAn = $this->projectQuery->tongSoDuAn();
        $tongTinh = $this->projectQuery->tongSoTinh();
        $tinhCoDuAn = $this->projectQuery->tinhCoDuAn(self::SO_TINH_COT_PHAI);

        return view('frontend.noxh.project.index', [
            'system' => $this->system,
            'seo' => $this->seoTrang($this->intro['project_heading'] ?? 'Danh sách dự án nhà ở xã hội', url('/du-an')),
            'duAn' => $duAn,
            'loc' => $loc,
            'locTho' => $request->all(),
            // Bo loc tinh cua thanh ben: lay tu vn_provinces (34 tinh/thanh,
            // co cau hai cap) chu khong phai bang `provinces` 63 tinh cu -
            // bang cu con ten cac tinh da sap nhap nen nguoi xem chon vao se
            // ra trang trong.
            'tinhThanh' => DB::table('vn_provinces')
                ->orderBy('order')
                ->orderBy('name')
                ->get(['code', 'name']),
            'tinhCoDuAn' => $tinhCoDuAn,
            'ghimBanDo' => $this->ghimBanDo(),
            'tinTuc' => $this->postQuery->moiNhat(self::SO_TIN_COT_PHAI),
            'demTrangThai' => $this->projectQuery->demTheoTrangThai($loc),
            'demGia' => $this->projectQuery->demTheoKhoang($loc, 'price_from', 'price_to', self::KHOANG_GIA),
            'demDienTich' => $this->projectQuery->demTheoKhoang($loc, 'area_from', 'area_to', self::KHOANG_DIEN_TICH),
            'tongDuAn' => $tongDuAn,
            'tongTinh' => $tongTinh,
            'soLieuDau' => $this->soLieuDau($tongDuAn, $tongTinh),
            'khoangGia' => self::KHOANG_GIA,
            'khoangDienTich' => self::KHOANG_DIEN_TICH,
        ]);
    }

    /**
     * Trang ban do du an - /du-an/ban-do.
     *
     * Ban do THAT, khong phai hinh Viet Nam ve san nhu truoc: moi du an mot
     * ghim dat dung toa do, bam vao ghim ra the thong tin, bam tiep vao the
     * sang trang chi tiet du an.
     *
     * Nen ban do do quan tri chon (Cai dat -> Ban do du an): OpenStreetMap
     * mien phi, hoac Google Maps neu da dan API key. Phan PHP o day khong
     * biet gi ve hai nha cung cap do - no chi giao ra danh sach ghim va mot
     * mo cai dat, JS lo phan ve.
     */
    public function map(Request $request)
    {
        $loc = $this->docBoLoc($request);

        $tinhDangXem = $loc['province_code']
            ? DB::table('vn_provinces')->where('code', $loc['province_code'])
                ->first(['code', 'name', 'lat', 'lng'])
            : null;

        // Ma tinh khong co that thi coi nhu khong loc, thay vi hien mot trang
        // trong khong giai thich duoc.
        if (!$tinhDangXem) {
            $loc['province_code'] = null;
        }

        // Phuong/xa chi co nghia trong mot tinh. Con lai o loc tinh da doi roi
        // ma xa cu van con thi ket qua ra rong ma nguoi dung khong hieu vi sao.
        $xaCoDuAn = $this->projectQuery->xaCoDuAn($loc['province_code']);

        if (!$loc['province_code'] || !$xaCoDuAn->firstWhere('ward_code', $loc['ward_code'])) {
            $loc['ward_code'] = null;
        }

        $xaDangXem = $xaCoDuAn->firstWhere('ward_code', $loc['ward_code']);

        $duAn = $this->projectQuery->choBanDo($loc);
        $diem = $this->diemBanDo($duAn);

        $tongDuAn = $this->projectQuery->tongSoDuAn();
        $tongTinh = $this->projectQuery->tongSoTinh();

        $tenTinh = $tinhDangXem ? nx_ten_dia_gioi_ngan($tinhDangXem->name) : null;

        return view('frontend.noxh.project.map', [
            'system' => $this->system,
            'seo' => $this->seoTrang(
                $tenTinh
                    ? 'Bản đồ dự án nhà ở xã hội tại ' . $tenTinh
                    : ($this->intro['projectmap_heading'] ?? 'Bản đồ dự án'),
                url('/du-an/ban-do')
            ),
            'loc' => $loc,
            'duAn' => $duAn,
            'diem' => $diem,
            'thieuToaDo' => $this->projectQuery->demThieuToaDo($loc),
            'tinhThanh' => $this->projectQuery->moiTinhThanh(),
            'xaCoDuAn' => $xaCoDuAn,
            'tinhDangXem' => $tinhDangXem,
            'xaDangXem' => $xaDangXem,
            'trangThai' => Product::TRANG_THAI_DU_AN,
            'khungBanDo' => $this->khungBanDo($diem, $tinhDangXem, $xaDangXem),
            'caiDatBanDo' => $this->caiDatBanDo(),
            'tongDuAn' => $tongDuAn,
            'tongTinh' => $tongTinh,
            'soLieuDau' => $this->soLieuDau($tongDuAn, $tongTinh),
        ]);
    }

    /**
     * Doi dong du an thanh ghim ban do.
     *
     * Chu in trong the thong tin duoc dung san o day chu khong dung ben JS:
     * dinh dang tien va dien tich da co ham PHP lo, viet lai mot ban nua bang
     * JS la hai cho se lech nhau ngay lan sua dau tien.
     */
    private function diemBanDo($duAn): array
    {
        $ghim = [];

        // So chu bo dau, bo gach noi: dia chi mau hay ghi "Song-Cong" trong
        // khi ten phuong/xa la "Song Cong" - khong go gach thi hai chuoi do
        // khong khop va ten bi in hai lan.
        $gon = fn (string $s) => str_replace('-', ' ', Str::lower(Str::ascii($s)));

        foreach ($duAn as $d) {
            // Dia chi cua du an thuong da chua san ten phuong/xa va ten tinh.
            // Noi thang ba manh vao nhau se ra "Phuong Song Cong, Thai Nguyen,
            // Song Cong, Thai Nguyen" - bo manh nao da nam trong manh truoc.
            $noi = [];

            $manhDs = [
                trim((string) $d->address),
                nx_ten_dia_gioi_ngan($d->ward_name),
                nx_ten_dia_gioi_ngan($d->province_name),
            ];

            foreach ($manhDs as $manh) {
                $manh = trim((string) $manh);

                if ($manh === '' || Str::contains($gon(implode(', ', $noi)), $gon($manh))) {
                    continue;
                }

                $noi[] = $manh;
            }

            $ghim[] = [
                'id' => (int) $d->id,
                'ten' => $d->name,
                'url' => url('/du-an/' . $d->canonical),
                'anh' => nx_anh($d->image ?? null, 'du-an'),
                'lat' => (float) $d->latitude,
                'lng' => (float) $d->longitude,
                'gia' => khoang_gia($d->price_from, $d->price_to),
                'giaNgan' => $d->price_from ? rtrim(rtrim(number_format((float) $d->price_from, 1, ',', '.'), '0'), ',') : null,
                'dienTich' => khoang_so($d->area_from, $d->area_to, ' m²'),
                'soCan' => $d->total_units ? number_format($d->total_units, 0, ',', '.') : null,
                'trangThai' => $d->status,
                'nhanTrangThai' => Product::TRANG_THAI_DU_AN[$d->status] ?? null,
                'noi' => implode(', ', $noi),
            ];
        }

        return $ghim;
    }

    /**
     * Diem giua va muc phong ban anh do mo lan dau.
     *
     * Co ghim thi lay giua cac ghim - JS con tu nam khung lai cho vua. Khong
     * co ghim nao (bo loc ra rong) thi lay giua cac tinh dang co trong CSDL,
     * chu khong ghi cung mot cap toa do vao ma nguon.
     */
    private function khungBanDo(array $diem, $tinhDangXem, $xaDangXem = null): array
    {
        $cai = $this->caiDatBanDo();

        // Loc den phuong/xa thi phong sat hon nua - nguoi dung dang muon xem
        // dung khu do chu khong phai ca tinh.
        if ($xaDangXem && $xaDangXem->lat !== null && $xaDangXem->lng !== null) {
            return [
                'lat' => (float) $xaDangXem->lat,
                'lng' => (float) $xaDangXem->lng,
                'zoom' => $cai['zoomXa'],
            ];
        }

        if ($tinhDangXem && $tinhDangXem->lat !== null && $tinhDangXem->lng !== null) {
            return [
                'lat' => (float) $tinhDangXem->lat,
                'lng' => (float) $tinhDangXem->lng,
                'zoom' => $cai['zoomTinh'],
            ];
        }

        if (count($diem)) {
            return [
                'lat' => array_sum(array_column($diem, 'lat')) / count($diem),
                'lng' => array_sum(array_column($diem, 'lng')) / count($diem),
                'zoom' => $cai['zoom'],
            ];
        }

        $giua = DB::table('vn_provinces')->whereNotNull('lat')->whereNotNull('lng')
            ->first([DB::raw('AVG(lat) as lat'), DB::raw('AVG(lng) as lng')]);

        return [
            'lat' => (float) ($giua->lat ?? 0),
            'lng' => (float) ($giua->lng ?? 0),
            'zoom' => $cai['zoom'],
        ];
    }

    /**
     * Cai dat nen ban do (Cai dat -> Ban do du an).
     *
     * Chon Google ma chua dan API key thi tu quay ve OpenStreetMap: tai thu
     * vien Google khong co key chi ra mot o xam kem dong chu bao loi, tha de
     * nguoi xem thay ban do mien phi con hon thay mot o hong.
     */
    private function caiDatBanDo(): array
    {
        $key = trim((string) cai_dat('map_google_key', ''));
        $nen = trim((string) cai_dat('map_provider', 'osm'));

        if ($nen !== 'google' || $key === '') {
            $nen = 'osm';
            $key = '';
        }

        $zoom = (int) (cai_dat('map_zoom', '') ?: 5);
        $zoomTinh = (int) (cai_dat('map_zoom_tinh', '') ?: 11);
        $zoomXa = (int) (cai_dat('map_zoom_xa', '') ?: 14);

        return [
            'nen' => $nen,
            'key' => $key,
            // Ghi nguon la dieu kien bat buoc cua giay phep OpenStreetMap,
            // khong phai dong chu trang tri - de trong thi dung ban mac dinh.
            'anhNen' => trim((string) cai_dat('map_tile_url', '')) ?: 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
            'nguon' => trim((string) cai_dat('map_tile_credit', '')) ?: '© OpenStreetMap',
            'zoom' => max(1, min(18, $zoom)),
            'zoomTinh' => max(1, min(18, $zoomTinh)),
            'zoomXa' => max(1, min(18, $zoomXa)),
        ];
    }

    /**
     * Ghim cho hinh ban do Viet Nam: moi tinh dang co du an mot ghim.
     *
     * Tinh nao chua co toa do trong vn_provinces thi nx_ban_do_diem tra ve
     * null va bi bo qua - ve ghim o toa do rong se dinh vao goc tren trai
     * cua hinh, trong nhu loi.
     */
    private function ghimBanDo(?string $maToSang = null): array
    {
        $ghim = [];

        foreach ($this->projectQuery->tinhCoDuAn(64) as $t) {
            $diem = nx_ban_do_diem($t->lat ?? null, $t->lng ?? null);

            if (!$diem) {
                continue;
            }

            $ghim[] = $diem + [
                'ten' => nx_ten_dia_gioi_ngan($t->province_name),
                'so' => (int) $t->so_du_an,
                'url' => url('/du-an/ban-do?province_code=' . $t->province_code),
                'sang' => $maToSang !== null && $maToSang === $t->province_code,
            ];
        }

        return $ghim;
    }

    /**
     * Bon o so lieu o dau trang.
     *
     * Chu va hinh do quan tri dat (module Gioi thieu, nhom "Khoi 4"); rieng
     * con so nhan hai dau thay the {du_an} va {tinh} de hai o dau luon dung
     * voi CSDL. O nao bo trong ca con so lan nhan thi khong hien.
     */
    private function soLieuDau(int $tongDuAn, int $tongTinh): array
    {
        $thay = [
            '{du_an}' => number_format($tongDuAn, 0, ',', '.'),
            '{tinh}' => number_format($tongTinh, 0, ',', '.'),
        ];

        $o = [];

        for ($i = 1; $i <= 4; $i++) {
            $gia = trim((string) ($this->intro["project_stat_{$i}_value"] ?? ''));
            $nhan = trim((string) ($this->intro["project_stat_{$i}_label"] ?? ''));

            if ($gia === '' && $nhan === '') {
                continue;
            }

            $hinh = $this->intro["project_stat_{$i}_icon"] ?? '';

            $o[] = [
                'value' => strtr($gia, $thay),
                'label' => $nhan,
                'icon' => NoxhIcon::hopLe($hinh) ? $hinh : 'building',
            ];
        }

        return $o;
    }

    /** So loai can ho ve tren trang chi tiet (ban thiet ke ve 3 the). */
    public const SO_LOAI_CAN_HO = 3;

    /** So nguoi trong khoi "Danh sach tu van ho tro" (ban thiet ke ve 6). */
    public const SO_TU_VAN = 6;

    /** So moc tien do ve san trong khoi; con lai xem trong hop bat len. */
    public const SO_MOC_TIEN_DO = 4;

    /** So du an trong khoi "Du an tuong tu". */
    public const SO_TUONG_TU = 3;

    public function show(string $canonical)
    {
        $duAn = $this->projectQuery->theoCanonical($canonical);

        if (!$duAn) {
            abort(404);
        }

        $tienDo = ProjectMilestone::where('product_id', $duAn->id)->orderBy('order')->get();

        // Ban thiet ke co hai tab rieng cho giay to: "Phap ly" va "Tai lieu".
        // Cung mot bang, khac nhau o cot group.
        $giayTo = ProjectDocument::where('product_id', $duAn->id)
            ->where('publish', 2)->orderBy('order')->get()->groupBy('group');
        $faq = ProjectFaq::where('product_id', $duAn->id)->where('publish', 2)->orderBy('order')->get();
        $loaiCanHo = ProjectUnit::where('product_id', $duAn->id)
            ->where('publish', 2)->orderBy('order')->orderBy('id')->get();

        $anh = $this->anhDuAn($duAn);
        $tenTinh = nx_ten_dia_gioi_ngan($duAn->province_name);

        // Lay ca danh sach: cot phai ve 6 nguoi, con lai nam trong hop
        // "Xem them tu van vien khac".
        $nhanVien = $this->nhanVienPhuTrach($duAn->id);

        return view('frontend.noxh.project.show', [
            'system' => $this->system,
            'seo' => [
                'meta_title' => $duAn->meta_title ?: $duAn->name,
                'meta_description' => $duAn->meta_description ?: strip_tags((string) $duAn->description),
                'meta_image' => $duAn->image,
                'canonical' => url('/du-an/' . $duAn->canonical),
            ],
            'duAn' => $duAn,
            'tenTinh' => $tenTinh,
            'anh' => $anh,
            'album' => $anh['phu'],
            'thongSo' => $this->oThongSo($duAn),
            'diemNhan' => $this->oDiemNhan($duAn),
            'tienIch' => ProjectHighlight::where('product_id', $duAn->id)
                ->where('group', 'amenity')
                ->orderBy('order')->orderBy('id')->get(),
            'bangTongQuan' => $this->bangTongQuan($duAn),
            'nhanTrangThai' => $this->nhanTrangThai(),
            'loaiCanHo' => $loaiCanHo->take(self::SO_LOAI_CAN_HO),
            'tatCaLoaiCanHo' => $loaiCanHo,
            'conLoaiCanHo' => max(0, $loaiCanHo->count() - self::SO_LOAI_CAN_HO),
            'banDoUrl' => $this->banDoUrl($duAn),
            'tienDo' => $tienDo->take(self::SO_MOC_TIEN_DO),
            'tatCaTienDo' => $tienDo,
            'hoSo' => $giayTo->get('legal', collect()),
            'taiLieu' => $giayTo->get('doc', collect()),
            'faq' => $faq,
            'tuongTu' => $this->projectQuery->tuongTu($duAn, self::SO_TUONG_TU),
            'nhanVien' => $nhanVien->take(self::SO_TU_VAN),
            'tatCaNhanVien' => $nhanVien,
        ]);
    }

    /**
     * Nhan vien kinh doanh phu trach mot du an.
     *
     * Chi lay nguoi con hieu luc (publish == 2): nhan vien nghi viec bi khoa
     * tai khoan la tu bien khoi moi trang du an, khong phai vao go tay tung cai.
     */
    private function nhanVienPhuTrach(int $duAnId, ?int $soLuong = null)
    {
        // Xep theo thu tu quan tri dat luc gan nguoi vao du an, roi moi den
        // ten. Khoi o trang chi tiet chi ve 6 nguoi nen thu tu nay quyet dinh
        // ai duoc hien - de mac cho ten tu xep la quan tri khong dieu duoc.
        $query = User::join('product_user as pu', 'pu.user_id', '=', 'users.id')
            ->where('pu.product_id', $duAnId)
            ->where('users.publish', 2)
            ->orderBy('pu.order')
            ->orderBy('users.name')
            ->select('users.*');

        if ($soLuong) {
            $query->limit($soLuong);
        }

        return $query->get();
    }

    /**
     * Anh chinh va dai anh nho o dau trang chi tiet.
     *
     * Album luu chuoi JSON do quan tri nhap, nen phai chap nhan ca truong hop
     * JSON hong ma khong lam vo trang.
     */
    private function anhDuAn($duAn): array
    {
        $phu = [];

        if (!empty($duAn->album)) {
            $giai = json_decode($duAn->album, true);
            $phu = is_array($giai) ? array_values(array_filter($giai)) : [];
        }

        // Anh dai dien co the trung voi anh dau album; bo trung de dai anh nho
        // khong hien hai o giong het nhau.
        $chinh = trim((string) $duAn->image);
        if ($chinh !== '') {
            $phu = array_values(array_filter($phu, fn ($a) => trim((string) $a) !== $chinh));
        }

        return ['chinh' => $chinh, 'phu' => $phu];
    }

    /**
     * Bon o thong so trong the gia.
     *
     * Nhan va hinh do quan tri dat trong Cau hinh chung; CON SO thi lay thang
     * tu du an. O nao khong co so lieu thi bo han, de the gia khong con o
     * trong mang chu "Dang cap nhat".
     */
    private function oThongSo($duAn): array
    {
        $gia = [
            1 => $duAn->total_land_area
                ? rtrim(rtrim(number_format((float) $duAn->total_land_area, 2, ',', '.'), '0'), ',') . ' ha'
                : null,
            2 => $duAn->total_units
                ? number_format((int) $duAn->total_units, 0, ',', '.') . ' căn'
                : null,
            3 => $duAn->apartment_types ?: null,
            4 => $duAn->timeline_label
                ?: ($duAn->handover_date ? nx_quy_nam($duAn->handover_date) : null),
        ];

        $mac = [1 => 'Quy mô', 2 => 'Số căn hộ', 3 => 'Loại hình', 4 => 'Bàn giao'];
        $o = [];

        foreach ($gia as $i => $giaTri) {
            if (!$giaTri) {
                continue;
            }

            $o[] = [
                'icon' => $this->intro["projectdetail_spec_{$i}_icon"] ?? 'building',
                'nhan' => $this->intro["projectdetail_spec_{$i}_label"] ?? $mac[$i],
                'gia' => $giaTri,
            ];
        }

        return $o;
    }

    /**
     * Bon o diem nhan duoi bang thong so.
     *
     * Uu tien cai du an tu khai (bang project_highlights); du an chua khai gi
     * thi dung bon o mac dinh trong Cau hinh chung.
     */
    private function oDiemNhan($duAn): array
    {
        $rieng = ProjectHighlight::where('product_id', $duAn->id)
            ->where('group', 'price')
            ->orderBy('order')->orderBy('id')->get();

        if ($rieng->count()) {
            return $rieng->map(fn ($d) => [
                'icon' => $d->icon ?: 'check-circle',
                'nhan' => $d->title,
                'gia' => $d->subtitle,
            ])->all();
        }

        $o = [];

        for ($i = 1; $i <= 4; $i++) {
            $nhan = trim((string) ($this->intro["projectdetail_point_{$i}_title"] ?? ''));

            if ($nhan === '') {
                continue;
            }

            $o[] = [
                'icon' => $this->intro["projectdetail_point_{$i}_icon"] ?? 'check-circle',
                'nhan' => $nhan,
                'gia' => $this->intro["projectdetail_point_{$i}_sub"] ?? '',
            ];
        }

        return $o;
    }

    /**
     * Bang "Tong quan du an".
     *
     * Gia tri lay tu cot cua du an, NHAN lay tu Cau hinh chung. Dong nao
     * khong co gia tri - hoac quan tri xoa trang nhan - thi khong ve ra:
     * mot bang ngan gon hon la mot bang day dong "Dang cap nhat".
     */
    private function bangTongQuan($duAn): array
    {
        $noi = array_filter([
            $duAn->ward_name ?: null,
            nx_ten_dia_gioi_ngan($duAn->province_name) ?: null,
        ]);

        $gia = [
            'name' => $duAn->name,
            'place' => $duAn->address ?: (count($noi) ? implode(', ', $noi) : null),
            'investor' => $duAn->investor_name ?? null,
            'land' => $duAn->total_land_area
                ? rtrim(rtrim(number_format((float) $duAn->total_land_area, 2, ',', '.'), '0'), ',') . ' ha'
                : null,
            'scale' => $duAn->scale_description ?: null,
            'units' => $duAn->total_units
                ? number_format((int) $duAn->total_units, 0, ',', '.') . ' căn hộ'
                : null,
            'types' => $duAn->apartment_types ?: null,
            'area' => khoang_so($duAn->area_from, $duAn->area_to, ' m²', '') ?: null,
            'price' => khoang_so($duAn->price_from, $duAn->price_to, ' triệu/m²', '') ?: null,
            'ownership' => $duAn->ownership_type ?: null,
            'start' => $duAn->start_date ? nx_quy_nam($duAn->start_date) : null,
            'handover' => $duAn->timeline_label
                ?: ($duAn->handover_date ? nx_quy_nam($duAn->handover_date) : null),
        ];

        $dong = [];

        foreach ($gia as $ma => $giaTri) {
            if ($giaTri === null || $giaTri === '') {
                continue;
            }

            $nhan = trim((string) ($this->intro["projectdetail_row_{$ma}"]
                ?? Introduce::DONG_TONG_QUAN[$ma]));

            if ($nhan === '') {
                continue;
            }

            $dong[$nhan] = $giaTri;
        }

        return $dong;
    }

    /** Nhan cua dong "Trang thai" - ve rieng vi gia tri la mot the mau. */
    private function nhanTrangThai(): string
    {
        return trim((string) ($this->intro['projectdetail_row_status']
            ?? Introduce::DONG_TONG_QUAN['status']));
    }

    /**
     * Duong dan mo vi tri du an tren Google Maps.
     *
     * Quan tri dan san link thi dung link do; khong thi dung tu toa do, cuoi
     * cung moi dung dia chi chu - toa do chinh xac hon han dia chi go tay.
     */
    private function banDoUrl($duAn): ?string
    {
        if (!empty($duAn->map_url)) {
            return $duAn->map_url;
        }

        if ($duAn->latitude !== null && $duAn->longitude !== null) {
            return 'https://www.google.com/maps/search/?api=1&query='
                . rawurlencode($duAn->latitude . ',' . $duAn->longitude);
        }

        $noi = array_filter([$duAn->name, $duAn->address, $duAn->province_name]);

        return count($noi)
            ? 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode(implode(', ', $noi))
            : null;
    }

    /** Trang "Theo tinh/thanh" - moi tinh mot the, lam trang dich SEO. */
    public function provinces()
    {
        return view('frontend.noxh.project.provinces', [
            'system' => $this->system,
            'seo' => $this->seoTrang('Nhà ở xã hội theo tỉnh/thành', url('/du-an/tinh-thanh')),
            'danhSach' => $this->projectQuery->tinhCoDuAn(64),
        ]);
    }

    public function byProvince(Request $request, string $code)
    {
        $request->merge(['province_code' => $code]);

        return $this->index($request);
    }

    // -------------------------------------------------------------------------

    /**
     * Doc bo loc tu duong dan.
     *
     * Ma khoang gia/dien tich duoc doi thanh cap so ngay tai day, de phan truy
     * van khong phai biet gi ve ten cac muc loc.
     */
    /**
     * Doi chuoi "tu-den" thanh cap so. Tra ve null neu khong dung dinh dang
     * hoac ca hai dau deu trong.
     */
    private function khoangTuDuongDan(string $ma): ?array
    {
        if (!preg_match('/^(\d*(?:\.\d+)?)-(\d*(?:\.\d+)?)$/', $ma, $khop)) {
            return null;
        }

        $tu = $khop[1] === '' ? null : (float) $khop[1];
        $den = $khop[2] === '' ? null : (float) $khop[2];

        return ($tu === null && $den === null) ? null : ['nhan' => $ma, 'tu' => $tu, 'den' => $den];
    }

    private function docBoLoc(Request $request): array
    {
        $loc = [
            'keyword' => trim((string) $request->input('tu-khoa')) ?: null,
            'province_code' => $request->input('province_code') ?: null,
            'ward_code' => $request->input('ward_code') ?: null,
            'status' => array_values(array_intersect(
                (array) $request->input('status', []),
                array_keys(Product::TRANG_THAI_DU_AN)
            )),
            'price' => [],
            'area' => [],
        ];

        foreach ((array) $request->input('gia', []) as $ma) {
            if (isset(self::KHOANG_GIA[$ma])) {
                $loc['price'][] = self::KHOANG_GIA[$ma];
                continue;
            }

            // Thanh tim o trang chu dung cac khoang gia do quan tri tu dat
            // trong module Gioi thieu, gui len thang duoi dang "tu-den"
            // (vi du "18-20", "-18"). Nhan luon de hai cho khong phai dung
            // chung mot bang ma cung.
            if ($m = $this->khoangTuDuongDan((string) $ma)) {
                $loc['price'][] = $m;
            }
        }

        foreach ((array) $request->input('dien-tich', []) as $ma) {
            if (isset(self::KHOANG_DIEN_TICH[$ma])) {
                $loc['area'][] = self::KHOANG_DIEN_TICH[$ma];
            }
        }

        // O tim gia nhanh tren trang chu gui len hai so roi, khong qua ma muc.
        $giaTu = $request->input('gia_tu');
        $giaDen = $request->input('gia_den');
        if ($giaTu !== null && $giaTu !== '' || $giaDen !== null && $giaDen !== '') {
            $loc['price'][] = [
                'tu' => $giaTu === '' ? null : (float) $giaTu,
                'den' => $giaDen === '' ? null : (float) $giaDen,
            ];
        }

        return $loc;
    }

    private function seoTrang(string $tieuDe, string $canonical): array
    {
        return [
            'meta_title' => $tieuDe . ' - NOXH.vn',
            'meta_description' => $this->system['seo_meta_description'] ?? '',
            'meta_image' => $this->system['seo_meta_images'] ?? '',
            'canonical' => $canonical,
        ];
    }
}
