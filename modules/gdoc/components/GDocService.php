<?php

namespace app\modules\gdoc\components;

use Yii;
use Google_Client;
use Google_Service_Docs;
use Google_Service_Docs_BatchUpdateDocumentRequest;
use Google_Service_Drive;
use Google_Service_Drive_DriveFile;

class GDocService
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
        $this->client->setAuthConfig(Yii::getAlias('@app/modules/gdoc/credentials.json')); // Service account
        $this->client->setAccessType('offline');

        $this->docsService = new Google_Service_Docs($this->client);
        $this->driveService = new Google_Service_Drive($this->client);
    }

    public function createDocumentFromTemplate($templateId, $replacements = [], $folderId = null)
{
    $copyMeta = new Google_Service_Drive_DriveFile([
        'name' => 'New Document ' . date('Y-m-d H:i:s'),
    ]);

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

    $this->docsService->documents->batchUpdate($newDocId, new \Google_Service_Docs_BatchUpdateDocumentRequest([
        'requests' => $requests,
    ]));

    return $newDocId;
}

//แชร์เอกสารให้ Anyone ดูได้
public function shareFileToAnyone($fileId)
{
    $permission = new \Google_Service_Drive_Permission([
        'type' => 'anyone',
        'role' => 'reader',
    ]);

    $this->driveService->permissions->create($fileId, $permission, [
        'sendNotificationEmail' => false,
    ]);
}



    // public function createDocumentFromTemplate($templateId, $replacements = [])
    // {
    //     $copy = new Google_Service_Drive_DriveFile([
    //         'name' => 'Exported ' . date('Y-m-d H:i:s'),
    //     ]);

    //     $copiedFile = $this->driveService->files->copy($templateId, $copy);
    //     $newDocumentId = $copiedFile->id;

    //     $requests = [];
    //     foreach ($replacements as $key => $value) {
    //         $requests[] = [
    //             'replaceAllText' => [
    //                 'containsText' => [
    //                     'text' => '{{' . $key . '}}',
    //                     'matchCase' => false,
    //                 ],
    //                 'replaceText' => $value,
    //             ],
    //         ];
    //     }

    //     $batchUpdateRequest = new \Google_Service_Docs_BatchUpdateDocumentRequest([
    //         'requests' => $requests,
    //     ]);

    //     $this->docsService->documents->batchUpdate($newDocumentId, $batchUpdateRequest);
    //     $permission = new \Google_Service_Drive_Permission([
    //         // 'type' => 'user',
    //         'type' => 'anyone',
    //         'role' => 'writer',
    //         // 'emailAddress' => 'patjawat@gmail.com', // 👈 อีเมลของคุณ
    //     ]);
    //     $this->driveService->permissions->create($newDocumentId, $permission, [
    //         'fields' => 'id',
    //         'sendNotificationEmail' => false,
    //     ]);

    //     // $this->driveService->permissions->create($newDocumentId, $permission, ['sendNotificationEmail' => false]);

    //     return $newDocumentId;
    // }

    public function exportToPDF($docId)
    {
        return $this->driveService->files->export($docId, 'application/pdf', [
            'alt' => 'media'
        ])->getBody()->getContents();
        $fileId = $gdoc->uploadPDFToDrive($pdfContent, 'pdf-1.pdf')->id;

        // แชร์สิทธิ์ให้สาธารณะ
        $permission = new \Google_Service_Drive_Permission([
            'type' => 'anyone',
            'role' => 'reader',
        ]);
        $gdoc->driveService->permissions->create($fileId, $permission, ['sendNotificationEmail' => false]);
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
