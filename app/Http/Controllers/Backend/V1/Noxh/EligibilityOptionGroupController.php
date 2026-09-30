<?php

namespace App\Http\Controllers\Backend\V1\Noxh;

use App\Http\Controllers\Controller;
use App\Http\Requests\Noxh\StoreEligibilityOptionGroupRequest;
use App\Models\EligibilityQuestion;
use App\Repositories\Noxh\EligibilityOptionGroupRepository;
use App\Services\V1\Eligibility\EligibilityOptionGroupService;
use Illuminate\Http\Request;

/**
 * Man hinh quan tri: tinh huong (nhom dap an) cua cau hoi dieu kien.
 *
 * Chi dung cho cau hoi dat bo cuc "Chia theo tinh huong" - ban ve
 * noxh_image/w-2.jpg.
 */
class EligibilityOptionGroupController extends Controller
{
    protected $groupService;
    protected $groupRepository;

    public function __construct(
        EligibilityOptionGroupService $groupService,
        EligibilityOptionGroupRepository $groupRepository
    ) {
        $this->groupService = $groupService;
        $this->groupRepository = $groupRepository;
    }

    public function index(Request $request)
    {
        $this->authorize('modules', 'eligibility.group.index');

        $groups = $this->groupService->paginate($request);
        $config = $this->config();
        $config['seo'] = __('messages.eligibilityOptionGroup');
        $template = 'backend.eligibility_group.index';

        return view('backend.dashboard.layout', compact('template', 'config', 'groups') + $this->chung());
    }

    public function create()
    {
        $this->authorize('modules', 'eligibility.group.create');

        $config = $this->config();
        $config['seo'] = __('messages.eligibilityOptionGroup');
        $config['method'] = 'create';
        $template = 'backend.eligibility_group.store';

        return view('backend.dashboard.layout', compact('template', 'config') + $this->chung());
    }

    public function store(StoreEligibilityOptionGroupRequest $request)
    {
        if ($this->groupService->create($request)) {
            return redirect()->route('eligibility.group.index')->with('success', 'Thêm mới bản ghi thành công');
        }
        return redirect()->route('eligibility.group.index')->with('error', 'Thêm mới bản ghi không thành công. Hãy thử lại');
    }

    public function edit($id)
    {
        $this->authorize('modules', 'eligibility.group.update');

        $group = $this->groupRepository->findById($id);
        $config = $this->config();
        $config['seo'] = __('messages.eligibilityOptionGroup');
        $config['method'] = 'edit';
        $template = 'backend.eligibility_group.store';

        return view('backend.dashboard.layout', compact('template', 'config', 'group') + $this->chung());
    }

    public function update($id, StoreEligibilityOptionGroupRequest $request)
    {
        if ($this->groupService->update($id, $request)) {
            return redirect()->route('eligibility.group.index')->with('success', 'Cập nhật bản ghi thành công');
        }
        return redirect()->route('eligibility.group.index')->with('error', 'Cập nhật bản ghi không thành công. Hãy thử lại');
    }

    public function delete($id)
    {
        $this->authorize('modules', 'eligibility.group.destroy');

        $group = $this->groupRepository->findById($id);
        $config = $this->config();
        $config['seo'] = __('messages.eligibilityOptionGroup');
        $template = 'backend.eligibility_group.delete';

        return view('backend.dashboard.layout', compact('template', 'config', 'group'));
    }

    public function destroy($id)
    {
        if ($this->groupService->destroy($id)) {
            return redirect()->route('eligibility.group.index')->with('success', 'Xóa bản ghi thành công');
        }
        return redirect()->route('eligibility.group.index')->with('error', 'Xóa bản ghi không thành công. Hãy thử lại');
    }

    private function chung(): array
    {
        return ['danhSachCha' => EligibilityQuestion::where('publish', 2)->orderBy('order')->get()];
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
            'model' => 'EligibilityOptionGroup',
        ];
    }
}
