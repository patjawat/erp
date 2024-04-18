<?php

namespace app\modules\backoffice\models;

use Yii;

/**
 * This is the model class for table "info_org".
 *
 * @property string $ORG_ID
 * @property string|null $ORG_NAME
 * @property string|null $ORG_LEADER
 * @property string|null $ORG_LEADER_POSITION
 * @property int|null $YEAR_NOW_ID ปีงบประมาณปัจจุบัน
 * @property string|null $ORG_AMPHUR
 * @property string|null $ORG_AMPHUR_LEADER
 * @property string|null $TYPE
 * @property string|null $CHECK_HR_ID
 * @property string|null $CHECK_HR_NAME
 * @property int|null $LEAVE_RELAX_BEFORE
 * @property string|null $ORG_ADDRESS
 * @property string|null $ORG_PHONE
 * @property string|null $ORG_EMAIL
 * @property string|null $ORG_FAX
 * @property string|null $ORG_WEBSITE
 * @property string|null $ORG_PCODE
 * @property string|null $updated_at
 * @property int|null $ORG_LEADER_ID
 * @property string|null $PROVINCE
 * @property string|null $DISTRICT
 * @property string|null $PROVINCE_DR_NAME
 * @property string|null $PROVINCE_DR_POSITION
 * @property resource|null $img_logo
 * @property string|null $ORG_POPULAR
 * @property string|null $POSECODE
 * @property string|null $created_at
 * @property string|null $ORG_INITIALS
 */
class InfoOrg extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'info_org';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db2');
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['ORG_ID'], 'required'],
            [['YEAR_NOW_ID', 'LEAVE_RELAX_BEFORE', 'ORG_LEADER_ID'], 'integer'],
            [['TYPE', 'img_logo'], 'string'],
            [['updated_at', 'created_at'], 'safe'],
            [['ORG_ID', 'ORG_PCODE'], 'string', 'max' => 10],
            [['ORG_NAME', 'ORG_LEADER', 'ORG_LEADER_POSITION', 'ORG_AMPHUR', 'ORG_AMPHUR_LEADER', 'CHECK_HR_NAME', 'ORG_ADDRESS', 'ORG_WEBSITE', 'PROVINCE', 'DISTRICT', 'PROVINCE_DR_NAME', 'PROVINCE_DR_POSITION', 'ORG_POPULAR', 'POSECODE', 'ORG_INITIALS'], 'string', 'max' => 255],
            [['CHECK_HR_ID'], 'string', 'max' => 11],
            [['ORG_PHONE'], 'string', 'max' => 15],
            [['ORG_EMAIL', 'ORG_FAX'], 'string', 'max' => 100],
            [['ORG_ID'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'ORG_ID' => 'Org ID',
            'ORG_NAME' => 'Org Name',
            'ORG_LEADER' => 'Org Leader',
            'ORG_LEADER_POSITION' => 'Org Leader Position',
            'YEAR_NOW_ID' => 'ปีงบประมาณปัจจุบัน',
            'ORG_AMPHUR' => 'Org Amphur',
            'ORG_AMPHUR_LEADER' => 'Org Amphur Leader',
            'TYPE' => 'Type',
            'CHECK_HR_ID' => 'Check Hr ID',
            'CHECK_HR_NAME' => 'Check Hr Name',
            'LEAVE_RELAX_BEFORE' => 'Leave Relax Before',
            'ORG_ADDRESS' => 'Org Address',
            'ORG_PHONE' => 'Org Phone',
            'ORG_EMAIL' => 'Org Email',
            'ORG_FAX' => 'Org Fax',
            'ORG_WEBSITE' => 'Org Website',
            'ORG_PCODE' => 'Org Pcode',
            'updated_at' => 'Updated At',
            'ORG_LEADER_ID' => 'Org Leader ID',
            'PROVINCE' => 'Province',
            'DISTRICT' => 'District',
            'PROVINCE_DR_NAME' => 'Province Dr Name',
            'PROVINCE_DR_POSITION' => 'Province Dr Position',
            'img_logo' => 'Img Logo',
            'ORG_POPULAR' => 'Org Popular',
            'POSECODE' => 'Posecode',
            'created_at' => 'Created At',
            'ORG_INITIALS' => 'Org Initials',
        ];
    }
}
