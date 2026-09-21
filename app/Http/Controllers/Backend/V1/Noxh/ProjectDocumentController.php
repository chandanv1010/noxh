<?php

namespace App\Http\Controllers\Backend\V1\Noxh;

use App\Http\Controllers\Controller;
use App\Http\Requests\Noxh\StoreProjectDocumentRequest;
use App\Models\Product;
use App\Repositories\Noxh\ProjectDocumentRepository;
use App\Services\V1\Project\ProjectDocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Man hinh quan tri: project/document.
 *
 * Luon gan voi mot du an. Vao tu man hinh sua du an thi duong dan da kem san
 * ?product_id nen o chon du an duoc dien truoc.
 */
class ProjectDocumentController extends Controller
{
    protected $documentService;
    protected $documentRepository;

    public function __construct(
        ProjectDocumentService $documentService,
        ProjectDocumentRepository $documentRepository
    ) {
        $this->documentService = $documentService;
        $this->documentRepository = $documentRepository;
    }

    public function index(Request $request)
    {
        $this->authorize('modules', 'project.document.index');

        $documents = $this->documentService->paginate($request);
        $config = $this->config();
        $config['seo'] = __('messages.projectDocument');
        $template = 'backend.project_document.index';

        return view('backend.dashboard.layout', compact('template', 'config', 'documents') + [
            'danhSachCha' => $this->danhSachDuAn(),
        ]);
    }

    public function create()
    {
        $this->authorize('modules', 'project.document.create');

        $config = $this->config();
        $config['seo'] = __('messages.projectDocument');
        $config['method'] = 'create';
        $template = 'backend.project_document.store';

        return view('backend.dashboard.layout', compact('template', 'config') + [
            'danhSachCha' => $this->danhSachDuAn(),
        ]);
    }

    public function store(StoreProjectDocumentRequest $request)
    {
        if ($this->documentService->create($request)) {
            return redirect()->route('project.document.index')->with('success', 'Thêm mới bản ghi thành công');
        }
        return redirect()->route('project.document.index')->with('error', 'Thêm mới bản ghi không thành công. Hãy thử lại');
    }

    public function edit($id)
    {
        $this->authorize('modules', 'project.document.update');

        $document = $this->documentRepository->findById($id);
        $config = $this->config();
        $config['seo'] = __('messages.projectDocument');
        $config['method'] = 'edit';
        $template = 'backend.project_document.store';

        return view('backend.dashboard.layout', compact('template', 'config', 'document') + [
            'danhSachCha' => $this->danhSachDuAn(),
        ]);
    }

    public function update($id, StoreProjectDocumentRequest $request)
    {
        if ($this->documentService->update($id, $request)) {
            return redirect()->route('project.document.index')->with('success', 'Cập nhật bản ghi thành công');
        }
        return redirect()->route('project.document.index')->with('error', 'Cập nhật bản ghi không thành công. Hãy thử lại');
    }

    public function delete($id)
    {
        $this->authorize('modules', 'project.document.destroy');

        $document = $this->documentRepository->findById($id);
        $config = $this->config();
        $config['seo'] = __('messages.projectDocument');
        $template = 'backend.project_document.delete';

        return view('backend.dashboard.layout', compact('template', 'config', 'document'));
    }

    public function destroy($id)
    {
        if ($this->documentService->destroy($id)) {
            return redirect()->route('project.document.index')->with('success', 'Xóa bản ghi thành công');
        }
        return redirect()->route('project.document.index')->with('error', 'Xóa bản ghi không thành công. Hãy thử lại');
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
            'model' => 'ProjectDocument',
        ];
    }
}
