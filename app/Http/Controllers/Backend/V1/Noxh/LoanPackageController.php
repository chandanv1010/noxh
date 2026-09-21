<?php

namespace App\Http\Controllers\Backend\V1\Noxh;

use App\Http\Controllers\Controller;
use App\Http\Requests\Noxh\StoreLoanPackageRequest;
use App\Repositories\Noxh\LoanPackageRepository;
use App\Services\V1\Loan\LoanPackageService;
use Illuminate\Http\Request;

/**
 * Man hinh quan tri: loan-package.
 */
class LoanPackageController extends Controller
{
    protected $packageService;
    protected $packageRepository;

    public function __construct(
        LoanPackageService $packageService,
        LoanPackageRepository $packageRepository
    ) {
        $this->packageService = $packageService;
        $this->packageRepository = $packageRepository;
    }

    public function index(Request $request)
    {
        $this->authorize('modules', 'loan.package.index');

        $packages = $this->packageService->paginate($request);
        $config = $this->config();
        $config['seo'] = __('messages.loanPackage');
        $template = 'backend.loan_package.index';

        return view('backend.dashboard.layout', compact('template', 'config', 'packages'));
    }

    public function create()
    {
        $this->authorize('modules', 'loan.package.create');

        $config = $this->config();
        $config['seo'] = __('messages.loanPackage');
        $config['method'] = 'create';
        $template = 'backend.loan_package.store';

        return view('backend.dashboard.layout', compact('template', 'config'));
    }

    public function store(StoreLoanPackageRequest $request)
    {
        if ($this->packageService->create($request)) {
            return redirect()->route('loan.package.index')->with('success', 'Thêm mới bản ghi thành công');
        }
        return redirect()->route('loan.package.index')->with('error', 'Thêm mới bản ghi không thành công. Hãy thử lại');
    }

    public function edit($id)
    {
        $this->authorize('modules', 'loan.package.update');

        $package = $this->packageRepository->findById($id);
        $config = $this->config();
        $config['seo'] = __('messages.loanPackage');
        $config['method'] = 'edit';
        $template = 'backend.loan_package.store';

        return view('backend.dashboard.layout', compact('template', 'config', 'package'));
    }

    public function update($id, StoreLoanPackageRequest $request)
    {
        if ($this->packageService->update($id, $request)) {
            return redirect()->route('loan.package.index')->with('success', 'Cập nhật bản ghi thành công');
        }
        return redirect()->route('loan.package.index')->with('error', 'Cập nhật bản ghi không thành công. Hãy thử lại');
    }

    public function delete($id)
    {
        $this->authorize('modules', 'loan.package.destroy');

        $package = $this->packageRepository->findById($id);
        $config = $this->config();
        $config['seo'] = __('messages.loanPackage');
        $template = 'backend.loan_package.delete';

        return view('backend.dashboard.layout', compact('template', 'config', 'package'));
    }

    public function destroy($id)
    {
        if ($this->packageService->destroy($id)) {
            return redirect()->route('loan.package.index')->with('success', 'Xóa bản ghi thành công');
        }
        return redirect()->route('loan.package.index')->with('error', 'Xóa bản ghi không thành công. Hãy thử lại');
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
                'backend/plugins/ckeditor/ckeditor.js',
            ],
            'model' => 'LoanPackage',
        ];
    }
}
