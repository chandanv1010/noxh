<?php

namespace App\Http\Controllers\Backend\V1\Noxh;

use App\Http\Controllers\Controller;
use App\Http\Requests\Noxh\StoreEligibilityCriterionRequest;
use App\Models\EligibilityQuestion;
use App\Repositories\Noxh\EligibilityCriterionRepository;
use App\Services\V1\Eligibility\EligibilityCriterionService;
use Illuminate\Http\Request;

/**
 * Man hinh quan tri: sau tieu chi in tren trang ket qua
 * (ban ve thanh-cong.jpg / luu y.jpg / that bai.jpg).
 */
class EligibilityCriterionController extends Controller
{
    protected $criterionService;
    protected $criterionRepository;

    public function __construct(
        EligibilityCriterionService $criterionService,
        EligibilityCriterionRepository $criterionRepository
    ) {
        $this->criterionService = $criterionService;
        $this->criterionRepository = $criterionRepository;
    }

    public function index(Request $request)
    {
        $this->authorize('modules', 'eligibility.criterion.index');

        $criteria = $this->criterionService->paginate($request);
        $config = $this->config();
        $config['seo'] = __('messages.eligibilityCriterion');
        $template = 'backend.eligibility_criterion.index';

        return view('backend.dashboard.layout', compact('template', 'config', 'criteria') + $this->chung());
    }

    public function create()
    {
        $this->authorize('modules', 'eligibility.criterion.create');

        $config = $this->config();
        $config['seo'] = __('messages.eligibilityCriterion');
        $config['method'] = 'create';
        $template = 'backend.eligibility_criterion.store';

        return view('backend.dashboard.layout', compact('template', 'config') + $this->chung());
    }

    public function store(StoreEligibilityCriterionRequest $request)
    {
        if ($this->criterionService->create($request)) {
            return redirect()->route('eligibility.criterion.index')->with('success', 'Thêm mới bản ghi thành công');
        }
        return redirect()->route('eligibility.criterion.index')->with('error', 'Thêm mới bản ghi không thành công. Hãy thử lại');
    }

    public function edit($id)
    {
        $this->authorize('modules', 'eligibility.criterion.update');

        $criterion = $this->criterionRepository->findById($id);
        $config = $this->config();
        $config['seo'] = __('messages.eligibilityCriterion');
        $config['method'] = 'edit';
        $template = 'backend.eligibility_criterion.store';

        return view('backend.dashboard.layout', compact('template', 'config', 'criterion') + $this->chung());
    }

    public function update($id, StoreEligibilityCriterionRequest $request)
    {
        if ($this->criterionService->update($id, $request)) {
            return redirect()->route('eligibility.criterion.index')->with('success', 'Cập nhật bản ghi thành công');
        }
        return redirect()->route('eligibility.criterion.index')->with('error', 'Cập nhật bản ghi không thành công. Hãy thử lại');
    }

    public function delete($id)
    {
        $this->authorize('modules', 'eligibility.criterion.destroy');

        $criterion = $this->criterionRepository->findById($id);
        $config = $this->config();
        $config['seo'] = __('messages.eligibilityCriterion');
        $template = 'backend.eligibility_criterion.delete';

        return view('backend.dashboard.layout', compact('template', 'config', 'criterion'));
    }

    public function destroy($id)
    {
        if ($this->criterionService->destroy($id)) {
            return redirect()->route('eligibility.criterion.index')->with('success', 'Xóa bản ghi thành công');
        }
        return redirect()->route('eligibility.criterion.index')->with('error', 'Xóa bản ghi không thành công. Hãy thử lại');
    }

    private function chung(): array
    {
        return ['danhSachCha' => EligibilityQuestion::where('publish', 2)->orderBy('order')->get()];
    }

    private function config()
    {
        return [
            'css' => ['backend/css/plugins/switchery/switchery.css'],
            'js' => ['backend/js/plugins/switchery/switchery.js'],
            'model' => 'EligibilityCriterion',
        ];
    }
}
