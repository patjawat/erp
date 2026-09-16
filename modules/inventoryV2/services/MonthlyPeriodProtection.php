<?php
namespace app\modules\inventoryV2\services;

use Yii;
use yii\db\Query;

/** Shared serialization for report writers, restores and reversals (not stock transactions). */
class MonthlyPeriodProtection
{
    private static $depth = 0;
    private static $name;
    private static $fileMutex;

    public static function installed(): bool
    {
        return Yii::$app->db->getTableSchema('stock_monthly_period_lock', true) !== null;
    }

    public static function acquire(): void
    {
        if (self::$depth > 0) { self::$depth++; return; }
        $db = Yii::$app->db;
        self::$name = 'monthly-report-' . substr(hash('sha256', $db->dsn), 0, 36);
        if ($db->driverName === 'mysql') {
            $ok = (int) $db->createCommand('SELECT GET_LOCK(:name, 10)', [':name'=>self::$name])->queryScalar() === 1;
        } else {
            self::$fileMutex = new \yii\mutex\FileMutex();
            $ok = self::$fileMutex->acquire(self::$name, 10);
        }
        if (!$ok) throw new \RuntimeException('มีงานปิดเดือนกำลังทำงาน กรุณาลองใหม่');
        self::$depth = 1;
    }

    public static function release(): void
    {
        if (self::$depth === 0 || --self::$depth > 0) return;
        if (Yii::$app->db->driverName === 'mysql') {
            Yii::$app->db->createCommand('SELECT RELEASE_LOCK(:name)', [':name'=>self::$name])->queryScalar();
        } elseif (self::$fileMutex) self::$fileMutex->release(self::$name);
    }

    public static function run(callable $work)
    {
        self::acquire();
        try { return $work(); } finally { self::release(); }
    }

    public static function assertWritable(array $warehouses, int $year, int $month, string $range = 'exact'): void
    {
        if (!self::installed()) return; // Compatibility before the deployment migration; restore itself fails closed.
        $q = (new Query())->from('stock_monthly_period_lock')->where(['warehouse_id'=>$warehouses]);
        $ord = new \yii\db\Expression('report_year * 12 + report_month');
        $op = ['exact'=>'=', 'through'=>'<=', 'from'=>'>='][$range] ?? '=';
        $lock = ($range === 'any' ? $q : $q->andWhere([$op,$ord,$year*12+$month]))->one();
        if ($lock) throw new \DomainException('งวด ' . $lock['report_month'] . '/' . ($lock['report_year']+543)
            . ' รับรองและล็อกแล้ว ห้ามปิดซ้ำ ซ่อม หรือยกเลิกทับ ใช้การปิดเดือนปกติสำหรับงวดถัดไป');
    }
}
