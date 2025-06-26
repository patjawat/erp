<?php

namespace app\modules\gdoc\components;

use app\models\Categorise;
use Yii;
use Google_Client;
use Google_Service_Docs;
use Google_Service_Docs_BatchUpdateDocumentRequest;
use Google_Service_Drive;
use Google_Service_Drive_DriveFile;

class GoogleService
{
    private $client;
    private $docsService;
    private $driveService;

    public function __construct()
    {
        $this->client = new Google_Client();
        $this->client->setApplicationName('Yii2 Google Docs');
        $this->client->setScopes([
            Google_Service_Docs::DOCUMENTS,
            Google_Service_Drive::DRIVE,
        ]);
        $this->client->setAuthConfig(Yii::getAlias('@app/modules/gdoc/credentials.json'));
        $this->client->setAccessType('offline');

        $this->docsService = new Google_Service_Docs($this->client);
        $this->driveService = new Google_Service_Drive($this->client);
    }

    private function getDefaultDriveId()
    {
        $drive = Categorise::findOne(['name' => 'google', 'code' => 'gdrive']);
        if ($drive) {
            return $drive->data_json['drive_id'] ?? null;
        } else {
            return null;
        }
    }

    public function createDocumentFromTemplate($templateId, $replacements = [], $folderId = null, $customFileName = null)
    {
        $fileName = $customFileName ?? ('New Document ' . date('Y-m-d_H-i-s'));

        $copyMeta = new Google_Service_Drive_DriveFile([
            'name' => $fileName,
        ]);

        if (!$folderId) {
            $folderId = $this->getDefaultDriveId();
        }

        if ($folderId) {
            $copyMeta->setParents([$folderId]);
        }

        $copiedFile = $this->driveService->files->copy($templateId, $copyMeta);
        $newDocId = $copiedFile->id;

        $requests = [];
        foreach ($replacements as $key => $value) {
            $requests[] = [
                'replaceAllText' => [
                    'containsText' => [
                        'text' => '{{' . $key . '}}',
                        'matchCase' => false,
                    ],
                    'replaceText' => $value,
                ],
            ];
        }

        if (!empty($requests)) {
            $this->docsService->documents->batchUpdate($newDocId, new \Google_Service_Docs_BatchUpdateDocumentRequest([
                'requests' => $requests,
            ]));
        }

        return $newDocId;
    }

    public function shareFileToAnyone($fileId)
    {
        try {
            $permission = new \Google_Service_Drive_Permission([
                'type' => 'anyone',
                'role' => 'reader',
            ]);

            $this->driveService->permissions->create($fileId, $permission, [
                'sendNotificationEmail' => false,
            ]);
            
            // รอสักครู่ให้ permission มีผล
            sleep(1);
            
            return true;
        } catch (\Exception $e) {
            Yii::error("Error sharing file {$fileId}: " . $e->getMessage());
            return false;
        }
    }

    public function uploadImageToDrive($imagePath, $fileName = null, $folderId = null)
    {
        if (!file_exists($imagePath)) {
            throw new \Exception("Image file not found: {$imagePath}");
        }

        $mimeType = mime_content_type($imagePath);
        if (strpos($mimeType, 'image/') !== 0) {
            throw new \Exception("File is not an image: {$imagePath}");
        }

        $fileMetadata = new \Google_Service_Drive_DriveFile([
            'name' => $fileName ?? basename($imagePath),
            'mimeType' => $mimeType,
        ]);

        if ($folderId) {
            $fileMetadata->setParents([$folderId]);
        }

        $file = $this->driveService->files->create($fileMetadata, [
            'data' => file_get_contents($imagePath),
            'mimeType' => $mimeType,
            'uploadType' => 'multipart',
            'fields' => 'id,webViewLink,webContentLink',
        ]);

        // แชร์ public และรอให้มีผล
        $this->shareFileToAnyone($file->id);
        
        return $file->id;
    }

    // ปรับปรุงฟังก์ชันแทรกรูปภาพให้ทำงานได้ดีขึ้น
    public function replaceMultipleImages($docId, array $images)
    {
        try {
            // รีเฟรชเอกสารก่อนแต่ละครั้ง
            $document = $this->docsService->documents->get($docId);
            
            foreach ($images as $placeholder => $imageUrl) {
                // แทนที่ทีละรูป และรีเฟรชเอกสารหลังจากแต่ละรูป
                $this->replaceImagePlaceholder($docId, $placeholder, $imageUrl);
                sleep(1); // รอให้การอัปเดตมีผล
            }
            
            return true;
        } catch (\Exception $e) {
            Yii::error("Error replacing images: " . $e->getMessage());
            return false;
        }
    }

    // ฟังก์ชันแทนที่ placeholder รูปภาพทีละตัว
    private function replaceImagePlaceholder($docId, $placeholder, $imageUrl)
    {
        // รีเฟรชเอกสารเพื่อให้ได้ข้อมูลล่าสุด
        $document = $this->docsService->documents->get($docId);
        $content = $document->getBody()->getContent();

        $placeholderText = '{{' . $placeholder . '}}';
        $found = false;
        $startIndex = null;
        $endIndex = null;

        // ค้นหา placeholder ในเอกสาร
        foreach ($content as $element) {
            if ($element->getParagraph()) {
                $elements = $element->getParagraph()->getElements();
                foreach ($elements as $e) {
                    $textRun = $e->getTextRun();
                    if ($textRun) {
                        $text = $textRun->getContent();
                        $pos = strpos($text, $placeholderText);
                        if ($pos !== false) {
                            $startIndex = $e->getStartIndex() + $pos;
                            $endIndex = $startIndex + strlen($placeholderText);
                            $found = true;
                            break 2;
                        }
                    }
                }
            }
        }

        if ($found && $endIndex > $startIndex) {
            $requests = [];
            
            // ลบข้อความ placeholder ก่อน
            $requests[] = [
                'deleteContentRange' => [
                    'range' => [
                        'startIndex' => $startIndex,
                        'endIndex' => $endIndex,
                    ],
                ],
            ];

            // แทรกรูปภาพ
            $requests[] = [
                'insertInlineImage' => [
                    'location' => [
                        'index' => $startIndex,
                    ],
                    'uri' => $imageUrl,
                    'objectSize' => [
                        'height' => ['magnitude' => 100, 'unit' => 'PT'],
                        'width' => ['magnitude' => 100, 'unit' => 'PT'],
                    ],
                ],
            ];

            $batchUpdateRequest = new \Google_Service_Docs_BatchUpdateDocumentRequest([
                'requests' => $requests,
            ]);
            
            $this->docsService->documents->batchUpdate($docId, $batchUpdateRequest);
            
            Yii::info("Successfully replaced placeholder {{$placeholder}} with image");
        } else {
            Yii::warning("Placeholder {{$placeholder}} not found in document");
        }
    }

    // ฟังก์ชันใหม่: แทรกรูปที่ตำแหน่งเฉพาะ
    public function insertImageAtPosition($docId, $imageUrl, $index = 1, $width = 100, $height = 100)
    {
        try {
            $requests = [
                [
                    'insertInlineImage' => [
                        'location' => [
                            'index' => $index,
                        ],
                        'uri' => $imageUrl,
                        'objectSize' => [
                            'height' => ['magnitude' => $height, 'unit' => 'PT'],
                            'width' => ['magnitude' => $width, 'unit' => 'PT'],
                        ],
                    ],
                ],
            ];

            $this->docsService->documents->batchUpdate($docId, new \Google_Service_Docs_BatchUpdateDocumentRequest([
                'requests' => $requests,
            ]));
            
            return true;
        } catch (\Exception $e) {
            Yii::error("Error inserting image: " . $e->getMessage());
            return false;
        }
    }

    // ฟังก์ชันตรวจสอบว่ารูปภาพสามารถเข้าถึงได้หรือไม่
    public function validateImageUrl($imageUrl)
    {
        $headers = @get_headers($imageUrl);
        return $headers && strpos($headers[0], '200') !== false;
    }

    public function exportToPDF($docId)
    {
        return $this->driveService->files->export($docId, 'application/pdf', [
            'alt' => 'media'
        ])->getBody()->getContents();
    }

    public function uploadPDFToDrive($pdfContent, $fileName, $folderId = null)
    {
        $fileMetadata = new Google_Service_Drive_DriveFile([
            'name' => $fileName,
            'mimeType' => 'application/pdf',
        ]);

        if ($folderId) {
            $fileMetadata->setParents([$folderId]);
        }

        return $this->driveService->files->create($fileMetadata, [
            'data' => $pdfContent,
            'mimeType' => 'application/pdf',
            'uploadType' => 'multipart',
        ]);
    }
}