<?php

namespace app\modules\pm\models;

use app\modules\pm\components\KpiStatus;

/**
 * ตัวชี้วัดนอกแผนยุทธศาสตร์ (กลุ่ม 2-5) — ตัวแม่คงที่ข้ามปี
 * ค่าเป้าหมาย/ผลจริงเก็บรายปีที่ KpiIndicatorYear
 */
class KpiIndicator extends StrategyRecord
{
    public static function tableName(): string { return '{{%pm_kpi_indicator}}'; }

    public function rules(): array
    {
        return [
            [['group_id', 'name'], 'required'],
            [['group_id', 'org_unit_id', 'ha_part_id', 'sort_order'], 'integer'],
            ['is_active', 'boolean'],
            [['name', 'definition', 'formula', 'evaluation_method', 'data_source'], 'string'],
            ['unit', 'string', 'max' => 100],
            ['operator', 'in', 'range' => array_keys(self::operatorList())],
            ['aggregation', 'in', 'range' => array_keys(self::aggregationList())],
            ['aggregation', 'default', 'value' => 'avg'],
            ['owner_name', 'string', 'max' => 150],
            ['frequency', 'in', 'range' => array_keys(self::frequencyList())],
            [['group_id'], 'exist', 'targetClass' => KpiGroup::class, 'targetAttribute' => 'id'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'group_id' => 'กลุ่มตัวชี้วัด', 'ha_part_id' => 'ตอน HA (Part)', 'org_unit_id' => 'หน่วยงาน', 'name' => 'ชื่อตัวชี้วัด',
            'unit' => 'หน่วยวัด', 'operator' => 'ทิศทาง/เงื่อนไข', 'aggregation' => 'วิธีสรุปค่ารายปี', 'definition' => 'คำนิยาม',
            'formula' => 'สูตรคำนวณ', 'evaluation_method' => 'วิธีประเมินผล', 'data_source' => 'แหล่งข้อมูล',
            'owner_name' => 'ผู้รับผิดชอบ', 'frequency' => 'ความถี่การบันทึก',
            'sort_order' => 'ลำดับ', 'is_active' => 'ใช้งาน',
        ];
    }

    /** วิธีสรุปค่าจริงรายปีจากรายเดือน */
    public static function aggregationList(): array
    {
        return ['avg' => 'เฉลี่ยรายเดือน (สำหรับ %)', 'sum' => 'ผลรวมรายเดือน (สำหรับจำนวน)', 'latest' => 'เดือนล่าสุด'];
    }

    public function getPart() { return $this->hasOne(KpiHaPart::class, ['id' => 'ha_part_id']); }

    public function richTextAttributes(): array
    {
        return ['definition', 'formula', 'evaluation_method', 'data_source'];
    }

    /** เงื่อนไขเทียบเป้า (เดียวกับ StrategyIndicatorYear) — >= = ยิ่งสูงยิ่งดี, <= = ยิ่งต่ำยิ่งดี */
    public static function operatorList(): array
    {
        return ['>=' => 'ไม่น้อยกว่า (ยิ่งสูงยิ่งดี)', '<=' => 'ไม่เกิน (ยิ่งต่ำยิ่งดี)', '=' => 'เท่ากับ', '>' => 'มากกว่า', '<' => 'น้อยกว่า'];
    }

    public static function frequencyList(): array
    {
        return ['month' => 'รายเดือน', 'quarter' => 'รายไตรมาส', 'year' => 'รายปี'];
    }

    public function getGroup() { return $this->hasOne(KpiGroup::class, ['id' => 'group_id']); }
    public function getYears() { return $this->hasMany(KpiIndicatorYear::class, ['kpi_indicator_id' => 'id'])->orderBy(['fiscal_year' => SORT_ASC]); }

    public function yearEntry(int $fiscalYear): ?KpiIndicatorYear
    {
        return KpiIndicatorYear::findOne(['kpi_indicator_id' => $this->id, 'fiscal_year' => $fiscalYear]);
    }

    /** สถานะของปีที่ระบุ (pass/gap/nodata) */
    public function statusFor(int $fiscalYear): string
    {
        $entry = $this->yearEntry($fiscalYear);
        if (!$entry) {
            return KpiStatus::NODATA;
        }
        return KpiStatus::evaluate($entry->target_value, $entry->actual_value, $this->operator);
    }
}
