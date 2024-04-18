<?php

namespace app\models;

use app\modules\hr\models\Employees;
use Yii;
use yii\base\Model;
use app\modules\usermanager\models\User;

/**
 * Signup form
 */
class SignupForm extends Model {
    public $username;
    public $fullname;
    public $email;
    public $password;

    public $cid;
    public $birthday;


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['username', 'trim'],
            // ['fullname', 'required'],
            [['email','cid'], 'required'],
            ['username', 'unique', 'targetClass' => '\app\modules\usermanager\models\User', 'message' => 'ถูกใช้แล้ว'],
            ['fullname', 'string', 'min' => 2, 'max' => 255],
            ['username', 'string', 'min' => 2, 'max' => 255],

            ['email', 'trim'],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            ['email', 'unique', 'targetClass' => '\app\modules\usermanager\models\User', 'message' => 'ถูกใช้แล้ว'],
            
            ['password', 'required'],
            [['fname', 'lname'], 'checkOwner'],
            // ['phone', 'unique', 'targetClass' => '\app\modules\hr\models\Employees', 'message' => 'เบอร์โทรถูกใช้ไปแล้ว'],
            // ['password', 'string', 'min' => Yii::$app->params['user.passwordMinLength']],
        ];
    }

    public function checkOwner(){
        // $model= $this->getEmployee();
       
        if($model == null){
            $this->addError('fname', 'ไม่พบชื่อในระบบ');
            $this->addError('lname', 'ไม่พบนามสกุลในระบบ');
        }

        if($model && $model->user_id >= 1){ 
            $this->addError('fname', 'ชื่อถูกลงทะเบียนแล้ว');
            $this->addError('lname', 'นามสกุลถูกลงทะเบียนแล้ว');
        }
    }

    /**
     * Signs user up.
     *
     * @return bool whether the creating new account was successful and email was sent
     */
    public function signup()
    {

            $transaction = Yii::$app->db->beginTransaction();
            try {
            
                $user = new User([
                    'password' => $this->password,
                    'confirm_password' => $this->password
                ]);
                $user->username = $this->email;
                $user->email = $this->email;
                
                $user->setPassword($this->password);
                $user->generateAuthKey();
                $user->status = 10;
                $user->save();
                
                $emp = $this->getEmployee();
                $emp->user_id  =  $user->id;
                // $emp->fname = $this->fname;
                // $emp->lname = $this->lname;
                // $emp->phone = $this->phone;
                $emp->email = $this->email;
                $emp->save(false);
                $user->assignment();
               

                $transaction->commit();
                return true;
               
            } catch (\Exception $e) {
                $transaction->rollBack();
                throw $e;
            }
    



}

public function getEmployee(){
    return  Employees::find()->where(['cid' => $this->cid])->one();
}

    /**
     * Sends confirmation email to user
     * @param User $user user model to with email should be send
     * @return bool whether the email was sent
     */

    }
