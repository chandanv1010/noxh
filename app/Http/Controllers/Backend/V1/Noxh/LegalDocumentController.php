<?php

namespace App\Http\Controllers\Backend\V1\Noxh;

use App\Http\Controllers\Controller;
use App\Http\Requests\Noxh\StoreLegalDocumentRequest;
use App\Repositories\Noxh\LegalDocumentRepository;
use App\Services\V1\Legal\LegalDocumentService;
use Illuminate\Http\Request;

/**
 * Man hinh quan tri: legal-document.
 */
class LegalDocumentController extends Controller
{
    protected $documentService;
    protected $documentRepository;

    public function __construct(
        LegalDocumentService $documentService,
        LegalDocumentRepository $documentRepository
    ) {
        $this->documentService = $documentService;
        $this->documentRepository = $documentRepository;
    }

    public function index(Request $request)
    {
        $this->authorize('modules', 'legal.document.index');

        $documents = $this->documentService->paginate($request);
        $config = $this->config();
        $config['seo'] = __('messages.legalDocument');
        $template = 'backend.legal_document.index';

        return view('backend.dashboard.layout', compact('template', 'config', 'documents'));
    }

    public function create()
    {
        $this->authorize('modules', 'legal.document.create');

        $config = $this->config();
        $config['seo'] = __('messages.legalDocument');
        $config['method'] = 'create';
        $template = 'backend.legal_document.store';

        return view('backend.dashboard.layout', compact('template', 'config'));
    }

    public function store(StoreLegalDocumentRequest $request)
    {
        if ($this->documentService->create($request)) {
            return redirect()->route('legal.document.index')->with('success', 'Thêm mới bản ghi thành công');
        }
        return redirect()->route('legal.document.index')->with('error', 'Thêm mới bản ghi không thành công. Hãy thử lại');
    }

    public function edit($id)
    {
        $this->authorize('modules', 'legal.document.update');

        $document = $this->documentRepository->findById($id);
        $config = $this->config();
        $config['seo'] = __('messages.legalDocument');
        $config['method'] = 'edit';
        $template = 'backend.legal_document.store';

        return view('backend.dashboard.layout', compact('template', 'config', 'document'));
    }

    public function update($id, StoreLegalDocumentRequest $request)
    {
        if ($this->documentService->update($id, $request)) {
            return redirect()->route('legal.document.index')->with('success', 'Cập nhật bản ghi thành công');
        }
        return redirect()->route('legal.document.index')->with('error', 'Cập nhật bản ghi không thành công. Hãy thử lại');
    }

    public function delete($id)
    {
        $this->authorize('modules', 'legal.document.destroy');

        $document = $this->documentRepository->findById($id);
        $config = $this->config();
        $config['seo'] = __('messages.legalDocument');
        $template = 'backend.legal_document.delete';

        return view('backend.dashboard.layout', compact('template', 'config', 'document'));
    }

    public function destroy($id)
    {
        if ($this->documentService->destroy($id)) {
            return redirect()->route('legal.document.index')->with('success', 'Xóa bản ghi thành công');
        }
        return redirect()->route('legal.document.index')->with('error', 'Xóa bản ghi không thành công. Hãy thử lại');
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
            'model' => 'LegalDocument',
        ];
    }
}
