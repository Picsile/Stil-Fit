<?php

namespace app\models;

use Yii;
use yii\base\Model;
use app\services\EmailService;

/**
 * ContactForm is the model behind the contact form.
 */
class RegisterForm extends Model
{
    public $login;
    public $email;
    public $password;
    public $privacy_policy_accepted;
    public $oferta_accepted;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['login', 'email', 'password', 'privacy_policy_accepted', 'oferta_accepted'], 'required'],

            ['login', 'unique', 'targetClass' => User::class, 'message' => 'Этот login уже используется'],
            ['email', 'unique', 'targetClass' => User::class, 'message' => 'Этот email уже используется'],
            
            ['email', 'email'],

            ['email', 'validateDisposableEmail'],

            [['privacy_policy_accepted', 'oferta_accepted'], 'boolean'],
            [['privacy_policy_accepted', 'oferta_accepted'], 'compare', 'compareValue' => 1, 'message' => 'Необходимо принять соглашение'],
        ];
    }

    public function validateDisposableEmail($attribute)
    {
        $emailService = new EmailService();
        if ($emailService->isDisposableEmail($this->$attribute)) {
            $this->addError($attribute, 'Невалидный email');
        }
    }

    public function register(): User | false
    {
        if ($this->validate()) {
            $user = new User();
            $user->load($this->attributes, '');
            $user->password = Yii::$app->security->generatePasswordHash($user->password);
            $user->auth_key = Yii::$app->security->generateRandomString();
            $user->privacy_policy_accepted = $this->privacy_policy_accepted ? 1 : 0;
            $user->oferta_accepted = $this->oferta_accepted ? 1 : 0;

            $user->verification_token = Yii::$app->security->generateRandomString(32);
            $user->verification_token_expires = date('Y-m-d H:i:s', strtotime('+24 hours'));

            if (!$user->save()) {
                $this->addErrors($user->errors);
                return false;
            }

            return $user;
        }
        return false;
    }
}
