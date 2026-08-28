<?php

namespace tests\unit\components;

require_once __DIR__ . '/../../../components/DocumentPdfResolver.php';

use app\components\DocumentPdfResolver;
use PHPUnit\Framework\TestCase;
use yii\helpers\BaseFileHelper;

final class DocumentPdfResolverTest extends TestCase
{
    private string $uploadRoot;

    protected function setUp(): void
    {
        parent::setUp();
        $this->uploadRoot = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'document-pdf-resolver-' . bin2hex(random_bytes(6));
        BaseFileHelper::createDirectory($this->uploadRoot);
    }

    protected function tearDown(): void
    {
        BaseFileHelper::removeDirectory($this->uploadRoot);
        parent::tearDown();
    }

    public function testSkipsStaleMetadataAndReturnsNextReadablePdf(): void
    {
        $ref = 'document-ref';
        BaseFileHelper::createDirectory($this->uploadRoot . DIRECTORY_SEPARATOR . $ref);
        file_put_contents(
            $this->uploadRoot . DIRECTORY_SEPARATOR . $ref . DIRECTORY_SEPARATOR . 'available.pdf',
            '%PDF-1.4 test'
        );

        $result = DocumentPdfResolver::resolve([
            $this->upload($ref, 'missing.pdf', 'missing-original.pdf', 'pdf'),
            $this->upload($ref, 'available.pdf', 'หนังสือราชการ.pdf', 'pdf'),
        ], $this->uploadRoot);

        $this->assertNotNull($result);
        $this->assertSame('available.pdf', basename($result['path']));
        $this->assertSame('หนังสือราชการ.pdf', $result['downloadName']);
    }

    public function testRejectsNonPdfAndUnsafeMetadata(): void
    {
        $ref = 'document-ref';
        BaseFileHelper::createDirectory($this->uploadRoot . DIRECTORY_SEPARATOR . $ref);
        file_put_contents($this->uploadRoot . DIRECTORY_SEPARATOR . $ref . DIRECTORY_SEPARATOR . 'image.jpg', 'image');

        $result = DocumentPdfResolver::resolve([
            $this->upload($ref, 'image.jpg', 'image.jpg', 'image'),
            $this->upload($ref, 'image.jpg', 'renamed.pdf', 'pdf'),
            $this->upload('../outside', 'document.pdf', 'document.pdf', 'pdf'),
            $this->upload($ref, '../document.pdf', 'document.pdf', 'pdf'),
        ], $this->uploadRoot);

        $this->assertNull($result);
    }

    public function testReturnsNullWhenEveryPdfFileIsMissing(): void
    {
        $result = DocumentPdfResolver::resolve([
            $this->upload('document-ref', 'missing.pdf', 'missing.pdf', 'pdf'),
        ], $this->uploadRoot);

        $this->assertNull($result);
    }

    private function upload(string $ref, string $realFilename, string $originalFilename, string $type): object
    {
        return (object) [
            'ref' => $ref,
            'real_filename' => $realFilename,
            'file_name' => $originalFilename,
            'type' => $type,
        ];
    }
}
