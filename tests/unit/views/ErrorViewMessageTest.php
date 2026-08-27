<?php

namespace tests\unit\views;

use Codeception\Test\Unit;

class ErrorViewMessageTest extends Unit
{
    public function testClientErrorsExposeActionableControllerMessage(): void
    {
        $view = file_get_contents(__DIR__ . '/../../../views/site/error.php');

        $this->assertStringContainsString('$isClientError', $view);
        $this->assertStringContainsString("trim((string) \$message)", $view);
        $this->assertStringContainsString("403 => ['title' => 'ไม่มีสิทธิ์ดำเนินการ'", $view);
        $this->assertStringContainsString('ย้อนกลับไปหน้าก่อน', $view);
    }

    public function testServerErrorsDoNotExposeInternalMessage(): void
    {
        $view = file_get_contents(__DIR__ . '/../../../views/site/error.php');

        $this->assertStringContainsString(': $state[\'defaultMessage\'];', $view);
        $this->assertStringContainsString('หากยังพบปัญหาจึงค่อยติดต่อผู้ดูแลระบบ', $view);
    }
}
