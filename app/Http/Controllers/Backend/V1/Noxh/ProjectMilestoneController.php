<?php

namespace App\Http\Controllers\Backend\V1\Noxh;

use App\Http\Controllers\Controller;
use App\Http\Requests\Noxh\StoreProjectMilestoneRequest;
use App\Models\Product;
use App\Repositories\Noxh\ProjectMilestoneRepository;
use App\Services\V1\Project\ProjectMilestoneService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Man hinh quan tri: project/milestone.
 *
 * Luon gan voi mot du an. Vao tu man hinh sua du an thi duong dan da kem san
 * ?product_id nen o chon du an duoc dien truoc.
 */
class ProjectMilestoneController extends Controller
{
    protected $milestoneService;
    protected $milestoneRepository;

    public function __construct(
        ProjectMilestoneService $milestoneService,
        ProjectMilestoneRepository $milestoneRepository
    ) {
        $this->milestoneService = $milestoneService;
        $this->milestoneRepository = $milestoneRepository;
    }

    public function index(Request $request)
    {
        $this->authorize('modules', 'project.milestone.index');

        $milestones = $this->milestoneService->paginate($request);
        $config = $this->config();
        $config['seo'] = __('messages.projectMilestone');
        $template = 'backend.project_milestone.index';

        return view('backend.dashboard.layout', compact('template', 'config', 'milestones') + [
            'danhSachCha' => $this->danhSachDuAn(),
        ]);
    }

    public function create()
    {
        $this->authorize('modules', 'project.milestone.create');

        $config = $this->config();
        $config['seo'] = __('messages.projectMilestone');
        $config['method'] = 'create';
        $template = 'backend.project_milestone.store';

        return view('backend.dashboard.layout', compact('template', 'config') + [
            'danhSachCha' => $this->danhSachDuAn(),
        ]);
    }

    public function store(StoreProjectMilestoneRequest $request)
    {
        if ($this->milestoneService->create($request)) {
            return redirect()->route('project.milestone.index')->with('success', 'Thêm mới bản ghi thành công');
        }
        return redirect()->route('project.milestone.index')->with('error', 'Thêm mới bản ghi không thành công. Hãy thử lại');
    }

    public function edit($id)
    {
        $this->authorize('modules', 'project.milestone.update');

        $milestone = $this->milestoneRepository->findById($id);
        $config = $this->config();
        $config['seo'] = __('messages.projectMilestone');
        $config['method'] = 'edit';
        $template = 'backend.project_milestone.store';

        return view('backend.dashboard.layout', compact('template', 'config', 'milestone') + [
            'danhSachCha' => $this->danhSachDuAn(),
        ]);
    }

    public function update($id, StoreProjectMilestoneRequest $request)
    {
        if ($this->milestoneService->update($id, $request)) {
            return redirect()->route('project.milestone.index')->with('success', 'Cập nhật bản ghi thành công');
        }
        return redirect()->route('project.milestone.index')->with('error', 'Cập nhật bản ghi không thành công. Hãy thử lại');
    }

    public function delete($id)
    {
        $this->authorize('modules', 'project.milestone.destroy');

        $milestone = $this->milestoneRepository->findById($id);
        $config = $this->config();
        $config['seo'] = __('messages.projectMilestone');
        $template = 'backend.project_milestone.delete';

        return view('backend.dashboard.layout', compact('template', 'config', 'milestone'));
    }

    public function destroy($id)
    {
        if ($this->milestoneService->destroy($id)) {
            return redirect()->route('project.milestone.index')->with('success', 'Xóa bản ghi thành công');
        }
        return redirect()->route('project.milestone.index')->with('error', 'Xóa bản ghi không thành công. Hãy thử lại');
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
            'model' => 'ProjectMilestone',
        ];
    }
}
