<?php

namespace app\components;

use Yii;
use yii\base\Component;

class ZipComponent extends Component
{
    /**
     * สร้างไฟล์ ZIP จากโฟลเดอร์ที่ระบุ
     * 
     * @param string $sourcePath เส้นทางของโฟลเดอร์ที่ต้องการบีบอัด
     * @param string $zipPath เส้นทางที่ต้องการสร้างไฟล์ ZIP
     * @return bool ส่งกลับ true ถ้าสำเร็จ, false ถ้าล้มเหลว
     */
    public function createZip($sourcePath, $zipPath)
    {
        // ตรวจสอบว่าโฟลเดอร์ที่ต้องการบีบอัดมีอยู่หรือไม่
        if (!is_dir($sourcePath)) {
            Yii::error("Source folder does not exist: $sourcePath");
            return false;
        }

        // สร้าง instance ของ ZipArchive
        $zip = new \ZipArchive();

        // เปิดไฟล์ ZIP สำหรับเขียน
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
            // เพิ่มไฟล์หรือโฟลเดอร์เข้าไปใน ZIP
            $this->addFilesToZip($sourcePath, $zip);

            // ปิดไฟล์ ZIP
            $zip->close();

            Yii::info("ZIP file created successfully: $zipPath");
            return true;
        } else {
            Yii::error("Failed to create ZIP file: $zipPath");
            return false;
        }
    }

    /**
     * ฟังก์ชันสำหรับเพิ่มไฟล์เข้าไปใน ZIP
     * 
     * @param string $source เส้นทางของโฟลเดอร์
     * @param \ZipArchive $zip Instance ของ ZipArchive
     * @param string $subfolder โฟลเดอร์ย่อยภายใน ZIP
     */
    private function addFilesToZip($source, $zip, $subfolder = '')
    {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            if (!$file->isDir()) {
                // เอา path ที่สมบูรณ์ของไฟล์
                $filePath = $file->getRealPath();
                
                // เอา relative path ของไฟล์ที่จะใส่ใน ZIP
                $relativePath = $subfolder . '/' . substr($filePath, strlen($source) + 1);

                // เพิ่มไฟล์เข้าไปใน ZIP
                $zip->addFile($filePath, $relativePath);
            }
        }
    }
}
