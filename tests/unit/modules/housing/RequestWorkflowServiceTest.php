<?php

namespace tests\unit\modules\housing;

use app\modules\housing\models\HousingRequest;
use app\modules\housing\models\RequestStatusLog;
use app\modules\housing\services\RequestWorkflowService;
use Codeception\Test\Unit;

final class RequestWorkflowServiceTest extends Unit
{
    public function testSubmitPersistsStatusAndAuditLog(): void
    {
        $request = new HousingRequest([
            'request_no' => 'TEST-HRQ-' . bin2hex(random_bytes(4)),
            'request_type' => HousingRequest::TYPE_MOVE_IN,
            'emp_id' => 999999,
        ]);
        $this->assertTrue($request->save(), json_encode($request->errors, JSON_UNESCAPED_UNICODE));

        try {
            (new RequestWorkflowService())->transition(
                $request,
                HousingRequest::STATUS_SUBMITTED,
                'ทดสอบส่งคำขอ'
            );

            $request->refresh();
            $this->assertSame(HousingRequest::STATUS_SUBMITTED, $request->status);
            $this->assertNotNull($request->submitted_at);
            $this->assertSame(1, RequestStatusLog::find()->where([
                'request_id' => $request->id,
                'from_status' => HousingRequest::STATUS_DRAFT,
                'to_status' => HousingRequest::STATUS_SUBMITTED,
            ])->count());
        } finally {
            $request->delete();
        }
    }

    public function testInvalidTransitionIsRejected(): void
    {
        $request = new HousingRequest([
            'request_no' => 'TEST-HRQ-' . bin2hex(random_bytes(4)),
            'request_type' => HousingRequest::TYPE_MOVE_IN,
            'emp_id' => 999999,
        ]);
        $this->assertTrue($request->save(), json_encode($request->errors, JSON_UNESCAPED_UNICODE));

        try {
            $this->expectException(\DomainException::class);
            (new RequestWorkflowService())->transition($request, HousingRequest::STATUS_ACTIVE);
        } finally {
            $request->delete();
        }
    }
}
