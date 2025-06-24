<?php
// modules/gdoc/components/GoogleService.php

namespace app\modules\gdoc\components;

use Google_Client;
use Google_Service_Docs;
use Google_Service_Drive;

class GoogleService
{
    public $docsService;
    public $driveService;

    public function __construct()
    {
        $client = new Google_Client();
        $client->setApplicationName('Yii2 Internal Export');
        $client->setAuthConfig(\Yii::getAlias('@app/credentials/service-account.json'));
        $client->setScopes([
            Google_Service_Docs::DOCUMENTS,
            Google_Service_Drive::DRIVE
        ]);
        $client->useApplicationDefaultCredentials();

        $this->docsService = new Google_Service_Docs($client);
        $this->driveService = new Google_Service_Drive($client);
    }

    public function copyAndReplace($templateId, $newTitle, $replacements = [])
    {
        $fileMetadata = new \Google_Service_Drive_DriveFile([
            'name' => $newTitle
        ]);
        $copy = $this->driveService->files->copy($templateId, $fileMetadata);
        $documentId = $copy->getId();

        $requests = [];
        foreach ($replacements as $search => $replace) {
            $requests[] = new \Google_Service_Docs_Request([
                'replaceAllText' => [
                    'containsText' => ['text' => '{{' . $search . '}}', 'matchCase' => true],
                    'replaceText' => $replace
                ]
            ]);
        }

        $batchUpdate = new \Google_Service_Docs_BatchUpdateDocumentRequest([
            'requests' => $requests
        ]);
        $this->docsService->documents->batchUpdate($documentId, $batchUpdate);

        return $documentId;
    }

    public function exportAsPdf($documentId)
    {
        $response = $this->driveService->files->export($documentId, 'application/pdf', ['alt' => 'media']);
        return $response->getBody()->getContents();
    }
}
