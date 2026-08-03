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
 * @property string|null $created_at
 * @property int|null $created_by
 * @property string|null $updated_at
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
            [['source_type', 'main_warehouse_id', 'sub_warehouse_id', 'contact_id', 'ref', 'data_json', 'disbursement_date', 'emp_id', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'default', 'value' => null],
            [['status'], 'default', 'value' => 'DRAFT'],
            [['order_type', 'order_date'], 'required'],
            [['order_no'], 'string', 'max' => 100],
            [['order_no'], 'unique'],
            [['order_type', 'status'], 'string'],
            [['order_date', 'data_json', 'created_at', 'updated_at'], 'safe'],
            [['main_warehouse_id', 'sub_warehouse_id', 'contact_id', 'disbursement_date', 'emp_id', 'created_by', 'updated_by'], 'integer'],
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
            'main_warehouse_id' => 'คลังที่จ่ายของ',
            'sub_warehouse_id' => 'คลังที่รับของ',
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
     * ข้อมูล marker ที่ระบุว่าเอกสารนี้ย้ายมาจาก Inventory V1
     * คืน array ที่มี source_id / source_code / source_status / migrated_at / migrated_by / v1_movement_date
     * คืน null ถ้าเอกสารสร้างใน V2 ตรง ๆ
     * @return array|null
     */
    public function getMigratedFromV1()
    {
        $json = $this->data_json;
        if (is_string($json)) {
            $json = json_decode($json, true) ?: [];
        }
        if (!is_array($json) || empty($json['migrated_from_v1']) || !is_array($json['migrated_from_v1'])) {
            return null;
        }
        return $json['migrated_from_v1'];
    }

    public function isMigratedFromV1()
    {
        return $this->getMigratedFromV1() !== null;
    }

    /**
     * เลขที่ส่งสินค้าจากผู้ขาย (delivery note) — เก็บใน data_json['delivery_note_no']
     * @return string
     */
    public function getDeliveryNoteNo()
    {
        $json = $this->data_json;
        if (is_string($json)) {
            $json = json_decode($json, true) ?: [];
        }
        return isset($json['delivery_note_no']) ? (string) $json['delivery_note_no'] : '';
    }

    /**
     * ใบสั่งซื้อ (orders.id) ต้นทางที่ใบรับเข้านี้อ้างอิง (กรณีรับเข้าจากการจัดซื้อผ่าน picker) — เก็บใน data_json['po_order_id']
     * @return int|null
     */
    public function getPoOrderId()
    {
        $json = $this->data_json;
        if (is_string($json)) {
            $json = json_decode($json, true) ?: [];
        }
        return (!empty($json['po_order_id'])) ? (int) $json['po_order_id'] : null;
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
     * ผู้อนุมัติแก้รายการได้เมื่อ status = PENDING และผู้ใช้คือ approver-emp
     * หรือมีสิทธิ์ inventory (เหมือนเงื่อนไข actionApprove)
     * @param int|null $userId user.id ที่ล็อกอินอยู่
     * @param bool $hasInventoryPermission ผลของ Yii::$app->user->can('inventory')
     * @return bool
     */
    public function canEditByApprover($userId, $hasInventoryPermission = false)
    {
        if ($this->status !== self::STATUS_PENDING && $this->status !== self::STATUS_DRAFT) {
            return false;
        }
        if ($hasInventoryPermission) {
            return true;
        }
        $approverEmpId = $this->getIssueSignatureEmpId('approver');
        if (!$approverEmpId || !$userId) {
            return false;
        }
        $approverEmp = \app\modules\hr\models\Employees::findOne($approverEmpId);
        return $approverEmp && (int) $approverEmp->user_id === (int) $userId;
    }

    /**
     * ตรวจสอบว่า user ปัจจุบันแก้ไขใบนี้ได้จริงหรือไม่ (รวม logic ที่ actionUpdate ใช้)
     * DRAFT: ผู้สร้าง หรือ จนท.คลัง — PENDING: ผู้อนุมัติที่ระบุไว้ หรือ จนท.คลัง
     * @param int|null $userId user.id ที่ล็อกอินอยู่
     * @param bool $hasInventoryPermission ผลของ Yii::$app->user->can('inventory')
     * @return bool
     */
    public function canEditByUser($userId, $hasInventoryPermission = false)
    {
        if ($this->status === self::STATUS_DRAFT) {
            return $hasInventoryPermission || ($userId && (int) $this->getCreatorUserId() === (int) $userId);
        }
        if ($this->status === self::STATUS_PENDING) {
            return $this->canEditByApprover($userId, $hasInventoryPermission);
        }
        return false;
    }

    /**
     * ตรวจสอบว่า user ปัจจุบันดึงใบกลับเป็นฉบับร่างได้หรือไม่
     * ดึงกลับได้เฉพาะใบสถานะ PENDING (รอหัวหน้าอนุมัติ) และผู้ใช้คือผู้สร้าง หรือ จนท.คลัง
     * @param int|null $userId user.id ที่ล็อกอินอยู่
     * @param bool $hasInventoryPermission ผลของ Yii::$app->user->can('inventory')
     * @return bool
     */
    public function canRecall($userId, $hasInventoryPermission = false)
    {
        if ($this->status !== self::STATUS_PENDING) {
            return false;
        }
        if ($hasInventoryPermission) {
            return true;
        }
        return $userId && (int) $this->getCreatorUserId() === (int) $userId;
    }

    /**
     * คืนสถานะใบที่ยกเลิกแล้วกลับเป็นฉบับร่าง (CANCELLED → DRAFT) เพื่อเบิกใหม่
     * คืนได้เฉพาะใบสถานะ CANCELLED และผู้ใช้คือผู้สร้าง หรือ จนท.คลัง
     * @param int|null $userId user.id ที่ล็อกอินอยู่
     * @param bool $hasInventoryPermission ผลของ Yii::$app->user->can('inventory')
     * @return bool
     */
    public function canRestore($userId, $hasInventoryPermission = false)
    {
        if ($this->status !== self::STATUS_CANCELLED) {
            return false;
        }
        if ($hasInventoryPermission) {
            return true;
        }
        return $userId && (int) $this->getCreatorUserId() === (int) $userId;
    }

    /**
     * คัดลอกใบที่จ่ายวัสดุแล้วเพื่อสร้างใบเบิกใหม่ (CONFIRMED → ใบใหม่ DRAFT)
     * อำนวยความสะดวกเมื่อต้องการเบิกข้อมูลแบบเดิมซ้ำ ๆ
     * ตรวจเฉพาะสถานะของใบต้นทาง — สิทธิ์สร้างใบใหม่ตรวจที่ระดับ controller (canCreateRequisition)
     * @return bool
     */
    public function canCopy()
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    /**
     * user.id ของ "ผู้สร้าง/ผู้ขอเบิก" ใบนี้
     * ยึด created_by ก่อน — ถ้าว่าง (ใบเก่าที่ยังไม่มี) fallback ไปที่ผู้ขอเบิก
     * (issue_requester.emp_id → Employees.user_id)
     * @return int|null
     */
    public function getCreatorUserId()
    {
        if (!empty($this->created_by)) {
            return (int) $this->created_by;
        }
        $requesterEmpId = $this->getIssueSignatureEmpId('requester');
        if ($requesterEmpId) {
            $emp = \app\modules\hr\models\Employees::findOne($requesterEmpId);
            if ($emp && $emp->user_id) {
                return (int) $emp->user_id;
            }
        }
        return null;
    }

    /**
     * บันทึก snapshot การปรับรายการโดยผู้อนุมัติลงใน data_json
     * เก็บใน data_json['approver_revisions'][] — ไม่เพิ่มคอลัมน์
     *
     * แต่ละ revision: {at, by_user_id, by_emp_id, by_name, before:[{item_code,qty}], after:[{item_code,qty}]}
     *
     * @param array $before [{item_code,qty},...]
     * @param array $after  [{item_code,qty},...]
     * @param int|null $byUserId
     * @param int|null $byEmpId
     * @param string $byName
     */
    public function recordApproverRevision(array $before, array $after, $byUserId = null, $byEmpId = null, $byName = '')
    {
        $json = is_array($this->data_json) ? $this->data_json : (is_string($this->data_json) ? (json_decode($this->data_json, true) ?: []) : []);
        if (!is_array($json)) {
            $json = [];
        }
        if (!isset($json['approver_revisions']) || !is_array($json['approver_revisions'])) {
            $json['approver_revisions'] = [];
        }
        $normalize = function ($rows) {
            $out = [];
            foreach ($rows as $r) {
                $code = trim((string) ($r['item_code'] ?? ''));
                if ($code === '') {
                    continue;
                }
                $out[] = [
                    'item_code' => $code,
                    'qty' => round((float) ($r['qty'] ?? 0), 2),
                ];
            }
            return $out;
        };
        $beforeN = $normalize($before);
        $afterN = $normalize($after);
        if ($beforeN === $afterN) {
            return;
        }
        $json['approver_revisions'][] = [
            'at' => date('Y-m-d H:i:s'),
            'by_user_id' => $byUserId ? (int) $byUserId : null,
            'by_emp_id' => $byEmpId ? (int) $byEmpId : null,
            'by_name' => trim($byName),
            'before' => $beforeN,
            'after' => $afterN,
        ];
        $this->data_json = $json;
    }

    /**
     * อ่านประวัติการปรับรายการโดยผู้อนุมัติ
     * @return array
     */
    public function getApproverRevisions()
    {
        $json = $this->data_json;
        if (is_string($json)) {
            $json = json_decode($json, true) ?: [];
        }
        $rows = isset($json['approver_revisions']) && is_array($json['approver_revisions']) ? $json['approver_revisions'] : [];
        return $rows;
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

    /**
     * ตรวจสอบว่าเอกสารนี้ลบได้หรือไม่
     * @return bool
     */
    public function canDelete()
    {
        return $this->getUndeletableReason() === null;
    }

    /**
     * เหตุผลที่ลบไม่ได้ (คืนค่า null หากลบได้)
     * ลบไม่ได้เมื่อยกเลิกไปแล้ว หรือ (กรณีตัดสต็อกแล้ว) มีการจ่ายวัสดุออกจาก "Lot เดียวกัน" ของคลังเดียวกันไปแล้ว
     *
     * หมายเหตุ: เช็คระดับ pool (item_code + lot_number + main_warehouse_id) ไม่ใช่แค่แถวของใบนี้เอง
     * เพราะหลายรายการ (โดยเฉพาะที่ไม่มีการควบคุม lot จริง ใช้ lot_number = '-') ใช้ lot ร่วมกันหลายใบรับเข้า
     * ระบบตัดสต็อกแบบ FIFO ตาม stock_detail.id (ดู IssueController::actionProcess) จึงอาจตัดจากใบรับเข้าใบอื่นที่อยู่ใน
     * lot เดียวกันก่อน ทำให้ remain_qty ของใบนี้ยังเท่าเดิมทั้งที่ lot ถูกจ่ายออกไปแล้วจริง
     * @return string|null
     */
    public function getUndeletableReason()
    {
        if ($this->status === self::STATUS_CANCELLED) {
            return 'ไม่สามารถลบใบที่ยกเลิกแล้วได้';
        }
        if ($this->status === self::STATUS_CONFIRMED) {
            $lots = [];
            foreach ($this->stockDetails as $detail) {
                $lotKey = $detail->item_code . '|' . ($detail->lot_number ?? '-');
                $lots[$lotKey] = ['item_code' => $detail->item_code, 'lot_number' => $detail->lot_number];
            }
            if (empty($lots)) {
                return null;
            }

            // Query แบบ batch เดียวสำหรับทุก (item_code, lot_number) ของใบนี้ แทนการยิงทีละแถว
            // (ใบที่มีหลายร้อยรายการ เช่น lot_number = '-' ที่ใช้ร่วมกันหลายรายการ จะทำให้ยิง query ซ้ำจำนวนมากถ้า loop ทีละแถว)
            $lotConditions = ['or'];
            foreach ($lots as $lot) {
                $lotConditions[] = ['and',
                    ['stock_detail.item_code' => $lot['item_code']],
                    ['stock_detail.lot_number' => $lot['lot_number']],
                ];
            }

            $rows = StockDetail::find()
                ->joinWith('stockOrder')
                ->where([
                    'stock_order.main_warehouse_id' => $this->main_warehouse_id,
                    'stock_order.status' => self::STATUS_CONFIRMED,
                ])
                ->andWhere(['or',
                    ['stock_order.order_type' => self::ORDER_TYPE_IN],
                    ['and', ['stock_order.order_type' => self::ORDER_TYPE_ADJUST], ['>', 'stock_detail.qty', 0]],
                ])
                ->andWhere($lotConditions)
                ->select([
                    'item_code' => 'stock_detail.item_code',
                    'lot_number' => 'stock_detail.lot_number',
                    'total_qty' => 'SUM(stock_detail.qty)',
                    'total_remain' => 'SUM(stock_detail.remain_qty)',
                ])
                ->groupBy(['stock_detail.item_code', 'stock_detail.lot_number'])
                ->asArray()
                ->all();

            foreach ($rows as $row) {
                $totalQty = (float) ($row['total_qty'] ?? 0);
                $totalRemain = (float) ($row['total_remain'] ?? 0);
                if ($totalQty - $totalRemain > 0.000001) {
                    return 'ไม่สามารถลบได้ เนื่องจากมีการจ่ายวัสดุ Lot เดียวกันนี้ออกไปแล้ว (รหัสพัสดุ: ' . $row['item_code'] . ', Lot: ' . $row['lot_number'] . ')';
                }
            }
        }
        return null;
    }
}
