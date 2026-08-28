<?php

namespace app\modules\filemanager\components;

use Yii;
use yii\helpers\Url;
use Imagine\Image\Box;
use yii\imagine\Image;
use yii\base\Component;
use yii\web\UploadedFile;
use kartik\file\FileInput;
use yii\helpers\BaseFileHelper;
use app\modules\filemanager\models\Uploads;

// รวม function ตที่ใช้งานบ่อยๆ
class FileManagerHelper extends Component
{

    const UPLOAD_FOLDER = 'fileupload';

    public static function getUploadPath()
    {
        return Yii::getAlias('@app') . '/modules/filemanager/' . self::UPLOAD_FOLDER . '/';
    }

    public static function FileUpload($ref, $name = null,$view = false)
    {
        list($initialPreview, $initialPreviewConfig) = self::getInitialPreview($ref, $name);
        // ตรวจสอบเงื่อนไข ถ้า $view เป็น 'tree' ให้เก็บค่า false ไว้ในตัวแปร

        return FileInput::widget([
            'name' => 'upload_ajax[]',
            'options' => ['multiple' => true, 'accept' => '*'],
            'pluginOptions' => [
                'showPreview' => true,
                'overwriteInitial' => true,
                'initialPreviewShowDelete' => !$view,
                'initialPreviewAsData' => true,
                'initialPreview' => $initialPreview,
                'initialPreviewConfig' => $initialPreviewConfig,
                'initialPreviewDownloadUrl' => Url::to(['@web/visit/{filename}']),
                'uploadUrl' => Url::to(['/filemanager/uploads/upload-ajax']),
                'uploadExtraData' => [
                    'ref' => $ref,
                    'name' => $name,
                ],
                'maxFileCount' => 100,
                // --- ควบคุมปุ่มหลัก ---
                'showBrowse' => !$view, // ถ้า $view เป็น true จะกลายเป็น false (ซ่อน)
                'showUpload' => !$view, // ถ้า $view เป็น true จะกลายเป็น false (ซ่อน)
                'showRemove' => !$view, // ซ่อนปุ่มลบทั้งหมด
                'showCaption' => true, // ซ่อนแถบชื่อไฟล์

                'fileActionSettings' => [
                    'showDelete' =>!$view,
                    'showRemove' => !$view, // ซ่อนปุ่มถังขยะรายไฟล์
                    'showDrag'   => true, // ซ่อนปุ่มลากสลับตำแหน่ง
                    'showZoom'   => true,   // ปุ่มแว่นขยายให้เปิดไว้เสมอแม้จะ view อย่างเดียว
                    'showDownload' => true, // ปุ่มดาวน์โหลดเปิดไว้เสมอ
                ],
                'previewFileIconSettings' => [
                    'doc' => '<i class="fas fa-file-word text-primary"></i>',
                    'docx' => '<i class="fa-regular fa-file-word"></i>',
                    'xls' => '<i class="fas fa-file-excel text-success"></i>',
                    'ppt' => '<i class="fas fa-file-powerpoint text-danger"></i>',
                    'pdf' => '<i class="fas fa-file-pdf text-danger"></i>',
                    'zip' => '<i class="fas fa-file-archive text-muted"></i>',
                    'htm' => '<i class="fas fa-file-code text-info"></i>',
                    'txt' => '<i class="fas fa-file-alt text-info"></i>',
                    'mov' => '<i class="fas fa-file-video text-warning"></i>',
                    'mp3' => '<i class="fas fa-file-audio text-warning"></i>',
                    'jpg' => '<i class="fas fa-file-image text-danger"></i>',
                    'gif' => '<i class="fas fa-file-image text-muted"></i>',
                    'png' => '<i class="fas fa-file-image text-primary"></i>',
                ],
            ],
        ]);
    }

    public static function Uploads($isAjax = false)
    {
        if (Yii::$app->request->isPost) {
            $images = UploadedFile::getInstancesByName('upload_ajax');
            if ($images) {
                if ($isAjax === true) {
                    $ref = Yii::$app->request->post('ref');
                    $name = Yii::$app->request->post('name');
                } else {
                    $Uploads = Yii::$app->request->post();
                    $ref = $Uploads['ref'];
                    $name = $Uploads['name'];
                }
                self::CreateDir($ref);
                $a = [];
                foreach ($images as $file) {
                    $fileName = $file->baseName . '.' . $file->extension;
                    $realFileName = md5($file->baseName . time()) . '.' . $file->extension;
                    $savePath = self::getUploadPath() . '/' . $ref . '/' . $realFileName;
                    $a[] = $savePath;
                    if ($file->saveAs($savePath)) {
                        // if (self::isImage(Url::base(true) . '/' . $savePath)) {
                        if (self::isImage($savePath)) {
                            self::createThumbnail($ref, $realFileName);
                        }

                        $model = new Uploads;
                        $model->ref = $ref;
                        $model->name = $name;
                        $model->file_name = $fileName;
                        $model->real_filename = $realFileName;
                        $model->type = self::checkFileType($file->extension);
                        $model->save(false);

                        if ($isAjax === true) {
                            return ['success' => 'true'];
                        }
                    } else {
                        if ($isAjax === true) {
                            return ['success' => 'false', 'eror' => $file->error];
                        }
                    }
                }
            }
        }
    }

    public static function UploadsSingle($isAjax = false)
    {
        if (Yii::$app->request->isPost) {
            $name = Yii::$app->request->post('name');
            $file = UploadedFile::getInstanceByName($name);

            if (!$file) {
                return [
                    'success' => 'false',
                    'error' => 'No file uploaded or file key mismatched.'
                ];
            }

            if ($isAjax === true) {
                $ref = Yii::$app->request->post('ref');
            } else {
                $Uploads = Yii::$app->request->post();
                $ref = $Uploads['ref'];
                $name = $Uploads['name'];
            }
            self::CreateDir($ref);

            $fileName = $file->baseName . '.' . $file->extension;
            $realFileName = md5($file->baseName . time()) . '.' . $file->extension;
            $savePath = self::getUploadPath() . $ref . '/' . $realFileName;
            if ($file->saveAs($savePath)) {

                if (self::isImage($savePath)) {
                    try {
                        self::createThumbnail($ref, $realFileName);
                    } catch (\Throwable $e) {
                        Yii::error("Failed to create thumbnail: " . $e->getMessage());
                    }
                }
                
                try {
                    $checkOld = Uploads::find()->where(['ref' => $ref, 'name' => $name])->one();
                    if ($checkOld) {
                        self::Deletefile($checkOld->id);
                    }

                    $model = new Uploads;
                    $model->ref = $ref;
                    $model->name = $name;
                    $model->file_name = $fileName;
                    $model->real_filename = $realFileName;
                    $model->type = self::checkFileType($file->extension);
                    if (!$model->save(false)) {
                        return [
                            'success' => 'false',
                            'error' => 'Failed to save to database. Errors: ' . json_encode($model->getErrors())
                        ];
                    }

                    return [
                        'success' => 'true',
                        'data' => $model,
                        'img' => self::getImg($model->id),
                    ];
                } catch (\Throwable $e) {
                    return [
                        'success' => 'false',
                        'error' => 'Database error: ' . $e->getMessage()
                    ];
                }
            } else {
                return [
                    'success' => 'false',
                    'error' => 'Failed to write file to disk. Please check directory permissions.'
                ];
            }
        }
        return [
            'success' => 'false',
            'error' => 'Invalid request method.'
        ];
    }

    /**
     * บันทึกไฟล์ PDF ที่อัปโหลดลงโฟลเดอร์ filemanager (ใช้จาก request หรือเรียกโดยตรง).
     * ถ้ามี ref+name เดิมอยู่จะลบของเดิมแล้วสร้างใหม่.
     *
     * @param UploadedFile $file ไฟล์จาก getInstanceByName
     * @param string $ref โฟลเดอร์ใน fileupload เช่น pdf_templates
     * @param string $name ชื่อ slot เช่น template_123
     * @return Uploads|null โมเดล Uploads ที่สร้างใหม่ หรือ null ถ้าล้มเหลว
     */
    public static function savePdfFile(UploadedFile $file, string $ref, string $name): ?Uploads
    {
        if (!$file || strtolower($file->extension) !== 'pdf') {
            return null;
        }
        self::CreateDir($ref);
        $fileName = $file->baseName . '.' . $file->extension;
        $realFileName = md5($file->baseName . time()) . '.' . $file->extension;
        $savePath = self::getUploadPath() . $ref . '/' . $realFileName;

        if (!$file->saveAs($savePath)) {
            return null;
        }
        $pdfVersion = null;
        $handle = fopen($savePath, 'rb');
        if ($handle) {
            $header = fread($handle, 20);
            if (preg_match('/%PDF-(\d\.\d)/', $header, $matches)) {
                $pdfVersion = floatval($matches[1]);
            }
            fclose($handle);
        }
        if ($pdfVersion !== null && $pdfVersion > 1.4) {
            $convertedFile = self::getUploadPath() . $ref . '/converted_' . $realFileName;
            $cmd = "gs -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dNOPAUSE -dQUIET -dBATCH -sOutputFile=" . escapeshellarg($convertedFile) . " " . escapeshellarg($savePath);
            exec($cmd, $output, $resultCode);
            if ($resultCode === 0 && file_exists($convertedFile)) {
                @unlink($savePath);
                rename($convertedFile, $savePath);
            }
        }
        $checkOld = Uploads::find()->where(['ref' => $ref, 'name' => $name])->one();
        if ($checkOld) {
            self::Deletefile($checkOld->id);
        }
        $model = new Uploads;
        $model->ref = $ref;
        $model->name = $name;
        $model->file_name = $fileName;
        $model->real_filename = $realFileName;
        $model->type = 'pdf';
        $model->save(false);
        return $model;
    }

    public static function UploadPdf($isAjax = false)
    {
        if (Yii::$app->request->isPost) {
            $name = Yii::$app->request->post('name');
            $file = UploadedFile::getInstanceByName($name);

            if ($isAjax === true) {
                $ref = Yii::$app->request->post('ref');
            } else {
                $Uploads = Yii::$app->request->post();
                $ref = $Uploads['ref'];
                $name = $Uploads['name'];
            }

            if ($file && strtolower($file->extension) === 'pdf') {
                $model = self::savePdfFile($file, $ref, $name);
                if ($model) {
                    return [
                        'success' => 'true',
                        'data' => $model,
                        'file_url' => Url::to(['/filemanager/uploads/show', 'id' => $model->id], true),
                    ];
                }
            }
            return [
                'success' => 'false',
                'error' => 'Only PDF files are allowed.',
            ];
        }
    }

    public static function CreateDir($folderName)
    {
        if ($folderName != null) {
            $basePath = self::getUploadPath();
            if (BaseFileHelper::createDirectory($basePath . $folderName, 0777)) {
                BaseFileHelper::createDirectory($basePath . $folderName . '/thumbnail', 0777);
            }
        }
        return;
    }

    public static function removeUploadDir($dir)
    {
        BaseFileHelper::removeDirectory(self::getUploadPath() . $dir);
    }

    public static function isImage($file_path)
    {
        if (!file_exists($file_path)) {
            return false;
        }
        if (function_exists('exif_imagetype')) {
            $type = @exif_imagetype($file_path);
            if ($type !== false) {
                return $type;
            }
        }
        $info = @getimagesize($file_path);
        return $info ? $info[2] : false;
    }

    public static function createThumbnail($folderName, $fileName, $width = 10)
    {
        $uploadPath = self::getUploadPath() . '/' . $folderName . '/';
        $savePath = $uploadPath . 'thumbnail/';
        $file = $uploadPath . $fileName;
        return Image::frame($file, 0, '666', 0)
            ->thumbnail(new Box(850, 850))
            ->rotate(0)
            ->save($savePath . $fileName, ['jpeg_quality' => 100]);

        return;
    }
    public static function getInitialPreview($ref, $name)
    {
        if ($name) {
            $datas = Uploads::find()->where(['ref' => $ref, 'name' => $name])->all();
        } else {
            $datas = Uploads::find()->where(['ref' => $ref])->all();
        }
        $initialPreview = [];
        $initialPreviewConfig = [];
        foreach ($datas as $key => $value) {
            array_push($initialPreview, self::getTemplatePreview($value));
            array_push($initialPreviewConfig, [
                'type' => $value->type,
                'caption' => $value->file_name,
                'width' => '120px',
                'size' => 827000,
                'url' => Url::to(['/filemanager/uploads/deletefile-ajax']),
                'key' => $value->id,
                'showDelete' => true, // เพิ่มบรรทัดนี้ลงไปใน Config รายไฟล์
            ]);
        }
        return [$initialPreview, $initialPreviewConfig];
    }

    public static function getTemplatePreview(Uploads $model)
    {
        return Url::to(['/filemanager/uploads/show', 'id' => $model->id]);
    }

    public static function Deletefile($id)
    {
        $model = Uploads::findOne(['id' => $id]);
        if ($model !== null) {
            $filename = self::getUploadPath() . $model->ref . '/' . $model->real_filename;
            $thumbnail = self::getUploadPath() . $model->ref . '/thumbnail/' . $model->real_filename;
            if ($model->delete()) {
                @unlink($filename);
                @unlink($thumbnail);
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public static function checkFileType($type)
    {
        switch ($type) {
            case 'doc':
                return 'office';
                break;

            case 'docx':
                return 'gdocs';
                break;
            case 'xls':
                return 'office';
                break;
            case 'xlsx':
                return 'office';
                break;
            case 'pdf':
                return 'pdf';
                break;
            case 'mp4':
                return 'video';
                break;
            case 'tif':
                return 'gdocs';
                break;

            default:
                return 'image';
                break;
        }
    }

    public static function getImg($id = null)
    {
        $model = Uploads::findOne($id);
        if ($model) {
            $filename = $model->real_filename;
            $filepath = FileManagerHelper::getUploadPath() . $model->ref . '/thumbnail/' . $filename;
            if (file_exists($filepath)) {
                return Url::to(['/filemanager/uploads/show', 'id' => $model->id], true);
            } else {
                $filepath = FileManagerHelper::getUploadPath() . $model->ref . '/' . $filename;
                if (file_exists($filepath)) {
                    return Url::to(['/filemanager/uploads/show', 'id' => $model->id], true);
                } else {
                    return Yii::getAlias('@web') . '/img/placeholder-img.jpg';
                }
            }
        } else {
            return Yii::getAlias('@web') . '/img/placeholder-img.jpg';
        }
    }

    //การดึง File จาก Path โดยตรง
    public static function getFilePath($id = null)
{
    $model = Uploads::findOne($id);
    if ($model) {
        $filename = $model->real_filename;
        $basePath = FileManagerHelper::getUploadPath() . $model->ref . '/';
        
        $thumbPath = $basePath . 'thumbnail/' . $filename;
        $fullPath  = $basePath . $filename;

        // 1. ลองหา thumbnail ก่อน
        if (file_exists($thumbPath)) {
            return $thumbPath;
        } 
        // 2. ถ้าไม่มี thumb ให้เอาไฟล์จริง
        if (file_exists($fullPath)) {
            return $fullPath;
        }
    }
    // 3. ถ้าไม่พบไฟล์เลย คืนค่า null หรือ path ของรูป placeholder (ถ้าจำเป็น)
    return null; 
}


    //ดึง file จาก ref
    public static function getFileFormRef($ref)
    {
        $fileUpload = Uploads::findOne(['ref' => $ref]);
        if ($fileUpload) {
            $filename = $fileUpload->real_filename;
            $filepath = FileManagerHelper::getUploadPath() . $fileUpload->ref . '/' . $filename;
            return $filepath;
        } else {
            return false;
        }
    }

    //ตรวจสอบที่ยังไม่มี Thempnail และให้ลร้าง
    public static function RecheckThumbnail()
    {
        $models = Uploads::find()->all();
        foreach ($models as $model) {
            $filepath = self::getUploadPath() . $model->ref . '/' . $model->real_filename;
            if (self::isImage($filepath)) {

                // return  self::createThumbnail($ref, $realFileName);
            }
        }
    }
    //แสดงรูปภาพตาม ref
    public static function listViewImages($ref)
    {
        // 1. ค้นหาข้อมูล
        $uploads = Uploads::find()->where(['ref' => $ref])->all();
        return Yii::$app->view->render('@app/modules/filemanager/views/uploads/list_images', [
            'uploads' => $uploads,
        ]);
    }
}
