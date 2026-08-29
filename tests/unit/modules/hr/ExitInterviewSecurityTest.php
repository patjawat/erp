<?php

namespace tests\unit\modules\hr;

$root = dirname(__DIR__, 4);
require_once $root . '/modules/hr/models/ExitInterviewRecord.php';
require_once $root . '/modules/hr/models/ExitInterviewQuestionOption.php';
require_once $root . '/modules/hr/models/ExitInterviewQuestion.php';
require_once $root . '/modules/hr/services/ExitInterviewService.php';

use PHPUnit\Framework\TestCase;
use yii\web\BadRequestHttpException;
use app\modules\hr\models\ExitInterviewQuestion;
use app\modules\hr\models\ExitInterviewQuestionOption;
use app\modules\hr\services\ExitInterviewService;

class TestExitInterviewQuestion extends ExitInterviewQuestion
{
    public function attributes(): array
    {
        return ['prompt', 'question_type', 'config_json'];
    }
}

class TestExitInterviewQuestionOption extends ExitInterviewQuestionOption
{
    public function attributes(): array
    {
        return ['value', 'label'];
    }
}

class ExitInterviewSecurityTest extends TestCase
{
    private function validate(ExitInterviewQuestion $question, $value): void
    {
        $method = new \ReflectionMethod(ExitInterviewService::class, 'validateQuestionValue');
        $method->setAccessible(true);
        $method->invoke(new ExitInterviewService(), $question, $value);
    }

    private function choiceQuestion(string $type = 'single_choice'): ExitInterviewQuestion
    {
        $question = new TestExitInterviewQuestion([
            'prompt' => 'เลือกคำตอบ',
            'question_type' => $type,
            'config_json' => $type === 'ranking' ? '{"max_selections":3}' : '{}',
        ]);
        $question->populateRelation('options', [
            new TestExitInterviewQuestionOption(['value' => 'yes', 'label' => 'ใช่']),
            new TestExitInterviewQuestionOption(['value' => 'no', 'label' => 'ไม่']),
        ]);
        return $question;
    }

    public function testChoiceRejectsValueOutsidePublishedOptions(): void
    {
        $this->expectException(BadRequestHttpException::class);
        $this->validate($this->choiceQuestion(), 'injected-value');
    }

    public function testSingleChoiceRejectsArrayPayload(): void
    {
        $this->expectException(BadRequestHttpException::class);
        $this->validate($this->choiceQuestion(), ['yes', 'no']);
    }

    public function testRankingRejectsDuplicateValues(): void
    {
        $this->expectException(BadRequestHttpException::class);
        $this->validate($this->choiceQuestion('ranking'), ['yes', 'yes']);
    }

    public function testRatingRejectsNonNumericPayload(): void
    {
        $question = new TestExitInterviewQuestion([
            'prompt' => 'ให้คะแนน',
            'question_type' => 'rating',
            'config_json' => '{"min":1,"max":5}',
        ]);
        $this->expectException(BadRequestHttpException::class);
        $this->validate($question, 'five');
    }

    public function testControllerKeepsPublicAndHrQuestionScopesSeparate(): void
    {
        $controller = file_get_contents(dirname(__DIR__, 4) . '/modules/hr/controllers/ExitInterviewController.php');
        $this->assertStringContainsString('null, false, $submit', $controller);
        $this->assertStringContainsString('$reason, true, false', $controller);
        $this->assertStringContainsString("assertPermission('exitInterviewManage')", $controller);
        $this->assertStringContainsString("assertAnyPermission(['exitInterviewViewIdentified', 'exitInterviewManage'])", $controller);
    }

    public function testPublicErrorsDoNotExposeUnexpectedExceptions(): void
    {
        $controller = file_get_contents(dirname(__DIR__, 4) . '/modules/hr/controllers/ExitInterviewController.php');
        $this->assertStringContainsString('Yii::error($e, __METHOD__)', $controller);
        $this->assertStringContainsString('ระบบไม่สามารถเปิดแบบสัมภาษณ์ได้ในขณะนี้', $controller);
    }
}
