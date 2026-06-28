<?php

namespace app\modules\inventoryV2\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use app\components\AppHelper;
use app\components\DateFilterHelper;

/**
 * RequisitionSearch — Search model สำหรับหน้า "รายการใบขอเบิกวัสดุ"
 *
 * แยกจาก StockOrderSearch เพื่อไม่กระทบหน้าใบรับเข้า — scope: เฉพาะ
 * order_type=OUT + source_type=REQUEST
 *
 * Virtual fields ที่ไม่อยู่ใน DB schema:
 *   - date_filter, date_start, date_end  ช่วงวันที่ขอเบิก (UI ผ่าน _date_filter)
 *   - q_requester_emp_id                  ผู้ขอเบิก (Select2 ajax ผ่าน input_emp)
     *   - q_approver_emp_id                   ผู้อนุมัติ (Select2 ajax ผ่าน input_emp)
     *   - sub_warehouse_id                    คลังย่อยที่รับของ (ใช้กับลิงก์จาก dashboard)
 */
class RequisitionSearch extends StockOrder
{
    public $date_filter;
    public $date_start;
    public $date_end;
    public $q_requester_emp_id;
    public $q_approver_emp_id;
    /** @var string 'v1' = เฉพาะย้ายมาจาก V1, 'v2' = สร้างใน V2, '' = ทั้งหมด */
    public $source_v1;

    public function rules()
    {
        return [
            [['main_warehouse_id', 'sub_warehouse_id', 'q_requester_emp_id', 'q_approver_emp_id'], 'integer'],
            [['order_no', 'status', 'date_filter', 'date_start', 'date_end', 'source_v1'], 'safe'],
        ];
    }

    public function scenarios()
    {
        // bypass parent scenarios — ใช้ rules() ของเราตรง ๆ
        return Model::scenarios();
    }

    public function formName()
    {
        return 'RequisitionSearch';
    }

    /**
     * @param array $params Yii::$app->request->queryParams
     */
    public function search($params)
    {
        $query = StockOrder::find()->where([
            'order_type' => StockOrder::ORDER_TYPE_OUT,
            'source_type' => 'REQUEST',
        ]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['id' => SORT_DESC]],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        // เลขที่เอกสาร — partial match
        $query->andFilterWhere(['like', 'order_no', $this->order_no]);

        // คลังที่จ่ายของ — exact
        $query->andFilterWhere(['main_warehouse_id' => $this->main_warehouse_id]);

        // คลังย่อยที่รับของ — exact
        $query->andFilterWhere(['sub_warehouse_id' => $this->sub_warehouse_id]);

        // สถานะ — exact
        $query->andFilterWhere(['status' => $this->status]);

        // ช่วงวันที่ขอเบิก
        [$dateStart, $dateEnd] = $this->resolveDateRange();
        if ($dateStart !== null) {
            $query->andFilterWhere(['>=', 'order_date', $dateStart]);
        }
        if ($dateEnd !== null) {
            $query->andFilterWhere(['<=', 'order_date', $dateEnd]);
        }

        // ผู้ขอเบิก / ผู้อนุมัติ — เก็บใน data_json (JSON_EXTRACT บน MySQL 5.7+)
        // ใช้ single-quote เพื่อกัน PHP interpolate `$.` ใน path
        if (!empty($this->q_requester_emp_id)) {
            $query->andWhere(new Expression(
                'JSON_EXTRACT(data_json, \'$.issue_requester.emp_id\') = :rid',
                [':rid' => (int) $this->q_requester_emp_id]
            ));
        }
        if (!empty($this->q_approver_emp_id)) {
            $query->andWhere(new Expression(
                'JSON_EXTRACT(data_json, \'$.issue_approver.emp_id\') = :aid',
                [':aid' => (int) $this->q_approver_emp_id]
            ));
        }

        // แหล่งที่มาของเอกสาร: V1 ย้ายเข้ามา vs สร้างใน V2
        if ($this->source_v1 === 'v1') {
            $query->andWhere(new Expression(
                'JSON_EXTRACT(data_json, \'$.migrated_from_v1.source_id\') IS NOT NULL'
            ));
        } elseif ($this->source_v1 === 'v2') {
            $query->andWhere(new Expression(
                'JSON_EXTRACT(data_json, \'$.migrated_from_v1.source_id\') IS NULL'
            ));
        }

        return $dataProvider;
    }

    /**
     * แปลงช่วงวันที่จาก preset (`date_filter`) + ปรับ พ.ศ. → ค.ศ.
     * เลียน pattern จาก MeetingController::resolveSearchDateRange()
     *
     * @return array{0:?string,1:?string}  [start, end] รูปแบบ Y-m-d (ค.ศ.) หรือ null
     */
    private function resolveDateRange(): array
    {
        $start = trim((string) ($this->date_start ?? ''));
        $end = trim((string) ($this->date_end ?? ''));

        $start = $start !== '' ? AppHelper::convertToGregorian($start) : null;
        $end = $end !== '' ? AppHelper::convertToGregorian($end) : null;

        if (($start === null || $end === null) && trim((string) ($this->date_filter ?? '')) !== '') {
            $range = DateFilterHelper::getRange((string) $this->date_filter);
            if ($range !== null) {
                if ($start === null) {
                    $start = date('Y-m-d', strtotime($range[0]));
                    $this->date_start = AppHelper::convertToThai($start);
                }
                if ($end === null) {
                    $end = date('Y-m-d', strtotime($range[1]));
                    $this->date_end = AppHelper::convertToThai($end);
                }
            }
        }

        return [$start, $end];
    }
}
