<?php

// Helper class สำหรับจัดการตราประทับ
namespace app\components;

use Yii;
use TCPDF;
use Exception;

class StampHelper
{
    /**
     * สร้างตราประทับแบบกำหนดเอง
     */
    public static function createCustomStamp($options = [])
    {
        $defaults = [
            'width' => 85,
            'height' => 65,
            'fontSize' => 9,
            'borderWidth' => 0.5,
            'backgroundColor' => [255, 255, 255], // RGB
            'textColor' => [0, 0, 0], // RGB
            'borderColor' => [0, 0, 0], // RGB
        ];
        
        return array_merge($defaults, $options);
    }
    
    /**
     * ตรวจสอบความถูกต้องของไฟล์ PDF
     */
    public static function validatePdfFile($filePath)
    {
        if (!file_exists($filePath)) {
            throw new Exception('ไม่พบไฟล์ที่ระบุ');
        }
        
        $fileInfo = pathinfo($filePath);
        if (strtolower($fileInfo['extension']) !== 'pdf') {
            throw new Exception('ไฟล์ต้องเป็นนามสกุล .pdf เท่านั้น');
        }
        
        $fileSize = filesize($filePath);
        $maxSize = Yii::$app->params['upload']['maxSize'];
        if ($fileSize > $maxSize) {
            throw new Exception('ไฟล์มีขนาดใหญ่เกิน ' . self::formatBytes($maxSize));
        }
        
        // ตรวจสอบว่าเป็นไฟล์ PDF จริง
        $handle = fopen($filePath, 'r');
        $header = fread($handle, 4);
        fclose($handle);
        
        if ($header !== '%PDF') {
            throw new Exception('ไฟล์ไม่ใช่ PDF ที่ถูกต้อง');
        }
        
        return true;
    }
    
    /**
     * แปลงขนาดไฟล์เป็นรูปแบบที่อ่านได้
     */
    public static function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
    
    /**
     * สร้างเลขที่เอกสารอัตโนมัติ
     */
    public static function generateDocumentNumber($format = 'Y/####')
    {
        $year = date('Y') + 543; // ปี พ.ศ.
        
        // หาเลขที่เอกสารล่าสุดในปีนี้
        $lastDoc = \app\models\Document::find()
            ->where(['like', 'document_number', $year])
            ->orderBy('id DESC')
            ->one();
            
        if ($lastDoc) {
            // แยกเลขที่ออกจากรูปแบบ
            preg_match('/(\d+)\/(\d+)/', $lastDoc->document_number, $matches);
            $lastNumber = isset($matches[2]) ? (int)$matches[2] : 0;
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        // สร้างเลขที่ใหม่ตามรูปแบบ
        $docNumber = str_replace('Y', $year, $format);
        $docNumber = str_replace('####', sprintf('%04d', $newNumber), $docNumber);
        
        return $docNumber;
    }
    
    /**
     * แปลงวันที่เป็นรูปแบบไทย
     */
    public static function formatThaiDate($date, $format = 'd/m/Y')
    {
        $thaiMonths = [
            1 => 'ม.ค.', 2 => 'ก.พ.', 3 => 'มี.ค.', 4 => 'เม.ย.',
            5 => 'พ.ค.', 6 => 'มิ.ย.', 7 => 'ก.ค.', 8 => 'ส.ค.',
            9 => 'ก.ย.', 10 => 'ต.ค.', 11 => 'พ.ย.', 12 => 'ธ.ค.'
        ];
        
        $dateTime = new \DateTime($date);
        $day = $dateTime->format('j');
        $month = $thaiMonths[(int)$dateTime->format('n')];
        $year = $dateTime->format('Y') + 543;
        
        return "$day $month $year";
    }
    
    /**
     * ตรวจสอบสิทธิ์การเข้าถึงไฟล์
     */
    public static function checkFileAccess($filePath, $userId = null)
    {
        if (!file_exists($filePath)) {
            return false;
        }
        
        // ตรวจสอบสิทธิ์ตามความต้องการของระบบ
        // ตัวอย่างนี้ให้สิทธิ์ทุกคน
        return true;
    }
}