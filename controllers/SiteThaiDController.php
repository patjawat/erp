<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\RegisterForm;
use app\models\ContactForm;

use yii\helpers\Url;
use yii\httpclient\Client;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {        
        return $this->render('index');
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
{
    if (!Yii::$app->user->isGuest) {
        return $this->goHome();
    }

    $model = new LoginForm();
    if ($model->load(Yii::$app->request->post()) && $model->login()) {
        return $this->goBack();
    }

    return $this->render('login', [
        'model' => $model,
    ]);
}

public function actionRegister()
{
    $model = new RegisterForm();
    if ($model->load(Yii::$app->request->post()) && $model->register()) {
        Yii::$app->session->setFlash('success', 'Registration successful. You can login now.');
        return $this->redirect(['login']);
    }

    return $this->render('register', [
        'model' => $model,
    ]);
}

public function actionLogout()
{
    Yii::$app->user->logout();
    return $this->goHome();
}

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }



    ############################################# 
    public function actionLoginThaid()
    {
        $params = Yii::$app->params['thaid'];

        $authorizeUrl = $params['authorizeUrl'] . '?' . http_build_query([
            'response_type' => 'code',
            'client_id' => $params['clientId'],
            'redirect_uri' => $params['redirectUri'],
            'scope' => 'title name given_name family_name birthdate house_address  openid',
            'state' => Yii::$app->security->generateRandomString(16),
        ]);

        return $this->redirect($authorizeUrl);
    }

    public function actionCallbackThaid($code = null, $state = null)
    {
        if ($code === null) {
            throw new \yii\web\BadRequestHttpException("No code received");
        }

        $params = Yii::$app->params['thaid'];

        $client = new Client();
        $response = $client->createRequest()
            ->setMethod('POST')
            ->setUrl($params['tokenUrl'])
            ->setData([
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => $params['redirectUri'],
            ])
            ->addHeaders([
                'Authorization' => 'Basic ' . base64_encode(
                    $params['clientId'] . ':' . $params['clientSecret']
                ),
                'Content-Type' => 'application/x-www-form-urlencoded',
            ])
            ->send();

        if (!$response->isOk) {
            throw new \yii\web\ServerErrorHttpException("Token request failed");
        }

        $data = $response->data;

        // -------- Decode id_token (JWT) --------
        $idToken = $data['id_token'] ?? null;
        if ($idToken) {
            $parts = explode(".", $idToken);
            $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);

            // ตัวอย่าง payload: sub, given_name, family_name, email
            $thaiId = $payload['sub'] ?? null;
            $email = $payload['email'] ?? null;
            $name = $payload['name'] ?? null;

            // ------ Register/Login user ------
            $user = \app\models\User::findOne(['thaid' => $thaiId]);
            if (!$user) {
                $user = new \app\models\User();
                $user->username = hash('sha256', $thaiId . 'abc123');
                $user->thaid=$thaiId;
                $user->email = $email ?? $thaiId . '@thaid.local';
                $user->setPassword(Yii::$app->security->generateRandomString(12));
                $user->generateAuthKey();
                $user->created_at = time();
                $user->updated_at = time();
                $user->save(false);
            }

            Yii::$app->user->login($user);
            return $this->goHome();
        }

        throw new \yii\web\ServerErrorHttpException("No id_token found");
    }

    ##############################################
    public function actionLoginMophid()
{
    $params = Yii::$app->params['healthid'];

    $authorizeUrl = $params['authorizeUrl'] . '?' . http_build_query([
        'client_id'     => $params['clientId'],
        'redirect_uri'  => $params['redirectUri'],
        'response_type' => $params['response_type'],
    ]);

    return $this->redirect($authorizeUrl);
}

    public function actionCallbackGettoken($code = null, $response_type = null)
    {
        if ($code === null) {
            throw new \yii\web\BadRequestHttpException("No code received");
        }

        $params = Yii::$app->params['healthid'];
        $client = new Client();

        // -------- Step 1: แลก code เป็น access_token --------
        $response = $client->createRequest()
            ->setMethod('POST')
            ->setUrl($params['tokenUrl'])
            ->setData([
                'grant_type'    => 'authorization_code',
                'code'          => $code,
                'redirect_uri'  => $params['redirectUri'],
                'client_id'     => $params['clientId'],
                'client_secret' => $params['clientSecret'],
            ])
            ->send();

        if (!$response->isOk) {
            throw new \yii\web\ServerErrorHttpException("Token request failed");
        }

        $data = $response->data;
        if (!isset($data['data']['access_token'])) {
            throw new \yii\web\ServerErrorHttpException("No access_token found");
        }

        $mophAccessToken = $data['data']['access_token'];

        $parts = explode(".", $mophAccessToken);
        $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);

            // ตัวอย่าง payload: sub, given_name, family_name, email
        $cid = $payload['scopes_detail']['id_card'] ?? null;

        // echo '<pre>';
        // print_r($parts);
        // echo '</pre>';
        // exit;

        // -------- Step 2: แลก token กับ Provider service --------
        $serviceAccessToken = $this->getAccessToken($mophAccessToken);

        // -------- Step 3: ดึง Profile --------
        $profile = $this->getProfile($serviceAccessToken);

        if (!$profile || !isset($profile['account_id'])) {
            throw new \yii\web\ServerErrorHttpException("Invalid profile data");
        }

        // -------- Step 4: จัดการ Register/Login --------
        // $thaiId = $profile['account_id'];   // ใช้ account_id เป็น unique key 
        $provider_id = $profile['provider_id'];   // ใช้ account_id เป็น unique key
        $email  = $profile['email'] ?? $thaiId . '@provider.local';
        $name   = $profile['name_th'] ?? ($profile['name_eng'] ?? 'Unknown');
        
        // $user = \app\models\User::findOne(['username' => $provider_id]);
        $user = \app\models\User::findOne(['thaid' => $cid]);
        if (!$user) {
            $user = new \app\models\User();
            $user->username = $provider_id;
            $user->thaid = $cid;
            $user->provider_id = $provider_id;
            $user->email = $email;
            $user->setPassword(Yii::$app->security->generateRandomString(12));
            $user->generateAuthKey();
            $user->created_at = time();
            $user->updated_at = time();
            $user->save(false);              
                    
        } else {
        // 🔁 ถ้ามีอยู่แล้ว — update provider_id และอัปเดตเวลา
        $user->provider_id = $provider_id;
        $user->updated_at = time();
        $user->save(false);
       }

        // -------- Step 5: Login --------
        Yii::$app->user->login($user);
        return $this->goHome();
    }


    private function getAccessToken($accessToken)
    {
        $params = Yii::$app->params['providerid'];
        $client = new Client();

        $response = $client->createRequest()
            ->setMethod('POST')
            ->setUrl($params['serviceUrl']) 
            ->setData([
                'token'         => $accessToken,
                'token_by'      => $params['token_by'], 
                'client_id'     => $params['clientId'],
                'secret_key'    => $params['clientSecret'],
            ])
            ->send();

        if (!$response->isOk || !isset($response->data['data']['access_token'])) {
            throw new \yii\web\ServerErrorHttpException("Service token request failed");
        }

        return $response->data['data']['access_token'];
    }

    private function getProfile($accessToken)
    {
        $params = Yii::$app->params['providerid'];
        $client = new Client();

        $response = $client->createRequest()
            ->setMethod('GET')
            ->setUrl($params['profileUrl']) 
            ->addHeaders([
                'client-id'     => $params['clientId'],
                'secret-key'    => $params['clientSecret'],
                'Authorization' => "Bearer {$accessToken}",
            ])
            ->send();

        if (!$response->isOk || !isset($response->data['data'])) {
            throw new \yii\web\ServerErrorHttpException("Profile request failed");
        }

        return $response->data['data'];
    }  
}


// Params.php

    // 'thaid' => [
    //     'clientId' => '', //เอามาใส่
    //     'clientSecret' => '',//เอามาใส่
    //     'authorizeUrl' => 'https://imauth.bora.dopa.go.th/api/v2/oauth2/auth/',
    //     'tokenUrl' => 'https://imauth.bora.dopa.go.th/api/v2/oauth2/token/',
    //     'redirectUri' => 'http://127.0.0.1/basic/web/site/callback-thaid',
    //     'scope' => 'title name given_name family_name birthdate house_address  openid',
    // ],
//  \yii\helpers\Html::a(
//     'Login with ThaiID',
//     ['site/login-thaid'],
//     ['class' => 'btn btn-primary']
// )
