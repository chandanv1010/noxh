<?php

namespace App\Http\Controllers\Sale;

use App\Classes\Nestedsetbie;
use App\Http\Requests\Post\StorePostRequest;
use App\Http\Requests\Post\UpdatePostRequest;
use App\Models\Post;
use App\Repositories\Post\PostRepository;
use App\Services\V1\Post\PostService;
use Illuminate\Http\Request;

/**
 * Bai viet do nhan vien kinh doanh tu dang.
 *
 * Nhan vien chi thay bai cua chinh minh (posts.user_id). Bai luon luu o trang
 * thai CHO DUYET va khong hien ra website cho den khi quan tri bat len - ke ca
 * khi sua lai mot bai da duoc duyet truoc do.
 *
 * Hai gia tri publish va approval_status duoc ep o phia may chu bang
 * $request->merge() chu khong lay tu o an trong form: o an thi nguoi dung sua
 * duoc truoc khi gui.
 */
class PostController extends SaleController
{
    protected $postService;
    protected $postRepository;
    protected $nestedset;

    public function __construct(
        PostService $postService,
        PostRepository $postRepository
    ) {
        parent::__construct();

        $this->postService = $postService;
        $this->postRepository = $postRepository;

        $this->middleware(function ($request, $next) {
            $this->nestedset = new Nestedsetbie([
                'table' => 'post_catalogues',
                'foreignkey' => 'post_catalogue_id',
                'language_id' => $this->language,
            ]);
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $baiViet = Post::where('posts.user_id', $this->toiLaAi())
            ->join('post_language as plg', function ($j) {
                $j->on('plg.post_id', '=', 'posts.id')
                  ->where('plg.language_id', '=', $this->language);
            })
            ->select(
                'posts.id',
                'posts.image',
                'posts.publish',
                'posts.approval_status',
                'posts.updated_at',
                'plg.name',
                'plg.canonical'
            );

        if ($tuKhoa = trim((string) $request->input('tu-khoa'))) {
            $baiViet->where('plg.name', 'like', '%' . $tuKhoa . '%');
        }

        return $this->khung('sale.post.index', [
            'baiViet' => $baiViet->orderByDesc('posts.updated_at')
                ->paginate(20)
                ->appends($request->query()),
            'tuKhoa' => $tuKhoa,
            'tieuDe' => 'Bài viết của tôi',
        ]);
    }

    public function create()
    {
        return $this->khung('sale.post.form', [
            'post' => null,
            'dropdown' => $this->nestedset->Dropdown(),
            'cachLam' => 'create',
            'tieuDe' => 'Viết bài mới',
        ]);
    }

    public function store(StorePostRequest $request)
    {
        $request->merge($this->trangThaiChoDuyet());

        if ($this->postService->create($request, $this->language)) {
            return redirect()->route('sale.post.index')
                ->with('success', 'Đã gửi bài, chờ quản trị duyệt');
        }

        return redirect()->back()->withInput()
            ->with('error', 'Lưu bài viết không thành công. Hãy thử lại');
    }

    public function edit($id)
    {
        $this->laiBaiCuaToi($id);

        return $this->khung('sale.post.form', [
            'post' => $this->postRepository->getPostById($id, $this->language),
            'dropdown' => $this->nestedset->Dropdown(),
            'cachLam' => 'edit',
            'tieuDe' => 'Sửa bài viết',
        ]);
    }

    public function update($id, UpdatePostRequest $request)
    {
        $this->laiBaiCuaToi($id);

        $request->merge($this->trangThaiChoDuyet());

        if ($this->postService->update($id, $request, $this->language)) {
            return redirect()->route('sale.post.index')
                ->with('success', 'Đã lưu, bài chờ quản trị duyệt lại');
        }

        return redirect()->back()->withInput()
            ->with('error', 'Lưu bài viết không thành công. Hãy thử lại');
    }

    /**
     * Bai viet sua xong phai duyet lai tu dau.
     *
     * Khong giu nguyen trang thai da duyet: nguoi dung co the gui mot bai hien
     * lanh de duoc duyet roi sua lai thanh noi dung khac han.
     */
    private function trangThaiChoDuyet(): array
    {
        return ['publish' => 1, 'approval_status' => 'pending'];
    }

    private function laiBaiCuaToi($id): void
    {
        abort_unless(
            Post::where('id', $id)->where('user_id', $this->toiLaAi())->exists(),
            404
        );
    }
}
