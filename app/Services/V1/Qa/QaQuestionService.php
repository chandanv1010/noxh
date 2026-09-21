<?php

namespace App\Services\V1\Qa;

use App\Models\QaAnswer;
use App\Models\QaQuestion;
use App\Repositories\Noxh\QaQuestionRepository;
use App\Services\V1\BaseService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Namespace phai la App\Services\V1\Qa de cong tac bat/tat hien thi tim thay
 * lop nay: no ghep duong dan tu ten model "QaQuestion" -> tu dau "Qa".
 */
class QaQuestionService extends BaseService
{
    protected $questionRepository;

    public function __construct(
        QaQuestionRepository $questionRepository
    ) {
        $this->questionRepository = $questionRepository;
    }

    public function paginate($request)
    {
        $perPage = $request->integer('perpage') > 0 ? $request->integer('perpage') : 20;

        $keyword = trim((string) $request->input('keyword'));
        $trangThai = $request->input('status');

        if (!array_key_exists((string) $trangThai, QaQuestion::TRANG_THAI)) {
            $trangThai = null;
        }

        return $this->questionRepository->phanTrang($keyword !== '' ? $keyword : null, $trangThai, $perPage);
    }

    /**
     * Luu cau hoi va cau tra loi trong cung mot lan bam.
     *
     * Moi cau hoi chi co mot cau tra loi chinh thuc nen ghi de vao ban ghi cu
     * thay vi tao them dong moi - neu khong moi lan sua lai sinh mot ban sao.
     */
    public function update($id, $request)
    {
        DB::beginTransaction();
        try {
            $question = QaQuestion::findOrFail($id);

            $question->fill([
                'title' => $request->input('title'),
                'content' => $request->input('content'),
                'status' => $request->input('status'),
                'is_featured' => $request->boolean('is_featured') ? 1 : 0,
                'publish' => $request->integer('publish') ?: 1,
            ])->save();

            $noiDung = trim((string) $request->input('answer_content'));

            if ($noiDung !== '') {
                $traLoi = QaAnswer::where('qa_question_id', $question->id)->orderBy('id')->first();

                $duLieu = [
                    'qa_question_id' => $question->id,
                    'content' => $noiDung,
                    'expert_id' => $request->integer('expert_id') ?: null,
                    'user_id' => Auth::id(),
                    'is_official' => 1,
                    'publish' => 2,
                ];

                $traLoi ? $traLoi->fill($duLieu)->save() : QaAnswer::create($duLieu);
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Cap nhat cau hoi hoi dap that bai: ' . $e->getMessage());
            return false;
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $this->questionRepository->delete($id);
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Xoa cau hoi hoi dap that bai: ' . $e->getMessage());
            return false;
        }
    }

    public function demTheoTrangThai(): array
    {
        return $this->questionRepository->demTheoTrangThai();
    }
}
