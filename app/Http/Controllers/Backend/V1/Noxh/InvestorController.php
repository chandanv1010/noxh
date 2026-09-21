<?php

namespace App\Http\Controllers\Backend\V1\Noxh;

use App\Http\Controllers\Controller;
use App\Http\Requests\Noxh\StoreInvestorRequest;
use App\Repositories\Noxh\InvestorRepository;
use App\Services\V1\Investor\InvestorService;
use Illuminate\Http\Request;

/**
 * Man hinh quan tri: investor.
 */
class InvestorController extends Controller
{
    protected $investorService;
    protected $investorRepository;

    public function __construct(
        InvestorService $investorService,
        InvestorRepository $investorRepository
    ) {
        $this->investorService = $investorService;
        $this->investorRepository = $investorRepository;
    }

    public function index(Request $request)
    {
        $this->authorize('modules', 'investor.index');

        $investors = $this->investorService->paginate($request);
        $config = $this->config();
        $config['seo'] = __('messages.investor');
        $template = 'backend.investor.index';

        return view('backend.dashboard.layout', compact('template', 'config', 'investors'));
    }

    public function create()
    {
        $this->authorize('modules', 'investor.create');

        $config = $this->config();
        $config['seo'] = __('messages.investor');
        $config['method'] = 'create';
        $template = 'backend.investor.store';

        return view('backend.dashboard.layout', compact('template', 'config'));
    }

    public function store(StoreInvestorRequest $request)
    {
        if ($this->investorService->create($request)) {
            return redirect()->route('investor.index')->with('success', 'Thêm mới bản ghi thành công');
        }
        return redirect()->route('investor.index')->with('error', 'Thêm mới bản ghi không thành công. Hãy thử lại');
    }

    public function edit($id)
    {
        $this->authorize('modules', 'investor.update');

        $investor = $this->investorRepository->findById($id);
        $config = $this->config();
        $config['seo'] = __('messages.investor');
        $config['method'] = 'edit';
        $template = 'backend.investor.store';

        return view('backend.dashboard.layout', compact('template', 'config', 'investor'));
    }

    public function update($id, StoreInvestorRequest $request)
    {
        if ($this->investorService->update($id, $request)) {
            return redirect()->route('investor.index')->with('success', 'Cập nhật bản ghi thành công');
        }
        return redirect()->route('investor.index')->with('error', 'Cập nhật bản ghi không thành công. Hãy thử lại');
    }

    public function delete($id)
    {
        $this->authorize('modules', 'investor.destroy');

        $investor = $this->investorRepository->findById($id);
        $config = $this->config();
        $config['seo'] = __('messages.investor');
        $template = 'backend.investor.delete';

        return view('backend.dashboard.layout', compact('template', 'config', 'investor'));
    }

    public function destroy($id)
    {
        if ($this->investorService->destroy($id)) {
            return redirect()->route('investor.index')->with('success', 'Xóa bản ghi thành công');
        }
        return redirect()->route('investor.index')->with('error', 'Xóa bản ghi không thành công. Hãy thử lại');
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
            'model' => 'Investor',
        ];
    }
}
