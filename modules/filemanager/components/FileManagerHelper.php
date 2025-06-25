<?php

namespace app\modules\filemanager\components;

use Yii;
use yii\helpers\Url;
use Imagine\Image\Box;
use yii\imagine\Image;
use yii\base\Component;
use yii\web\UploadedFile;
use kartik\file\FileInput;
use yii\helpers\FileHelper;
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

    public static function FileUpload($ref, $name = null)
    {
        list($initialPreview, $initialPreviewConfig) = self::getInitialPreview($ref, $name);
        return FileInput::widget([
            'name' => 'upload_ajax[]',
            'options' => ['multiple' => true, 'accept' => '*'],
            'pluginOptions' => [
                 'showPreview' => true,
                'overwriteInitial' => false,
                'initialPreviewShowDelete' => true,
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
                'previewFileIconSettings' => [
                    // configure your icon file extensions
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
            // return $name = Yii::$app->request->post('name');
            $name = Yii::$app->request->post('name');
            $file = UploadedFile::getInstanceByName($name);

            // if ($images) {
            if ($isAjax === true) {
                // return 'file component';
                $ref = Yii::$app->request->post('ref');
                // return Yii::$app->request->post();
            } else {
                $Uploads = Yii::$app->request->post();
                $ref = $Uploads['ref'];
                $name = $Uploads['name'];
            }
            self::CreateDir($ref);

            // foreach ($images as $file) {
            $fileName = $file->baseName . '.' . $file->extension;
            $realFileName = md5($file->baseName . time()) . '.' . $file->extension;
            $savePath = self::getUploadPath() . $ref . '/' . $realFileName;
            if ($file->saveAs($savePath)) {

                if (self::isImage($savePath)) {
                    self::createThumbnail($ref, $realFileName);
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
                $model->type = self::checkFileType($file->extension);
                $model->save(false);

                return [
                    'success' => 'true',
                    'data' => $model,
                    'img' => self::getImg($model->id),
                ];

            }
            //     } else {
            //         if ($isAjax === true) {
            //             return ['success' => 'false', 'eror' => $file->error];
            //         }
            //     }

            // }
            // }
        }
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
            self::CreateDir($ref);

            if ($file && strtolower($file->extension) === 'pdf') {
                $fileName = $file->baseName . '.' . $file->extension;
                $realFileName = md5($file->baseName . time()) . '.' . $file->extension;
                $savePath = self::getUploadPath() . $ref . '/' . $realFileName;

                if ($file->saveAs($savePath)) {
                    // ตรวจสอบ PDF version
                    $pdfVersion = null;
                    $handle = fopen($savePath, 'rb');
                    if ($handle) {
                        $header = fread($handle, 20);
                        if (preg_match('/%PDF-(\d\.\d)/', $header, $matches)) {
                            $pdfVersion = floatval($matches[1]);
                        }
                        fclose($handle);
                    }

                    // ถ้า version > 1.4 ให้แปลงไฟล์
                    if ($pdfVersion !== null && $pdfVersion > 1.4) {
                        $convertedFile = self::getUploadPath() . $ref . '/converted_' . $realFileName;
                        $cmd = "gs -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dNOPAUSE -dQUIET -dBATCH -sOutputFile=" . escapeshellarg($convertedFile) . " " . escapeshellarg($savePath);
                        exec($cmd, $output, $resultCode);
                        if ($resultCode === 0 && file_exists($convertedFile)) {
                            // ลบไฟล์เดิมและเปลี่ยนชื่อไฟล์ใหม่
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

                    return [
                        'success' => 'true',
                        'data' => $model,
                        'file_url' => Url::to(['/filemanager/uploads/show', 'id' => $model->id], true),
                    ];
                }
            } else {
                return [
                    'success' => 'false',
                    'error' => 'Only PDF files are allowed.',
                ];
            }
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
        return file_exists($file_path) ? exif_imagetype($file_path) : false;
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

    //ตรวจสอบที่ยังไม่มี Thempnail และให้ลร้าง
    public static function RecheckThumbnail()
    {
        $models = Uploads::find()->all();
        foreach ($models as $model) {
            $filepath = self::getUploadPath() . $model->ref . '/' . $filename;
            if (self::isImage($filepath)) {

                // return  self::createThumbnail($ref, $realFileName);
            }
        }
    }
}
