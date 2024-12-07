<?php

namespace app\modules\hr\models;

/**
 * This is the ActiveQuery class for [[Leave]].
 *
 * @see Leave
 */
class LeaveQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

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
