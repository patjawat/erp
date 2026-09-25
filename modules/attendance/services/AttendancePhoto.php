<?php

namespace app\modules\attendance\services;

use Yii;
use yii\web\UploadedFile;

/**
 * รูปยืนยันตัวตนตอนลงเวลานอกพื้นที่
 * - เก็บนอก webroot (modules/attendance/uploads/YYYY/MM/<file>.jpg) ดูผ่าน controller ที่ตรวจสิทธิ์
 * - ย่อด้านยาวไม่เกิน 640px, JPEG, ลบ EXIF (พิกัด/รุ่นเครื่อง) โดยการ re-encode
 * - ประทับเวลาเซิร์ฟเวอร์ (watermark) มุมล่าง
 * - เก็บ 90 วัน แล้วลบไฟล์ (ข้อมูลลงเวลายังอยู่ แค่รูปหมดอายุ)
 * DB เก็บ path สัมพัทธ์ "YYYY/MM/<file>.jpg" — ของเก่า "uploads/checkin/..." (web) ยังเปิดได้
 */
class AttendancePhoto
{
    public const MAX_EDGE = 640;
    public const MAX_UPLOAD = 8 * 1024 * 1024; // เผื่อเครื่องที่ย่อฝั่งมือถือไม่ได้ — เซิร์ฟเวอร์ย่อซ้ำเสมอ
    public const RETENTION_DAYS = 90;
    public const QUALITY = 75;

    public static function basePath(): string
    {
        return Yii::getAlias('@app/modules/attendance/uploads');
    }

    /** path ที่ถูกต้องตามรูปแบบและเป็นของพนักงานคนนี้ (กันส่ง path คนอื่นมาอ้าง) */
    public static function isValidPath(string $path, int $empId): bool
    {
        return (bool)preg_match('~^\d{4}/\d{2}/\d{8}_\d{6}_' . $empId . '_[a-f0-9]{8}\.jpg$~D', $path)
            && is_file(self::basePath() . '/' . $path);
    }

    /** absolute path สำหรับเสิร์ฟไฟล์ (รองรับของเก่าใน web/uploads/checkin) หรือ null ถ้าไม่มีไฟล์ */
    public static function absolutePath(?string $path): ?string
    {
        if (!$path) return null;
        if (preg_match('~^uploads/checkin/[\w.-]+$~D', $path)) {
            $abs = Yii::getAlias('@webroot/' . $path);
        } elseif (preg_match('~^\d{4}/\d{2}/[\w.-]+\.jpg$~D', $path)) {
            $abs = self::basePath() . '/' . $path;
        } else {
            return null;
        }
        return is_file($abs) ? $abs : null;
    }

    /**
     * ย่อ + ประทับเวลา + บันทึก → คืน path สัมพัทธ์ หรือโยน DomainException
     */
    public static function store(UploadedFile $file, int $empId): string
    {
        if ($file->error !== UPLOAD_ERR_OK || $file->size <= 0) throw new \DomainException('อัปโหลดรูปไม่สำเร็จ กรุณาถ่ายใหม่');
        if ($file->size > self::MAX_UPLOAD) throw new \DomainException('รูปมีขนาดใหญ่เกินไป กรุณาถ่ายใหม่');
        $info = @getimagesize($file->tempName);
        if (!$info || !in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true)) {
            throw new \DomainException('กรุณาถ่ายรูปหรือเลือกไฟล์รูปภาพ (JPG/PNG/WEBP)');
        }
        $src = @imagecreatefromstring((string)file_get_contents($file->tempName));
        if (!$src) throw new \DomainException('อ่านไฟล์รูปไม่ได้ กรุณาถ่ายใหม่');
        if ($info[2] === IMAGETYPE_JPEG) $src = self::applyExifOrientation($src, $file->tempName);

        $img = self::resize($src, self::MAX_EDGE);
        $now = AttendanceService::now();
        self::watermark($img, $now);

        $sub = substr($now, 0, 4) . '/' . substr($now, 5, 2);
        $dir = self::basePath() . '/' . $sub;
        if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) throw new \DomainException('สร้างโฟลเดอร์เก็บรูปไม่สำเร็จ');
        $name = str_replace(['-', ':', ' '], ['', '', '_'], $now) . '_' . $empId . '_' . substr(bin2hex(random_bytes(4)), 0, 8) . '.jpg';
        $ok = imagejpeg($img, $dir . '/' . $name, self::QUALITY);
        imagedestroy($img);
        if (!$ok) throw new \DomainException('บันทึกรูปไม่สำเร็จ');

        self::purgeDaily();
        return $sub . '/' . $name;
    }

    private static function applyExifOrientation(\GdImage $img, string $file): \GdImage
    {
        if (!function_exists('exif_read_data')) return $img;
        $o = (int)(@exif_read_data($file)['Orientation'] ?? 1);
        $rotated = match ($o) { 3 => imagerotate($img, 180, 0), 6 => imagerotate($img, -90, 0), 8 => imagerotate($img, 90, 0), default => null };
        if ($rotated) { imagedestroy($img); return $rotated; }
        return $img;
    }

    private static function resize(\GdImage $src, int $max): \GdImage
    {
        $w = imagesx($src); $h = imagesy($src);
        $scale = min(1, $max / max($w, $h));
        $nw = max(1, (int)round($w * $scale)); $nh = max(1, (int)round($h * $scale));
        $dst = imagecreatetruecolor($nw, $nh);
        imagefill($dst, 0, 0, imagecolorallocate($dst, 255, 255, 255)); // PNG โปร่งใส → พื้นขาว
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
        imagedestroy($src);
        return $dst;
    }

    /** แถบดำโปร่งแสงล่างภาพ + วันเวลาเซิร์ฟเวอร์ (พ.ศ.) */
    private static function watermark(\GdImage $img, string $now): void
    {
        $w = imagesx($img); $h = imagesy($img);
        $ts = strtotime($now);
        $text = date('d/m/', $ts) . ((int)date('Y', $ts) + 543) . ' ' . date('H:i:s', $ts) . ' น.';
        $size = max(11, (int)round($w / 32));
        $bar = (int)round($size * 2.2);
        imagefilledrectangle($img, 0, $h - $bar, $w, $h, imagecolorallocatealpha($img, 0, 0, 0, 60));
        $white = imagecolorallocate($img, 255, 255, 255);
        $font = Yii::getAlias('@app/vendor/mpdf/mpdf/ttfonts/Garuda.ttf');
        if (function_exists('imagettftext') && is_file($font)) {
            $box = imagettfbbox($size, 0, $font, $text);
            $tw = $box[2] - $box[0];
            imagettftext($img, $size, 0, max(4, $w - $tw - (int)($size * 0.8)), $h - (int)round($bar * 0.3), $white, $font, $text);
        } else {
            imagestring($img, 5, 8, $h - $bar + 6, $text, $white); // ไม่มีฟอนต์ — ตัวเลขล้วน ใช้ฟอนต์ในตัวได้
        }
    }

    /**
     * ลบรูปที่เกินอายุเก็บ — ทำอย่างมากวันละครั้ง (เรียกจากการอัปโหลด เพราะไม่มี cron ใน container)
     * @return int จำนวนไฟล์ที่ลบ
     */
    public static function purgeDaily(bool $force = false): int
    {
        $marker = Yii::getAlias('@runtime/attendance-photo-purge.txt');
        $today = substr(AttendanceService::now(), 0, 10);
        if (!$force && is_file($marker) && trim((string)@file_get_contents($marker)) === $today) return 0;
        @file_put_contents($marker, $today);
        return self::purge(self::RETENTION_DAYS);
    }

    public static function purge(int $days): int
    {
        $base = self::basePath();
        if (!is_dir($base)) return 0;
        $cutoff = time() - $days * 86400;
        $count = 0;
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($base, \FilesystemIterator::SKIP_DOTS)) as $f) {
            if ($f->isFile() && $f->getExtension() === 'jpg' && $f->getMTime() < $cutoff && @unlink($f->getPathname())) $count++;
        }
        return $count;
    }
}
