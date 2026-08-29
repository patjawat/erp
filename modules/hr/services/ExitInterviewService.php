<?php

namespace app\modules\hr\services;

use Yii;
use yii\db\Query;
use yii\web\BadRequestHttpException;
use app\modules\hr\models\Employees;
use app\modules\hr\models\ExitInterview;
use app\modules\hr\models\ExitInterviewAnswer;
use app\modules\hr\models\ExitInterviewLink;
use app\modules\hr\models\ExitInterviewQuestion;
use app\modules\hr\models\ExitInterviewQuestionOption;
use app\modules\hr\models\ExitInterviewTemplateVersion;

class ExitInterviewService
{
    public const MINIMUM_ANALYTICS_GROUP = 5;

    public function createInterview(Employees $employee, array $attributes = []): ExitInterview
    {
        $version = ExitInterviewTemplateVersion::published();
        if (!$version) {
            throw new BadRequestHttpException('ยังไม่มีแบบสัมภาษณ์ที่เผยแพร่');
        }
        $employee->populateRelation('empDepartment', $employee->empDepartment);
        $model = new ExitInterview([
            'emp_id' => $employee->id,
            'version_id' => $version->id,
            'status' => 'pending',
            'response_source' => 'hr_interview',
            'employee_name_snapshot' => trim($employee->prefix . $employee->fname . ' ' . $employee->lname),
            'department_id_snapshot' => $employee->department ?: null,
            'department_name_snapshot' => $employee->empDepartment?->name ?? $employee->empDepartment?->title ?? null,
            'position_name_snapshot' => $employee->employeePosition?->name ?? $employee->employeePosition?->title ?? $employee->position_name ?? null,
            'employee_type_snapshot' => $employee->employeeType?->title ?? $employee->employeeType?->name ?? null,
            'join_date_snapshot' => $employee->join_date ?: null,
            'exit_date' => $employee->end_date ?: null,
        ]);
        $model->setAttributes($attributes);
        if (!$model->save()) {
            throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
        }
        $this->audit($model->id, 'created', null, null, json_encode($model->attributes, JSON_UNESCAPED_UNICODE), null);
        return $model;
    }

    public function questionsFor(ExitInterview $interview, bool $includeHrOnly = false): array
    {
        $version = ExitInterviewTemplateVersion::find()->where(['id' => $interview->version_id])
            ->with(['sections.questions.options'])->one();
        if (!$version) {
            return [];
        }
        $sections = [];
        foreach ($version->sections as $section) {
            $questions = [];
            foreach ($section->questions as $question) {
                if (!$includeHrOnly && $question->is_hr_only) {
                    continue;
                }
                if ($this->conditionMatches($question->condition(), $interview)) {
                    $questions[] = $question;
                }
            }
            if ($questions) {
                $section->populateRelation('questions', $questions);
                $sections[] = $section;
            }
        }
        return $sections;
    }

    private function conditionMatches(array $condition, ExitInterview $interview): bool
    {
        if (!$condition) {
            return true;
        }
        if (isset($condition['exit_type'])) {
            return in_array($interview->exit_type, (array) $condition['exit_type'], true);
        }
        return true;
    }

    public function answerMap(ExitInterview $interview): array
    {
        $map = [];
        foreach ($interview->answers as $answer) {
            $map[(int) $answer->question_id] = $answer->decodedValue();
        }
        return $map;
    }

    public function saveAnswers(
        ExitInterview $interview,
        array $postedAnswers,
        bool $submit,
        ?string $reason = null,
        bool $includeHrOnly = false,
        bool $recordConsent = false
    ): void
    {
        $sections = $this->questionsFor($interview, $includeHrOnly);
        $questions = [];
        foreach ($sections as $section) {
            foreach ($section->questions as $question) {
                $questions[(int) $question->id] = $question;
            }
        }
        $unknownQuestionIds = array_diff(array_map('intval', array_keys($postedAnswers)), array_keys($questions));
        if ($unknownQuestionIds) {
            throw new BadRequestHttpException('พบคำตอบที่ไม่อยู่ในแบบสัมภาษณ์เวอร์ชันนี้');
        }
        if ($submit) {
            foreach ($questions as $question) {
                $value = $postedAnswers[$question->id] ?? null;
                if ($question->is_required && $this->isEmptyValue($value)) {
                    throw new BadRequestHttpException('กรุณาตอบคำถามที่จำเป็น: ' . $question->prompt);
                }
            }
        }

        $transaction = Yii::$app->db->getTransaction();
        $ownsTransaction = $transaction === null;
        if ($ownsTransaction) $transaction = Yii::$app->db->beginTransaction();
        try {
            foreach ($questions as $id => $question) {
                if (!array_key_exists($id, $postedAnswers)) {
                    continue;
                }
                $value = $postedAnswers[$id];
                $this->validateQuestionValue($question, $value);
                $answer = ExitInterviewAnswer::findOne(['interview_id' => $interview->id, 'question_id' => $id]) ?: new ExitInterviewAnswer([
                    'interview_id' => $interview->id, 'question_id' => $id,
                ]);
                $old = $answer->isNewRecord ? null : json_encode($answer->decodedValue(), JSON_UNESCAPED_UNICODE);
                $answer->question_snapshot = $question->prompt;
                $answer->value_text = null;
                $answer->value_number = null;
                $answer->value_json = null;
                if (is_array($value)) {
                    $answer->value_json = json_encode(array_values($value), JSON_UNESCAPED_UNICODE);
                } elseif ($question->question_type === 'rating' || $question->question_type === 'number') {
                    $answer->value_number = $value === '' ? null : (float) $value;
                } else {
                    $answer->value_text = trim((string) $value);
                }
                if (!$answer->save()) {
                    throw new BadRequestHttpException(implode(' ', $answer->getFirstErrors()));
                }
                $new = json_encode($answer->decodedValue(), JSON_UNESCAPED_UNICODE);
                if ($old !== $new) {
                    $this->audit($interview->id, $submit ? 'answer_submitted' : 'draft_saved', $question->code, $old, $new, $reason);
                }
            }
            $interview->status = $submit ? 'submitted' : 'draft';
            if ($submit) {
                $interview->submitted_at = date('Y-m-d H:i:s');
                if ($recordConsent) {
                    $interview->consent_at = $interview->consent_at ?: date('Y-m-d H:i:s');
                }
            }
            if (!$interview->save(false)) {
                throw new BadRequestHttpException('ไม่สามารถบันทึกสถานะแบบสัมภาษณ์ได้');
            }
            if ($ownsTransaction) $transaction->commit();
        } catch (\Throwable $e) {
            if ($ownsTransaction && $transaction->isActive) $transaction->rollBack();
            throw $e;
        }
    }

    private function validateQuestionValue(ExitInterviewQuestion $question, $value): void
    {
        $config = $question->config();
        if ($question->question_type === 'single_choice' && is_array($value)) {
            throw new BadRequestHttpException('คำถามนี้เลือกได้เพียง 1 ตัวเลือก');
        }
        if (in_array($question->question_type, ['multi_choice', 'ranking'], true) && !is_array($value)) {
            throw new BadRequestHttpException('รูปแบบคำตอบไม่ถูกต้องสำหรับคำถาม: ' . $question->prompt);
        }
        if (in_array($question->question_type, ['single_choice', 'multi_choice', 'ranking'], true)) {
            $submitted = is_array($value) ? $value : [$value];
            $submitted = array_values(array_filter($submitted, static fn($item) => $item !== null && $item !== ''));
            $allowed = array_map(static fn($option) => (string) $option->value, $question->options);
            foreach ($submitted as $item) {
                if (!in_array((string) $item, $allowed, true)) {
                    throw new BadRequestHttpException('พบตัวเลือกที่ไม่ถูกต้องสำหรับคำถาม: ' . $question->prompt);
                }
            }
        }
        if ($question->question_type === 'ranking') {
            $values = array_values(array_filter((array) $value, static fn($v) => $v !== ''));
            $max = (int) ($config['max_selections'] ?? 3);
            if (count($values) > $max || count($values) !== count(array_unique($values))) {
                throw new BadRequestHttpException('กรุณาเลือกเหตุผลไม่เกิน ' . $max . ' ข้อและไม่เลือกซ้ำ');
            }
        }
        if ($question->question_type === 'rating' && $value !== '') {
            if (!is_numeric($value)) {
                throw new BadRequestHttpException('คะแนนต้องเป็นตัวเลข');
            }
            $number = (int) $value;
            if ($number < (int) ($config['min'] ?? 1) || $number > (int) ($config['max'] ?? 5)) {
                throw new BadRequestHttpException('คะแนนอยู่นอกช่วงที่กำหนด');
            }
        }
        if ($question->question_type === 'number' && $value !== '' && !is_numeric($value)) {
            throw new BadRequestHttpException('คำตอบต้องเป็นตัวเลข');
        }
    }

    private function isEmptyValue($value): bool
    {
        if (is_array($value)) {
            return count(array_filter($value, static fn($v) => $v !== null && $v !== '')) === 0;
        }
        return $value === null || trim((string) $value) === '';
    }

    public function issueLink(ExitInterview $interview, int $days = 14): string
    {
        if (!in_array($interview->status, ['pending', 'draft'], true)) {
            throw new BadRequestHttpException('สร้างลิงก์ได้เฉพาะรายการที่รอดำเนินการหรือบันทึกร่าง');
        }
        $days = min(90, max(1, $days));
        ExitInterviewLink::updateAll(['status' => 'revoked', 'updated_at' => date('Y-m-d H:i:s')], [
            'interview_id' => $interview->id, 'status' => 'active',
        ]);
        $token = Yii::$app->security->generateRandomString(48);
        $link = new ExitInterviewLink([
            'interview_id' => $interview->id,
            'token_hash' => hash('sha256', $token),
            'status' => 'active',
            'expires_at' => date('Y-m-d H:i:s', strtotime('+' . $days . ' days')),
        ]);
        if (!$link->save()) {
            throw new BadRequestHttpException(implode(' ', $link->getFirstErrors()));
        }
        if ($interview->response_source !== 'self_service') {
            $interview->response_source = 'self_service';
            $interview->save(false);
        }
        $this->audit($interview->id, 'link_issued', null, null, $link->expires_at, null);
        return $token;
    }

    public function findUsableLink(string $token): ExitInterviewLink
    {
        $link = ExitInterviewLink::find()->where(['token_hash' => hash('sha256', $token)])->with(['interview.version'])->one();
        if (!$link || !$link->isUsable()) {
            throw new BadRequestHttpException('ลิงก์ไม่ถูกต้อง หมดอายุ หรือถูกยกเลิกแล้ว');
        }
        $now = date('Y-m-d H:i:s');
        $link->first_opened_at = $link->first_opened_at ?: $now;
        $link->last_opened_at = $now;
        $link->save(false);
        return $link;
    }

    public function dashboard(array $filters = []): array
    {
        $base = ExitInterview::find()->alias('i')->where(['i.status' => 'submitted']);
        if (!empty($filters['date_from'])) $base->andWhere(['>=', 'i.exit_date', $filters['date_from']]);
        if (!empty($filters['date_to'])) $base->andWhere(['<=', 'i.exit_date', $filters['date_to']]);
        if (!empty($filters['department'])) $base->andWhere(['i.department_id_snapshot' => (int) $filters['department']]);
        if (!empty($filters['exit_type'])) $base->andWhere(['i.exit_type' => $filters['exit_type']]);
        $ids = array_map('intval', (clone $base)->select('i.id')->column());
        $total = count($ids);
        $metrics = ['total' => $total, 'satisfaction_t2b' => null, 'rehire_percent' => null, 'at_risk_categories' => 0];
        $categories = [];
        $reasons = [];
        $departments = [];
        $suppressed = $total > 0 && $total < self::MINIMUM_ANALYTICS_GROUP;
        if (!$ids || $suppressed) return compact('metrics', 'categories', 'reasons', 'departments', 'suppressed');

        $ratingRows = (new Query())->select(['q.analytics_key', 'avg_score' => 'AVG(a.value_number)', 't2b' => 'SUM(CASE WHEN a.value_number >= 4 THEN 1 ELSE 0 END)', 'cnt' => 'COUNT(a.id)'])
            ->from(['a' => ExitInterviewAnswer::tableName()])
            ->innerJoin(['q' => ExitInterviewQuestion::tableName()], 'q.id = a.question_id')
            ->where(['a.interview_id' => $ids])->andWhere(['not', ['q.analytics_key' => null]])
            ->andWhere(['not', ['a.value_number' => null]])->groupBy('q.analytics_key')->all();
        foreach ($ratingRows as $row) {
            $key = $row['analytics_key'];
            if ((int) $row['cnt'] < self::MINIMUM_ANALYTICS_GROUP) continue;
            $percent = (int) $row['cnt'] > 0 ? round(((int) $row['t2b'] * 100) / (int) $row['cnt'], 1) : null;
            if ($key === 'overall_satisfaction') $metrics['satisfaction_t2b'] = $percent;
            else $categories[$key] = ['average' => round((float) $row['avg_score'], 2), 't2b' => $percent, 'count' => (int) $row['cnt']];
        }
        $metrics['at_risk_categories'] = count(array_filter($categories, static fn($v) => $v['t2b'] !== null && $v['t2b'] < 40));

        $rehireQuestion = ExitInterviewQuestion::find()->where(['analytics_key' => 'rehire'])->select('id')->column();
        $rehireAnswers = ExitInterviewAnswer::find()->where(['interview_id' => $ids, 'question_id' => $rehireQuestion, 'value_text' => ['yes', 'maybe']])->count();
        $rehireTotal = ExitInterviewAnswer::find()->where(['interview_id' => $ids, 'question_id' => $rehireQuestion])->andWhere(['not', ['value_text' => null]])->count();
        $metrics['rehire_percent'] = $rehireTotal >= self::MINIMUM_ANALYTICS_GROUP ? round($rehireAnswers * 100 / $rehireTotal, 1) : null;

        $reasonQuestionIds = ExitInterviewQuestion::find()->where(['code' => 'exit_reasons'])->select('id')->column();
        $reasonAnswers = ExitInterviewAnswer::find()->where(['interview_id' => $ids, 'question_id' => $reasonQuestionIds])->all();
        foreach (count($reasonAnswers) >= self::MINIMUM_ANALYTICS_GROUP ? $reasonAnswers : [] as $answer) {
            foreach ((array) $answer->decodedValue() as $rank => $value) {
                $reasons[$value] = ($reasons[$value] ?? 0) + max(1, 3 - (int) $rank);
            }
        }
        arsort($reasons);
        $reasonLabels = ExitInterviewQuestionOption::find()->where(['question_id' => $reasonQuestionIds])->select('label')->indexBy('value')->column();
        $reasons = array_combine(array_map(static fn($key) => $reasonLabels[$key] ?? $key, array_keys($reasons)), array_values($reasons)) ?: [];

        $departmentRows = (clone $base)->select(['department_name_snapshot', 'cnt' => 'COUNT(*)'])->groupBy('department_name_snapshot')->having(['>=', 'COUNT(*)', self::MINIMUM_ANALYTICS_GROUP])->asArray()->all();
        foreach ($departmentRows as $row) $departments[] = ['name' => $row['department_name_snapshot'] ?: 'ไม่ระบุ', 'count' => (int) $row['cnt']];
        return compact('metrics', 'categories', 'reasons', 'departments', 'suppressed');
    }

    public function validateTemplateVersion(ExitInterviewTemplateVersion $version): void
    {
        $version->populateRelation('sections', $version->sections);
        if (!$version->sections) {
            throw new BadRequestHttpException('แบบสัมภาษณ์ต้องมีอย่างน้อย 1 หมวด');
        }
        $questionCount = 0;
        foreach ($version->sections as $section) {
            foreach ($section->questions as $question) {
                $questionCount++;
                if (in_array($question->question_type, ['single_choice', 'multi_choice', 'ranking'], true) && !$question->options) {
                    throw new BadRequestHttpException('คำถามแบบตัวเลือกต้องมีตัวเลือกอย่างน้อย 1 ข้อ: ' . $question->prompt);
                }
            }
        }
        if ($questionCount === 0) {
            throw new BadRequestHttpException('แบบสัมภาษณ์ต้องมีอย่างน้อย 1 คำถาม');
        }
    }

    public function audit(int $interviewId, string $action, ?string $field, ?string $old, ?string $new, ?string $reason): void
    {
        Yii::$app->db->createCommand()->insert('{{%exit_interview_audit_log}}', [
            'interview_id' => $interviewId, 'action' => $action, 'field_name' => $field,
            'old_value' => $old, 'new_value' => $new, 'reason' => $reason,
            'created_at' => date('Y-m-d H:i:s'), 'created_by' => Yii::$app->has('user') && !Yii::$app->user->isGuest ? Yii::$app->user->id : null,
        ])->execute();
    }
}
