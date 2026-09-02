<?php

class ApiVersionCest
{
    public function guestCanReadApplicationVersion(\FunctionalTester $I)
    {
        $I->amOnRoute('api/version');
        $I->seeResponseCodeIs(200);

        $response = json_decode($I->grabPageSource(), true);
        $displayVersion = require dirname(__DIR__, 2) . '/config/version.php';

        $I->assertSame([
            'schema_version' => 1,
            'version' => preg_replace('/^v(?=\d)/i', '', $displayVersion),
            'display_version' => $displayVersion,
        ], $response);
        $I->assertStringStartsWith(
            'application/json',
            (string) Yii::$app->response->headers->get('Content-Type')
        );
        $I->assertStringContainsString(
            'no-store',
            (string) Yii::$app->response->headers->get('Cache-Control')
        );
    }

    public function corsAllowsExternalVersionChecker(\FunctionalTester $I)
    {
        $I->haveHttpHeader('Origin', 'https://updates.example.test');
        $I->amOnRoute('api/version');
        $I->seeResponseCodeIs(200);
        $I->assertSame(
            '*',
            Yii::$app->response->headers->get('Access-Control-Allow-Origin')
        );
    }

    public function postIsNotAllowed(\FunctionalTester $I)
    {
        $I->sendAjaxPostRequest('?r=api/version');
        $I->seeResponseCodeIs(405);
    }
}
