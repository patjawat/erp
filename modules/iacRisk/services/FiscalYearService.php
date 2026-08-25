<?php

namespace app\modules\iacRisk\services;

use app\modules\iacRisk\models\FiscalYear;
use app\modules\iacRisk\models\ReportingPeriod;
use Yii;

class FiscalYearService
{
    public function createWithPeriods(FiscalYear $model): FiscalYear
    {
        $model->applyDefaultDates();
        $tx = Yii::$app->db->beginTransaction();
        try {
            if (!$model->save()) throw new \DomainException(implode(' ', $model->getFirstErrors()));
            $yearAd = (int) $model->fiscal_year - 543;
            $definitions = [
                [ReportingPeriod::CODE_SIX_MONTH, 'รอบ 6 เดือน', 10, $model->start_date, sprintf('%04d-03-31', $yearAd)],
                [ReportingPeriod::CODE_NINE_MONTH, 'รอบ 9 เดือน', 20, $model->start_date, sprintf('%04d-06-30', $yearAd)],
                [ReportingPeriod::CODE_YEAR_END, 'สิ้นปีงบประมาณ', 30, $model->start_date, $model->end_date],
            ];
            foreach ($definitions as [$code, $name, $sequence, $start, $end]) {
                $period = new ReportingPeriod([
                    'fiscal_year_id' => $model->id, 'code' => $code, 'name' => $name,
                    'sequence' => $sequence, 'start_date' => $start, 'end_date' => $end,
                    'status' => ReportingPeriod::STATUS_PENDING,
                ]);
                if (!$period->save()) throw new \DomainException(implode(' ', $period->getFirstErrors()));
            }
            (new ActivityService())->log([
                'hospital_id' => $model->hospital_id, 'fiscal_year_id' => $model->id,
                'entity_type' => 'fiscal_year', 'entity_id' => $model->id, 'action' => 'created',
                'to_status' => $model->status, 'message' => 'สร้างปีงบประมาณและรอบรายงานเริ่มต้น',
            ]);
            $tx->commit();
            return $model;
        } catch (\Throwable $e) { $tx->rollBack(); throw $e; }
    }

    public function open(FiscalYear $model): void
    {
        if ($model->status !== FiscalYear::STATUS_DRAFT) throw new \DomainException('เปิดใช้งานได้เฉพาะปีงบประมาณฉบับร่าง');
        $tx = Yii::$app->db->beginTransaction();
        try {
            FiscalYear::updateAll(['is_current' => 0], ['hospital_id' => $model->hospital_id]);
            $from = $model->status;
            $model->status = FiscalYear::STATUS_OPEN;
            $model->is_current = 1;
            $model->opened_at = date('Y-m-d H:i:s');
            $model->opened_by = Yii::$app->user->id;
            $model->save(false);
            (new ActivityService())->log([
                'hospital_id' => $model->hospital_id, 'fiscal_year_id' => $model->id,
                'entity_type' => 'fiscal_year', 'entity_id' => $model->id, 'action' => 'opened',
                'from_status' => $from, 'to_status' => $model->status, 'message' => 'เปิดใช้งานปีงบประมาณ',
            ]);
            $tx->commit();
        } catch (\Throwable $e) { $tx->rollBack(); throw $e; }
    }

    public function close(FiscalYear $model): void
    {
        if ($model->status !== FiscalYear::STATUS_OPEN) throw new \DomainException('ปิดได้เฉพาะปีงบประมาณที่เปิดใช้งาน');
        $tx = Yii::$app->db->beginTransaction();
        try {
            $from = $model->status;
            $model->status = FiscalYear::STATUS_CLOSED;
            $model->is_current = 0;
            $model->closed_at = date('Y-m-d H:i:s');
            $model->closed_by = Yii::$app->user->id;
            $model->save(false);
            ReportingPeriod::updateAll(['status' => ReportingPeriod::STATUS_CLOSED, 'closed_at' => date('Y-m-d H:i:s')], ['fiscal_year_id' => $model->id]);
            (new ActivityService())->log([
                'hospital_id' => $model->hospital_id, 'fiscal_year_id' => $model->id,
                'entity_type' => 'fiscal_year', 'entity_id' => $model->id, 'action' => 'closed',
                'from_status' => $from, 'to_status' => $model->status, 'message' => 'ปิดปีงบประมาณ',
            ]);
            $tx->commit();
        } catch (\Throwable $e) { $tx->rollBack(); throw $e; }
    }
}
