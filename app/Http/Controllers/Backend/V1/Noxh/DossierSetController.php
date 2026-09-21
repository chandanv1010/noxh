<?php

namespace App\Http\Controllers\Backend\V1\Noxh;

use App\Http\Controllers\Controller;
use App\Http\Requests\Noxh\StoreDossierSetRequest;
use App\Repositories\Noxh\DossierSetRepository;
use App\Services\V1\Dossier\DossierSetService;
use Illuminate\Http\Request;

/**
 * Man hinh quan tri: dossier set.
 */
class DossierSetController extends Controller
{
    protected $setService;
    protected $setRepository;

    public function __construct(
        DossierSetService $setService,
        DossierSetRepository $setRepository
    ) {
        $this->setService = $setService;
        $this->setRepository = $setRepository;
    }

    public function index(Request $request)
    {
        $this->authorize('modules', 'dossier.set.index');

        $sets = $this->setService->paginate($request);
        $config = $this->config();
        $config['seo'] = __('messages.dossierSet');
        $template = 'backend.dossier_set.index';

        return view('backend.dashboard.layout', compact('template', 'config', 'sets'));
    }

    public function create()
    {
        $this->authorize('modules', 'dossier.set.create');

        $config = $this->config();
        $config['seo'] = __('messages.dossierSet');
        $config['method'] = 'create';
        $template = 'backend.dossier_set.store';

        return view('backend.dashboard.layout', compact('template', 'config'));
    }

    public function store(StoreDossierSetRequest $request)
    {
        if ($this->setService->create($request)) {
            return redirect()->route('dossier.set.index')->with('success', 'Thêm mới bản ghi thành công');
        }
        return redirect()->route('dossier.set.index')->with('error', 'Thêm mới bản ghi không thành công. Hãy thử lại');
    }

    public function edit($id)
    {
        $this->authorize('modules', 'dossier.set.update');

        $set = $this->setRepository->findById($id);
        $config = $this->config();
        $config['seo'] = __('messages.dossierSet');
        $config['method'] = 'edit';
        $template = 'backend.dossier_set.store';

        return view('backend.dashboard.layout', compact('template', 'config', 'set'));
    }

    public function update($id, StoreDossierSetRequest $request)
    {
        if ($this->setService->update($id, $request)) {
            return redirect()->route('dossier.set.index')->with('success', 'Cập nhật bản ghi thành công');
        }
        return redirect()->route('dossier.set.index')->with('error', 'Cập nhật bản ghi không thành công. Hãy thử lại');
    }

    public function delete($id)
    {
        $this->authorize('modules', 'dossier.set.destroy');

        $set = $this->setRepository->findById($id);
        $config = $this->config();
        $config['seo'] = __('messages.dossierSet');
        $template = 'backend.dossier_set.delete';

        return view('backend.dashboard.layout', compact('template', 'config', 'set'));
    }

    public function destroy($id)
    {
        if ($this->setService->destroy($id)) {
            return redirect()->route('dossier.set.index')->with('success', 'Xóa bản ghi thành công');
        }
        return redirect()->route('dossier.set.index')->with('error', 'Xóa bản ghi không thành công. Hãy thử lại');
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
            'model' => 'DossierSet',
        ];
    }
}
