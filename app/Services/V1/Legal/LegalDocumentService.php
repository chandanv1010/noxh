<?php

namespace App\Services\V1\Legal;

use App\Repositories\Noxh\LegalDocumentRepository;
use App\Services\V1\BaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Lop nay PHAI nam trong App\Services\V1\Legal: cong tac bat/tat hien thi
 * (Ajax\DashboardController::changeStatus) tu ghep duong dan lop tu ten model
 * "LegalDocument" -> tu dau la "Legal". Doi cho khac la cong tac chet lang.
 */
class LegalDocumentService extends BaseService
{
    /** Noi cat file van ban, tinh tu thu muc public. */
    public const THU_MUC = 'uploads/van-ban';

    protected $documentRepository;

    public function __construct(
        LegalDocumentRepository $documentRepository
    ) {
        $this->documentRepository = $documentRepository;
    }

    public function paginate($request)
    {
        $perPage = $request->integer('perpage') > 0 ? $request->integer('perpage') : 20;

        $where = [];
        if ($request->input('doc_type')) {
            $where[] = ['doc_type', '=', $request->input('doc_type')];
        }
        // Tu loc theo tu khoa thay vi dung scope keyword mac dinh - scope do
        // chi do cot `name`, ma bang nay can do title / doc_number / issuer.
        $keyword = trim((string) $request->input('keyword'));
        if ($keyword !== '') {
            $where[] = ['title', 'LIKE', '%' . $keyword . '%'];
        }

        $condition = [
            'keyword' => null,
            'publish' => $request->integer('publish'),
            'where' => $where,
        ];

        return $this->documentRepository->pagination(
            ['*'],
            $condition,
            $perPage,
            ['path' => 'legal-document/index'],
            ['effective_date', 'DESC'],
            [],
            []
        );
    }

    public function create($request)
    {
        DB::beginTransaction();
        try {
            $this->documentRepository->create($this->duLieu($request));
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Them legal-document that bai: ' . $e->getMessage());
            return false;
        }
    }

    public function update($id, $request)
    {
        DB::beginTransaction();
        try {
            $this->documentRepository->update($id, $this->duLieu($request));
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Cap nhat legal-document that bai: ' . $e->getMessage());
            return false;
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $this->documentRepository->delete($id);
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Xoa legal-document that bai: ' . $e->getMessage());
            return false;
        }
    }

    private function duLieu($request): array
    {
        $payload = $request->except(['_token', 'send', 'tep_tai_len']);

        $payload['issued_date'] = empty($payload['issued_date']) ? null : $payload['issued_date'];
        $payload['effective_date'] = empty($payload['effective_date']) ? null : $payload['effective_date'];
        // O tich khong duoc gui len khi bo tich, phai tu dat ve 0.
        $payload['is_featured'] = $request->boolean('is_featured') ? 1 : 0;
        $payload['order'] = $request->integer('order');
        $payload['publish'] = $request->integer('publish') ?: 2;

        $payload = $this->nhanTep($payload, $request);

        return $payload;
    }

    /**
     * Nhan file van ban tai truc tiep tu form.
     *
     * Quan tri co hai duong: go duong dan vao o "File tai ve", hoac chon file
     * tu may. Co file tai len thi no thang - nguoc lai giu nguyen duong dan cu,
     * KHONG ghi de bang chuoi rong (form sua khong gui lai file da co).
     */
    private function nhanTep(array $payload, $request): array
    {
        $tep = $request->file('tep_tai_len');

        if (!$tep || !$tep->isValid()) {
            if (($payload['file'] ?? '') === '') {
                unset($payload['file'], $payload['file_type'], $payload['file_size']);
            }

            return $payload;
        }

        $duoi = strtolower($tep->getClientOriginalExtension());
        // Phai lay dung luong TRUOC khi move(): sau khi chuyen, doi tuong
        // UploadedFile khong con tro toi file nao nua.
        $coLon = $tep->getSize();

        // Ten file giu phan goc cho de nhan ra trong kho, them ma thoi gian de
        // hai van ban trung ten khong de len nhau.
        $ten = Str::slug(pathinfo($tep->getClientOriginalName(), PATHINFO_FILENAME));
        $ten = ($ten ?: 'van-ban') . '-' . now()->format('YmdHis') . '.' . $duoi;

        $tep->move(public_path(self::THU_MUC), $ten);

        $payload['file'] = '/' . self::THU_MUC . '/' . $ten;
        $payload['file_type'] = $duoi;
        $payload['file_size'] = $coLon ?: null;

        return $payload;
    }
}
