<?php

namespace app\models;

use app\modules\usermanager\models\User;
use Yii;
use yii\base\Model;

/**
 * Password reset request form
 */
class PasswordResetRequestForm extends Model
{
    public $email;


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['email', 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'exist',
                'targetClass' => '\app\modules\usermanager\models\User',
                'filter' => ['status' => \app\modules\usermanager\models\User::STATUS_ACTIVE],
                'message' => 'There is no user with this email address.'
            ],
        ];
    }

    /**
     * Sends an email with a link, for resetting the password.
     *
     * @return bool whether the email was send
     */
    public function sendEmail()
    {
        /* @var $user User */
        $user = User::findOne([
            'status' => User::STATUS_ACTIVE,
            'email' => $this->email,
        ]);
        

        if (!$user) {
            return false;
        }

        if (!User::isPasswordResetTokenValid($user->password_reset_token)) {
            $user->generatePasswordResetToken();
           
            if (!$user->save(false)) {
                return false;
            }
        }

                // OK Worikng
        $link = Yii::$app->urlManager->createAbsoluteUrl(['/site/reset-password', 'token' => $user->password_reset_token]);
                return Yii::$app->mailer->compose()
                    ->setFrom([\Yii::$app->params['supportEmail'] => \Yii::$app->name . ''])
                    ->setTo($this->email)
                    ->setSubject('คุณต้องการรีเซ็ตรหัสผ่าน ' . \Yii::$app->name)
                    ->setTextBody('คลิกที่นี่ : '.$link) //เลือกอยางใดอย่างหนึ่ง
                    ->send();
                // End Workings

    }
}