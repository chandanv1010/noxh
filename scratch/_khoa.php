<?php
/**
 * Doc thong tin dang nhap cho cac script kiem tra trong scratch/.
 *
 * KHONG viet mat khau thang vao ma nguon. Thu muc scratch/ nam trong git va kho
 * ma nguon la cong khai, nen mat khau phai truyen qua bien moi truong:
 *
 *   PowerShell : $env:NOXH_ADMIN_PASS = '...'
 *   bash       : export NOXH_ADMIN_PASS='...'
 *
 * Vi du:
 *   $env:NOXH_ADMIN_PASS='...'; php scratch/kiem-tra-admin.php
 *
 * Thieu bien thi script dung ngay va in ra cach dat, chu khong doan bua.
 */

if (!function_exists('nx_khoa')) {
    function nx_khoa(string $bien, string $moTa, bool $batBuoc = true): string
    {
        $giaTri = getenv($bien);
        if ($giaTri === false || $giaTri === '') {
            if (!$batBuoc) {
                return '';
            }
            fwrite(STDERR, "Thieu bien moi truong $bien ($moTa).\n"
                . "  PowerShell: \$env:$bien = '<gia tri>'\n"
                . "  bash      : export $bien='<gia tri>'\n");
            exit(1);
        }
        return $giaTri;
    }

    /** Mat khau tai khoan quan tri. */
    function nx_khoa_admin(): string
    {
        return nx_khoa('NOXH_ADMIN_PASS', 'mat khau tai khoan quan tri');
    }

    /** Mat khau tai khoan nhan vien kinh doanh. */
    function nx_khoa_sale(): string
    {
        return nx_khoa('NOXH_SALE_PASS', 'mat khau tai khoan nhan vien kinh doanh');
    }

    /** Email tai khoan quan tri; khong bat buoc. */
    function nx_email_admin(): string
    {
        return nx_khoa('NOXH_ADMIN_EMAIL', 'email tai khoan quan tri', false);
    }
}
