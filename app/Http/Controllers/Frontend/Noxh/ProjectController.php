<?php

namespace App\Http\Controllers\Frontend\Noxh;

use App\Classes\NoxhIcon;
use App\Http\Controllers\FrontendController;
use App\Http\ViewComposers\NoxhComposer;
use App\Models\Product;
use App\Models\ProjectDocument;
use App\Models\ProjectFaq;
use App\Models\ProjectMilestone;
use App\Models\User;
use App\Models\Province;
use App\Repositories\Noxh\PostQuery;
use App\Repositories\Noxh\ProjectQuery;
use Illuminate\Http\Request;

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
            'tinhThanh' => Province::select('code', 'name')->orderBy('name')->get(),
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
     * Bam vao mot tinh tren khoi ban do (o cot phai trang danh sach) hoac
     * vao chinh hinh ban do deu den day. Co ?province_code thi ghim cua tinh
     * do duoc to sang va du an cua tinh hien ngay duoi ban do.
     */
    public function map(Request $request)
    {
        $maTinh = $request->input('province_code') ?: null;
        $danhSach = $this->projectQuery->tinhCoDuAn(64);

        $tinhDangXem = $maTinh
            ? $danhSach->firstWhere('province_code', $maTinh)
            : null;

        // Ma tinh khong co du an nao thi coi nhu khong loc, thay vi hien mot
        // trang trong khong giai thich duoc.
        if (!$tinhDangXem) {
            $maTinh = null;
        }

        $tongDuAn = $this->projectQuery->tongSoDuAn();
        $tongTinh = $this->projectQuery->tongSoTinh();

        return view('frontend.noxh.project.map', [
            'system' => $this->system,
            'seo' => $this->seoTrang(
                $tinhDangXem
                    ? 'Dự án nhà ở xã hội tại ' . nx_ten_dia_gioi_ngan($tinhDangXem->province_name)
                    : ($this->intro['projectaside_map_heading'] ?? 'Bản đồ dự án'),
                url('/du-an/ban-do')
            ),
            'ghimBanDo' => $this->ghimBanDo($maTinh),
            'danhSach' => $danhSach,
            'maTinh' => $maTinh,
            'tinhDangXem' => $tinhDangXem,
            'duAn' => $maTinh
                ? $this->projectQuery->danhSach(['province_code' => $maTinh], 'moi-nhat', 20)
                : null,
            'tongDuAn' => $tongDuAn,
            'tongTinh' => $tongTinh,
            'soLieuDau' => $this->soLieuDau($tongDuAn, $tongTinh),
        ]);
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

    public function show(string $canonical)
    {
        $duAn = $this->projectQuery->theoCanonical($canonical);

        if (!$duAn) {
            abort(404);
        }

        // Anh phu luu chuoi JSON, du lieu do quan tri nhap nen phai chap nhan
        // ca truong hop JSON hong ma khong lam vo trang.
        $album = [];
        if (!empty($duAn->album)) {
            $decoded = json_decode($duAn->album, true);
            $album = is_array($decoded) ? array_filter($decoded) : [];
        }

        return view('frontend.noxh.project.show', [
            'system' => $this->system,
            'seo' => [
                'meta_title' => $duAn->meta_title ?: $duAn->name,
                'meta_description' => $duAn->meta_description ?: strip_tags((string) $duAn->description),
                'meta_image' => $duAn->image,
                'canonical' => url('/du-an/' . $duAn->canonical),
            ],
            'duAn' => $duAn,
            'album' => $album,
            'tienDo' => ProjectMilestone::where('product_id', $duAn->id)->orderBy('order')->get(),
            'hoSo' => ProjectDocument::where('product_id', $duAn->id)->where('publish', 2)->orderBy('order')->get(),
            'faq' => ProjectFaq::where('product_id', $duAn->id)->where('publish', 2)->orderBy('order')->get(),
            'tuongTu' => $this->projectQuery->tuongTu($duAn, 3),
            'nhanVien' => $this->nhanVienPhuTrach($duAn->id),
        ]);
    }

    /**
     * Nhan vien kinh doanh phu trach mot du an.
     *
     * Chi lay nguoi con hieu luc (publish == 2): nhan vien nghi viec bi khoa
     * tai khoan la tu bien khoi moi trang du an, khong phai vao go tay tung cai.
     */
    private function nhanVienPhuTrach(int $duAnId)
    {
        return User::whereHas('duAnPhuTrach', function ($q) use ($duAnId) {
                $q->where('products.id', $duAnId);
            })
            ->where('users.publish', 2)
            ->orderBy('users.name')
            ->get();
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
