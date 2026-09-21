<?php

namespace App\Http\Controllers\Backend\V1\Noxh;

use App\Http\Controllers\Controller;
use App\Http\Requests\Noxh\StoreEligibilityQuestionRequest;
use App\Repositories\Noxh\EligibilityQuestionRepository;
use App\Services\V1\Eligibility\EligibilityQuestionService;
use Illuminate\Http\Request;

/**
 * Man hinh quan tri: eligibility question.
 */
class EligibilityQuestionController extends Controller
{
    protected $questionService;
    protected $questionRepository;

    public function __construct(
        EligibilityQuestionService $questionService,
        EligibilityQuestionRepository $questionRepository
    ) {
        $this->questionService = $questionService;
        $this->questionRepository = $questionRepository;
    }

    public function index(Request $request)
    {
        $this->authorize('modules', 'eligibility.question.index');

        $questions = $this->questionService->paginate($request);
        $config = $this->config();
        $config['seo'] = __('messages.eligibilityQuestion');
        $template = 'backend.eligibility_question.index';

        return view('backend.dashboard.layout', compact('template', 'config', 'questions'));
    }

    public function create()
    {
        $this->authorize('modules', 'eligibility.question.create');

        $config = $this->config();
        $config['seo'] = __('messages.eligibilityQuestion');
        $config['method'] = 'create';
        $template = 'backend.eligibility_question.store';

        return view('backend.dashboard.layout', compact('template', 'config'));
    }

    public function store(StoreEligibilityQuestionRequest $request)
    {
        if ($this->questionService->create($request)) {
            return redirect()->route('eligibility.question.index')->with('success', 'Thêm mới bản ghi thành công');
        }
        return redirect()->route('eligibility.question.index')->with('error', 'Thêm mới bản ghi không thành công. Hãy thử lại');
    }

    public function edit($id)
    {
        $this->authorize('modules', 'eligibility.question.update');

        $question = $this->questionRepository->findById($id);
        $config = $this->config();
        $config['seo'] = __('messages.eligibilityQuestion');
        $config['method'] = 'edit';
        $template = 'backend.eligibility_question.store';

        return view('backend.dashboard.layout', compact('template', 'config', 'question'));
    }

    public function update($id, StoreEligibilityQuestionRequest $request)
    {
        if ($this->questionService->update($id, $request)) {
            return redirect()->route('eligibility.question.index')->with('success', 'Cập nhật bản ghi thành công');
        }
        return redirect()->route('eligibility.question.index')->with('error', 'Cập nhật bản ghi không thành công. Hãy thử lại');
    }

    public function delete($id)
    {
        $this->authorize('modules', 'eligibility.question.destroy');

        $question = $this->questionRepository->findById($id);
        $config = $this->config();
        $config['seo'] = __('messages.eligibilityQuestion');
        $template = 'backend.eligibility_question.delete';

        return view('backend.dashboard.layout', compact('template', 'config', 'question'));
    }

    public function destroy($id)
    {
        if ($this->questionService->destroy($id)) {
            return redirect()->route('eligibility.question.index')->with('success', 'Xóa bản ghi thành công');
        }
        return redirect()->route('eligibility.question.index')->with('error', 'Xóa bản ghi không thành công. Hãy thử lại');
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
            'model' => 'EligibilityQuestion',
        ];
    }
}
