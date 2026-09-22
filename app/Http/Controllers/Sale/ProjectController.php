<?php

namespace App\Http\Controllers\Sale;

use App\Classes\Nestedsetbie;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Models\Investor;
use App\Models\Product;
use App\Models\Province;
use App\Repositories\Product\ProductRepository;
use App\Services\V1\Product\ProductService;
use Illuminate\Http\Request;

/**
 * Du an trong bang dieu khien cua nhan vien kinh doanh.
 *
 * Nhan vien chi thay va sua duoc du an TU HO TAO hoac duoc quan tri GIAO cho.
 * Pham vi do nam trong scope Product::cuaNhanVien().
 *
 * Diem quan trong: moi ham dong vao mot du an cu the deu goi laiDuAnCuaToi()
 * chu khong tin vao viec man hinh danh sach da loc san. Chi loc o danh sach
 * thi nguoi dung doi so tren thanh dia chi la vao thang du an cua nguoi khac.
 */
class ProjectController extends SaleController
{
    protected $productService;
    protected $productRepository;
    protected $nestedset;

    public function __construct(
        ProductService $productService,
        ProductRepository $productRepository
    ) {
        parent::__construct();

        $this->productService = $productService;
        $this->productRepository = $productRepository;

        $this->middleware(function ($request, $next) {
            $this->nestedset = new Nestedsetbie([
                'table' => 'product_catalogues',
                'foreignkey' => 'product_catalogue_id',
                'language_id' => $this->language,
            ]);
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $duAn = Product::cuaNhanVien($this->toiLaAi())
            ->join('product_language as pl', function ($j) {
                $j->on('pl.product_id', '=', 'products.id')
                  ->where('pl.language_id', '=', $this->language);
            })
            ->select(
                'products.id',
                'products.image',
                'products.publish',
                'products.status',
                'products.user_id',
                'products.updated_at',
                'pl.name',
                'pl.canonical'
            );

        if ($tuKhoa = trim((string) $request->input('tu-khoa'))) {
            $duAn->where('pl.name', 'like', '%' . $tuKhoa . '%');
        }

        return $this->khung('sale.project.index', [
            'duAn' => $duAn->orderByDesc('products.updated_at')
                ->paginate(20)
                ->appends($request->query()),
            'trangThaiDuAn' => Product::TRANG_THAI_DU_AN,
            'tuKhoa' => $tuKhoa,
            'tieuDe' => 'Dự án của tôi',
        ]);
    }

    public function create()
    {
        return $this->khung('sale.project.form', [
            'product' => null,
            'cachLam' => 'create',
            'tieuDe' => 'Thêm dự án mới',
        ] + $this->duLieuForm());
    }

    public function store(StoreProductRequest $request)
    {
        // ProductService::create() tu gan products.user_id = Auth::id(), nen du
        // an vua tao mac nhien thuoc ve nguoi dang nhap.
        if ($this->productService->create($request, $this->language)) {
            return redirect()->route('sale.project.index')
                ->with('success', 'Thêm dự án thành công');
        }

        return redirect()->back()->withInput()
            ->with('error', 'Thêm dự án không thành công. Hãy thử lại');
    }

    public function edit($id)
    {
        $this->laiDuAnCuaToi($id);

        $product = $this->productRepository->getProductById($id, $this->language);

        return $this->khung('sale.project.form', [
            'product' => $product,
            'album' => json_decode($product->album),
            'cachLam' => 'edit',
            'tieuDe' => 'Sửa dự án',
        ] + $this->duLieuForm());
    }

    public function update($id, UpdateProductRequest $request)
    {
        $this->laiDuAnCuaToi($id);

        if ($this->productService->update($id, $request, $this->language)) {
            return redirect()->route('sale.project.index')
                ->with('success', 'Cập nhật dự án thành công');
        }

        return redirect()->back()->withInput()
            ->with('error', 'Cập nhật dự án không thành công. Hãy thử lại');
    }

    /**
     * Du an nay co nam trong pham vi cua toi khong. Khong thi 404 - khong bao
     * "ban khong co quyen" vi cau do da la mot loi thua nhan rang du an ton tai.
     */
    private function laiDuAnCuaToi($id): void
    {
        $co = Product::cuaNhanVien($this->toiLaAi())->where('products.id', $id)->exists();

        abort_unless($co, 404);
    }

    private function duLieuForm(): array
    {
        return [
            'dropdown' => $this->nestedset->Dropdown(),
            'chuDauTu' => Investor::where('publish', 2)->orderBy('order')->get(['id', 'name']),
            'trangThaiDuAn' => Product::TRANG_THAI_DU_AN,
            'tinhThanh' => Province::select('code', 'name')->orderBy('name')->get(),
        ];
    }
}
