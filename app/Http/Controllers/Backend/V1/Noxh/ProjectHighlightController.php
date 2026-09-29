<?php

namespace App\Http\Controllers\Backend\V1\Noxh;

use App\Classes\NoxhIcon;
use App\Http\Controllers\Backend\V1\Noxh\Concerns\ChonDuAn;
use App\Http\Controllers\Controller;
use App\Http\Requests\Noxh\StoreProjectHighlightRequest;
use App\Repositories\Noxh\ProjectHighlightRepository;
use App\Services\V1\Project\ProjectHighlightService;
use Illuminate\Http\Request;

/**
 * Man hinh quan tri: project/highlight - bon o diem nhan trong the gia o dau
 * trang chi tiet du an.
 *
 * Du an nao khong khai o nao thi trang lay bon o mac dinh trong Cau hinh
 * chung (nhom "Trang Du an - chi tiet").
 */
class ProjectHighlightController extends Controller
{
    use ChonDuAn;

    protected $highlightService;
    protected $highlightRepository;

    public function __construct(
        ProjectHighlightService $highlightService,
        ProjectHighlightRepository $highlightRepository
    ) {
        $this->highlightService = $highlightService;
        $this->highlightRepository = $highlightRepository;
    }

    public function index(Request $request)
    {
        $this->authorize('modules', 'project.highlight.index');

        $highlights = $this->highlightService->paginate($request);
        $config = $this->config();
        $config['seo'] = __('messages.projectHighlight');
        $template = 'backend.project_highlight.index';

        return view('backend.dashboard.layout', compact('template', 'config', 'highlights') + [
            'danhSachCha' => $this->danhSachDuAn(),
        ]);
    }

    public function create()
    {
        $this->authorize('modules', 'project.highlight.create');

        $config = $this->config();
        $config['seo'] = __('messages.projectHighlight');
        $config['method'] = 'create';
        $template = 'backend.project_highlight.store';

        return view('backend.dashboard.layout', compact('template', 'config') + [
            'danhSachCha' => $this->danhSachDuAn(),
            'danhSachIcon' => NoxhIcon::chon(),
        ]);
    }

    public function store(StoreProjectHighlightRequest $request)
    {
        if ($this->highlightService->create($request)) {
            return redirect()->route('project.highlight.index')->with('success', 'Thêm mới bản ghi thành công');
        }
        return redirect()->route('project.highlight.index')->with('error', 'Thêm mới bản ghi không thành công. Hãy thử lại');
    }

    public function edit($id)
    {
        $this->authorize('modules', 'project.highlight.update');

        $highlight = $this->highlightRepository->findById($id);
        $config = $this->config();
        $config['seo'] = __('messages.projectHighlight');
        $config['method'] = 'edit';
        $template = 'backend.project_highlight.store';

        return view('backend.dashboard.layout', compact('template', 'config', 'highlight') + [
            'danhSachCha' => $this->danhSachDuAn(),
            'danhSachIcon' => NoxhIcon::chon(),
        ]);
    }

    public function update($id, StoreProjectHighlightRequest $request)
    {
        if ($this->highlightService->update($id, $request)) {
            return redirect()->route('project.highlight.index')->with('success', 'Cập nhật bản ghi thành công');
        }
        return redirect()->route('project.highlight.index')->with('error', 'Cập nhật bản ghi không thành công. Hãy thử lại');
    }

    public function delete($id)
    {
        $this->authorize('modules', 'project.highlight.destroy');

        $highlight = $this->highlightRepository->findById($id);
        $config = $this->config();
        $config['seo'] = __('messages.projectHighlight');
        $template = 'backend.project_highlight.delete';

        return view('backend.dashboard.layout', compact('template', 'config', 'highlight'));
    }

    public function destroy($id)
    {
        if ($this->highlightService->destroy($id)) {
            return redirect()->route('project.highlight.index')->with('success', 'Xóa bản ghi thành công');
        }
        return redirect()->route('project.highlight.index')->with('error', 'Xóa bản ghi không thành công. Hãy thử lại');
    }

    private function config()
    {
        return [
            'css' => ['backend/css/plugins/switchery/switchery.css'],
            'js' => ['backend/js/plugins/switchery/switchery.js'],
            'model' => 'ProjectHighlight',
        ];
    }
}
