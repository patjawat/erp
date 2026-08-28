<?php

namespace tests\unit\controllers;

use PHPUnit\Framework\TestCase;

final class DocumentsAccessContractTest extends TestCase
{
    private string $controllerSource;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controllerSource = file_get_contents(
            __DIR__ . '/../../../modules/me/controllers/DocumentsController.php'
        );
    }

    public function testDocumentPermissionBypassesRecipientPolicy(): void
    {
        $methodStart = strpos($this->controllerSource, 'private function canCurrentUserReadDocument');
        $methodEnd = strpos($this->controllerSource, 'private function applyDocumentsIndexBaseQuery', $methodStart);
        $method = substr($this->controllerSource, $methodStart, $methodEnd - $methodStart);

        $this->assertStringContainsString("Yii::\$app->user->can('document')", $method);
        $this->assertStringContainsString('return true;', $method);
        $this->assertStringContainsString('DocumentAccessPolicy::canRead(', $method);
    }

    public function testViewAndPdfActionsUseTheSameAccessDecision(): void
    {
        $this->assertSame(
            3,
            substr_count($this->controllerSource, 'canCurrentUserReadDocument('),
            'Expected the method declaration plus calls from actionView and actionShow.'
        );
        $this->assertStringContainsString(
            'if ($emp && !empty($emp->id))',
            $this->controllerSource,
            'Document staff without an employee mapping must not fail while marking a route as read.'
        );
    }
}
