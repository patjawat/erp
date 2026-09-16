<?php

namespace app\modules\complaint\services;

use app\modules\complaint\models\Complaint;
use app\modules\complaint\models\ComplaintAttachment;
use Yii;
use yii\web\UploadedFile;

/**
 * จัดการไฟล์แนบเรื่องร้องเรียน — เก็บไฟล์นอก webroot (เสิร์ฟผ่าน controller) เหมือน KmPhotoService
 * รองรับทั้งรูปภาพ (สร้าง thumbnail) และเอกสาร (PDF/Office)
 *
 * โครงโฟลเดอร์:  modules/complaint/uploads/<complaint_id>/<file>
 *                modules/complaint/uploads/<complaint_id>/thumb/<file>   (เฉพาะรูป)
 * เก็บใน DB เป็น path สัมพัทธ์ (<complaint_id>/<file>)
 */
class ComplaintFileService
{
    public const MAX_BYTES = 15 * 1024 * 1024; // 15 MB / ไฟล์
    public const THUMB_MAX = 480;

    /** mime => ext ที่อนุญาต */
    public const ALLOWED = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
        'application/pdf' => 'pdf',
        'application/msword' => 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        'application/vnd.ms-excel' => 'xls',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
    ];

    public const IMAGE_MIME = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

    public static function basePath(): string
    {
        return Yii::getAlias('@app/modules/complaint/uploads');
    }

    public static function absolutePath(string $relative): string
    {
        return self::basePath() . '/' . ltrim($relative, '/');
    }

    /**
     * บันทึกไฟล์ 1 ไฟล์ → สร้าง row ComplaintAttachment
     *
     * @return ComplaintAttachment|string  โมเดลที่ save แล้ว หรือข้อความ error
     */
    public static function store(Complaint $complaint, UploadedFile $file, string $category, int $sort, ?int $actionId = null)
    {
        if ($file->error !== UPLOAD_ERR_OK) {
            return 'อัปโหลดไม่สำเร็จ (error ' . $file->error . '): ' . $file->name;
        }
        if ($file->size > self::MAX_BYTES) {
            return 'ไฟล์ใหญ่เกิน ' . (self::MAX_BYTES / 1024 / 1024) . ' MB: ' . $file->name;
        }

        $mime = mime_content_type($file->tempName) ?: $file->type;
        if (!isset(self::ALLOWED[$mime])) {
            return 'ชนิดไฟล์ไม่รองรับ (' . $mime . '): ' . $file->name;
        }
        $ext = self::ALLOWED[$mime];

        $dir = self::basePath() . '/' . $complaint->id;
        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            return 'สร้างโฟลเดอร์จัดเก็บไม่สำเร็จ';
        }

        $name = date('YmdHis') . '_' . Yii::$app->security->generateRandomString(8) . '.' . $ext;
        $absOrig = $dir . '/' . $name;
        if (!$file->saveAs($absOrig)) {
            return 'บันทึกไฟล์ไม่สำเร็จ: ' . $file->name;
        }

        $relOrig = $complaint->id . '/' . $name;
        $relThumb = null;
        if (in_array($mime, self::IMAGE_MIME, true)) {
            $thumbDir = $dir . '/thumb';
            if (is_dir($thumbDir) || mkdir($thumbDir, 0775, true) || is_dir($thumbDir)) {
                if (self::makeThumbnail($absOrig, $thumbDir . '/' . $name, $mime)) {
                    $relThumb = $complaint->id . '/thumb/' . $name;
                }
            }
        }

        $att = new ComplaintAttachment([
            'complaint_id' => $complaint->id,
            'action_id' => $actionId,
            'category' => array_key_exists($category, ComplaintAttachment::CATEGORIES) ? $category : 'general',
            'file_path' => $relOrig,
            'thumbnail_path' => $relThumb,
            'file_name' => mb_substr((string) $file->name, 0, 255),
            'mime' => $mime,
            'size' => (int) $file->size,
            'sort' => $sort,
        ]);
        if (!$att->save()) {
            @unlink($absOrig);
            return 'บันทึกข้อมูลไฟล์ไม่สำเร็จ: ' . implode(' ', $att->getFirstErrors());
        }

        return $att;
    }

    public static function delete(ComplaintAttachment $att): void
    {
        foreach ([$att->file_path, $att->thumbnail_path] as $rel) {
            if ($rel) {
                $abs = self::absolutePath($rel);
                if (is_file($abs)) {
                    @unlink($abs);
                }
            }
        }
        $att->delete();
    }

    private static function makeThumbnail(string $src, string $dest, string $mime): bool
    {
        if (!function_exists('imagecreatetruecolor')) {
            return false;
        }
        $img = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($src),
            'image/png' => @imagecreatefrompng($src),
            'image/webp' => @imagecreatefromwebp($src),
            'image/gif' => @imagecreatefromgif($src),
            default => false,
        };
        if (!$img) {
            return false;
        }
        $w = imagesx($img);
        $h = imagesy($img);
        $scale = min(1, self::THUMB_MAX / max($w, $h));
        $nw = max(1, (int) round($w * $scale));
        $nh = max(1, (int) round($h * $scale));
        $thumb = imagecreatetruecolor($nw, $nh);
        if (in_array($mime, ['image/png', 'image/gif', 'image/webp'], true)) {
            imagealphablending($thumb, false);
            imagesavealpha($thumb, true);
        }
        imagecopyresampled($thumb, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);
        $ok = match ($mime) {
            'image/jpeg' => imagejpeg($thumb, $dest, 82),
            'image/png' => imagepng($thumb, $dest, 6),
            'image/webp' => imagewebp($thumb, $dest, 82),
            'image/gif' => imagegif($thumb, $dest),
            default => false,
        };
        imagedestroy($img);
        imagedestroy($thumb);
        return (bool) $ok;
    }
}
