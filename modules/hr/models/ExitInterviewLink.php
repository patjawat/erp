<?php

namespace app\modules\hr\models;

class ExitInterviewLink extends ExitInterviewRecord
{
    public static function tableName() { return '{{%exit_interview_link}}'; }
    public function rules() { return [[['interview_id', 'token_hash', 'expires_at'], 'required'], [['interview_id'], 'integer'], [['expires_at', 'first_opened_at', 'last_opened_at', 'submitted_at'], 'safe'], [['token_hash'], 'string', 'max' => 64], ['status', 'in', 'range' => ['active', 'revoked', 'submitted', 'expired']]]; }
    public function getInterview() { return $this->hasOne(ExitInterview::class, ['id' => 'interview_id']); }
    public function isUsable(): bool { return $this->status === 'active' && strtotime($this->expires_at) >= time(); }
}
