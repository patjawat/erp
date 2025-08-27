<?php
use creocoder\nestedsets\NestedSetsQueryBehavior;

class OrganizationQuery extends \yii\db\ActiveQuery
{
    public function behaviors()
    {
        return [
            NestedSetsQueryBehavior::class,
        ];
    }
}
