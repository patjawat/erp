<?php

namespace app\modules\laundry\models;

class CollectionRound extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%laundry_collection_round}}';
    }
}
