<?php

namespace app\modules\medsop\models;

use app\modules\hr\models\Organization;
use Yii;
use yii\db\ActiveRecord;

/**
 * สิทธิ์ให้หน่วยงานอื่นเข้าดูเอกสารที่เผยแพร่แล้วของหน่วยงานเจ้าของ
 *
 * เจ้าของเอกสารเป็นผู้เปิดสิทธิ์เอง สิทธิ์นี้จึงกว้างกว่ารายชื่อผู้รับ (audience)
 * ที่ผูกกับเอกสารทีละฉบับ — เปิดครั้งเดียวครอบคลุมเอกสารที่เผยแพร่แล้วทั้งหมดของหน่วยงาน
 */
class OrganizationAccess extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%medsop_organization_access}}';
    }

    public function rules()
    {
        return [
            [['owner_organization_id', 'viewer_organization_id'], 'required'],
            [['owner_organization_id', 'viewer_organization_id', 'created_by'], 'integer'],
            [['note'], 'string', 'max' => 255],
            [['created_at'], 'safe'],
            [['viewer_organization_id'], 'compare', 'compareAttribute' => 'owner_organization_id', 'operator' => '!=',
                'message' => 'หน่วยงานเจ้าของเอกสารเห็นเอกสารตัวเองอยู่แล้ว ไม่ต้องเปิดสิทธิ์ซ้ำ'],
            [['viewer_organization_id'], 'unique', 'targetAttribute' => ['owner_organization_id', 'viewer_organization_id'],
                'message' => 'เปิดสิทธิ์ให้หน่วยงานนี้ไว้แล้ว'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'owner_organization_id' => 'หน่วยงานเจ้าของเอกสาร',
            'viewer_organization_id' => 'หน่วยงานที่ให้เข้าดู',
            'note' => 'หมายเหตุ',
        ];
    }

    public function getOwnerOrganization()
    {
        return $this->hasOne(Organization::class, ['id' => 'owner_organization_id']);
    }

    public function getViewerOrganization()
    {
        return $this->hasOne(Organization::class, ['id' => 'viewer_organization_id']);
    }

    public function beforeSave($insert)
    {
        if ($insert) {
            $this->created_at = $this->created_at ?: date('Y-m-d H:i:s');
            $this->created_by = $this->created_by ?: Yii::$app->user->id;
        }
        return parent::beforeSave($insert);
    }

    /** id หน่วยงานเจ้าของเอกสารที่เปิดสิทธิ์ให้หน่วยงาน $viewerOrganizationId เข้าดู */
    public static function ownerIdsFor(int $viewerOrganizationId): array
    {
        if ($viewerOrganizationId < 1) {
            return [];
        }
        return array_map('intval', static::find()
            ->select('owner_organization_id')
            ->distinct()
            ->where(['viewer_organization_id' => $viewerOrganizationId])
            ->column());
    }
}
