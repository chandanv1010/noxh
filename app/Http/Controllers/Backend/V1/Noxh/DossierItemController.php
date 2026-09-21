<?php

namespace App\Http\Controllers\Backend\V1\Noxh;

use App\Http\Controllers\Controller;
use App\Http\Requests\Noxh\StoreDossierItemRequest;
use App\Repositories\Noxh\DossierItemRepository;
use App\Services\V1\Dossier\DossierItemService;
use Illuminate\Http\Request;
use App\Models\DossierSet;

/**
 * Man hinh quan tri: dossier item.
 */
class DossierItemController extends Controller
{
    protected $itemService;
    protected $itemRepository;

    public function __construct(
        DossierItemService $itemService,
        DossierItemRepository $itemRepository
    ) {
        $this->itemService = $itemService;
        $this->itemRepository = $itemRepository;
    }

    public function index(Request $request)
    {
        $this->authorize('modules', 'dossier.item.index');

        $items = $this->itemService->paginate($request);
        $config = $this->config();
        $config['seo'] = __('messages.dossierItem');
        $template = 'backend.dossier_item.index';

        return view('backend.dashboard.layout', compact('template', 'config', 'items') + ['danhSachCha' => DossierSet::where('publish', 2)->orderBy('order')->get()]);
    }

    public function create()
    {
        $this->authorize('modules', 'dossier.item.create');

        $config = $this->config();
        $config['seo'] = __('messages.dossierItem');
        $config['method'] = 'create';
        $template = 'backend.dossier_item.store';

        return view('backend.dashboard.layout', compact('template', 'config') + ['danhSachCha' => DossierSet::where('publish', 2)->orderBy('order')->get()]);
    }

    public function store(StoreDossierItemRequest $request)
    {
        if ($this->itemService->create($request)) {
            return redirect()->route('dossier.item.index')->with('success', 'Thêm mới bản ghi thành công');
        }
        return redirect()->route('dossier.item.index')->with('error', 'Thêm mới bản ghi không thành công. Hãy thử lại');
    }

    public function edit($id)
    {
        $this->authorize('modules', 'dossier.item.update');

        $item = $this->itemRepository->findById($id);
        $config = $this->config();
        $config['seo'] = __('messages.dossierItem');
        $config['method'] = 'edit';
        $template = 'backend.dossier_item.store';

        return view('backend.dashboard.layout', compact('template', 'config', 'item') + ['danhSachCha' => DossierSet::where('publish', 2)->orderBy('order')->get()]);
    }

    public function update($id, StoreDossierItemRequest $request)
    {
        if ($this->itemService->update($id, $request)) {
            return redirect()->route('dossier.item.index')->with('success', 'Cập nhật bản ghi thành công');
        }
        return redirect()->route('dossier.item.index')->with('error', 'Cập nhật bản ghi không thành công. Hãy thử lại');
    }

    public function delete($id)
    {
        $this->authorize('modules', 'dossier.item.destroy');

        $item = $this->itemRepository->findById($id);
        $config = $this->config();
        $config['seo'] = __('messages.dossierItem');
        $template = 'backend.dossier_item.delete';

        return view('backend.dashboard.layout', compact('template', 'config', 'item'));
    }

    public function destroy($id)
    {
        if ($this->itemService->destroy($id)) {
            return redirect()->route('dossier.item.index')->with('success', 'Xóa bản ghi thành công');
        }
        return redirect()->route('dossier.item.index')->with('error', 'Xóa bản ghi không thành công. Hãy thử lại');
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
            'model' => 'DossierItem',
        ];
    }
}
