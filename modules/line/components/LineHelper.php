<?php
namespace app\components\line;

use yii\authclient\OAuth2;

class LineHelper extends OAuth2
{
    /**
     * @inheritdoc
     */
    public $authUrl = 'https://access.line.me/oauth2/v2.1/authorize';

    /**
     * @inheritdoc
     */
    public $tokenUrl = 'https://api.line.me/oauth2/v2.1/token';

    /**
     * @inheritdoc
     */
    public $apiBaseUrl = 'https://api.line.me/v2/profile'; // ใช้สำหรับดึงข้อมูล Profile

    /**
     * @inheritdoc
     */
    protected function initUserAttributes()
    {
        // Line API v2.1 จะส่ง access token และ ID token กลับมา
        // เราต้องใช้ Access Token เพื่อไปเรียก API ดึง Profile อีกครั้ง (Get Profile)
        
        $response = $this->api($this->apiBaseUrl, 'GET', ['access_token' => $this->accessToken->getToken()]);

        // response จะมี 'userId' (คือ Line ID ที่เราต้องการ), 'displayName', 'pictureUrl' ฯลฯ
        return $response;
    }

    /**
     * @inheritdoc
     */
    protected function defaultName()
    {
        return 'line';
    }

    /**
     * @inheritdoc
     */
    protected function defaultTitle()
    {
        return 'LINE';
    }
}