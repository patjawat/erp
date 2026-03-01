<?php

namespace app\modules\leave\models;

/**
 * ActiveQuery class for [[Leave]].
 *
 * @see Leave
 */
class LeaveQuery extends \yii\db\ActiveQuery
{
    public function countStatus($status)
    {
        return $this->andWhere(['leave.status' => $status])->count();
    }

    /**
     * {@inheritdoc}
     * @return Leave[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Leave|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
