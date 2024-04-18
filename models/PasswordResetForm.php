<?php

namespace app\models;

use app\modules\usermanager\models\User;
use Yii;
use yii\base\Model;

/**
 * Password reset request form
 */
class PasswordResetForm extends Model
{
    public $password;
    public $password_;
    public $token;


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['password','password_'], 'trim'],
            [['password','password_','token'], 'required'],
            [['password'], 'string','min' => 6],
            ['password_', 'compare', 'compareAttribute'=>'password', 'message'=>"Passwords don't match"], 
        ];
    }

    /**
     * Sends an email with a link, for resetting the password.
     *
     * @return bool whether the email was send
     */

    public  function setPassword(){
        $user = User::findOne([
            'status' => User::STATUS_ACTIVE,
            'password_reset_token' => $this->token,
        ]);

        if($this->password == $this->password_){
            $user->setPassword($this->password);
            $user->password_reset_token = null;
            return $user->save(false);
        }
        // return null;
    }
    public  function resetPassword()
    {
        /* @var $user User */
        $user = User::findOne([
            'status' => User::STATUS_ACTIVE,
            // 'password_reset_token' => $this->,
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