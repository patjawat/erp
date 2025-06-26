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

        // แทนที่ข้อความก่อน (ไม่รวมรูปภาพ)
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

    // เพิ่มฟังก์ชัน findPlaceholders ที่ขาดหายไป
    public function findPlaceholders($docId)
    {
        try {
            $document = $this->docsService->documents->get($docId);
            $content = $document->getBody()->getContent();
            $placeholders = [];

            foreach ($content as $element) {
                if ($element->getParagraph()) {
                    $elements = $element->getParagraph()->getElements();
                    foreach ($elements as $e) {
                        $textRun = $e->getTextRun();
                        if ($textRun) {
                            $text = $textRun->getContent();
                            // ค้นหา pattern {{placeholder}}
                            preg_match_all('/\{\{([^}]+)\}\}/', $text, $matches);
                            if (!empty($matches[0])) {
                                foreach ($matches[0] as $match) {
                                    if (!in_array($match, $placeholders)) {
                                        $placeholders[] = $match;
                                    }
                                }
                            }
                        }
                    }
                }
            }

            return $placeholders;
        } catch (\Exception $e) {
            Yii::error("Error finding placeholders: " . $e->getMessage());
            return [];
        }
    }

    // เพิ่มฟังก์ชัน insertImageAtPosition ที่ขาดหายไป
    public function insertImageAtPosition($docId, $imageUrl, $position, $width = 100, $height = 100)
    {
        try {
            $requests = [
                [
                    'insertInlineImage' => [
                        'location' => [
                            'index' => $position,
                        ],
                        'uri' => $imageUrl,
                        'objectSize' => [
                            'height' => ['magnitude' => $height, 'unit' => 'PT'],
                            'width' => ['magnitude' => $width, 'unit' => 'PT'],
                        ],
                    ],
                ],
            ];

            $batchUpdateRequest = new \Google_Service_Docs_BatchUpdateDocumentRequest([
                'requests' => $requests,
            ]);
            
            $this->docsService->documents->batchUpdate($docId, $batchUpdateRequest);
            return true;
        } catch (\Exception $e) {
            Yii::error("Error inserting image at position: " . $e->getMessage());
            return false;
        }
    }

    public function shareFileToAnyone($fileId)
    {
        try {
            // ลบ permission เก่าก่อน (ถ้ามี)
            try {
                $permissions = $this->driveService->permissions->listPermissions($fileId);
                foreach ($permissions->getPermissions() as $permission) {
                    if ($permission->getType() === 'anyone') {
                        $this->driveService->permissions->delete($fileId, $permission->getId());
                    }
                }
            } catch (\Exception $e) {
                // ไม่สำคัญถ้าลบไม่ได้
            }

            // เพิ่ม permission ใหม่
            $permission = new \Google_Service_Drive_Permission([
                'type' => 'anyone',
                'role' => 'reader',
                'allowFileDiscovery' => false, // เปลี่ยนเป็น false เพื่อความปลอดภัย
            ]);

            $this->driveService->permissions->create($fileId, $permission, [
                'sendNotificationEmail' => false,
                'supportsAllDrives' => true,
            ]);
            
            // รอให้ permission มีผล
            sleep(2);
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

        if (!$folderId) {
            $folderId = $this->getDefaultDriveId();
        }

        if ($folderId) {
            $fileMetadata->setParents([$folderId]);
        }

        $file = $this->driveService->files->create($fileMetadata, [
            'data' => file_get_contents($imagePath),
            'mimeType' => $mimeType,
            'uploadType' => 'multipart',
            'fields' => 'id,webViewLink,webContentLink',
            'supportsAllDrives' => true,
        ]);

        // แชร์ public และรอให้มีผล
        $this->shareFileToAnyone($file->id);
        
        return $file->id;
    }

    // ปรับปรุงฟังก์ชัน validateImageUrl
    public function validateImageUrl($imageUrl, $timeout = 10)
    {
        $urls = [$imageUrl];
        
        // เพิ่ม URL formats ต่างๆ
        if (preg_match('/id=([a-zA-Z0-9_-]+)/', $imageUrl, $matches)) {
            $fileId = $matches[1];
            $urls = [
                "https://drive.google.com/uc?id={$fileId}",
                "https://drive.google.com/uc?export=view&id={$fileId}",
                "https://drive.google.com/thumbnail?id={$fileId}&sz=w200",
                $imageUrl // original URL
            ];
        }

        foreach ($urls as $url) {
            $context = stream_context_create([
                'http' => [
                    'timeout' => $timeout,
                    'user_agent' => 'Mozilla/5.0 (compatible; GoogleDocsBot/1.0)',
                    'follow_location' => true,
                    'max_redirects' => 5,
                ]
            ]);
            
            $headers = @get_headers($url, 1, $context);
            if ($headers && (
                strpos($headers[0], '200') !== false || 
                strpos($headers[0], '302') !== false ||
                strpos($headers[0], '301') !== false
            )) {
                return $url;
            }
        }
        
        return false;
    }

    public function waitForFileReady($fileId, $maxWait = 30)
    {
        $waited = 0;
        $interval = 3;
        
        while ($waited < $maxWait) {
            try {
                // ตรวจสอบ permissions
                $permissions = $this->driveService->permissions->listPermissions($fileId);
                $hasPublicAccess = false;
                
                foreach ($permissions->getPermissions() as $permission) {
                    if ($permission->getType() === 'anyone') {
                        $hasPublicAccess = true;
                        break;
                    }
                }
                
                if ($hasPublicAccess) {
                    // สร้าง URL และทดสอบ
                    $testUrl = "https://drive.google.com/uc?id={$fileId}";
                    $validUrl = $this->validateImageUrl($testUrl, 5);
                    if ($validUrl) {
                        return $validUrl;
                    }
                }
                
            } catch (\Exception $e) {
                Yii::warning("Waiting for file {$fileId}: " . $e->getMessage());
            }
            
            sleep($interval);
            $waited += $interval;
        }
        
        // ถ้ารอไม่ไหว ให้คืน URL พื้นฐาน
        return "https://drive.google.com/uc?id={$fileId}";
    }

    // ปรับปรุงฟังก์ชัน replaceImagePlaceholder
    // เพิ่ม public method เพื่อเข้าถึง document
    public function getDocument($docId)
    {
        try {
            return $this->docsService->documents->get($docId);
        } catch (\Exception $e) {
            Yii::error("Error getting document: " . $e->getMessage());
            return null;
        }
    }

    // เพิ่ม method สำหรับหา placeholder index
    public function findPlaceholderPosition($docId, $placeholder)
    {
        try {
            $document = $this->getDocument($docId);
            if (!$document) {
                return null;
            }

            $content = $document->getBody()->getContent();
            $placeholderText = '{{' . $placeholder . '}}';

            foreach ($content as $element) {
                if ($element->getParagraph()) {
                    $elements = $element->getParagraph()->getElements();
                    foreach ($elements as $e) {
                        $textRun = $e->getTextRun();
                        if ($textRun) {
                            $text = $textRun->getContent();
                            $pos = strpos($text, $placeholderText);
                            if ($pos !== false) {
                                return [
                                    'startIndex' => $e->getStartIndex() + $pos,
                                    'endIndex' => $e->getStartIndex() + $pos + strlen($placeholderText),
                                    'found' => true
                                ];
                            }
                        }
                    }
                }
            }

            return ['found' => false];
        } catch (\Exception $e) {
            Yii::error("Error finding placeholder position: " . $e->getMessage());
            return null;
        }
    }

    
    public function replaceImagePlaceholder($docId, $placeholder, $imageUrl)
    {
        try {
            // ตรวจสอบ URL ก่อน
            $validUrl = $this->validateImageUrl($imageUrl, 5);
            if (!$validUrl) {
                // ถ้า URL ไม่ valid ให้ลองใช้ format อื่น
                if (preg_match('/id=([a-zA-Z0-9_-]+)/', $imageUrl, $matches)) {
                    $fileId = $matches[1];
                    $validUrl = "https://drive.google.com/uc?export=view&id={$fileId}";
                } else {
                    $validUrl = $imageUrl; // ใช้ URL เดิม
                }
            }

            // หาตำแหน่ง placeholder
            $position = $this->findPlaceholderPosition($docId, $placeholder);
            if (!$position || !$position['found']) {
                Yii::warning("Placeholder {{$placeholder}} not found in document");
                return false;
            }

            $startIndex = $position['startIndex'];
            $endIndex = $position['endIndex'];

            $requests = [];
            
            // แทรกรูปภาพก่อน
            $requests[] = [
                'insertInlineImage' => [
                    'location' => [
                        'index' => $startIndex,
                    ],
                    'uri' => $validUrl,
                    'objectSize' => [
                        'height' => ['magnitude' => 100, 'unit' => 'PT'],
                        'width' => ['magnitude' => 100, 'unit' => 'PT'],
                    ],
                ],
            ];

            // จากนั้นลบ placeholder (index จะเปลี่ยนหลังจากแทรกรูป)
            $requests[] = [
                'deleteContentRange' => [
                    'range' => [
                        'startIndex' => $startIndex + 1, // +1 เพราะมีรูปแทรกเข้าไป
                        'endIndex' => $endIndex + 1,
                    ],
                ],
            ];

            $batchUpdateRequest = new \Google_Service_Docs_BatchUpdateDocumentRequest([
                'requests' => $requests,
            ]);
            
            $this->docsService->documents->batchUpdate($docId, $batchUpdateRequest);
            
            Yii::info("Successfully replaced placeholder {{$placeholder}} with image: $validUrl");
            return true;

        } catch (\Exception $e) {
            Yii::error("Error replacing placeholder {{$placeholder}}: " . $e->getMessage());
            return false;
        }
    }
}