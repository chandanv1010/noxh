<?php

namespace App\Http\Controllers\Backend\V1\Noxh;

use App\Http\Controllers\Controller;
use App\Http\Requests\Noxh\StoreEligibilityOptionRequest;
use App\Repositories\Noxh\EligibilityOptionRepository;
use App\Services\V1\Eligibility\EligibilityOptionService;
use Illuminate\Http\Request;
use App\Models\EligibilityQuestion;

/**
 * Man hinh quan tri: eligibility option.
 */
class EligibilityOptionController extends Controller
{
    protected $optionService;
    protected $optionRepository;

    public function __construct(
        EligibilityOptionService $optionService,
        EligibilityOptionRepository $optionRepository
    ) {
        $this->optionService = $optionService;
        $this->optionRepository = $optionRepository;
    }

    public function index(Request $request)
    {
        $this->authorize('modules', 'eligibility.option.index');

        $options = $this->optionService->paginate($request);
        $config = $this->config();
        $config['seo'] = __('messages.eligibilityOption');
        $template = 'backend.eligibility_option.index';

        return view('backend.dashboard.layout', compact('template', 'config', 'options') + ['danhSachCha' => EligibilityQuestion::where('publish', 2)->orderBy('order')->get()]);
    }

    public function create()
    {
        $this->authorize('modules', 'eligibility.option.create');

        $config = $this->config();
        $config['seo'] = __('messages.eligibilityOption');
        $config['method'] = 'create';
        $template = 'backend.eligibility_option.store';

        return view('backend.dashboard.layout', compact('template', 'config') + ['danhSachCha' => EligibilityQuestion::where('publish', 2)->orderBy('order')->get()]);
    }

    public function store(StoreEligibilityOptionRequest $request)
    {
        if ($this->optionService->create($request)) {
            return redirect()->route('eligibility.option.index')->with('success', 'Thêm mới bản ghi thành công');
        }
        return redirect()->route('eligibility.option.index')->with('error', 'Thêm mới bản ghi không thành công. Hãy thử lại');
    }

    public function edit($id)
    {
        $this->authorize('modules', 'eligibility.option.update');

        $option = $this->optionRepository->findById($id);
        $config = $this->config();
        $config['seo'] = __('messages.eligibilityOption');
        $config['method'] = 'edit';
        $template = 'backend.eligibility_option.store';

        return view('backend.dashboard.layout', compact('template', 'config', 'option') + ['danhSachCha' => EligibilityQuestion::where('publish', 2)->orderBy('order')->get()]);
    }

    public function update($id, StoreEligibilityOptionRequest $request)
    {
        if ($this->optionService->update($id, $request)) {
            return redirect()->route('eligibility.option.index')->with('success', 'Cập nhật bản ghi thành công');
        }
        return redirect()->route('eligibility.option.index')->with('error', 'Cập nhật bản ghi không thành công. Hãy thử lại');
    }

    public function delete($id)
    {
        $this->authorize('modules', 'eligibility.option.destroy');

        $option = $this->optionRepository->findById($id);
        $config = $this->config();
        $config['seo'] = __('messages.eligibilityOption');
        $template = 'backend.eligibility_option.delete';

        return view('backend.dashboard.layout', compact('template', 'config', 'option'));
    }

    public function destroy($id)
    {
        if ($this->optionService->destroy($id)) {
            return redirect()->route('eligibility.option.index')->with('success', 'Xóa bản ghi thành công');
        }
        return redirect()->route('eligibility.option.index')->with('error', 'Xóa bản ghi không thành công. Hãy thử lại');
    }

    private function config()
    {
        return [
            'css' => [
                'backend/css/plugins/switchery/switchery.css',
            ],
            'js' => [
                'backend/js/plugins/switchery/switchery.js',
                'backend/library/finder.js',
            ],
            'model' => 'EligibilityOption',
        ];
    }
}
