<?php

namespace App\Http\Controllers\Backend\V1\Noxh;

use App\Http\Controllers\Controller;
use App\Http\Requests\Noxh\StoreEligibilityPanelRequest;
use App\Models\EligibilityQuestion;
use App\Repositories\Noxh\EligibilityPanelRepository;
use App\Services\V1\Eligibility\EligibilityPanelService;
use Illuminate\Http\Request;

/**
 * Man hinh quan tri: tam o cot phai cua tung buoc kiem tra dieu kien
 * (ban ve w-3, w-4, w-5).
 */
class EligibilityPanelController extends Controller
{
    protected $panelService;
    protected $panelRepository;

    public function __construct(
        EligibilityPanelService $panelService,
        EligibilityPanelRepository $panelRepository
    ) {
        $this->panelService = $panelService;
        $this->panelRepository = $panelRepository;
    }

    public function index(Request $request)
    {
        $this->authorize('modules', 'eligibility.panel.index');

        $panels = $this->panelService->paginate($request);
        $config = $this->config();
        $config['seo'] = __('messages.eligibilityPanel');
        $template = 'backend.eligibility_panel.index';

        return view('backend.dashboard.layout', compact('template', 'config', 'panels') + $this->chung());
    }

    public function create()
    {
        $this->authorize('modules', 'eligibility.panel.create');

        $config = $this->config();
        $config['seo'] = __('messages.eligibilityPanel');
        $config['method'] = 'create';
        $template = 'backend.eligibility_panel.store';

        return view('backend.dashboard.layout', compact('template', 'config') + $this->chung());
    }

    public function store(StoreEligibilityPanelRequest $request)
    {
        if ($this->panelService->create($request)) {
            return redirect()->route('eligibility.panel.index')->with('success', 'Thêm mới bản ghi thành công');
        }
        return redirect()->route('eligibility.panel.index')->with('error', 'Thêm mới bản ghi không thành công. Hãy thử lại');
    }

    public function edit($id)
    {
        $this->authorize('modules', 'eligibility.panel.update');

        $panel = $this->panelRepository->findById($id);
        $config = $this->config();
        $config['seo'] = __('messages.eligibilityPanel');
        $config['method'] = 'edit';
        $template = 'backend.eligibility_panel.store';

        return view('backend.dashboard.layout', compact('template', 'config', 'panel') + $this->chung());
    }

    public function update($id, StoreEligibilityPanelRequest $request)
    {
        if ($this->panelService->update($id, $request)) {
            return redirect()->route('eligibility.panel.index')->with('success', 'Cập nhật bản ghi thành công');
        }
        return redirect()->route('eligibility.panel.index')->with('error', 'Cập nhật bản ghi không thành công. Hãy thử lại');
    }

    public function delete($id)
    {
        $this->authorize('modules', 'eligibility.panel.destroy');

        $panel = $this->panelRepository->findById($id);
        $config = $this->config();
        $config['seo'] = __('messages.eligibilityPanel');
        $template = 'backend.eligibility_panel.delete';

        return view('backend.dashboard.layout', compact('template', 'config', 'panel'));
    }

    public function destroy($id)
    {
        if ($this->panelService->destroy($id)) {
            return redirect()->route('eligibility.panel.index')->with('success', 'Xóa bản ghi thành công');
        }
        return redirect()->route('eligibility.panel.index')->with('error', 'Xóa bản ghi không thành công. Hãy thử lại');
    }

    private function chung(): array
    {
        return ['danhSachCha' => EligibilityQuestion::where('publish', 2)->orderBy('order')->get()];
    }

    private function config()
    {
        return [
            'css' => ['backend/css/plugins/switchery/switchery.css'],
            'js' => [
                'backend/js/plugins/switchery/switchery.js',
                'backend/library/finder.js',
            ],
            'model' => 'EligibilityPanel',
        ];
    }
}
