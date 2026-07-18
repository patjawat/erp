<?php

namespace app\modules\medsop\models;

use app\modules\medsop\services\DocumentAccessService;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class DocumentSearch extends Model
{
    public $q;
    public $document_type;
    public $status;
    public $organization_id;

    public function rules()
    {
        return [
            [['q', 'document_type', 'status'], 'string'],
            [['organization_id'], 'integer'],
        ];
    }

    public function search(array $params, DocumentAccessService $access): ActiveDataProvider
    {
        $query = Document::find()->alias('d')->andWhere(['d.deleted_at' => null]);
        $access->applyVisibleScope($query);

        $provider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 4],
            'sort' => ['defaultOrder' => ['updated_at' => SORT_DESC]],
        ]);

        if (!$this->load($params) || !$this->validate()) {
            return $provider;
        }

        $query->andFilterWhere(['d.document_type' => $this->document_type])
            ->andFilterWhere(['d.status' => $this->status])
            ->andFilterWhere(['d.organization_id' => $this->organization_id]);
        if (trim((string) $this->q) !== '') {
            $query->andWhere(['or',
                ['like', 'd.title', $this->q],
                ['like', 'd.document_no', $this->q],
                ['like', 'd.objective', $this->q],
                ['like', 'd.keywords', $this->q],
                ['like', 'd.category', $this->q],
            ]);
        }
        return $provider;
    }
}
