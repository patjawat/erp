<?php

namespace app\modules\km\services;

use app\modules\km\models\KmActivity;
use app\modules\km\models\KmActivityPhoto;
use Yii;
use yii\web\UploadedFile;

/**
 * จัดการไฟล์รูปกิจกรรม KM — เก็บไฟล์นอก webroot (เสิร์ฟผ่าน controller) + ย่อ thumbnail ด้วย GD
 *
 * โครงโฟลเดอร์:  modules/km/uploads/<activity_id>/<file>          (ต้นฉบับ)
 *                modules/km/uploads/<activity_id>/thumb/<file>    (ย่อ)
 * เก็บใน DB เป็น path สัมพัทธ์ (<activity_id>/<file>) ให้ย้ายโฟลเดอร์ base ได้
 */
class KmPhotoService
{
    public const MAX_BYTES = 8 * 1024 * 1024; // 8 MB / รูป
    public const THUMB_MAX = 480;             // ด้านยาวสุดของ thumbnail (px)

    public const ALLOWED = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];

    public static function basePath(): string
    {
        return Yii::getAlias('@app/modules/km/uploads');
    }

    /** absolute path ของไฟล์จาก path สัมพัทธ์ที่เก็บใน DB */
    public static function absolutePath(string $relative): string
    {
        return self::basePath() . '/' . ltrim($relative, '/');
    }

    /**
     * บันทึกไฟล์ที่อัปโหลด 1 ไฟล์ → สร้าง row KmActivityPhoto (ยังไม่ save cover)
     *
     * @return KmActivityPhoto|string  โมเดลที่ save แล้ว หรือข้อความ error
     */
    public static function store(KmActivity $activity, UploadedFile $file, int $sort)
    {
        if ($file->error !== UPLOAD_ERR_OK) {
            return 'อัปโหลดไม่สำเร็จ (error ' . $file->error . ')';
        }
        if ($file->size > self::MAX_BYTES) {
            return 'ไฟล์ใหญ่เกิน ' . (self::MAX_BYTES / 1024 / 1024) . ' MB: ' . $file->name;
        }

        $mime = mime_content_type($file->tempName) ?: $file->type;
        if (!isset(self::ALLOWED[$mime])) {
            return 'ชนิดไฟล์ไม่รองรับ (' . $mime . '): ' . $file->name;
        }
        $ext = self::ALLOWED[$mime];

        $dir = self::basePath() . '/' . $activity->id;
        $thumbDir = $dir . '/thumb';
        if (!is_dir($thumbDir) && !mkdir($thumbDir, 0775, true) && !is_dir($thumbDir)) {
            return 'สร้างโฟลเดอร์จัดเก็บไม่สำเร็จ';
        }

        $name = date('YmdHis') . '_' . Yii::$app->security->generateRandomString(8) . '.' . $ext;
        $absOrig = $dir . '/' . $name;
        if (!$file->saveAs($absOrig)) {
            return 'บันทึกไฟล์ไม่สำเร็จ: ' . $file->name;
        }

        $relOrig = $activity->id . '/' . $name;
        $relThumb = $activity->id . '/thumb/' . $name;
        if (!self::makeThumbnail($absOrig, $thumbDir . '/' . $name, $mime)) {
            // ย่อไม่สำเร็จก็ยังใช้ต้นฉบับแทน thumbnail ได้
            $relThumb = $relOrig;
        }

        $photo = new KmActivityPhoto([
            'activity_id' => $activity->id,
            'file_path' => $relOrig,
            'file_name' => mb_substr((string) $file->name, 0, 255),
            'mime' => $mime,
            'size' => (int) $file->size,
            'thumbnail_path' => $relThumb,
            'sort' => $sort,
        ]);
        if (!$photo->save()) {
            @unlink($absOrig);
            return 'บันทึกข้อมูลรูปไม่สำเร็จ: ' . implode(' ', $photo->getFirstErrors());
        }

        return $photo;
    }

    /** ลบไฟล์ต้นฉบับ+thumb ของรูป แล้วลบ row */
    public static function delete(KmActivityPhoto $photo): void
    {
        foreach ([$photo->file_path, $photo->thumbnail_path] as $rel) {
            if ($rel) {
                $abs = self::absolutePath($rel);
                if (is_file($abs)) {
                    @unlink($abs);
                }
            }
        }
        $photo->delete();
    }

    /** สร้าง thumbnail ด้วย GD (คงสัดส่วน ด้านยาวสุดไม่เกิน THUMB_MAX) */
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
        // คง transparency สำหรับ png/gif/webp
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
