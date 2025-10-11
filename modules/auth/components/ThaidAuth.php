<?php
namespace app\modules\auth\components;

use Yii;
use yii\base\Component;
use yii\httpclient\Client;
use yii\web\BadRequestHttpException;
use yii\web\ServerErrorHttpException;
use app\modules\usermanager\models\User;

class ThaidAuth extends Component
{
    public string $clientId;
    public string $clientSecret;
    public string $redirectUri;
    public string $authorizeUrl;
    public string $tokenUrl;
    public string $scope;

    public function init(): void
    {
        parent::init();

        $this->clientId = env('THAID_CLIENT_ID');
        $this->clientSecret = env('THAID_CLIENT_SECRET');
        $this->redirectUri = env('THAID_REDIRECT_URI');
        $this->authorizeUrl = env('THAID_AUTHORIZE_URL');
        $this->tokenUrl = env('THAID_TOKEN_URL');
        $this->scope = env('THAID_SCOPE');

        if (!$this->clientId || !$this->clientSecret) {
            throw new \RuntimeException("THAID credentials not configured in .env");
        }
    }

    /**
     * สร้าง URL สำหรับ redirect ไป login ThaiD
     */
    public function getLoginUrl(): string
    {
        $state = Yii::$app->security->generateRandomString(16);

        return $this->authorizeUrl . '?' . http_build_query([
            'response_type' => 'code',
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'scope' => $this->scope,
            'state' => $state,
        ]);
    }
    
    /**
     * ดึง token และข้อมูลผู้ใช้จาก code
     */
    public function getUserFromCode(string $code)
    {
        if (!$code) {
            throw new BadRequestHttpException("No code received");
        }

        $client = new Client();
        $response = $client->createRequest()
            ->setMethod('POST')
            ->setUrl($this->tokenUrl)
            ->setData([
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => $this->redirectUri,
            ])
            ->addHeaders([
                'Authorization' => 'Basic ' . base64_encode($this->clientId . ':' . $this->clientSecret),
                'Content-Type' => 'application/x-www-form-urlencoded',
            ])
            ->send();

        if (!$response->isOk) {
            throw new ServerErrorHttpException("Token request failed");
        }

        $data = $response->data;

        $idToken = $data['id_token'] ?? null;
        if (!$idToken) {
            throw new ServerErrorHttpException("No id_token found");
        }

        $parts = explode(".", $idToken);
        $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
        return $payload;

        $thaiId = $payload['sub'] ?? null;
        $email = $payload['email'] ?? null;
        $name = $payload['name'] ?? null;

        if (!$thaiId) {
            throw new ServerErrorHttpException("Invalid id_token payload");
        }

        // ------ Register/Login user ------
        $user = User::findOne(['thaid' => $thaiId]);
        if (!$user) {
            $user = new User();
            $user->username = hash('sha256', $thaiId . 'abc123');
            $user->thaid = $thaiId;
            $user->email = $email ?? $thaiId . '@thaid.local';
            $user->setPassword(Yii::$app->security->generateRandomString(12));
            $user->generateAuthKey();
            $user->created_at = time();
            $user->updated_at = time();
            $user->save(false);
        }

        return $user;
    }
}
