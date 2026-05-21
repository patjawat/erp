php yii migrate --migrationPath=@app/modules/purchaseV2/migrations

จากระบบเดิม
php yii purchase-v2/preview
php yii purchase-v2/migrate --id=123 --force=1
php yii purchase-v2/migrate --fromId=1 --toId=1000 --force=1
php yii purchase-v2/migrate --q=PR --force=1