<?php

namespace app\modules\serviceProfile\services;

use app\modules\hr\models\Employees;
use app\modules\serviceProfile\models\ServiceProfile;
use app\modules\serviceProfile\models\ServiceProfileApproval;
use app\modules\serviceProfile\models\ServiceProfileQualityReviewer;
use app\modules\serviceProfile\models\ServiceProfileReview;
use app\modules\serviceProfile\models\ServiceProfileSectionComment;
use Yii;

class WorkflowService
{
    public function submit(ServiceProfile $profile): void
    {
        if (!in_array($profile->status, [ServiceProfile::STATUS_DRAFT, ServiceProfile::STATUS_RETURNED], true)) throw new \DomainException('เอกสารไม่อยู่ในสถานะที่ส่งพิจารณาได้');
        $missing = [];
        foreach ($profile->sections as $section) if ($section->is_required && !$section->isComplete()) $missing[] = $section->title;
        if ($missing) throw new \DomainException('กรุณากรอกหัวข้อบังคับให้ครบ: ' . implode(', ', array_slice($missing, 0, 5)));
        if(ServiceProfileSectionComment::find()->where(['service_profile_id'=>$profile->id,'status'=>ServiceProfileSectionComment::STATUS_OPEN])->exists()) throw new \DomainException('ยังมีความคิดเห็นรายหัวข้อที่รอแก้ไข กรุณาดำเนินการให้ครบก่อนส่งพิจารณา');

        $lead = ServiceProfileQualityReviewer::find()->with('employee')->where([
            'owner_type' => $profile->owner_type, 'owner_id' => $profile->owner_id, 'active' => 1, 'is_lead' => 1,
        ])->one();
        if (!$lead || !$lead->employee || !$lead->employee->user_id) throw new \DomainException('ยังไม่ได้กำหนดผู้แทนคุณภาพหลักของหน่วยงาน');
        $director = (new DirectorResolver())->resolve();
        if (!$director) throw new \DomainException('ยังไม่ได้กำหนดผู้อำนวยการ หรือผู้อำนวยการยังไม่มีบัญชีผู้ใช้');
        $head = (new OwnerDirectoryService())->headEmployee($profile->owner_type, (int) $profile->owner_id, (int) $profile->fiscal_year);
        if (!$head || !$head->user_id) throw new \DomainException('ยังไม่ได้กำหนดหัวหน้าหน่วยงานหรือหัวหน้ายังไม่มีบัญชีผู้ใช้');

        $tx = Yii::$app->db->beginTransaction();
        try {
            ServiceProfileApproval::deleteAll(['service_profile_id' => $profile->id]);
            ServiceProfileReview::deleteAll(['service_profile_id' => $profile->id]);
            $this->createStage($profile, ServiceProfileApproval::STAGE_QUALITY, $lead->employee, ServiceProfileApproval::STATUS_PENDING);
            $this->createStage($profile, ServiceProfileApproval::STAGE_DIRECTOR, $director, ServiceProfileApproval::STATUS_WAITING);
            $this->createStage($profile, ServiceProfileApproval::STAGE_HEAD, $head, ServiceProfileApproval::STATUS_WAITING);
            $from = $profile->status;
            $profile->status = ServiceProfile::STATUS_REVIEW_PENDING;
            $profile->submitted_at = date('Y-m-d H:i:s');
            $profile->save(false);
            (new ProfileService())->log($profile, 'submitted', $from, $profile->status, 'ส่งให้ผู้แทนคุณภาพเห็นชอบ');
            $tx->commit();
        } catch (\Throwable $e) { $tx->rollBack(); throw $e; }
        ServiceProfileTelegramService::notify($lead->employee, $profile, 'มี Service Profile รอเห็นชอบ', 'กรุณาตรวจสอบและบันทึกผลการเห็นชอบ');
    }

    public function saveReview(ServiceProfile $profile, Employees $reviewer, string $decision, string $comment): void
    {
        if ($profile->status !== ServiceProfile::STATUS_REVIEW_PENDING) throw new \DomainException('เอกสารไม่ได้อยู่ระหว่างการตรวจคุณภาพ');
        $row = ServiceProfileReview::findOne(['service_profile_id' => $profile->id, 'reviewer_employee_id' => $reviewer->id]) ?: new ServiceProfileReview([
            'service_profile_id' => $profile->id, 'reviewer_employee_id' => $reviewer->id, 'created_at' => date('Y-m-d H:i:s'),
        ]);
        $row->decision = $decision;
        $row->comment = trim($comment) ?: null;
        $row->decided_at = date('Y-m-d H:i:s');
        $row->updated_at = date('Y-m-d H:i:s');
        if (!$row->save()) throw new \RuntimeException(implode(' ', $row->getFirstErrors()));
        (new ProfileService())->log($profile, 'quality_' . $decision, $profile->status, $profile->status, $row->comment);
    }

    public function endorse(ServiceProfile $profile, Employees $reviewer, string $comment = ''): void
    {
        $tx = Yii::$app->db->beginTransaction();
        try {
            $this->saveReview($profile, $reviewer, ServiceProfileReview::DECISION_ENDORSED, $comment);
            $stage = $this->pendingStage($profile, ServiceProfileApproval::STAGE_QUALITY, $reviewer);
            $this->passStage($stage, $comment);
            $this->activateStage($profile, ServiceProfileApproval::STAGE_DIRECTOR);
            $from = $profile->status;
            $profile->status = ServiceProfile::STATUS_APPROVAL_PENDING;
            $profile->save(false);
            (new ProfileService())->log($profile, 'quality_endorsed', $from, $profile->status, 'ผู้แทนคุณภาพหลักเห็นชอบ');
            $tx->commit();
        } catch (\Throwable $e) { $tx->rollBack(); throw $e; }
        $directorStage = ServiceProfileApproval::findOne(['service_profile_id' => $profile->id, 'stage' => ServiceProfileApproval::STAGE_DIRECTOR]);
        if ($directorStage?->employee) ServiceProfileTelegramService::notify($directorStage->employee, $profile, 'มี Service Profile รออนุมัติ', 'ผู้แทนคุณภาพเห็นชอบและส่งต่อให้คุณแล้ว');
    }

    public function approve(ServiceProfile $profile, Employees $director, string $comment = ''): void
    {
        if ($profile->status !== ServiceProfile::STATUS_APPROVAL_PENDING) throw new \DomainException('เอกสารไม่ได้รอผู้อำนวยการอนุมัติ');
        $tx = Yii::$app->db->beginTransaction();
        try {
            // Repair pending documents created before the configured director
            // became the canonical approver. This also makes the current queue
            // actionable without requiring the document to be resubmitted.
            $directorStage = ServiceProfileApproval::findOne([
                'service_profile_id' => $profile->id,
                'stage' => ServiceProfileApproval::STAGE_DIRECTOR,
                'status' => ServiceProfileApproval::STATUS_PENDING,
            ]);
            if ($directorStage
                && (int) $directorStage->employee_id !== (int) $director->id
                && (new DirectorResolver())->isConfiguredDirector($director)) {
                $directorStage->employee_id = $director->id;
                $directorStage->employee_name_snapshot = $director->fullname();
                $directorStage->updated_at = date('Y-m-d H:i:s');
                $directorStage->save(false);
            }
            $this->passStage($this->pendingStage($profile, ServiceProfileApproval::STAGE_DIRECTOR, $director), $comment);
            $this->activateStage($profile, ServiceProfileApproval::STAGE_HEAD);
            $from = $profile->status;
            $profile->status = ServiceProfile::STATUS_ACKNOWLEDGEMENT_PENDING;
            $profile->save(false);
            (new ProfileService())->log($profile, 'director_approved', $from, $profile->status, $comment ?: 'ผู้อำนวยการอนุมัติ');
            $tx->commit();
        } catch (\Throwable $e) { $tx->rollBack(); throw $e; }
        $headStage = ServiceProfileApproval::findOne(['service_profile_id' => $profile->id, 'stage' => ServiceProfileApproval::STAGE_HEAD]);
        if ($headStage?->employee) ServiceProfileTelegramService::notify($headStage->employee, $profile, 'มี Service Profile รอรับทราบ', 'ผู้อำนวยการอนุมัติแล้ว กรุณารับทราบเพื่อประกาศใช้');
    }

    public function acknowledge(ServiceProfile $profile, Employees $head, string $comment = ''): void
    {
        if ($profile->status !== ServiceProfile::STATUS_ACKNOWLEDGEMENT_PENDING) throw new \DomainException('เอกสารไม่ได้รอหัวหน้าหน่วยงานรับทราบ');
        $tx = Yii::$app->db->beginTransaction();
        try {
            $this->passStage($this->pendingStage($profile, ServiceProfileApproval::STAGE_HEAD, $head), $comment);
            $today = date('Y-m-d');
            $current = ServiceProfile::findCurrent($profile->owner_type, (int) $profile->owner_id);
            if ($current && (int) $current->id !== (int) $profile->id) {
                $current->status = ServiceProfile::STATUS_RETIRED;
                $current->effective_to = date('Y-m-d', strtotime($today . ' -1 day'));
                $current->save(false);
                $profile->supersedes_id = $current->id;
            }
            $from = $profile->status;
            $profile->status = ServiceProfile::STATUS_ACTIVE;
            $profile->effective_from = $today;
            $profile->effective_to = null;
            $profile->published_at = date('Y-m-d H:i:s');
            $profile->published_by = Yii::$app->user->id;
            $profile->save(false);
            (new ProfileService())->log($profile, 'published', $from, $profile->status, $comment ?: 'หัวหน้าหน่วยงานรับทราบและประกาศใช้');
            $tx->commit();
        } catch (\Throwable $e) { $tx->rollBack(); throw $e; }
        ServiceProfileTelegramService::notifyMany($this->authorEmployees($profile), $profile, 'ประกาศใช้ Service Profile แล้ว', 'เอกสารผ่านการรับทราบและเป็นฉบับปัจจุบันแล้ว');
    }

    public function returnForCorrection(ServiceProfile $profile, Employees $actor, string $comment): void
    {
        if (trim($comment) === '') throw new \DomainException('กรุณาระบุเหตุผลที่ส่งกลับแก้ไข');
        $stageMap = [
            ServiceProfile::STATUS_REVIEW_PENDING => ServiceProfileApproval::STAGE_QUALITY,
            ServiceProfile::STATUS_APPROVAL_PENDING => ServiceProfileApproval::STAGE_DIRECTOR,
        ];
        $stageName = $stageMap[$profile->status] ?? null;
        if (!$stageName) throw new \DomainException('สถานะนี้ส่งกลับแก้ไขไม่ได้');
        $tx = Yii::$app->db->beginTransaction();
        try {
            if ($stageName === ServiceProfileApproval::STAGE_QUALITY) {
                $assigned = ServiceProfileQualityReviewer::find()->where([
                    'owner_type' => $profile->owner_type, 'owner_id' => $profile->owner_id,
                    'employee_id' => $actor->id, 'active' => 1,
                ])->exists();
                if (!$assigned) throw new \DomainException('คุณไม่ได้รับมอบหมายให้ตรวจหน่วยงานนี้');
                $stage = ServiceProfileApproval::findOne([
                    'service_profile_id' => $profile->id, 'stage' => $stageName,
                    'status' => ServiceProfileApproval::STATUS_PENDING,
                ]);
                if (!$stage) throw new \DomainException('ขั้นตอนนี้ถูกดำเนินการแล้ว');
                $this->saveReview($profile, $actor, ServiceProfileReview::DECISION_RETURNED, $comment);
            } else {
                $stage = $this->pendingStage($profile, $stageName, $actor);
            }
            $stage->status = ServiceProfileApproval::STATUS_RETURNED;
            $stage->comment = $comment;
            $stage->acted_at = date('Y-m-d H:i:s');
            $stage->acted_by_user_id = Yii::$app->user->id;
            $stage->updated_at = date('Y-m-d H:i:s');
            $stage->save(false);
            $from = $profile->status;
            $profile->status = ServiceProfile::STATUS_RETURNED;
            $profile->save(false);
            (new ProfileService())->log($profile, 'returned', $from, $profile->status, $comment);
            $tx->commit();
        } catch (\Throwable $e) { $tx->rollBack(); throw $e; }
        ServiceProfileTelegramService::notifyMany($this->authorEmployees($profile), $profile, 'Service Profile ถูกส่งกลับแก้ไข', $comment);
    }

    private function createStage(ServiceProfile $profile, string $stage, Employees $employee, string $status): void
    {
        $row = new ServiceProfileApproval([
            'service_profile_id' => $profile->id, 'stage' => $stage, 'employee_id' => $employee->id,
            'employee_name_snapshot' => $employee->fullname(), 'status' => $status,
            'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'),
        ]);
        if (!$row->save()) throw new \RuntimeException(implode(' ', $row->getFirstErrors()));
    }

    private function pendingStage(ServiceProfile $profile, string $stage, Employees $employee): ServiceProfileApproval
    {
        $row = ServiceProfileApproval::findOne(['service_profile_id' => $profile->id, 'stage' => $stage, 'employee_id' => $employee->id, 'status' => ServiceProfileApproval::STATUS_PENDING]);
        if (!$row) throw new \DomainException('คุณไม่ได้รับมอบหมายในขั้นตอนนี้ หรือรายการถูกดำเนินการแล้ว');
        return $row;
    }

    private function passStage(ServiceProfileApproval $stage, string $comment): void
    {
        $stage->status = ServiceProfileApproval::STATUS_PASSED;
        $stage->comment = trim($comment) ?: null;
        $stage->acted_at = date('Y-m-d H:i:s');
        $stage->acted_by_user_id = Yii::$app->user->id;
        $stage->updated_at = date('Y-m-d H:i:s');
        $stage->save(false);
    }

    private function activateStage(ServiceProfile $profile, string $stage): void
    {
        ServiceProfileApproval::updateAll(['status' => ServiceProfileApproval::STATUS_PENDING, 'updated_at' => date('Y-m-d H:i:s')], ['service_profile_id' => $profile->id, 'stage' => $stage, 'status' => ServiceProfileApproval::STATUS_WAITING]);
    }

    private function authorEmployees(ServiceProfile $profile): array
    {
        $employees = [];
        foreach ($profile->getAuthors()->with('employee.user')->all() as $author) {
            if ($author->employee) $employees[] = $author->employee;
        }
        return $employees;
    }
}
