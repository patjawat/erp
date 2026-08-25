<?php

namespace app\modules\serviceProfile\services;

use app\modules\hr\models\Employees;
use app\modules\serviceProfile\forms\CreateProfileForm;
use app\modules\serviceProfile\models\ServiceProfile;
use app\modules\serviceProfile\models\ServiceProfileActivity;
use app\modules\serviceProfile\models\ServiceProfileAuthor;
use app\modules\serviceProfile\models\ServiceProfileSection;
use app\modules\serviceProfile\models\ServiceProfileTemplate;
use Yii;

class ProfileService
{
    public function createDraft(CreateProfileForm $form): ServiceProfile
    {
        $resolved = (new OwnerDirectoryService())->resolveOwner((int) $form->owner_id, (int) $form->fiscal_year);
        $owner = $resolved['unit'];
        $ownerType = $resolved['owner_type'];
        $ownerId = $resolved['owner_id'];
        $template = ServiceProfileTemplate::find()->where([
            'owner_type' => $ownerType, 'owner_id' => $ownerId,
            'is_active' => 1, 'lifecycle_status' => ServiceProfileTemplate::STATUS_ACTIVE,
        ])->andWhere(['<=', 'effective_fiscal_year', $form->fiscal_year])
            ->orderBy(['effective_fiscal_year' => SORT_DESC, 'revision_no' => SORT_DESC])->one();
        if (!$template) throw new \DomainException('หน่วยงานนี้ยังไม่มี Template ที่ประกาศใช้');

        $source = $form->copy_latest ? ServiceProfile::findCurrent($ownerType, $ownerId) : null;
        $revision = (int) ServiceProfile::find()->where(['owner_type' => $ownerType, 'owner_id' => $ownerId, 'fiscal_year' => $form->fiscal_year])->max('revision_no') + 1;
        $tx = Yii::$app->db->beginTransaction();
        try {
            $profile = new ServiceProfile([
                'owner_type' => $ownerType, 'owner_id' => $ownerId,
                'owner_name_snapshot' => $owner->name, 'fiscal_year' => $form->fiscal_year,
                'revision_no' => $revision, 'template_id' => $template->id,
                'template_revision_snapshot' => $template->revision_no, 'status' => ServiceProfile::STATUS_DRAFT,
                'supersedes_id' => $source?->id,
            ]);
            if (!$profile->save()) throw new \RuntimeException(implode(' ', $profile->getFirstErrors()));
            $sourceByCode = [];
            if ($source) foreach ($source->sections as $section) $sourceByCode[$section->section_code] = $section;
            foreach ($template->sections as $templateSection) {
                if (!$templateSection->is_enabled) continue;
                $old = $sourceByCode[$templateSection->section_code] ?? null;
                $section = new ServiceProfileSection([
                    'service_profile_id' => $profile->id, 'template_section_id' => $templateSection->id,
                    'section_code' => $templateSection->section_code, 'title' => $templateSection->title,
                    'block_type' => $templateSection->block_type, 'content' => $old?->content,
                    'data_json' => $old?->data_json, 'config_snapshot_json' => $templateSection->config_json,
                    'is_required' => $templateSection->is_required, 'sort_order' => $templateSection->sort_order,
                ]);
                if (!$section->save()) throw new \RuntimeException(implode(' ', $section->getFirstErrors()));
                if (($section->section_code === 'key_processes' || $section->block_type === 'key_process_table') && $old) {
                    (new \app\modules\iacRisk\services\ProcessSyncService())->syncSection($section);
                }
            }
            $ids = array_values(array_unique(array_map('intval', array_merge((array) $form->author_ids, [(int) $form->coordinator_id]))));
            $validIds = Employees::find()->select('id')->where(['id' => $ids, 'status' => Employees::STATUS_WORKING])->column();
            if (count($validIds) !== count($ids)) throw new \DomainException('รายชื่อคณะทำงานมีบุคลากรที่ไม่พบหรือพ้นสภาพแล้ว');
            foreach ($ids as $employeeId) {
                $author = new ServiceProfileAuthor([
                    'service_profile_id' => $profile->id, 'employee_id' => $employeeId,
                    'role' => $employeeId === (int) $form->coordinator_id ? ServiceProfileAuthor::ROLE_COORDINATOR : ServiceProfileAuthor::ROLE_AUTHOR,
                    'assigned_at' => date('Y-m-d H:i:s'), 'assigned_by' => Yii::$app->user->id,
                ]);
                if (!$author->save()) throw new \RuntimeException(implode(' ', $author->getFirstErrors()));
            }
            $this->log($profile, 'created', null, $profile->status, $source ? 'สร้างโดยคัดลอกจากฉบับปัจจุบัน' : 'สร้างจาก Template');
            $tx->commit();
            return $profile;
        } catch (\Throwable $e) {
            $tx->rollBack();
            throw $e;
        }
    }

    public function log(ServiceProfile $profile, string $action, ?string $from, ?string $to, ?string $message = null, ?int $sectionId = null): void
    {
        (new ServiceProfileActivity([
            'service_profile_id' => $profile->id, 'section_id' => $sectionId, 'action' => $action,
            'from_status' => $from, 'to_status' => $to, 'message' => $message,
            'created_at' => date('Y-m-d H:i:s'), 'created_by' => Yii::$app->user->isGuest ? null : Yii::$app->user->id,
        ]))->save(false);
    }
}
