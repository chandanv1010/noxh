#!/bin/sh
# Chuan bi container truoc khi Apache chay.
#
# Thu muc vendor/ nam tren o dia CUA CONTAINER (named volume trong
# docker-compose.yml) chu khong doc qua thu muc chia se voi Windows. Doc mot
# file qua thu muc chia se cham hon khoang 90 lan, ma moi lan mo trang Laravel
# doc chung 660 file trong vendor - do la toan bo ly do trang cham.
#
# Doi lai, lan dau dung (hoac sau khi xoa volume) thi vendor rong, nen phai tu
# cai o day. Vai phut mot lan duy nhat.
set -e

if [ ! -f /var/www/html/vendor/autoload.php ]; then
    echo "[entrypoint] Chua co vendor/ - dang chay composer install, lan dau se lau vai phut..."
    cd /var/www/html
    composer install --no-interaction --prefer-dist --optimize-autoloader --no-progress
    echo "[entrypoint] Xong."
fi

# Quyen ghi cho Laravel. Hai thu muc nay nam tren thu muc chia se voi Windows
# nen quyen co the lech sau khi chay lenh tu ngoai host.
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true

exec docker-php-entrypoint "$@"
