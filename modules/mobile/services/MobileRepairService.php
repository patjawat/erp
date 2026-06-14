<?php

namespace app\modules\mobile\services;

use app\components\AppHelper;
use app\components\FileManagerHelper as ComponentFileManagerHelper;
use app\modules\am\models\Asset;
use app\modules\filemanager\components\FileManagerHelper;
use app\modules\filemanager\models\Uploads;
use app\modules\helpdesk2\models\Helpdesk;
use app\modules\hr\models\Employees;
use Yii;

/**
 * Mobile helpers สำหรับ "แจ้งส่งซ่อม" — Wizard ใหม่บนมือถือ
 * ใช้ Helpdesk model เดิม + reuse listDeviceType/listRepairGroup/listUrgency
 * + Reuse HelpdeskGenNumber + เปลี่ยนสถานะ asset + Telegram notify ตามของเดิม
 * เพื่อให้ยังเข้ากับ workflow เดิม 100% (ไม่แตะ business logic)
 */
class MobileRepairService
{
    public const PHOTO_UPLOAD_NAME = 'repair_request';

    /**
     * สร้าง draft Helpdesk + prefill asset_number ถ้ามี
     * @return Helpdesk
     */
    public function newDraft(Employees $me, ?string $assetNumber = null): Helpdesk
    {
        $model = new Helpdesk([
            'ref'           => substr(Yii::$app->getSecurity()->generateRandomString(), 10),
            'emp_id'        => $me->id,
            'name'          => 'repair',
            'asset_number'  => $assetNumber ?: null,
            'status'        => 'pending',
            'thai_year'     => (int) AppHelper::YearBudget(),
        ]);
        if ($assetNumber) {
            $group = $this->resolveRepairGroupByAsset($assetNumber);
            if ($group !== null) $model->repair_group = (string) $group;
        }
        return $model;
    }

    /**
     * โหลด Helpdesk ของ employee (สำหรับ success view / detail).
     */
    public function findOwnedById(int $id, $empId): ?Helpdesk
    {
        $row = Helpdesk::findOne($id);
        if (!$row || (string) $row->emp_id !== (string) $empId) return null;
        return $row;
    }

    public function getDeviceTypes(): array { return (new Helpdesk())->listDeviceType(); }
    public function getUrgencyOptions(): array { return Helpdesk::listUrgency(); }
    public function getRepairGroups(): array { return Helpdesk::listRepairGroup(); }

    /**
     * ค้นหา asset จากรหัส → ส่งคืน metadata ครุภัณฑ์ (สำหรับ confirm chip ใน wizard).
     * @return array{code:string,name:string,location:string,repair_group:?int}|null
     */
    public function lookupAsset(string $code): ?array
    {
        $code = trim($code);
        if ($code === '') return null;
        try {
            $asset = Asset::findOne(['code' => $code]);
        } catch (\Throwable $e) { return null; }
        if (!$asset) return null;

        $data = is_array($asset->data_json ?? null) ? $asset->data_json : [];
        $name = '';
        try { $name = (string) ($asset->AssetitemName() ?: ''); } catch (\Throwable $e) { $name = ''; }
        if ($name === '') $name = (string) ($asset->name ?? $code);
        return [
            'code'         => $code,
            'name'         => $name,
            'location'     => (string) ($data['location'] ?? ''),
            'repair_group' => $this->resolveRepairGroupByAsset($code),
        ];
    }

    /**
     * คืน repair_group code ที่เหมาะกับ asset จาก asset.asset_type / data_json
     * Logic เดิมใช้ "device type 4 → คอม → group=2" หรือ default group=1
     * ที่นี่ขั้นต่ำเรา map asset_type ของ Asset → group
     * (RepairV2 มี endpoint /get-repair-group ที่ใช้ logic อะไรซับซ้อนก็ตาม
     *  ในมือถือเราเริ่มที่ default + ให้ผู้ใช้แก้ได้เอง)
     */
    public function resolveRepairGroupByAsset(string $assetNumber): ?int
    {
        try {
            $asset = Asset::findOne(['code' => $assetNumber]);
            if (!$asset) return null;
            $data = is_array($asset->data_json ?? null) ? $asset->data_json : [];
            // กฎ heuristic: ถ้าเป็นคอม/IT → กลุ่ม 2; การแพทย์ → 3; ทั่วไป → 1
            $typeId = (int) ($asset->asset_type_id ?? 0);
            $typeName = strtolower((string) ($asset->type_name ?? ($data['asset_type']['title'] ?? '')));
            if (preg_match('/comput|printer|laptop|ไอที|คอม/u', $typeName)) return 2;
            if (preg_match('/medic|ทางการแพทย์|การแพทย์/u', $typeName)) return 3;
            return 1;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Validate ตามกฎเดิม (ดู actionCreateValidator):
     *  - title required (รายละเอียดปัญหา)
     *  - data_json[urgency] required
     *  - data_json[location] required
     *  - repair_group required
     *
     * @return array<string,string>  attr → message  (ว่าง = ผ่าน)
     */
    public function validate(Helpdesk $model): array
    {
        $errors = [];
        $data = is_array($model->data_json) ? $model->data_json : [];
        if (trim((string) $model->title) === '') $errors['title'] = 'กรุณาระบุรายละเอียดปัญหา';
        if (trim((string) ($data['urgency'] ?? '')) === '') $errors['urgency'] = 'กรุณาเลือกความเร่งด่วน';
        if (trim((string) ($data['location'] ?? '')) === '') $errors['location'] = 'กรุณาระบุสถานที่';
        if (trim((string) $model->repair_group) === '') $errors['repair_group'] = 'กรุณาเลือกแผนกช่าง';
        return $errors;
    }

    /**
     * บันทึก Helpdesk + workflow side effects (เหมือน RepairV2Controller::actionCreate)
     *
     * @return array{ok:bool, errors:array, model:Helpdesk}
     */
    public function save(Helpdesk $model): array
    {
        // convert Thai date → Gregorian (เหมือนของเดิม)
        if (!empty($model->request_repair_date)) {
            try { $model->request_repair_date = AppHelper::convertToGregorian($model->request_repair_date); }
            catch (\Throwable $e) {}
        }
        if (empty($model->status)) $model->status = 'pending';

        $errors = $this->validate($model);
        if (!empty($errors)) {
            foreach ($errors as $attr => $msg) {
                $model->addError($attr === 'urgency' || $attr === 'location' ? "data_json[$attr]" : $attr, $msg);
            }
            return ['ok' => false, 'errors' => $errors, 'model' => $model];
        }

        // generate repair_number (เหมือนของเดิม)
        $depCode = '';
        switch ((string) $model->repair_group) {
            case '1': $depCode = 'GEN'; break;
            case '2': $depCode = 'IT'; break;
            case '3': $depCode = 'MED'; break;
        }
        try { $model->repair_number = $model->HelpdeskGenNumber($depCode); }
        catch (\Throwable $e) {}

        if (!$model->save()) {
            return ['ok' => false, 'errors' => $model->getFirstErrors(), 'model' => $model];
        }

        // side effects เหมือน RepairV2: เปลี่ยน asset_status + แจ้ง Telegram
        if (!empty($model->asset_number)) {
            $this->changeAssetStatus((string) $model->asset_number);
        }
        $this->notifyTelegram($model);

        return ['ok' => true, 'errors' => [], 'model' => $model];
    }

    /** เปลี่ยน asset.asset_status='repair' (ตาม RepairV2::changAssetStatus) */
    protected function changeAssetStatus(string $code): void
    {
        try {
            $asset = Asset::findOne(['code' => $code]);
            if ($asset) {
                $asset->asset_status = 'repair';
                $asset->save(false);
            }
        } catch (\Throwable $e) {}
    }

    /** แจ้งเตือน Telegram (mirror RepairV2::sendMsg) */
    protected function notifyTelegram(Helpdesk $model): void
    {
        try {
            $sendTo = '';
            $sentName = '';
            switch ((string) $model->repair_group) {
                case '1': $sendTo = 'repair';            $sentName = 'งานซ่อมบำรุง'; break;
                case '2': $sendTo = 'computer_service';  $sentName = 'งานซ่อมคอมพิวเตอร์'; break;
                case '3': $sendTo = 'medical_service';   $sentName = 'งานซ่อมครุภัณฑ์การแพทย์'; break;
            }
            if (!$sendTo) return;

            $data = is_array($model->data_json) ? $model->data_json : [];
            $reporter = '';
            try { $reporter = (string) ($model->emp->fullname ?? ''); } catch (\Throwable $e) {}
            $urgencyTxt = '';
            try { $urgencyTxt = (string) ($model->viewUrgent()['title'] ?? ''); } catch (\Throwable $e) {}
            $deviceTxt = '';
            try { $deviceTxt = (string) ($model->deviceType->title ?? ''); } catch (\Throwable $e) {}

            $message  = "🔧 รหัสซ่อม : " . ($model->repair_number ?: '-') . "\n";
            $message .= "📂 ประเภทงาน : " . ($deviceTxt ?: '-') . "\n";
            $message .= "🛠️ รหัสครุภัณฑ์ : " . ($model->asset_number ?: '-') . "\n";
            $message .= "📝 รายละเอียด : " . ($model->title ?: '-') . "\n";
            $message .= "📍 สถานที่ : " . ($data['location'] ?? '-') . "\n";
            $message .= "⚠️ ความเร่งด่วน : " . ($urgencyTxt ?: '-') . "\n";
            $message .= "👤 ผู้แจ้ง : " . ($reporter ?: '-') . "\n";
            $message .= "📞 โทร : " . ($data['phone'] ?? '-') . "\n\n";
            $message .= "📌 แจ้งซ่อม " . $sentName . " (จากมือถือ)\n";

            Yii::$app->telegram->sendMessage($sendTo, $message, [
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => true,
            ]);
        } catch (\Throwable $e) {
            // swallow — บันทึกหลักไม่ควรพังเพราะ notify ล้ม
        }
    }

    /**
     * อัปโหลดรูปแนบ (name=repair_request) — เหมือนหน้า maintenance-request เดิม
     * @return int จำนวนไฟล์ที่บันทึกสำเร็จ
     */
    public function savePhotos(Helpdesk $model): int
    {
        $files = \yii\web\UploadedFile::getInstancesByName('photos');
        if (empty($files)) return 0;

        FileManagerHelper::CreateDir($model->ref);
        $saved = 0;
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'heic', 'heif'];

        foreach ($files as $file) {
            $ext = strtolower((string) $file->extension);
            if ($file->error !== UPLOAD_ERR_OK || !in_array($ext, $allowed, true)) continue;

            $fileName = $file->baseName . '.' . $ext;
            $realName = md5($file->baseName . microtime(true) . Yii::$app->security->generateRandomString(8)) . '.' . $ext;
            $path     = FileManagerHelper::getUploadPath() . '/' . $model->ref . '/' . $realName;
            if (!$file->saveAs($path)) continue;

            try {
                if (FileManagerHelper::isImage($path)) {
                    FileManagerHelper::createThumbnail($model->ref, $realName);
                }
            } catch (\Throwable $e) {}

            $up = new Uploads();
            $up->ref = $model->ref;
            $up->name = self::PHOTO_UPLOAD_NAME;
            $up->file_name = $fileName;
            $up->real_filename = $realName;
            $up->type = FileManagerHelper::checkFileType($ext);
            if ($up->save(false)) $saved++;
        }
        return $saved;
    }
}
