<?php

namespace app\commands;

use yii\helpers\Console;
use yii\console\Controller;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class ScanController extends Controller
{
    public function actionJpg($path = '@app/modules/filemanager/fileupload')
    {
        $directory = \Yii::getAlias($path); // แปลง path alias เป็น path จริง
        if (!is_dir($directory)) {
            $this->stderr("❌ Directory not found: $directory\n", Console::FG_RED);
            return 1; // Exit code 1 หมายถึง error
        }

        $this->stdout("🔍 Scanning .jpg files in: $directory (including subdirectories)\n", Console::FG_GREEN);

        // ใช้ RecursiveDirectoryIterator และ RecursiveIteratorIterator
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        $found = false;
        foreach ($iterator as $fileInfo) {
            if ($fileInfo->isFile() && strtolower($fileInfo->getExtension()) === 'jpg') {
                $size = $fileInfo->getSize();
                $relativePath = str_replace($directory . DIRECTORY_SEPARATOR, '', $fileInfo->getPathname());
                $this->stdout("📸 $relativePath - " . $this->formatSize($size) . "\n", Console::FG_CYAN);
                $found = true;
            }
        }

        if (!$found) {
            $this->stdout("📂 No .jpg files found in: $directory\n", Console::FG_YELLOW);
        }

        return 0; // Exit code 0 หมายถึงสำเร็จ
    }

    private function formatSize($size)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = $size > 0 ? floor(log($size, 1024)) : 0;
        return number_format($size / pow(1024, $power), 2) . ' ' . $units[$power];
    }
    }
}
