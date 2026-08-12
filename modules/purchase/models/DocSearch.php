<?php

namespace app\modules\purchase\models;

use yii\data\ActiveDataProvider;

/**
 * ค้นหาทะเบียนเอกสารที่สร้างแล้ว
 */
class DocSearch extends Doc
{
    public function rules()
    {
        return [
            [['thai_year', 'template_id', 'ref_id'], 'integer'],
            [['q', 'status', 'ref_type'], 'safe'],
        ];
    }

    public function scenarios()
    {
        // ปิด scenario ของ model แม่ ไม่ให้ required ของฟอร์มมาบังคับตอนค้นหา
        return \yii\base\Model::scenarios();
    }

    public function search($params)
    {
        $query = Doc::find()
            ->alias('d')
            ->where(['d.deleted_at' => null])
            ->with('template');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 20],
            'sort' => ['defaultOrder' => ['id' => SORT_DESC]],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'd.thai_year' => $this->thai_year,
            'd.status' => $this->status,
            'd.ref_type' => $this->ref_type,
            'd.template_id' => $this->template_id,
            'd.ref_id' => $this->ref_id,
        ]);

        if (!empty($this->q)) {
            $query->andWhere([
                'or',
                ['like', 'd.title', $this->q],
                ['like', 'd.doc_no', $this->q],
                ['like', 'd.note', $this->q],
            ]);
        }

        return $dataProvider;
    }

    /** จำนวนแยกตามสถานะของปีที่กำลังดู ใช้กับการ์ดสรุปหัวหน้าทะเบียน */
    public function counters(): array
    {
        $base = Doc::find()->where(['deleted_at' => null]);
        if (!empty($this->thai_year)) {
            $base->andWhere(['thai_year' => (int) $this->thai_year]);
        }

        return [
            'total' => (int) (clone $base)->count(),
            'draft' => (int) (clone $base)->andWhere(['status' => Doc::STATUS_DRAFT])->count(),
            'final' => (int) (clone $base)->andWhere(['status' => Doc::STATUS_FINAL])->count(),
            'printed' => (int) (clone $base)->andWhere(['not', ['printed_at' => null]])->count(),
        ];
    }
}
