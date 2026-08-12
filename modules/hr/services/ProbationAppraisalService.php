<?php

namespace app\modules\hr\services;

use Yii;
use app\modules\hr\models\ProbationAcknowledgement;
use app\modules\hr\models\ProbationCase;
use app\modules\hr\models\ProbationDecision;
use app\modules\hr\models\ProbationEvaluation;
use app\modules\hr\models\ProbationEvaluationScore;
use app\modules\hr\models\ProbationRound;

class ProbationAppraisalService
{
    public function createCase(ProbationCase $case): bool
    {
        $tx = Yii::$app->db->beginTransaction();
        try {
            if (!$case->save()) {
                $tx->rollBack();
                return false;
            }
            $evaluators = [
                'self' => (int) $case->employee_id,
                'supervisor' => (int) $case->supervisor_employee_id,
            ];
            if ((int) $case->group_head_employee_id !== (int) $case->supervisor_employee_id) {
                $evaluators['group_head'] = (int) $case->group_head_employee_id;
            }
            for ($month = 1; $month <= 3; $month++) {
                $dueDate = $this->addMonthsClamped($case->start_date, $month);
                $round = new ProbationRound([
                    'case_id' => $case->id, 'month_no' => $month, 'due_date' => $dueDate,
                    'status' => $month === 1 ? 'waiting_self' : 'scheduled',
                    'opened_at' => $month === 1 ? date('Y-m-d H:i:s') : null,
                ]);
                if (!$round->save()) throw new \RuntimeException(implode(', ', $round->getFirstErrors()));
                foreach ($evaluators as $role => $employeeId) {
                    $evaluation = new ProbationEvaluation([
                        'round_id' => $round->id, 'evaluator_employee_id' => $employeeId, 'role' => $role,
                        'status' => $month === 1 && $role === 'self' ? 'open' : 'pending',
                    ]);
                    if (!$evaluation->save()) throw new \RuntimeException(implode(', ', $evaluation->getFirstErrors()));
                }
            }
            $tx->commit();
            return true;
        } catch (\Throwable $e) {
            $tx->rollBack();
            if (!$case->hasErrors()) $case->addError('employee_id', $e->getMessage());
            return false;
        }
    }

    private function addMonthsClamped(string $date, int $months): string
    {
        $source = new \DateTimeImmutable($date);
        $target = $source->modify('first day of this month')->modify('+' . $months . ' month');
        $day = min((int) $source->format('d'), (int) $target->format('t'));
        return $target->setDate((int) $target->format('Y'), (int) $target->format('m'), $day)->format('Y-m-d');
    }

    public function submitEvaluation(ProbationEvaluation $evaluation, array $submittedScores, string $comment): void
    {
        if ($evaluation->status !== 'open') throw new \DomainException('แบบประเมินนี้ยังไม่เปิดให้บันทึก');
        $comment = trim($comment);
        if ($comment === '') throw new \DomainException('กรุณาระบุความคิดเห็นหรือข้อเสนอแนะ');
        $evaluation->populateRelation('round', $evaluation->round);
        $items = $evaluation->round->case->template->items;
        if (!$items) throw new \DomainException('Template ไม่มีข้อประเมิน');
        $tx = Yii::$app->db->beginTransaction();
        try {
            ProbationEvaluationScore::deleteAll(['evaluation_id' => $evaluation->id]);
            $total = 0.0; $max = 0.0;
            foreach ($items as $item) {
                $key = (string) $item->id;
                if (!array_key_exists($key, $submittedScores) || $submittedScores[$key] === '') throw new \DomainException('กรุณาให้คะแนนทุกข้อ');
                $score = (float) $submittedScores[$key];
                if ($score < 1 || $score > 5) throw new \DomainException('คะแนนต้องอยู่ระหว่าง 1 ถึง 5');
                if (abs($score - round($score)) > 0.00001) throw new \DomainException('คะแนนต้องเป็นจำนวนเต็ม');
                $row = new ProbationEvaluationScore(['evaluation_id' => $evaluation->id, 'template_item_id' => $item->id, 'score' => $score]);
                if (!$row->save()) throw new \RuntimeException(implode(', ', $row->getFirstErrors()));
                $total += $score; $max += 5;
            }
            $evaluation->total_score = $total;
            $evaluation->max_score = $max;
            $evaluation->percent_score = $max > 0 ? round($total * 100 / $max, 2) : 0;
            $evaluation->comment = $comment;
            $evaluation->status = 'submitted';
            $evaluation->submitted_at = date('Y-m-d H:i:s');
            if (!$evaluation->save()) throw new \RuntimeException(implode(', ', $evaluation->getFirstErrors()));
            $this->advanceRound($evaluation);
            $tx->commit();
        } catch (\Throwable $e) {
            $tx->rollBack();
            throw $e;
        }
    }

    private function advanceRound(ProbationEvaluation $evaluation): void
    {
        $round = $evaluation->round;
        $case = $round->case;
        $roles = ['self', 'supervisor', 'group_head'];
        $index = array_search($evaluation->role, $roles, true);
        $next = null;
        $nextRole = null;
        for ($i = $index + 1; $i < count($roles); $i++) {
            $candidate = ProbationEvaluation::findOne(['round_id' => $round->id, 'role' => $roles[$i]]);
            if ($candidate
                && in_array($evaluation->role, ['supervisor', 'group_head'], true)
                && in_array($candidate->role, ['supervisor', 'group_head'], true)
                && (int) $candidate->evaluator_employee_id === (int) $evaluation->evaluator_employee_id) {
                // Compatibility for cases created before duplicate management roles were removed.
                $candidate->delete();
                continue;
            }
            if ($candidate && $candidate->status === 'pending') {
                $next = $candidate;
                $nextRole = $roles[$i];
                break;
            }
        }
        if ($next) {
            $next->status = 'open';
            $next->save(false);
            $round->status = $nextRole === 'supervisor' ? 'waiting_supervisor' : 'waiting_group_head';
            $round->save(false);
            $case->status = 'in_progress';
            $case->save(false);
            return;
        }
        if ((int) $round->month_no === 3) {
            $round->status = 'waiting_decision';
            $round->save(false);
            $case->status = 'waiting_decision';
            $case->save(false);
            return;
        }
        $round->status = 'waiting_acknowledgement';
        $round->save(false);
        $case->status = 'in_progress';
        $case->save(false);
    }

    public function saveDecision(ProbationCase $case, string $recommendation, string $comment, int $employeeId): ProbationDecision
    {
        if ($case->status !== 'waiting_decision') throw new \DomainException('ยังไม่ถึงขั้นตอนสรุปผล');
        if ((int) $case->final_recommender_employee_id !== $employeeId) throw new \DomainException('คุณไม่ได้รับมอบหมายให้สรุปผล');
        $round = ProbationRound::findOne(['case_id' => $case->id, 'month_no' => 3]);
        $evaluations = ProbationEvaluation::find()->where([
            'round_id' => $round->id,
            'status' => 'submitted',
            'role' => ['supervisor', 'group_head'],
        ])->all();
        $scoresByEvaluator = [];
        foreach ($evaluations as $evaluation) {
            $scoresByEvaluator[(int) $evaluation->evaluator_employee_id] = (float) $evaluation->percent_score;
        }
        $expectedEvaluatorIds = array_values(array_unique(array_map('intval', [
            $case->supervisor_employee_id,
            $case->group_head_employee_id,
        ])));
        if (count($scoresByEvaluator) !== count($expectedEvaluatorIds)) {
            throw new \DomainException('ผลประเมินเดือนที่ 3 ของผู้บังคับบัญชายังไม่ครบ');
        }
        $average = round(array_sum($scoresByEvaluator) / count($scoresByEvaluator), 2);
        $result = $average >= 60 ? 'passed' : 'failed';
        if (!in_array($recommendation, ['hire', 'no_hire'], true)) throw new \DomainException('กรุณาเลือกข้อเสนอ');
        $requiredRecommendation = $result === 'passed' ? 'hire' : 'no_hire';
        if ($recommendation !== $requiredRecommendation) throw new \DomainException('ข้อเสนอการจ้างต้องเป็นไปตามเกณฑ์คะแนนร้อยละ 60');
        if (trim($comment) === '') throw new \DomainException('กรุณาระบุความเห็นสรุป');
        $decision = new ProbationDecision([
            'case_id' => $case->id, 'average_percent' => $average, 'threshold_percent' => 60,
            'result' => $result, 'recommendation' => $recommendation, 'summary_comment' => trim($comment),
            'decided_by_employee_id' => $employeeId, 'decided_at' => date('Y-m-d H:i:s'),
        ]);
        if (!$decision->save()) throw new \RuntimeException(implode(', ', $decision->getFirstErrors()));
        $round->status = 'waiting_acknowledgement'; $round->save(false);
        $case->status = 'waiting_acknowledgement'; $case->save(false);
        return $decision;
    }

    public function acknowledge(ProbationCase $case, ProbationRound $round, int $employeeId): void
    {
        if ((int) $round->case_id !== (int) $case->id || $round->status !== 'waiting_acknowledgement') throw new \DomainException('รายการนี้ยังไม่พร้อมให้รับทราบ');
        if ((int) $case->director_employee_id !== $employeeId) throw new \DomainException('คุณไม่ได้รับมอบหมายให้รับทราบรายการนี้');
        if ((int) $round->month_no === 3 && !$case->decision) throw new \DomainException('ยังไม่มีผลสรุปการจ้างสำหรับเดือนที่ 3');
        if ($round->acknowledgement) throw new \DomainException('ผอ.รับทราบผลเดือนนี้แล้ว');
        $ack = new ProbationAcknowledgement(['case_id' => $case->id, 'round_id' => $round->id, 'director_employee_id' => $employeeId, 'acknowledged_at' => date('Y-m-d H:i:s')]);
        if (!$ack->save()) throw new \RuntimeException(implode(', ', $ack->getFirstErrors()));
        $round->status = 'completed';
        $round->completed_at = date('Y-m-d H:i:s');
        $round->save(false);
        if ((int) $round->month_no < 3) {
            $nextRound = ProbationRound::findOne(['case_id' => $case->id, 'month_no' => $round->month_no + 1]);
            if ($nextRound->status === 'scheduled') {
                $nextRound->status = 'waiting_self';
                $nextRound->opened_at = date('Y-m-d H:i:s');
                $nextRound->save(false);
                $nextSelf = ProbationEvaluation::findOne(['round_id' => $nextRound->id, 'role' => 'self']);
                $nextSelf->status = 'open';
                $nextSelf->save(false);
            }
            $case->status = 'in_progress';
            $case->save(false);
            return;
        }
        $case->status = $case->decision->recommendation === 'hire' ? 'completed_hire' : 'completed_no_hire';
        $case->completed_at = date('Y-m-d H:i:s');
        $case->save(false);
    }
}
