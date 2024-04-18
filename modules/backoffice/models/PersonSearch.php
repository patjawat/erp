<?php

namespace app\modules\backoffice\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\backoffice\models\Person;

/**
 * PersonSearch represents the model behind the search form of `app\modules\backoffice\models\Person`.
 */
class PersonSearch extends Person
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['ID', 'FINGLE_ID'], 'integer'],
            [['HR_CID', 'HR_PREFIX_ID', 'HR_FNAME', 'HR_LNAME', 'HR_EN_NAME', 'PAY', 'SEX', 'HR_BLOODGROUP_ID', 'HR_MARRY_STATUS_ID', 'HR_BIRTHDAY', 'HR_PHONE', 'HR_EMAIL', 'HR_FACEBOOK', 'HR_LINE', 'HR_HOME_NUMBER', 'HR_VILLAGE_NO', 'HR_ROAD_NAME', 'HR_SOI_NAME', 'PROVINCE_ID', 'AMPHUR_ID', 'TUMBON_ID', 'HR_VILLAGE_NAME', 'HR_ZIPCODE', 'HR_RELIGION_ID', 'HR_NATIONALITY_ID', 'HR_CITIZENSHIP_ID', 'HR_DEPARTMENT_ID', 'HR_DEPARTMENT_SUB_ID', 'HR_POSITION_ID', 'HR_FARTHER_NAME', 'HR_FARTHER_CID', 'HR_MATHER_NAME', 'HR_MATHER_CID', 'HR_STATUS_ID', 'HR_LEVEL_ID', 'HR_IMAGE', 'HR_USERNAME', 'HR_PASSWORD', 'DATE_TIME_UPDATE', 'DATE_TIME_CREATE', 'HR_STARTWORK_DATE', 'HR_WORK_REGISTER_DATE', 'HR_WORK_END_DATE', 'HR_PIC', 'HR_POSITION_NUM', 'IP_INSERT', 'IP_UPDATE', 'PCODE', 'PERSON_TYPE', 'PCODE_MAIN', 'USER_TYPE', 'PERMIS_ID', 'VCODE', 'VCODE_DATE', 'VGROUP_ID', 'NICKNAME', 'HR_PERSON_TYPE_ID', 'POSITION_IN_WORK', 'BOOK_BANK_NUMBER', 'BOOK_BANK_NAME', 'BOOK_BANK', 'BOOK_BANK_BRANCH', 'HR_DATE_PUT', 'HR_HOME_NUMBER_1', 'HR_HOME_NUMBER_2', 'HR_ROAD_NAME_1', 'HR_ROAD_NAME_2', 'HR_VILLAGE_NO_1', 'HR_VILLAGE_NO_2', 'HR_VILLAGE_NAME_1', 'HR_VILLAGE_NAME_2', 'PROVINCE_ID_1', 'PROVINCE_ID_2', 'AMPHUR_ID_1', 'AMPHUR_ID_2', 'TUMBON_ID_1', 'TUMBON_ID_2', 'HR_ZIPCODE_1', 'HR_ZIPCODE_2', 'HR_HOME_PHONE_1', 'HR_HOME_PHONE_2', 'SAME_ADDR_1', 'SAME_ADDR_2', 'HR_BANK_ID', 'HR_FINGLE1', 'HR_FINGLE2', 'HR_FINGLE3', 'LICEN', 'BOOK_BANK_OT_NUMBER', 'BOOK_BANK_OT_NAME', 'HR_BANK_OT_ID', 'BOOK_BANK_OT', 'BOOK_BANK_OT_BRANCH', 'MARRY_CID', 'MARRY_NAME', 'HR_DEPARTMENT_SUB_SUB_ID', 'HOS_USE_CODE', 'HR_KIND_ID', 'HR_KIND_TYPE_ID', 'LINE_NAME', 'LINE_TOKEN', 'LINE_TOKEN1', 'LINE_TOKEN2', 'updated_at', 'HR_IMAGE_NAME', 'created_at', 'HR_AGENCY_ID', 'LEAVEDAY_ACTIVE', 'HR_SOI_NAME_1', 'HR_SOI_NAME_2'], 'safe'],
            [['HR_SALARY', 'MONEY_POSITION', 'HR_HIGH', 'HR_WEIGHT'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Person::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'ID' => $this->ID,
            'FINGLE_ID' => $this->FINGLE_ID,
            'HR_BIRTHDAY' => $this->HR_BIRTHDAY,
            'DATE_TIME_UPDATE' => $this->DATE_TIME_UPDATE,
            'DATE_TIME_CREATE' => $this->DATE_TIME_CREATE,
            'HR_STARTWORK_DATE' => $this->HR_STARTWORK_DATE,
            'HR_WORK_REGISTER_DATE' => $this->HR_WORK_REGISTER_DATE,
            'HR_WORK_END_DATE' => $this->HR_WORK_END_DATE,
            'HR_SALARY' => $this->HR_SALARY,
            'MONEY_POSITION' => $this->MONEY_POSITION,
            'HR_HIGH' => $this->HR_HIGH,
            'HR_WEIGHT' => $this->HR_WEIGHT,
            'VCODE_DATE' => $this->VCODE_DATE,
            'HR_DATE_PUT' => $this->HR_DATE_PUT,
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
        ]);

        $query->andFilterWhere(['like', 'HR_CID', $this->HR_CID])
            ->andFilterWhere(['like', 'HR_PREFIX_ID', $this->HR_PREFIX_ID])
            ->andFilterWhere(['like', 'HR_FNAME', $this->HR_FNAME])
            ->andFilterWhere(['like', 'HR_LNAME', $this->HR_LNAME])
            ->andFilterWhere(['like', 'HR_EN_NAME', $this->HR_EN_NAME])
            ->andFilterWhere(['like', 'PAY', $this->PAY])
            ->andFilterWhere(['like', 'SEX', $this->SEX])
            ->andFilterWhere(['like', 'HR_BLOODGROUP_ID', $this->HR_BLOODGROUP_ID])
            ->andFilterWhere(['like', 'HR_MARRY_STATUS_ID', $this->HR_MARRY_STATUS_ID])
            ->andFilterWhere(['like', 'HR_PHONE', $this->HR_PHONE])
            ->andFilterWhere(['like', 'HR_EMAIL', $this->HR_EMAIL])
            ->andFilterWhere(['like', 'HR_FACEBOOK', $this->HR_FACEBOOK])
            ->andFilterWhere(['like', 'HR_LINE', $this->HR_LINE])
            ->andFilterWhere(['like', 'HR_HOME_NUMBER', $this->HR_HOME_NUMBER])
            ->andFilterWhere(['like', 'HR_VILLAGE_NO', $this->HR_VILLAGE_NO])
            ->andFilterWhere(['like', 'HR_ROAD_NAME', $this->HR_ROAD_NAME])
            ->andFilterWhere(['like', 'HR_SOI_NAME', $this->HR_SOI_NAME])
            ->andFilterWhere(['like', 'PROVINCE_ID', $this->PROVINCE_ID])
            ->andFilterWhere(['like', 'AMPHUR_ID', $this->AMPHUR_ID])
            ->andFilterWhere(['like', 'TUMBON_ID', $this->TUMBON_ID])
            ->andFilterWhere(['like', 'HR_VILLAGE_NAME', $this->HR_VILLAGE_NAME])
            ->andFilterWhere(['like', 'HR_ZIPCODE', $this->HR_ZIPCODE])
            ->andFilterWhere(['like', 'HR_RELIGION_ID', $this->HR_RELIGION_ID])
            ->andFilterWhere(['like', 'HR_NATIONALITY_ID', $this->HR_NATIONALITY_ID])
            ->andFilterWhere(['like', 'HR_CITIZENSHIP_ID', $this->HR_CITIZENSHIP_ID])
            ->andFilterWhere(['like', 'HR_DEPARTMENT_ID', $this->HR_DEPARTMENT_ID])
            ->andFilterWhere(['like', 'HR_DEPARTMENT_SUB_ID', $this->HR_DEPARTMENT_SUB_ID])
            ->andFilterWhere(['like', 'HR_POSITION_ID', $this->HR_POSITION_ID])
            ->andFilterWhere(['like', 'HR_FARTHER_NAME', $this->HR_FARTHER_NAME])
            ->andFilterWhere(['like', 'HR_FARTHER_CID', $this->HR_FARTHER_CID])
            ->andFilterWhere(['like', 'HR_MATHER_NAME', $this->HR_MATHER_NAME])
            ->andFilterWhere(['like', 'HR_MATHER_CID', $this->HR_MATHER_CID])
            ->andFilterWhere(['like', 'HR_STATUS_ID', $this->HR_STATUS_ID])
            ->andFilterWhere(['like', 'HR_LEVEL_ID', $this->HR_LEVEL_ID])
            ->andFilterWhere(['like', 'HR_IMAGE', $this->HR_IMAGE])
            ->andFilterWhere(['like', 'HR_USERNAME', $this->HR_USERNAME])
            ->andFilterWhere(['like', 'HR_PASSWORD', $this->HR_PASSWORD])
            ->andFilterWhere(['like', 'HR_PIC', $this->HR_PIC])
            ->andFilterWhere(['like', 'HR_POSITION_NUM', $this->HR_POSITION_NUM])
            ->andFilterWhere(['like', 'IP_INSERT', $this->IP_INSERT])
            ->andFilterWhere(['like', 'IP_UPDATE', $this->IP_UPDATE])
            ->andFilterWhere(['like', 'PCODE', $this->PCODE])
            ->andFilterWhere(['like', 'PERSON_TYPE', $this->PERSON_TYPE])
            ->andFilterWhere(['like', 'PCODE_MAIN', $this->PCODE_MAIN])
            ->andFilterWhere(['like', 'USER_TYPE', $this->USER_TYPE])
            ->andFilterWhere(['like', 'PERMIS_ID', $this->PERMIS_ID])
            ->andFilterWhere(['like', 'VCODE', $this->VCODE])
            ->andFilterWhere(['like', 'VGROUP_ID', $this->VGROUP_ID])
            ->andFilterWhere(['like', 'NICKNAME', $this->NICKNAME])
            ->andFilterWhere(['like', 'HR_PERSON_TYPE_ID', $this->HR_PERSON_TYPE_ID])
            ->andFilterWhere(['like', 'POSITION_IN_WORK', $this->POSITION_IN_WORK])
            ->andFilterWhere(['like', 'BOOK_BANK_NUMBER', $this->BOOK_BANK_NUMBER])
            ->andFilterWhere(['like', 'BOOK_BANK_NAME', $this->BOOK_BANK_NAME])
            ->andFilterWhere(['like', 'BOOK_BANK', $this->BOOK_BANK])
            ->andFilterWhere(['like', 'BOOK_BANK_BRANCH', $this->BOOK_BANK_BRANCH])
            ->andFilterWhere(['like', 'HR_HOME_NUMBER_1', $this->HR_HOME_NUMBER_1])
            ->andFilterWhere(['like', 'HR_HOME_NUMBER_2', $this->HR_HOME_NUMBER_2])
            ->andFilterWhere(['like', 'HR_ROAD_NAME_1', $this->HR_ROAD_NAME_1])
            ->andFilterWhere(['like', 'HR_ROAD_NAME_2', $this->HR_ROAD_NAME_2])
            ->andFilterWhere(['like', 'HR_VILLAGE_NO_1', $this->HR_VILLAGE_NO_1])
            ->andFilterWhere(['like', 'HR_VILLAGE_NO_2', $this->HR_VILLAGE_NO_2])
            ->andFilterWhere(['like', 'HR_VILLAGE_NAME_1', $this->HR_VILLAGE_NAME_1])
            ->andFilterWhere(['like', 'HR_VILLAGE_NAME_2', $this->HR_VILLAGE_NAME_2])
            ->andFilterWhere(['like', 'PROVINCE_ID_1', $this->PROVINCE_ID_1])
            ->andFilterWhere(['like', 'PROVINCE_ID_2', $this->PROVINCE_ID_2])
            ->andFilterWhere(['like', 'AMPHUR_ID_1', $this->AMPHUR_ID_1])
            ->andFilterWhere(['like', 'AMPHUR_ID_2', $this->AMPHUR_ID_2])
            ->andFilterWhere(['like', 'TUMBON_ID_1', $this->TUMBON_ID_1])
            ->andFilterWhere(['like', 'TUMBON_ID_2', $this->TUMBON_ID_2])
            ->andFilterWhere(['like', 'HR_ZIPCODE_1', $this->HR_ZIPCODE_1])
            ->andFilterWhere(['like', 'HR_ZIPCODE_2', $this->HR_ZIPCODE_2])
            ->andFilterWhere(['like', 'HR_HOME_PHONE_1', $this->HR_HOME_PHONE_1])
            ->andFilterWhere(['like', 'HR_HOME_PHONE_2', $this->HR_HOME_PHONE_2])
            ->andFilterWhere(['like', 'SAME_ADDR_1', $this->SAME_ADDR_1])
            ->andFilterWhere(['like', 'SAME_ADDR_2', $this->SAME_ADDR_2])
            ->andFilterWhere(['like', 'HR_BANK_ID', $this->HR_BANK_ID])
            ->andFilterWhere(['like', 'HR_FINGLE1', $this->HR_FINGLE1])
            ->andFilterWhere(['like', 'HR_FINGLE2', $this->HR_FINGLE2])
            ->andFilterWhere(['like', 'HR_FINGLE3', $this->HR_FINGLE3])
            ->andFilterWhere(['like', 'LICEN', $this->LICEN])
            ->andFilterWhere(['like', 'BOOK_BANK_OT_NUMBER', $this->BOOK_BANK_OT_NUMBER])
            ->andFilterWhere(['like', 'BOOK_BANK_OT_NAME', $this->BOOK_BANK_OT_NAME])
            ->andFilterWhere(['like', 'HR_BANK_OT_ID', $this->HR_BANK_OT_ID])
            ->andFilterWhere(['like', 'BOOK_BANK_OT', $this->BOOK_BANK_OT])
            ->andFilterWhere(['like', 'BOOK_BANK_OT_BRANCH', $this->BOOK_BANK_OT_BRANCH])
            ->andFilterWhere(['like', 'MARRY_CID', $this->MARRY_CID])
            ->andFilterWhere(['like', 'MARRY_NAME', $this->MARRY_NAME])
            ->andFilterWhere(['like', 'HR_DEPARTMENT_SUB_SUB_ID', $this->HR_DEPARTMENT_SUB_SUB_ID])
            ->andFilterWhere(['like', 'HOS_USE_CODE', $this->HOS_USE_CODE])
            ->andFilterWhere(['like', 'HR_KIND_ID', $this->HR_KIND_ID])
            ->andFilterWhere(['like', 'HR_KIND_TYPE_ID', $this->HR_KIND_TYPE_ID])
            ->andFilterWhere(['like', 'LINE_NAME', $this->LINE_NAME])
            ->andFilterWhere(['like', 'LINE_TOKEN', $this->LINE_TOKEN])
            ->andFilterWhere(['like', 'LINE_TOKEN1', $this->LINE_TOKEN1])
            ->andFilterWhere(['like', 'LINE_TOKEN2', $this->LINE_TOKEN2])
            ->andFilterWhere(['like', 'HR_IMAGE_NAME', $this->HR_IMAGE_NAME])
            ->andFilterWhere(['like', 'HR_AGENCY_ID', $this->HR_AGENCY_ID])
            ->andFilterWhere(['like', 'LEAVEDAY_ACTIVE', $this->LEAVEDAY_ACTIVE])
            ->andFilterWhere(['like', 'HR_SOI_NAME_1', $this->HR_SOI_NAME_1])
            ->andFilterWhere(['like', 'HR_SOI_NAME_2', $this->HR_SOI_NAME_2]);

        return $dataProvider;
    }
}
