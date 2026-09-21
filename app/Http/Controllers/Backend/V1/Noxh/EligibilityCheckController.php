<?php

namespace App\Http\Controllers\Backend\V1\Noxh;

use App\Http\Controllers\Controller;
use App\Models\EligibilityCheck;
use App\Repositories\Noxh\EligibilityCheckRepository;
use App\Services\V1\Eligibility\EligibilityCheckService;
use Illuminate\Http\Request;

/**
 * Ket qua kiem tra dieu kien do khach thuc hien ngoai website.
 *
 * Chi doc va xoa: ban ghi la ket qua tinh toan tai thoi diem khach tra loi,
 * sua lai trong admin se lam sai lech thu khach da nhan duoc.
 */
class EligibilityCheckController extends Controller
{
    protected $checkService;
    protected $checkRepository;

    public function __construct(
        EligibilityCheckService $checkService,
        EligibilityCheckRepository $checkRepository
    ) {
        $this->checkService = $checkService;
        $this->checkRepository = $checkRepository;
    }

    public function index(Request $request)
    {
        $this->authorize('modules', 'eligibility.check.index');

        $checks = $this->checkService->paginate($request);
        $config = $this->config();
        $config['seo'] = __('messages.eligibilityCheck');
        $template = 'backend.eligibility_check.index';

        return view('backend.dashboard.layout', compact('template', 'config', 'checks') + [
            'muc' => EligibilityCheck::MUC,
            'demTheoMuc' => $this->checkService->demTheoMuc(),
        ]);
    }

    public function edit($id)
    {
        $this->authorize('modules', 'eligibility.check.index');

        $check = $this->checkRepository->findById($id, ['*'], ['answers.question', 'answers.option']);
        $config = $this->config();
        $config['seo'] = __('messages.eligibilityCheck');
        $config['method'] = 'edit';
        $template = 'backend.eligibility_check.store';

        return view('backend.dashboard.layout', compact('template', 'config', 'check'));
    }

    public function delete($id)
    {
        $this->authorize('modules', 'eligibility.check.destroy');

        $check = $this->checkRepository->findById($id);
        $config = $this->config();
        $config['seo'] = __('messages.eligibilityCheck');
        $template = 'backend.eligibility_check.delete';

        return view('backend.dashboard.layout', compact('template', 'config', 'check'));
    }

    public function destroy($id)
    {
        if ($this->checkService->destroy($id)) {
            return redirect()->route('eligibility.check.index')->with('success', 'Xóa bản ghi thành công');
        }
        return redirect()->route('eligibility.check.index')->with('error', 'Xóa bản ghi không thành công. Hãy thử lại');
    }

    private function config()
    {
        return ['css' => [], 'js' => [], 'model' => 'EligibilityCheck'];
    }
}
