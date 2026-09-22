<?php

namespace App\Http\Controllers\Frontend\Noxh;

use App\Http\Controllers\FrontendController;
use App\Models\Product;
use App\Models\ProjectDocument;
use App\Models\ProjectFaq;
use App\Models\ProjectMilestone;
use App\Models\User;
use App\Models\Province;
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

    protected $projectQuery;

    public function __construct(ProjectQuery $projectQuery)
    {
        $this->projectQuery = $projectQuery;
        parent::__construct();
    }

    public function index(Request $request)
    {
        $loc = $this->docBoLoc($request);

        $duAn = $this->projectQuery->danhSach($loc, $request->input('sap-xep', 'moi-nhat'), 10);

        return view('frontend.noxh.project.index', [
            'system' => $this->system,
            'seo' => $this->seoTrang('Danh sách dự án nhà ở xã hội', url('/du-an')),
            'duAn' => $duAn,
            'loc' => $loc,
            'locTho' => $request->all(),
            'tinhThanh' => Province::select('code', 'name')->orderBy('name')->get(),
            'tinhCoDuAn' => $this->projectQuery->tinhCoDuAn(8),
            'demTrangThai' => $this->projectQuery->demTheoTrangThai($loc),
            'demGia' => $this->projectQuery->demTheoKhoang($loc, 'price_from', 'price_to', self::KHOANG_GIA),
            'demDienTich' => $this->projectQuery->demTheoKhoang($loc, 'area_from', 'area_to', self::KHOANG_DIEN_TICH),
            'tongDuAn' => $this->projectQuery->tongSoDuAn(),
            'tongTinh' => $this->projectQuery->tongSoTinh(),
            'khoangGia' => self::KHOANG_GIA,
            'khoangDienTich' => self::KHOANG_DIEN_TICH,
        ]);
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
    private function docBoLoc(Request $request): array
    {
        $loc = [
            'keyword' => trim((string) $request->input('tu-khoa')) ?: null,
            'province_code' => $request->input('province_code') ?: null,
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
