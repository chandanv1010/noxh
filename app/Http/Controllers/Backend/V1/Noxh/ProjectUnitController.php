<?php

namespace App\Http\Controllers\Backend\V1\Noxh;

use App\Http\Controllers\Backend\V1\Noxh\Concerns\ChonDuAn;
use App\Http\Controllers\Controller;
use App\Http\Requests\Noxh\StoreProjectUnitRequest;
use App\Repositories\Noxh\ProjectUnitRepository;
use App\Services\V1\Project\ProjectUnitService;
use Illuminate\Http\Request;

/**
 * Man hinh quan tri: project/unit - cac loai can ho cua mot du an.
 *
 * Vao tu man hinh sua du an thi duong dan da kem san ?product_id nen o chon
 * du an duoc dien truoc.
 */
class ProjectUnitController extends Controller
{
    use ChonDuAn;

    protected $unitService;
    protected $unitRepository;

    public function __construct(
        ProjectUnitService $unitService,
        ProjectUnitRepository $unitRepository
    ) {
        $this->unitService = $unitService;
        $this->unitRepository = $unitRepository;
    }

    public function index(Request $request)
    {
        $this->authorize('modules', 'project.unit.index');

        $units = $this->unitService->paginate($request);
        $config = $this->config();
        $config['seo'] = __('messages.projectUnit');
        $template = 'backend.project_unit.index';

        return view('backend.dashboard.layout', compact('template', 'config', 'units') + [
            'danhSachCha' => $this->danhSachDuAn(),
        ]);
    }

    public function create()
    {
        $this->authorize('modules', 'project.unit.create');

        $config = $this->config();
        $config['seo'] = __('messages.projectUnit');
        $config['method'] = 'create';
        $template = 'backend.project_unit.store';

        return view('backend.dashboard.layout', compact('template', 'config') + [
            'danhSachCha' => $this->danhSachDuAn(),
        ]);
    }

    public function store(StoreProjectUnitRequest $request)
    {
        if ($this->unitService->create($request)) {
            return redirect()->route('project.unit.index')->with('success', 'Thêm mới bản ghi thành công');
        }
        return redirect()->route('project.unit.index')->with('error', 'Thêm mới bản ghi không thành công. Hãy thử lại');
    }

    public function edit($id)
    {
        $this->authorize('modules', 'project.unit.update');

        $unit = $this->unitRepository->findById($id);
        $config = $this->config();
        $config['seo'] = __('messages.projectUnit');
        $config['method'] = 'edit';
        $template = 'backend.project_unit.store';

        return view('backend.dashboard.layout', compact('template', 'config', 'unit') + [
            'danhSachCha' => $this->danhSachDuAn(),
        ]);
    }

    public function update($id, StoreProjectUnitRequest $request)
    {
        if ($this->unitService->update($id, $request)) {
            return redirect()->route('project.unit.index')->with('success', 'Cập nhật bản ghi thành công');
        }
        return redirect()->route('project.unit.index')->with('error', 'Cập nhật bản ghi không thành công. Hãy thử lại');
    }

    public function delete($id)
    {
        $this->authorize('modules', 'project.unit.destroy');

        $unit = $this->unitRepository->findById($id);
        $config = $this->config();
        $config['seo'] = __('messages.projectUnit');
        $template = 'backend.project_unit.delete';

        return view('backend.dashboard.layout', compact('template', 'config', 'unit'));
    }

    public function destroy($id)
    {
        if ($this->unitService->destroy($id)) {
            return redirect()->route('project.unit.index')->with('success', 'Xóa bản ghi thành công');
        }
        return redirect()->route('project.unit.index')->with('error', 'Xóa bản ghi không thành công. Hãy thử lại');
    }

    private function config()
    {
        return [
            'css' => ['backend/css/plugins/switchery/switchery.css'],
            'js' => [
                'backend/js/plugins/switchery/switchery.js',
                'backend/library/finder.js',
            ],
            'model' => 'ProjectUnit',
        ];
    }
}
