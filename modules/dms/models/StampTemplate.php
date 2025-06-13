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

/**
 * StampTemplate เป็น model สำหรับจัดการเทมเพลต stamp
 */
class StampTemplate extends Model
{
    public $text;
    public $fontSize;
    public $color;
    public $fontFamily;
    public $isBold;
    public $isItalic;
    public $hasBackground;
    public $backgroundColor;
    public $hasBorder;
    public $borderColor;
    public $borderWidth;
    public $rotation;
    public $opacity;
    public $x;
    public $y;
    public $page;

    /**
     * @return array กฎการ validation
     */
    public function rules()
    {
        return [
            [['text'], 'required'],
            [['text'], 'string', 'max' => 255],
            [['fontSize'], 'integer', 'min' => 8, 'max' => 72],
            [['color', 'backgroundColor', 'borderColor'], 'match', 'pattern' => '/^#[0-9A-Fa-f]{6}$/'],
            [['fontFamily'], 'string', 'max' => 50],
            [['isBold', 'isItalic', 'hasBackground', 'hasBorder'], 'boolean'],
            [['borderWidth'], 'integer', 'min' => 1, 'max' => 10],
            [['rotation'], 'integer', 'min' => -360, 'max' => 360],
            [['opacity'], 'integer', 'min' => 10, 'max' => 100],
            [['x', 'y'], 'number'],
            [['page'], 'integer', 'min' => 1],
        ];
    }

    /**
     * @return array ค่าเริ่มต้น
     */
    public function init()
    {
        parent::init();
        
        $this->text = 'APPROVED';
        $this->fontSize = 14;
        $this->color = '#FF0000';
        $this->fontFamily = 'DejaVu Sans';
        $this->isBold = true;
        $this->isItalic = false;
        $this->hasBackground = false;
        $this->backgroundColor = '#FFFFFF';
        $this->hasBorder = true;
        $this->borderColor = '#FF0000';
        $this->borderWidth = 2;
        $this->rotation = 0;
        $this->opacity = 100;
        $this->x = 100;
        $this->y = 100;
        $this->page = 1;
    }

    /**
     * @return array ชื่อ attribute
     */
    public function attributeLabels()
    {
        return [
            'text' => 'ข้อความ',
            'fontSize' => 'ขนาดตัวอักษร',
            'color' => 'สีตัวอักษร',
            'fontFamily' => 'ฟอนต์',
            'isBold' => 'ตัวหนา',
            'isItalic' => 'ตัวเอียง',
            'hasBackground' => 'มีพื้นหลัง',
            'backgroundColor' => 'สีพื้นหลัง',
            'hasBorder' => 'มีกรอบ',
            'borderColor' => 'สีกรอบ',
            'borderWidth' => 'ความหนากรอบ',
            'rotation' => 'การหมุน (องศา)',
            'opacity' => 'ความทึบแสง (%)',
            'x' => 'ตำแหน่ง X',
            'y' => 'ตำแหน่ง Y',
            'page' => 'หน้า',
        ];
    }

    /**
     * สร้าง CSS style สำหรับ preview
     */
    public function getPreviewStyle()
    {
        $styles = [];
        
        $styles[] = 'color: ' . $this->color;
        $styles[] = 'font-size: ' . $this->fontSize . 'px';
        $styles[] = 'font-family: ' . $this->fontFamily;
        
        if ($this->isBold) {
            $styles[] = 'font-weight: bold';
        }
        
        if ($this->isItalic) {
            $styles[] = 'font-style: italic';
        }
        
        if ($this->hasBackground) {
            $styles[] = 'background-color: ' . $this->backgroundColor;
            $styles[] = 'padding: 2px 4px';
        }
        
        if ($this->hasBorder) {
            $styles[] = 'border: ' . $this->borderWidth . 'px solid ' . $this->borderColor;
        }
        
        if ($this->rotation != 0) {
            $styles[] = 'transform: rotate(' . $this->rotation . 'deg)';
        }
        
        $styles[] = 'opacity: ' . ($this->opacity / 100);
        $styles[] = 'position: absolute';
        $styles[] = 'left: ' . $this->x . 'px';
        $styles[] = 'top: ' . $this->y . 'px';
        $styles[] = 'cursor: move';
        $styles[] = 'user-select: none';
        $styles[] = 'white-space: nowrap';
        
        return implode('; ', $styles);
    }

    /**
     * ตัวอย่างเทมเพลต stamp ที่มีให้เลือก
     */
    public static function getPresetTemplates()
    {
        return [
            'approved' => [
                'text' => 'APPROVED',
                'color' => '#00AA00',
                'fontSize' => 16,
                'isBold' => true,
                'hasBorder' => true,
                'borderColor' => '#00AA00',
            ],
            'rejected' => [
                'text' => 'REJECTED',
                'color' => '#FF0000',
                'fontSize' => 16,
                'isBold' => true,
                'hasBorder' => true,
                'borderColor' => '#FF0000',
            ],
            'pending' => [
                'text' => 'PENDING',
                'color' => '#FF8800',
                'fontSize' => 14,
                'isBold' => true,
                'hasBorder' => true,
                'borderColor' => '#FF8800',
            ],
            'confidential' => [
                'text' => 'CONFIDENTIAL',
                'color' => '#FF0000',
                'fontSize' => 12,
                'isBold' => true,
                'rotation' => -45,
                'hasBackground' => true,
                'backgroundColor' => '#FFEEEE',
            ],
            'draft' => [
                'text' => 'DRAFT',
                'color' => '#888888',
                'fontSize' => 20,
                'isBold' => true,
                'rotation' => -30,
                'opacity' => 50,
            ],
            'copy' => [
                'text' => 'COPY',
                'color' => '#0066CC',
                'fontSize' => 14,
                'isBold' => true,
                'hasBorder' => true,
                'borderColor' => '#0066CC',
            ],
        ];
    }
}