<?php

namespace app\modules\sm\models;

use yii\base\Model;
use yii\db\Expression;
use yii\data\ActiveDataProvider;
use app\modules\sm\models\Vendor;

/**
 * VendorSearch represents the model behind the search form of Vendor.
 */
class VendorSearch extends Vendor
{
    /** @var int|string 1 = แสดงเฉพาะรายการที่ข้อมูลไม่ครบถ้วน */
    public $incomplete_only = 0;

    /** @var int|string 1 = แสดงเฉพาะรายการที่รหัสยังไม่กรอกหรือเป็น - */
    public $missing_code_only = 0;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'active', 'incomplete_only', 'missing_code_only'], 'integer'],
            [['ref', 'category_id', 'code', 'emp_id', 'name', 'title', 'description', 'data_json','address','contact_name','tel','email','q'], 'safe'],
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
        $query = Vendor::find();

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
            'id' => $this->id,
            'active' => $this->active,
        ]);

        $query->andFilterWhere(['like', 'ref', $this->ref])
            ->andFilterWhere(['like', 'category_id', $this->category_id])
            ->andFilterWhere(['like', 'code', $this->code])
            ->andFilterWhere(['like', 'emp_id', $this->emp_id])
            ->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'data_json', $this->data_json]);

        if ((int) $this->incomplete_only === 1) {
            $emptyJson = function ($path) {
                return new Expression("(NULLIF(TRIM(JSON_UNQUOTE(JSON_EXTRACT(data_json, '$path'))), '') IS NULL)");
            };
            $query->andWhere([
                'or',
                ['or', ['code' => null], ['code' => ''], ['code' => '-']],
                ['or', ['title' => null], ['title' => '']],
                $emptyJson('$.tax_id'),
                $emptyJson('$.contact_name'),
                $emptyJson('$.phone'),
                $emptyJson('$.email'),
            ]);
        }

        if ((int) $this->missing_code_only === 1) {
            $query->andWhere([
                'or',
                ['code' => null],
                ['code' => ''],
                ['code' => '-'],
            ]);
        }

        $query->orderBy(['id' => SORT_DESC]);

        return $dataProvider;
    }
}
