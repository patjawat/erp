<?php

namespace app\modules\inventoryV2\models;

use app\modules\inventoryV2\models\Warehouse;
use Yii;

/**
 * This is the model class for table "stock_order".
 *
 * @property int $id
 * @property string $order_no เลขที่เอกสาร (RCV, ISS, TRN)
 * @property string $order_type ประเภทธุรกรรม
 * @property string|null $source_type เช่น PO, DONATE, INITIAL, REQUEST, ADJUST
 * @property string $order_date วันที่ทำรายการ
 * @property int|null $main_warehouse_id คลังสินค้าต้นทาง/คลังหลัก/คลังจ่าย
 * @property int|null $sub_warehouse_id คลังสินค้าปลายทาง/คลังรับ (กรณีโอน)
 * @property int|null $contact_id ID ผู้ขาย หรือ ผู้เบิก/แผนก
 * @property string|null $status สถานะเอกสาร
 * @property string|null $ref อ้างอิงเลขที่ใบ PO หรือ PR
 * @property string|null $data_json
 * @property int|null $disbursement_date วันที่จ่าย (Unix timestamp)
 * @property int|null $created_at
 * @property int|null $created_by
 * @property int|null $updated_at
 * @property int|null $updated_by
 *
 * @property StockDetail[] $stockDetails
 */
class StockOrder extends \yii\db\ActiveRecord
{

    /**
     * ENUM field values
     */
    const ORDER_TYPE_IN = 'IN';
    const ORDER_TYPE_OUT = 'OUT';
    const ORDER_TYPE_TRANSFER = 'TRANSFER';
    const ORDER_TYPE_ADJUST = 'ADJUST';
    const STATUS_DRAFT = 'DRAFT';
    const STATUS_PENDING = 'PENDING';
    const STATUS_APPROVED = 'APPROVED';
    const STATUS_CONFIRMED = 'CONFIRMED';
    const STATUS_CANCELLED = 'CANCELLED';

    /** ประเภทการรับเข้า (source_type สำหรับ order_type = IN) */
    const SOURCE_NORMAL = 'NORMAL';
    const SOURCE_PO = 'PO';
    const SOURCE_INITIAL = 'INITIAL';
    const SOURCE_FREE_GIFT = 'FREE_GIFT';
    const SOURCE_DONATE = 'DONATE';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'stock_order';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['source_type', 'main_warehouse_id', 'sub_warehouse_id', 'contact_id', 'ref', 'data_json', 'disbursement_date', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'default', 'value' => null],
            [['status'], 'default', 'value' => 'DRAFT'],
            [['order_type', 'order_date'], 'required'],
            [['order_no'], 'string', 'max' => 100],
            [['order_no'], 'unique'],
            [['order_type', 'status'], 'string'],
            [['order_date', 'data_json'], 'safe'],
            [['main_warehouse_id', 'sub_warehouse_id', 'contact_id', 'disbursement_date', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['source_type'], 'string', 'max' => 50],
            [['ref'], 'string', 'max' => 255],
            ['order_type', 'in', 'range' => array_keys(self::optsOrderType())],
            ['status', 'in', 'range' => array_keys(self::optsStatus())],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'order_no' => 'Order No',
            'order_type' => 'Order Type',
            'source_type' => 'Source Type',
            'order_date' => 'Order Date',
            'main_warehouse_id' => 'Warehouse ID',
            'sub_warehouse_id' => 'To Warehouse ID',
            'contact_id' => 'Contact ID',
            'status' => 'Status',
            'ref' => 'Ref',
            'data_json' => 'Data Json',
            'disbursement_date' => 'วันที่จ่าย',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * Gets query for [[StockDetails]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStockDetails()
    {
        return $this->hasMany(StockDetail::class, ['stock_order_id' => 'id']);
    }
    

    public function getMainWarehouse()
{
    // เปลี่ยน Warehouse::class เป็นชื่อ Model คลังสินค้าของคุณ
    // 'id' คือ PK ของตารางคลังสินค้า, 'main_warehouse_id' คือ FK ในตาราง stock_order
    return $this->hasOne(Warehouse::class, ['id' => 'main_warehouse_id']);
}

public function getSubWarehouse()
{
    // เชื่อม sub_warehouse_id ในตาราง stock_order กับ id ในตาราง warehouse
    return $this->hasOne(Warehouse::class, ['id' => 'sub_warehouse_id']);
}

public function getToWarehouse()
{
    // เชื่อม sub_warehouse_id ในตาราง stock_order กับ id ในตาราง warehouse (คลังปลายทาง)
    return $this->hasOne(Warehouse::class, ['id' => 'sub_warehouse_id']);
}

    /**
     * ดึงชื่อและตำแหน่งจากระบบพนักงาน (Employees) — ใช้ positionName() เป็นตำแหน่ง
     * @param int|null $empId รหัสพนักงาน
     * @return array ['name' => string, 'position' => string]
     */
    public static function getEmployeeNameAndPosition($empId)
    {
        if (empty($empId)) {
            return ['name' => '', 'position' => ''];
        }
        $emp = \app\modules\hr\models\Employees::findOne($empId);
        if (!$emp) {
            return ['name' => '', 'position' => ''];
        }
        $name = $emp->fullname ?? trim(($emp->prefix ?? '') . ' ' . ($emp->fname ?? '') . ' ' . ($emp->lname ?? ''));
        $position = method_exists($emp, 'positionName') ? ($emp->positionName() ?: '-') : '-';
        return ['name' => $name, 'position' => $position];
    }

    /**
     * ชุดข้อมูลผู้ลงนามใบเบิกวัสดุ (เก็บใน data_json)
     * คีย์: issue_requester, issue_disbursing, issue_approver, issue_recipient, issue_authorizer
     * แต่ละตัวมี name, position, date (ถ้ามี), emp_id (ถ้ามี = ดึงตำแหน่งจากระบบพนักงาน)
     * @param string $role หนึ่งใน: requester, disbursing, approver, recipient, authorizer
     * @return array ['name' => string, 'position' => string, 'date' => string]
     */
    public function getIssueSignature($role)
    {
        $key = 'issue_' . $role;
        $json = $this->data_json;
        if (is_string($json)) {
            $json = json_decode($json, true) ?: [];
        }
        $data = $json[$key] ?? [];
        if (!is_array($data)) {
            $data = [];
        }
        $name = $data['name'] ?? '';
        $position = $data['position'] ?? '';
        $date = $data['date'] ?? '';
        $empId = isset($data['emp_id']) ? (int) $data['emp_id'] : null;
        if ($empId > 0) {
            $fromEmp = self::getEmployeeNameAndPosition($empId);
            if ($fromEmp['name'] !== '' || $fromEmp['position'] !== '') {
                $name = $fromEmp['name'] !== '' ? $fromEmp['name'] : $name;
                $position = $fromEmp['position'] !== '' ? $fromEmp['position'] : $position;
            }
        }
        return [
            'name' => $name,
            'position' => $position,
            'date' => $date,
        ];
    }

    /**
     * คืนค่า emp_id ของบทบาทผู้ลงนาม (สำหรับใช้ในฟอร์ม)
     * @param string $role requester, disbursing, approver, recipient, authorizer
     * @return int|null
     */
    public function getIssueSignatureEmpId($role)
    {
        $key = 'issue_' . $role;
        $json = $this->data_json;
        if (is_string($json)) {
            $json = json_decode($json, true) ?: [];
        }
        $data = $json[$key] ?? [];
        $empId = isset($data['emp_id']) ? (int) $data['emp_id'] : null;
        return $empId > 0 ? $empId : null;
    }

    /**
     * บันทึกข้อมูลผู้ลงนามใบเบิก (ผู้เบิก, ผู้จ่ายพัสดุ, ผู้เห็นชอบ, ผู้รับวัสดุ, ผู้สั่งจ่าย)
     * @param array $signatures [ 'requester' => ['name'=>'','position'=>'','date'=>''], ... ]
     */
    public function setIssueSignatures(array $signatures)
    {
        $json = $this->data_json;
        if (is_string($json)) {
            $json = json_decode($json, true) ?: [];
        }
        if (!is_array($json)) {
            $json = [];
        }
        foreach (['requester', 'disbursing', 'approver', 'recipient', 'authorizer'] as $role) {
            $key = 'issue_' . $role;
            if (isset($signatures[$role]) && is_array($signatures[$role])) {
                $empId = isset($signatures[$role]['emp_id']) ? (int) $signatures[$role]['emp_id'] : null;
                $json[$key] = [
                    'name' => trim($signatures[$role]['name'] ?? ''),
                    'position' => trim($signatures[$role]['position'] ?? ''),
                    'date' => trim($signatures[$role]['date'] ?? ''),
                    'emp_id' => $empId ?: null,
                ];
            }
        }
        $this->data_json = $json;
    }

    /**
     * วันที่จ่าย — อ่านจากคอลัมน์ disbursement_date ก่อน แล้ว fallback เป็น data_json / updated_at
     * @return int|null Unix timestamp หรือ null
     */
    public function getDisbursementDate()
    {
        if (isset($this->disbursement_date) && $this->disbursement_date !== '') {
            return (int) $this->disbursement_date;
        }
        $json = $this->data_json;
        if (is_string($json)) {
            $json = json_decode($json, true) ?: [];
        }
        if (isset($json['disbursement_date']) && $json['disbursement_date'] !== '') {
            return (int) $json['disbursement_date'];
        }
        return $this->updated_at !== null ? (int) $this->updated_at : null;
    }

    /**
     * ตั้งค่าวันที่จ่าย — เก็บในคอลัมน์ disbursement_date (และ data_json สำหรับ backward compat)
     * @param int $timestamp Unix timestamp
     */
    public function setDisbursementDate($timestamp)
    {
        $this->disbursement_date = (int) $timestamp;
        $json = $this->data_json;
        if (is_string($json)) {
            $json = json_decode($json, true) ?: [];
        }
        if (!is_array($json)) {
            $json = [];
        }
        $json['disbursement_date'] = (int) $timestamp;
        $this->data_json = $json;
    }

    /**
     * เหตุผล/วัตถุประสงค์การเบิก (เก็บใน data_json['issue_reason'])
     * @return string
     */
    public function getIssueReason()
    {
        $json = $this->data_json;
        if (is_string($json)) {
            $json = json_decode($json, true) ?: [];
        }
        return isset($json['issue_reason']) ? (string) $json['issue_reason'] : '';
    }

    /**
     * @param string $reason
     */
    public function setIssueReason($reason)
    {
        $json = $this->data_json;
        if (is_string($json)) {
            $json = json_decode($json, true) ?: [];
        }
        if (!is_array($json)) {
            $json = [];
        }
        $json['issue_reason'] = trim((string) $reason);
        $this->data_json = $json;
    }

    /**
     * รายการค่าใช้จ่ายและใบเสร็จแนบ (เก็บใน data_json['expenses'])
     * แต่ละรายการ: ['description' => string, 'amount' => number, 'receipt_path' => string|null]
     * @return array
     */
    public function getExpenseItems()
    {
        $json = $this->data_json;
        if (is_string($json)) {
            $json = json_decode($json, true) ?: [];
        }
        $items = isset($json['expenses']) && is_array($json['expenses']) ? $json['expenses'] : [];
        return $items;
    }

    /**
     * @param array $items [ ['description' => string, 'amount' => number, 'receipt_path' => string|null], ... ]
     */
    public function setExpenseItems(array $items)
    {
        $json = is_array($this->data_json) ? $this->data_json : (is_string($this->data_json) ? (json_decode($this->data_json, true) ?: []) : []);
        if (!is_array($json)) {
            $json = [];
        }
        $json['expenses'] = $items;
        $this->data_json = $json;
    }

    /**
     * ผู้สั่งจ่าย = หัวหน้าเจ้าหน้าที่ จากตั้งค่าองค์กร (settings/company)
     * ดึงชื่อและตำแหน่งจากระบบพนักงาน (Employees) ตาม data_json[leader]
     * @return array ['name' => string, 'position' => string, 'emp_id' => int|null]
     */
    public static function getCompanyAuthorizer()
    {
        $cat = \app\models\Categorise::findOne(['name' => 'site']);
        if (!$cat || empty($cat->data_json)) {
            return ['name' => '', 'position' => '', 'emp_id' => null];
        }
        $json = is_array($cat->data_json) ? $cat->data_json : (is_string($cat->data_json) ? (json_decode($cat->data_json, true) ?: []) : []);
        $leaderId = isset($json['leader']) ? (int) $json['leader'] : null;
        if ($leaderId > 0) {
            $info = self::getEmployeeNameAndPosition($leaderId);
            $info['emp_id'] = $leaderId;
            return $info;
        }
        return [
            'name' => trim($json['leader_fullname'] ?? ''),
            'position' => trim($json['leader_position'] ?? ''),
            'emp_id' => null,
        ];
    }

    /**
     * พนักงานผู้ขอเบิก (สำหรับแสดง avatar / ชื่อ-ตำแหน่ง)
     * ดึงจาก issue_requester.emp_id หรือจาก created_by (user_id) ถ้าไม่มี emp_id
     * @return \app\modules\hr\models\Employees|null
     */
    public function getRequesterEmployee()
    {
        $empId = $this->getIssueSignatureEmpId('requester');
        if ($empId > 0) {
            $emp = \app\modules\hr\models\Employees::findOne($empId);
            if ($emp) {
                return $emp;
            }
        }
        if (!empty($this->created_by)) {
            return \app\modules\hr\models\Employees::findOne(['user_id' => $this->created_by]);
        }
        return null;
    }

    /**
     * column order_type ENUM value labels
     * @return string[]
     */
    public static function optsOrderType()
    {
        return [
            self::ORDER_TYPE_IN => 'IN',
            self::ORDER_TYPE_OUT => 'OUT',
            self::ORDER_TYPE_TRANSFER => 'TRANSFER',
            self::ORDER_TYPE_ADJUST => 'ADJUST',
        ];
    }

    /**
     * column status ENUM value labels (English keys)
     * @return string[]
     */
    public static function optsStatus()
    {
        return [
            self::STATUS_DRAFT => 'DRAFT',
            self::STATUS_PENDING => 'PENDING',
            self::STATUS_APPROVED => 'APPROVED',
            self::STATUS_CONFIRMED => 'CONFIRMED',
            self::STATUS_CANCELLED => 'CANCELLED',
        ];
    }

    /**
     * สถานะเอกสาร (ตามกฎ flow): label ภาษาไทยสำหรับ dropdown/แสดงผล
     * @return string[]
     */
    public static function optsStatusLabels()
    {
        return [
            self::STATUS_DRAFT    => 'ฉบับร่าง',
            self::STATUS_PENDING  => 'รอหัวหน้าอนุมัติ',
            self::STATUS_APPROVED => 'อนุมัติแล้ว — รอคลังจ่าย',
            self::STATUS_CONFIRMED => 'จ่ายพัสดุแล้ว',
            self::STATUS_CANCELLED => 'ยกเลิก',
        ];
    }

    /**
     * คอนฟิกแสดง badge สถานะ (class, label, icon) ตามกฎ flow — ใช้ในทุก view ให้ตรงกัน
     * @return array<string, array{class: string, label: string, icon: string}>
     */
    public static function getStatusBadgeConfig()
    {
        return [
            self::STATUS_DRAFT    => ['class' => 'badge bg-warning bg-opacity-10 text-warning border border-warning-subtle rounded-pill fw-medium px-2 py-1', 'label' => 'ฉบับร่าง', 'icon' => 'file-edit'],
            self::STATUS_PENDING   => ['class' => 'badge bg-info bg-opacity-10 text-info border border-info-subtle rounded-pill fw-medium px-2 py-1', 'label' => 'รอหัวหน้าอนุมัติ', 'icon' => 'clock'],
            self::STATUS_APPROVED  => ['class' => 'badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill fw-medium px-2 py-1', 'label' => 'อนุมัติแล้ว — รอคลังจ่าย', 'icon' => 'circle-check'],
            self::STATUS_CONFIRMED => ['class' => 'badge bg-success bg-opacity-10 text-success border border-success-subtle rounded-pill fw-medium px-2 py-1', 'label' => 'จ่ายพัสดุแล้ว', 'icon' => 'package-check'],
            self::STATUS_CANCELLED => ['class' => 'badge bg-danger bg-opacity-10 text-danger border border-danger-subtle rounded-pill fw-medium px-2 py-1', 'label' => 'ยกเลิก', 'icon' => 'x-circle'],
        ];
    }

    /**
     * คืน config แสดง badge สำหรับสถานะเดียว (fallback เป็น secondary ถ้าไม่มีในกฎ)
     * @param string $status
     * @return array{class: string, label: string, icon: string}
     */
    public static function getStatusBadgeConfigFor($status)
    {
        $config = self::getStatusBadgeConfig();
        return $config[$status] ?? [
            'class' => 'badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle rounded-pill fw-medium px-2 py-1',
            'label' => $status ?: '—',
            'icon' => 'circle',
        ];
    }

    /**
     * ประเภทการรับเข้า (ใช้ในหน้าสร้างใบรับเข้า)
     * @return string[]
     */
    public static function optsReceiveSourceType()
    {
        return [
            self::SOURCE_NORMAL => 'รายการปกติ',
            self::SOURCE_PO => 'จัดซื้อ',
            self::SOURCE_INITIAL => 'ยอดยกมา',
            self::SOURCE_FREE_GIFT => 'ของแถม',
            self::SOURCE_DONATE => 'บริจาค',
        ];
    }

    /** คืน label ประเภทการรับเข้า (สำหรับแสดงใน view/รายงาน) */
    public function displayReceiveSourceType()
    {
        $opts = self::optsReceiveSourceType();
        return $opts[$this->source_type] ?? $this->source_type;
    }

    /**
     * @return string
     */
    public function displayOrderType()
    {
        return self::optsOrderType()[$this->order_type];
    }

    /**
     * @return bool
     */
    public function isOrderTypeIn()
    {
        return $this->order_type === self::ORDER_TYPE_IN;
    }

    public function setOrderTypeToIn()
    {
        $this->order_type = self::ORDER_TYPE_IN;
    }

    /**
     * @return bool
     */
    public function isOrderTypeOut()
    {
        return $this->order_type === self::ORDER_TYPE_OUT;
    }

    public function setOrderTypeToOut()
    {
        $this->order_type = self::ORDER_TYPE_OUT;
    }

    /**
     * @return bool
     */
    public function isOrderTypeTransfer()
    {
        return $this->order_type === self::ORDER_TYPE_TRANSFER;
    }

    public function setOrderTypeToTransfer()
    {
        $this->order_type = self::ORDER_TYPE_TRANSFER;
    }

    /**
     * @return string คืนค่า key สถานะ (EN)
     */
    public function displayStatus()
    {
        return self::optsStatus()[$this->status] ?? $this->status;
    }

    /** คืน label สถานะภาษาไทยตามกฎ */
    public function displayStatusLabel()
    {
        $labels = self::optsStatusLabels();
        return $labels[$this->status] ?? $this->status;
    }

    /**
     * @return bool
     */
    public function isStatusDraft()
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function setStatusToDraft()
    {
        $this->status = self::STATUS_DRAFT;
    }

    /**
     * @return bool
     */
    public function isStatusPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * @return bool
     */
    public function isStatusApproved()
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function setStatusToApproved()
    {
        $this->status = self::STATUS_APPROVED;
    }

    /**
     * @return bool
     */
    public function isStatusConfirmed()
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    public function setStatusToConfirmed()
    {
        $this->status = self::STATUS_CONFIRMED;
    }

    /**
     * @return bool
     */
    public function isStatusCancelled()
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function setStatusToCancelled()
    {
        $this->status = self::STATUS_CANCELLED;
    }

    /**
     * ตรวจสอบว่าเอกสารนี้แก้ไขได้หรือไม่
     * แก้ไขได้เฉพาะเมื่อ status = DRAFT หรือ PENDING (ยังไม่ตัดสต็อก)
     * @return bool
     */
    public function canEdit()
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_PENDING]);
    }

    /**
     * ตรวจสอบว่าเอกสารนี้ยกเลิกได้หรือไม่
     * ยกเลิกได้ทุกสถานะยกเว้น CANCELLED
     * @return bool
     */
    public function canCancel()
    {
        return $this->status !== self::STATUS_CANCELLED;
    }

    /**
     * ตรวจสอบว่าเอกสารนี้ตัดสต็อกแล้วหรือยัง
     * ตัดสต็อกแล้วเมื่อ status = CONFIRMED
     * @return bool
     */
    public function isStockDeducted()
    {
        return $this->status === self::STATUS_CONFIRMED;
    }
}
