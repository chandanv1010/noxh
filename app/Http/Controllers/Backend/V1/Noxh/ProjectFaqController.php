<?php

namespace App\Http\Controllers\Backend\V1\Noxh;

use App\Http\Controllers\Controller;
use App\Http\Requests\Noxh\StoreProjectFaqRequest;
use App\Models\Product;
use App\Repositories\Noxh\ProjectFaqRepository;
use App\Services\V1\Project\ProjectFaqService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Man hinh quan tri: project/faq.
 *
 * Luon gan voi mot du an. Vao tu man hinh sua du an thi duong dan da kem san
 * ?product_id nen o chon du an duoc dien truoc.
 */
class ProjectFaqController extends Controller
{
    protected $faqService;
    protected $faqRepository;

    public function __construct(
        ProjectFaqService $faqService,
        ProjectFaqRepository $faqRepository
    ) {
        $this->faqService = $faqService;
        $this->faqRepository = $faqRepository;
    }

    public function index(Request $request)
    {
        $this->authorize('modules', 'project.faq.index');

        $faqs = $this->faqService->paginate($request);
        $config = $this->config();
        $config['seo'] = __('messages.projectFaq');
        $template = 'backend.project_faq.index';

        return view('backend.dashboard.layout', compact('template', 'config', 'faqs') + [
            'danhSachCha' => $this->danhSachDuAn(),
        ]);
    }

    public function create()
    {
        $this->authorize('modules', 'project.faq.create');

        $config = $this->config();
        $config['seo'] = __('messages.projectFaq');
        $config['method'] = 'create';
        $template = 'backend.project_faq.store';

        return view('backend.dashboard.layout', compact('template', 'config') + [
            'danhSachCha' => $this->danhSachDuAn(),
        ]);
    }

    public function store(StoreProjectFaqRequest $request)
    {
        if ($this->faqService->create($request)) {
            return redirect()->route('project.faq.index')->with('success', 'Thêm mới bản ghi thành công');
        }
        return redirect()->route('project.faq.index')->with('error', 'Thêm mới bản ghi không thành công. Hãy thử lại');
    }

    public function edit($id)
    {
        $this->authorize('modules', 'project.faq.update');

        $faq = $this->faqRepository->findById($id);
        $config = $this->config();
        $config['seo'] = __('messages.projectFaq');
        $config['method'] = 'edit';
        $template = 'backend.project_faq.store';

        return view('backend.dashboard.layout', compact('template', 'config', 'faq') + [
            'danhSachCha' => $this->danhSachDuAn(),
        ]);
    }

    public function update($id, StoreProjectFaqRequest $request)
    {
        if ($this->faqService->update($id, $request)) {
            return redirect()->route('project.faq.index')->with('success', 'Cập nhật bản ghi thành công');
        }
        return redirect()->route('project.faq.index')->with('error', 'Cập nhật bản ghi không thành công. Hãy thử lại');
    }

    public function delete($id)
    {
        $this->authorize('modules', 'project.faq.destroy');

        $faq = $this->faqRepository->findById($id);
        $config = $this->config();
        $config['seo'] = __('messages.projectFaq');
        $template = 'backend.project_faq.delete';

        return view('backend.dashboard.layout', compact('template', 'config', 'faq'));
    }

    public function destroy($id)
    {
        if ($this->faqService->destroy($id)) {
            return redirect()->route('project.faq.index')->with('success', 'Xóa bản ghi thành công');
        }
        return redirect()->route('project.faq.index')->with('error', 'Xóa bản ghi không thành công. Hãy thử lại');
    }

    /**
     * Danh sach du an cho o chon.
     *
     * Ten du an nam o bang ngon ngu nen phai join; lay luon ma du an de phan
     * biet khi hai du an trung ten.
     */
    private function danhSachDuAn()
    {
        return DB::table('products as p')
            ->leftJoin('product_language as pl', function ($join) {
                $join->on('pl.product_id', '=', 'p.id')
                     ->where('pl.language_id', '=', 1);
            })
            ->whereNull('p.deleted_at')
            ->orderByDesc('p.id')
            ->get([
                'p.id',
                DB::raw("CONCAT(COALESCE(pl.name, CONCAT('Du an #', p.id)), IF(p.code IS NULL OR p.code = '', '', CONCAT(' (', p.code, ')'))) AS name"),
            ]);
    }

    private function config()
    {
        return [
            'css' => ['backend/css/plugins/switchery/switchery.css'],
            'js' => [
                'backend/js/plugins/switchery/switchery.js',
                'backend/library/finder.js',
                'backend/plugins/ckeditor/ckeditor.js',
            ],
            'model' => 'ProjectFaq',
        ];
    }
}
