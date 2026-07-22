<?php

use app\modules\filemanager\components\FileManagerHelper;
use app\modules\filemanager\models\Uploads;
use yii\db\Migration;

/**
 * ย้ายไฟล์รูปปกเอกสารและสื่อของขั้นตอน จากที่เก็บเดิม (web/uploads/medsop/...)
 * เข้าสู่ระบบ filemanager (fileupload/<document.ref>/ + ตาราง uploads) ตาม convention:
 *   - ref  = medsop_document.ref (1 เอกสาร = 1 โฟลเดอร์)
 *   - name = 'cover' (รูปปก) / 'step_media' (สื่อขั้นตอน)
 * แล้วเปลี่ยนคอลัมน์อ้างอิง:
 *   - medsop_document.cover_image           => uploads.id
 *   - medsop_document_step_media.upload_id  => uploads.id (+ file_path เป็น show-url)
 *
 * ไฟล์ต้นทางเดิมไม่ถูกลบ (กันข้อมูลหายกรณี rollback); เป็น one-way data migration.
 */
class m260722_100100_migrate_medsop_files_to_filemanager extends Migration
{
    public function safeUp()
    {
        $this->migrateCovers();
        $this->migrateStepMedia();
    }

    public function safeDown()
    {
        echo "m260722_100100_migrate_medsop_files_to_filemanager ไม่รองรับการ rollback ข้อมูล\n";
        return true;
    }

    private function webRoot(): string
    {
        return Yii::getAlias('@app') . '/web';
    }

    /**
     * คัดลอกไฟล์เดิมเข้า filemanager แล้วสร้างแถว uploads คืน id (หรือ null ถ้าไฟล์ไม่มี)
     */
    private function importFile(string $relativePath, string $ref, ?string $name): ?int
    {
        $source = $this->webRoot() . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
        if (!is_file($source)) {
            echo "  ข้าม: ไม่พบไฟล์ต้นทาง $source\n";
            return null;
        }
        FileManagerHelper::CreateDir($ref);
        $ext = strtolower(pathinfo($source, PATHINFO_EXTENSION));
        $realFileName = md5($source . microtime(true)) . '.' . $ext;
        $dest = FileManagerHelper::getUploadPath() . $ref . '/' . $realFileName;
        if (!@copy($source, $dest)) {
            echo "  ข้าม: คัดลอกไม่สำเร็จ $source\n";
            return null;
        }
        if (FileManagerHelper::isImage($dest)) {
            try {
                FileManagerHelper::createThumbnail($ref, $realFileName);
            } catch (\Throwable $e) {
                echo "  เตือน: สร้าง thumbnail ไม่สำเร็จ: {$e->getMessage()}\n";
            }
        }
        $upload = new Uploads();
        $upload->ref = $ref;
        $upload->name = $name;
        $upload->file_name = basename($source);
        $upload->real_filename = $realFileName;
        $upload->type = FileManagerHelper::checkFileType($ext);
        $upload->size = filesize($dest);
        if (!$upload->save(false)) {
            echo "  ข้าม: บันทึก uploads ไม่สำเร็จ\n";
            @unlink($dest);
            return null;
        }
        return (int) $upload->id;
    }

    private function migrateCovers(): void
    {
        $rows = (new \yii\db\Query())
            ->select(['id', 'ref', 'cover_image'])
            ->from('{{%medsop_document}}')
            ->where(['like', 'cover_image', '/uploads/medsop'])
            ->all($this->db);
        foreach ($rows as $row) {
            if (empty($row['ref'])) {
                echo "  ข้าม: เอกสาร #{$row['id']} ไม่มี ref\n";
                continue;
            }
            $uploadId = $this->importFile($row['cover_image'], $row['ref'], 'cover');
            if ($uploadId !== null) {
                $this->update('{{%medsop_document}}', ['cover_image' => (string) $uploadId], ['id' => $row['id']]);
                echo "  cover: document #{$row['id']} (ref={$row['ref']}) => uploads #{$uploadId}\n";
            }
        }
    }

    private function migrateStepMedia(): void
    {
        $rows = (new \yii\db\Query())
            ->select([
                'm.id',
                'm.file_path',
                'doc.ref',
            ])
            ->from('{{%medsop_document_step_media}} m')
            ->innerJoin('{{%medsop_document_step}} s', 's.id = m.step_id')
            ->innerJoin('{{%medsop_document}} doc', 'doc.id = s.document_id')
            ->where(['like', 'm.file_path', '/uploads/medsop'])
            ->andWhere(['m.upload_id' => null])
            ->all($this->db);
        foreach ($rows as $row) {
            if (empty($row['ref'])) {
                echo "  ข้าม: step_media #{$row['id']} เอกสารไม่มี ref\n";
                continue;
            }
            $uploadId = $this->importFile($row['file_path'], $row['ref'], 'step_media');
            if ($uploadId !== null) {
                $this->update('{{%medsop_document_step_media}}', [
                    'upload_id' => $uploadId,
                    'file_path' => '/filemanager/uploads/show?id=' . $uploadId,
                ], ['id' => $row['id']]);
                echo "  media: step_media #{$row['id']} (ref={$row['ref']}) => uploads #{$uploadId}\n";
            }
        }
    }
}
