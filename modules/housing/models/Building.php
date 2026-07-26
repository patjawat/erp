<?php

declare(strict_types=1);

namespace app\modules\housing\models;

use app\modules\hr\models\Employees;
use app\modules\housing\services\HousingAccessService;
use app\modules\housing\validators\HousingImageDimensionsValidator;
use yii\db\ActiveQuery;

final class Building extends HousingActiveRecord
{
    public $building_image;

    public const TYPE_HOUSE = 'house';
    public const TYPE_FLAT = 'flat';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    public static function tableName(): string
    {
        return '{{%housing_building}}';
    }

    public function rules(): array
    {
        return [
            [['code', 'name', 'building_type'], 'required'],
            [['responsible_employee_id'], 'required', 'message' => 'กรุณาเลือกผู้รับผิดชอบบ้านพัก'],
            [['description', 'address'], 'string'],
            [['sort_order', 'responsible_employee_id', 'created_by', 'updated_by'], 'integer'],
            [['responsible_employee_id'], 'exist',
                'targetClass' => Employees::class,
                'targetAttribute' => ['responsible_employee_id' => 'id'],
            ],
            [['responsible_employee_id'], 'validateResponsibleEmployee'],
            [['code'], 'string', 'max' => 50],
            [['electric_account_no'], 'string', 'max' => 100],
            [['code'], 'unique'],
            [['name'], 'string', 'max' => 255],
            [['building_type'], 'in', 'range' => array_keys(self::typeOptions())],
            [['status'], 'in', 'range' => array_keys(self::statusOptions())],
            [['status'], 'default', 'value' => self::STATUS_ACTIVE],
            [['sort_order'], 'default', 'value' => 0],
            [['building_image'], 'file',
                'extensions' => ['jpg', 'jpeg', 'png', 'webp'],
                'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
                'maxSize' => 10 * 1024 * 1024,
                'skipOnEmpty' => true,
            ],
            [['building_image'], HousingImageDimensionsValidator::class],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'code' => 'รหัส',
            'name' => 'ชื่อบ้านพัก/แฟลต',
            'building_type' => 'ประเภท',
            'address' => 'ที่ตั้ง',
            'description' => 'รายละเอียด',
            'status' => 'สถานะ',
            'sort_order' => 'ลำดับแสดงผล',
            'building_image' => 'รูปภาพบ้านพัก',
            'responsible_employee_id' => 'ผู้รับผิดชอบดูแล',
            'electric_account_no' => 'หมายเลขผู้ใช้ไฟฟ้า',
        ];
    }

    public function getFloors(): ActiveQuery
    {
        return $this->hasMany(Floor::class, ['building_id' => 'id'])
            ->orderBy(['sort_order' => SORT_ASC, 'floor_no' => SORT_ASC]);
    }

    public function getUnits(): ActiveQuery
    {
        return $this->hasMany(Unit::class, ['building_id' => 'id']);
    }

    public function getResponsibleEmployee(): ActiveQuery
    {
        return $this->hasOne(Employees::class, ['id' => 'responsible_employee_id']);
    }

    public function getMaintenanceRequests(): ActiveQuery
    {
        return $this->hasMany(MaintenanceRequest::class, ['building_id' => 'id'])
            ->orderBy(['reported_at' => SORT_DESC]);
    }

    public function validateResponsibleEmployee(string $attribute): void
    {
        if ($this->$attribute === null || $this->$attribute === '') {
            return;
        }

        $employee = Employees::findOne((int) $this->$attribute);
        if ($employee !== null && !HousingAccessService::canBeResponsible($employee)) {
            $this->addError(
                $attribute,
                'เลือกได้เฉพาะบุคลากรที่ยังปฏิบัติงานและมีสิทธิ์เจ้าหน้าที่บ้านพัก'
            );
        }
    }

    public function hasActiveResponsibleEmployee(): bool
    {
        return HousingAccessService::canBeResponsible($this->responsibleEmployee);
    }

    public function responsibleStatusLabel(): string
    {
        if ($this->responsibleEmployee === null) {
            return 'ยังไม่กำหนดผู้รับผิดชอบ';
        }
        if ((string) $this->responsibleEmployee->status === '1') {
            return 'ไม่มีสิทธิ์เจ้าหน้าที่บ้านพัก';
        }

        return $this->responsibleEmployee->statusName->title ?? 'ไม่ได้ปฏิบัติงาน';
    }

    public static function typeOptions(): array
    {
        return [self::TYPE_HOUSE => 'บ้านพัก', self::TYPE_FLAT => 'แฟลต'];
    }

    public static function statusOptions(): array
    {
        return [self::STATUS_ACTIVE => 'ใช้งาน', self::STATUS_INACTIVE => 'งดใช้งาน'];
    }
}
