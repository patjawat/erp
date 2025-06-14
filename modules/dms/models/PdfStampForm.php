<?php

namespace app\modules\dms\models;

use yii\base\Model;
use yii\web\UploadedFile;

/**
 * PdfStampForm เป็น model สำหรับจัดการฟอร์มอัพโลด PDF
 */
class PdfStampForm extends Model
{
    /**
     * @var UploadedFile
     */
    public $pdfFile;

    /**
     * @return array กฎการ validation
     */
    public function rules()
    {
        return [
            [['pdfFile'], 'file', 
                'skipOnEmpty' => false,
                'extensions' => 'pdf',
                'maxSize' => 10 * 1024 * 1024, // 10MB
                'tooBig' => 'ไฟล์ PDF มีขนาดใหญ่เกินไป ขนาดสูงสุดคือ 10MB',
                'wrongExtension' => 'อนุญาตให้อัพโหลดเฉพาะไฟล์ PDF เท่านั้น'
            ],
        ];
    }

    /**
     * @return array ชื่อ attribute
     */
    public function attributeLabels()
    {
        return [
            'pdfFile' => 'ไฟล์ PDF',
        ];
    }
}
