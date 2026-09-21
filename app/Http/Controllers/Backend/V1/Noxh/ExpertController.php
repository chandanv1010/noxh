<?php

namespace App\Http\Controllers\Backend\V1\Noxh;

use App\Http\Controllers\Controller;
use App\Http\Requests\Noxh\StoreExpertRequest;
use App\Repositories\Noxh\ExpertRepository;
use App\Services\V1\Expert\ExpertService;
use Illuminate\Http\Request;

/**
 * Man hinh quan tri: expert.
 */
class ExpertController extends Controller
{
    protected $expertService;
    protected $expertRepository;

    public function __construct(
        ExpertService $expertService,
        ExpertRepository $expertRepository
    ) {
        $this->expertService = $expertService;
        $this->expertRepository = $expertRepository;
    }

    public function index(Request $request)
    {
        $this->authorize('modules', 'expert.index');

        $experts = $this->expertService->paginate($request);
        $config = $this->config();
        $config['seo'] = __('messages.expert');
        $template = 'backend.expert.index';

        return view('backend.dashboard.layout', compact('template', 'config', 'experts'));
    }

    public function create()
    {
        $this->authorize('modules', 'expert.create');

        $config = $this->config();
        $config['seo'] = __('messages.expert');
        $config['method'] = 'create';
        $template = 'backend.expert.store';

        return view('backend.dashboard.layout', compact('template', 'config'));
    }

    public function store(StoreExpertRequest $request)
    {
        if ($this->expertService->create($request)) {
            return redirect()->route('expert.index')->with('success', 'Thêm mới bản ghi thành công');
        }
        return redirect()->route('expert.index')->with('error', 'Thêm mới bản ghi không thành công. Hãy thử lại');
    }

    public function edit($id)
    {
        $this->authorize('modules', 'expert.update');

        $expert = $this->expertRepository->findById($id);
        $config = $this->config();
        $config['seo'] = __('messages.expert');
        $config['method'] = 'edit';
        $template = 'backend.expert.store';

        return view('backend.dashboard.layout', compact('template', 'config', 'expert'));
    }

    public function update($id, StoreExpertRequest $request)
    {
        if ($this->expertService->update($id, $request)) {
            return redirect()->route('expert.index')->with('success', 'Cập nhật bản ghi thành công');
        }
        return redirect()->route('expert.index')->with('error', 'Cập nhật bản ghi không thành công. Hãy thử lại');
    }

    public function delete($id)
    {
        $this->authorize('modules', 'expert.destroy');

        $expert = $this->expertRepository->findById($id);
        $config = $this->config();
        $config['seo'] = __('messages.expert');
        $template = 'backend.expert.delete';

        return view('backend.dashboard.layout', compact('template', 'config', 'expert'));
    }

    public function destroy($id)
    {
        if ($this->expertService->destroy($id)) {
            return redirect()->route('expert.index')->with('success', 'Xóa bản ghi thành công');
        }
        return redirect()->route('expert.index')->with('error', 'Xóa bản ghi không thành công. Hãy thử lại');
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
            'model' => 'Expert',
        ];
    }
}
