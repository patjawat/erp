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
    public $line_id;


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['username', 'trim'],
            ['line_id', 'string'],
            [['email','cid'], 'required'],
            ['username', 'unique', 'targetClass' => '\app\modules\usermanager\models\User', 'message' => 'ถูกใช้แล้ว'],
            ['fullname', 'string', 'min' => 2, 'max' => 255],
            ['username', 'string', 'min' => 2, 'max' => 255],

            ['email', 'trim'],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            ['email', 'unique', 'targetClass' => '\app\modules\usermanager\models\User', 'message' => 'ถูกใช้แล้ว'],
            
            ['password', 'required'],
            [['cid','email'], 'checkOwner'],
            // ['phone', 'unique', 'targetClass' => '\app\modules\hr\models\Employees', 'message' => 'เบอร์โทรถูกใช้ไปแล้ว'],
            // ['password', 'string', 'min' => Yii::$app->params['user.passwordMinLength']],
        ];
    }


    public function attributeLabels()
    {
        return [
            'cid' => 'เลขบัตรประชาชน',
            
        ];
    }

    public function checkOwner(){
        $model= $this->getEmployee();
       
        if($model == null){
            $this->addError('cid', 'ไม่พบชื่อในระบบ');
        }

        if($model && $model->user_id >= 1){ 
            $this->addError('cid', 'ลงทะเบียนแล้ว');
        }

        if($model && $model->email !=  $this->email){ 
            $this->addError('email', 'ไม่พบอีเมล');
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
                $user->line_id = $this->line_id;
                
                $user->setPassword($this->password);
                $user->generateAuthKey();
                $user->status = 10;
                $user->save();
                
                // return $user->getErrors();
                
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
