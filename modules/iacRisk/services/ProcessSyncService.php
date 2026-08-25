<?php

namespace app\modules\iacRisk\services;

use app\modules\iacRisk\models\Hospital;
use app\modules\iacRisk\models\ServiceProcess;
use app\modules\iacRisk\models\ServiceProcessVersion;
use app\modules\serviceProfile\models\ServiceProfileSection;
use Yii;

class ProcessSyncService
{
    public function syncSection(ServiceProfileSection $section): int
    {
        if ($section->section_code !== 'key_processes' && $section->block_type !== 'key_process_table') return 0;
        $profile = $section->profile;
        if (!$profile) return 0;
        $hospitalId = (int) Hospital::find()->where(['is_current' => 1, 'active' => 1])->select('id')->scalar();
        if (!$hospitalId) return 0;

        $data = $section->getData();
        $items = (array) ($data['items'] ?? []);
        $userId = Yii::$app->has('user') && !Yii::$app->user->isGuest ? (int) Yii::$app->user->id : null;
        $keptRefs = [];
        $count = 0;
        foreach ($items as $index => &$item) {
            $name = trim(strip_tags((string) ($item['process'] ?? '')));
            if ($name === '') continue;
            $itemRef = preg_match('/^[A-Za-z0-9_-]{12,64}$/', (string) ($item['_process_ref'] ?? '')) ? (string) $item['_process_ref'] : Yii::$app->security->generateRandomString(24);
            $item['_process_ref'] = $itemRef;
            $keptRefs[] = $itemRef;
            $version = ServiceProcessVersion::findOne(['service_profile_section_id' => $section->id, 'source_item_ref' => $itemRef]);
            $isExistingVersion = $version !== null;
            $previousName = $isExistingVersion ? (string) $version->name : null;
            $previousObjective = $isExistingVersion ? (string) $version->objective : null;
            if (!$version) {
                $previous = ServiceProcessVersion::find()->alias('v')->joinWith('process p')->where([
                    'v.source_item_ref' => $itemRef, 'p.hospital_id' => $hospitalId,
                    'p.owner_type' => $profile->owner_type, 'p.owner_id' => $profile->owner_id,
                ])->orderBy(['v.fiscal_year' => SORT_DESC, 'v.revision_no' => SORT_DESC])->one();
                $process = $previous?->process;
                if (!$process) {
                    $process = new ServiceProcess([
                        'ref' => Yii::$app->security->generateRandomString(24), 'hospital_id' => $hospitalId,
                        'owner_type' => $profile->owner_type, 'owner_id' => $profile->owner_id, 'active' => 1,
                        'created_at' => date('Y-m-d H:i:s'), 'created_by' => $userId,
                    ]);
                    if (!$process->save(false)) throw new \RuntimeException('ไม่สามารถสร้างทะเบียนกระบวนงานได้');
                }
                $version = new ServiceProcessVersion([
                    'ref' => Yii::$app->security->generateRandomString(24), 'process_id' => $process->id,
                    'service_profile_id' => $profile->id, 'service_profile_section_id' => $section->id,
                    'source_item_ref' => $itemRef, 'fiscal_year' => $profile->fiscal_year,
                    'revision_no' => $profile->revision_no,
                    'review_status' => $previous ? ServiceProcessVersion::REVIEW_PENDING : ServiceProcessVersion::REVIEW_NEW,
                    'created_at' => date('Y-m-d H:i:s'), 'created_by' => $userId,
                ]);
            }
            $version->sequence = ($index + 1) * 10;
            $version->name = $name;
            $version->objective = trim(strip_tags((string) ($item['objective'] ?? '')));
            if ($isExistingVersion && ($previousName !== $version->name || $previousObjective !== (string) $version->objective)) {
                $version->review_status = ServiceProcessVersion::REVIEW_MODIFIED;
                $version->reviewed_at = date('Y-m-d H:i:s');
                $version->reviewed_by = $userId;
            }
            $version->updated_at = date('Y-m-d H:i:s');
            $version->updated_by = $userId;
            if (!$version->save()) throw new \RuntimeException(implode(' ', $version->getFirstErrors()));
            $count++;
        }
        unset($item);
        $section->setData(['items' => $items]);
        $section->updateAttributes(['data_json' => $section->data_json]);
        $stale = ServiceProcessVersion::find()->where(['service_profile_section_id' => $section->id]);
        if ($keptRefs) $stale->andWhere(['not in', 'source_item_ref', $keptRefs]);
        foreach ($stale->all() as $version) {
            $version->review_status = ServiceProcessVersion::REVIEW_RETIRED;
            $version->save(false, ['review_status', 'updated_at', 'updated_by']);
        }
        return $count;
    }
}
