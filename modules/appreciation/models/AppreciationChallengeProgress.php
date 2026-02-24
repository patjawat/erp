<?php

namespace app\modules\appreciation\models;

use app\modules\hr\models\Employees;
use Yii;

/**
 * @property int $id
 * @property int $challenge_id
 * @property int $emp_id
 * @property int $current_value
 * @property string|null $completed_at
 * @property string $created_at
 * @property string|null $updated_at
 */
class AppreciationChallengeProgress extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%appreciation_challenge_progress}}';
    }

    public function rules()
    {
        return [
            [['challenge_id', 'emp_id'], 'required'],
            [['challenge_id', 'emp_id', 'current_value'], 'integer'],
            [['completed_at', 'created_at', 'updated_at'], 'safe'],
            [['challenge_id', 'emp_id'], 'unique', 'targetAttribute' => ['challenge_id', 'emp_id']],
            [['challenge_id'], 'exist', 'targetClass' => AppreciationChallenge::class, 'targetAttribute' => 'id'],
            [['emp_id'], 'exist', 'targetClass' => Employees::class, 'targetAttribute' => 'id'],
        ];
    }

    public function getChallenge()
    {
        return $this->hasOne(AppreciationChallenge::class, ['id' => 'challenge_id']);
    }

    public function getEmp()
    {
        return $this->hasOne(Employees::class, ['id' => 'emp_id']);
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert && empty($this->created_at)) {
                $this->created_at = date('Y-m-d H:i:s');
            }
            return true;
        }
        return false;
    }

    public function isCompleted()
    {
        return $this->completed_at !== null;
    }
}
