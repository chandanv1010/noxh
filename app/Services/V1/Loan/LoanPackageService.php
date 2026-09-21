<?php

namespace App\Services\V1\Loan;

use App\Repositories\Noxh\LoanPackageRepository;
use App\Services\V1\BaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Lop nay PHAI nam trong App\Services\V1\Loan: cong tac bat/tat hien thi
 * (Ajax\DashboardController::changeStatus) tu ghep duong dan lop tu ten model
 * "LoanPackage" -> tu dau la "Loan". Doi cho khac la cong tac chet lang.
 */
class LoanPackageService extends BaseService
{
    protected $packageRepository;

    public function __construct(
        LoanPackageRepository $packageRepository
    ) {
        $this->packageRepository = $packageRepository;
    }

    public function paginate($request)
    {
        $perPage = $request->integer('perpage') > 0 ? $request->integer('perpage') : 20;

        $where = [];
        // Tu loc theo tu khoa thay vi dung scope keyword mac dinh - scope do
        // chi do cot `name`, ma bang nay can do bank_name / package_name.
        $keyword = trim((string) $request->input('keyword'));
        if ($keyword !== '') {
            $where[] = ['bank_name', 'LIKE', '%' . $keyword . '%'];
        }

        $condition = [
            'keyword' => null,
            'publish' => $request->integer('publish'),
            'where' => $where,
        ];

        return $this->packageRepository->pagination(
            ['*'],
            $condition,
            $perPage,
            ['path' => 'loan-package/index'],
            ['order', 'ASC'],
            [],
            []
        );
    }

    public function create($request)
    {
        DB::beginTransaction();
        try {
            $this->packageRepository->create($this->duLieu($request));
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Them loan-package that bai: ' . $e->getMessage());
            return false;
        }
    }

    public function update($id, $request)
    {
        DB::beginTransaction();
        try {
            $this->packageRepository->update($id, $this->duLieu($request));
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Cap nhat loan-package that bai: ' . $e->getMessage());
            return false;
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $this->packageRepository->delete($id);
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Xoa loan-package that bai: ' . $e->getMessage());
            return false;
        }
    }

    private function duLieu($request): array
    {
        $payload = $request->except(['_token', 'send']);

        $payload['preferential_rate'] = !isset($payload['preferential_rate']) || $payload['preferential_rate'] === '' ? null : $payload['preferential_rate'];
        $payload['preferential_months'] = !isset($payload['preferential_months']) || $payload['preferential_months'] === '' ? null : $payload['preferential_months'];
        $payload['standard_rate'] = !isset($payload['standard_rate']) || $payload['standard_rate'] === '' ? null : $payload['standard_rate'];
        $payload['max_loan_ratio'] = !isset($payload['max_loan_ratio']) || $payload['max_loan_ratio'] === '' ? null : $payload['max_loan_ratio'];
        $payload['max_term_years'] = !isset($payload['max_term_years']) || $payload['max_term_years'] === '' ? null : $payload['max_term_years'];
        $payload['prepayment_fee'] = !isset($payload['prepayment_fee']) || $payload['prepayment_fee'] === '' ? null : $payload['prepayment_fee'];
        $payload['effective_from'] = empty($payload['effective_from']) ? null : $payload['effective_from'];
        // O tich khong duoc gui len khi bo tich, phai tu dat ve 0.
        $payload['is_featured'] = $request->boolean('is_featured') ? 1 : 0;
        $payload['order'] = $request->integer('order');
        $payload['publish'] = $request->integer('publish') ?: 2;

        return $payload;
    }
}
