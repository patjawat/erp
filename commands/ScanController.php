<?php

namespace app\commands;

use yii\helpers\Console;
use yii\console\Controller;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class ScanController extends Controller
{
    public function actionCompressJpg($path = '@app/modules/filemanager/fileupload', $maxSize = 500000)
    {
        $directory = \Yii::getAlias($path);
        if (!is_dir($directory)) {
            $this->stderr("❌ Directory not found: $directory\n", Console::FG_RED);
            return 1;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $fileInfo) {
            if ($fileInfo->isFile() && strtolower($fileInfo->getExtension()) === 'jpg') {
                $size = $fileInfo->getSize();
                if ($size > $maxSize) {
                    $this->compressImage($fileInfo->getPathname());
                    $this->stdout("✅ Compressed: " . $fileInfo->getFilename() . "\n", Console::FG_GREEN);
                } else {
                    $this->stdout("📸 Skipped (Already small): " . $fileInfo->getFilename() . "\n", Console::FG_YELLOW);
                }
            }
        }

        return 0;
    }

    private function compressImage($filePath, $quality = 80)
    {
        // ตรวจสอบประเภทของไฟล์
        $imageInfo = getimagesize($filePath);
        if ($imageInfo === false) {
            $this->stderr("❌ Error: $filePath is not an image file.\n", Console::FG_RED);
            return;
        }
    
        // ตรวจสอบว่าเป็น JPEG หรือไม่
        if ($imageInfo['mime'] !== 'image/jpeg') {
            $this->stderr("🚫 Skipping: $filePath is not a JPEG file (Detected: {$imageInfo['mime']})\n", Console::FG_YELLOW);
            return;
        }
    
        // บีบอัดเฉพาะไฟล์ JPEG เท่านั้น
        $image = imagecreatefromjpeg($filePath);
        if ($image === false) {
            $this->stderr("❌ Failed to open: $filePath\n", Console::FG_RED);
            return;
        }
    
        imagejpeg($image, $filePath, $quality);
        imagedestroy($image);
    
        $this->stdout("✅ Compressed: $filePath\n", Console::FG_GREEN);
    }
}
