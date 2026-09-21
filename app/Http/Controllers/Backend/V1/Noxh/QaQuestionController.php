<?php

namespace App\Http\Controllers\Backend\V1\Noxh;

use App\Http\Controllers\Controller;
use App\Models\Expert;
use App\Models\QaQuestion;
use App\Repositories\Noxh\QaQuestionRepository;
use App\Services\V1\Qa\QaQuestionService;
use Illuminate\Http\Request;

/**
 * Hoi dap: cau hoi do ban doc gui len, chuyen gia tra loi trong admin.
 *
 * Khong co man hinh "them moi" - cau hoi chi sinh ra tu form ngoai website.
 */
class QaQuestionController extends Controller
{
    protected $questionService;
    protected $questionRepository;

    public function __construct(
        QaQuestionService $questionService,
        QaQuestionRepository $questionRepository
    ) {
        $this->questionService = $questionService;
        $this->questionRepository = $questionRepository;
    }

    public function index(Request $request)
    {
        $this->authorize('modules', 'qa.question.index');

        $questions = $this->questionService->paginate($request);
        $config = $this->config();
        $config['seo'] = __('messages.qaQuestion');
        $template = 'backend.qa_question.index';

        return view('backend.dashboard.layout', compact('template', 'config', 'questions') + [
            'trangThai' => QaQuestion::TRANG_THAI,
            'demTheoTrangThai' => $this->questionService->demTheoTrangThai(),
        ]);
    }

    public function edit($id)
    {
        $this->authorize('modules', 'qa.question.update');

        $question = $this->questionRepository->findById($id, ['*'], ['answers']);
        $config = $this->config();
        $config['seo'] = __('messages.qaQuestion');
        $config['method'] = 'edit';
        $template = 'backend.qa_question.store';

        return view('backend.dashboard.layout', compact('template', 'config', 'question') + [
            'trangThai' => QaQuestion::TRANG_THAI,
            'chuyenGia' => Expert::where('publish', 2)->orderBy('order')->get(),
        ]);
    }

    public function update($id, Request $request)
    {
        $request->validate(
            [
                'title' => 'required|string|max:500',
                'status' => 'required|in:' . implode(',', array_keys(QaQuestion::TRANG_THAI)),
                'answer_content' => 'nullable|string|max:20000',
            ],
            [
                'title.required' => 'Bạn chưa nhập nội dung câu hỏi.',
                'status.required' => 'Bạn chưa chọn trạng thái.',
                'status.in' => 'Trạng thái không hợp lệ.',
            ]
        );

        if ($this->questionService->update($id, $request)) {
            return redirect()->route('qa.question.index')->with('success', 'Cập nhật bản ghi thành công');
        }
        return redirect()->route('qa.question.index')->with('error', 'Cập nhật bản ghi không thành công. Hãy thử lại');
    }

    public function delete($id)
    {
        $this->authorize('modules', 'qa.question.destroy');

        $question = $this->questionRepository->findById($id);
        $config = $this->config();
        $config['seo'] = __('messages.qaQuestion');
        $template = 'backend.qa_question.delete';

        return view('backend.dashboard.layout', compact('template', 'config', 'question'));
    }

    public function destroy($id)
    {
        if ($this->questionService->destroy($id)) {
            return redirect()->route('qa.question.index')->with('success', 'Xóa bản ghi thành công');
        }
        return redirect()->route('qa.question.index')->with('error', 'Xóa bản ghi không thành công. Hãy thử lại');
    }

    private function config()
    {
        return [
            'css' => ['backend/css/plugins/switchery/switchery.css'],
            'js' => [
                'backend/js/plugins/switchery/switchery.js',
                'backend/library/finder.js',
                'backend/plugins/ckeditor/ckeditor.js',
            ],
            'model' => 'QaQuestion',
        ];
    }
}
