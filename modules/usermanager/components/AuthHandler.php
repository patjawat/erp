<?php
namespace app\modules\usermanager\components;

use Yii;
use app\modules\usermanager\models\Auth;
use app\modules\usermanager\models\User;
use yii\authclient\ClientInterface;
use yii\helpers\ArrayHelper;

/**
 * AuthHandler handles successful authentication via Yii auth component
 */
class AuthHandler
{
    /**
     * @var ClientInterface
     */
    private $client;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    public function handle()
    {
        $attributes = $this->client->getUserAttributes();
        $email = ArrayHelper::getValue($attributes, 'email');
        $id = ArrayHelper::getValue($attributes, 'id');
        $fullname = ArrayHelper::getValue($attributes, 'name');
        $nickname = ArrayHelper::getValue($attributes, 'login') =='' ? ArrayHelper::getValue($attributes, 'email') : ArrayHelper::getValue($attributes, 'name');

        /* @var Auth $auth */
        $auth = Auth::find()->where([
            'source' => $this->client->getId(),
            'source_id' => $id,
        ])->one();

        if (Yii::$app->user->isGuest) {
            if ($auth) { // login
                /* @var User $user */
                $user = $auth->user;
                // $this->updateUserInfo($user);
                // Yii::$app->user->login($user, Yii::$app->params['user.rememberMeDuration']);
                Yii::$app->user->login($user);
            } else { // signup
                // if ($email !== null && User::find()->where(['email' => $email])->exists()) {
                $userEmail = User::find()->where(['email' => $email])->one();
                if ($email !== null &&  $userEmail) {
                    // Yii::$app->getSession()->setFlash('error', [
                    //     Yii::t('app', "User with the same email as in {client} account already exists but isn't linked to it. Login using email first to link it.", ['client' => $this->client->getTitle()]),
                    // ]);
                    if(!$auth){
                        $newAuth = new Auth([
                            'user_id' => $userEmail->id,
                            'source' => $this->client->getId(),
                            'source_id' => (string)$id,
                        ]);
                        $newAuth->save();
                    }

                    Yii::$app->user->login($userEmail);
                } else {
                    $password = Yii::$app->security->generateRandomString(6);
                  
                    $user = new User([
                        'username' => $nickname,
                        'email' => $email,
                        'fullname' => $fullname,
                        'password' => $password,
                        'password_hash' => $password,
                        'confirm_password' => $password,
                        'status' => 0,
                        // 'status' => User::STATUS_ACTIVE // make sure you set status properly
                    ]);
      
                    $user->generateAuthKey();
                    $user->generatePasswordResetToken();

                    $transaction = User::getDb()->beginTransaction();

                    
                    if ($user->save()) {

                        // กำหนด role default เป็น  user
                        $auth_assignment = Yii::$app->authManager;
                        $roleUser = $auth_assignment->getRolesByUser($user->id);
                        $auth_assignment->revokeAll($user->id);
                        $auth_assignment->assign($auth_assignment->getRole('user'), $user->id);
                        

                        $auth = new Auth([
                            'user_id' => $user->id,
                            'source' => $this->client->getId(),
                            'source_id' => (string)$id,
                        ]);
                        if ($auth->save()) {
                            $transaction->commit();
                            // Yii::$app->user->login($user, Yii::$app->params['user.rememberMeDuration']);
                            Yii::$app->user->login($user);
                        } else {
                            Yii::$app->getSession()->setFlash('error', [
                                Yii::t('app', 'Unable to save {client} account: {errors}', [
                                    'client' => $this->client->getTitle(),
                                    'errors' => json_encode($auth->getErrors()),
                                ]),
                            ]);
                        }
                    } else {
                        Yii::$app->getSession()->setFlash('error', [
                            Yii::t('app', 'Unable to save user: {errors}', [
                                'client' => $this->client->getTitle(),
                                'errors' => json_encode($user->getErrors()),
                            ]),
                        ]);
                    }
                }
            }
        } else { // user already logged in
            if (!$auth) { // add auth provider
                $auth = new Auth([
                    'user_id' => Yii::$app->user->id,
                    'source' => $this->client->getId(),
                    'source_id' => (string)$attributes['id'],
                ]);
                if ($auth->save()) {
                    /** @var User $user */
                    $user = $auth->user;
                    $this->updateUserInfo($user);
                    Yii::$app->getSession()->setFlash('success', [
                        Yii::t('app', 'Linked {client} account.', [
                            'client' => $this->client->getTitle()
                        ]),
                    ]);
                } else {
                    Yii::$app->getSession()->setFlash('error', [
                        Yii::t('app', 'Unable to link {client} account: {errors}', [
                            'client' => $this->client->getTitle(),
                            'errors' => json_encode($auth->getErrors()),
                        ]),
                    ]);
                }
            } else { // there's existing auth
                Yii::$app->getSession()->setFlash('error', [
                    Yii::t('app',
                        'Unable to link {client} account. There is another user using it.',
                        ['client' => $this->client->getTitle()]),
                ]);
            }
        }
    }

    /**
     * @param User $user
     */
    private function updateUserInfo(User $user)
    {
        $attributes = $this->client->getUserAttributes();
        $github = ArrayHelper::getValue($attributes, 'login');
        if ($user->github === null && $github) {
            $user->github = $github;
            $user->save();
        }
    }
}