<?php

namespace app\controllers;

use Yii;
use yii\web\Response;
use app\models\LoginForm;
use app\models\Post;
use app\models\RegisterForm;
use app\models\User;
use app\services\EmailService;
use yii\filters\auth\HttpBearerAuth;
use yii\rest\ActiveController;

class AuthController extends ActiveController
{
    public $modelClass = '';
    public $enableCsrfValidation = false;

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        unset($behaviors['authenticator']);

        // CORS filter
        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::class,
            'cors' => [
                'Origin' => [isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '*'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
                'Access-Control-Allow-Credentials' => false,
                'Access-Control-Allow-Origin' => ['*'],
            ],
        ];

        $auth = [
            'class' => HttpBearerAuth::class,
            'except' => ['options', "login", "register", "get-some-posts", "get-post", "verify-email"],
        ];

        $behaviors['authenticator'] = $auth;

        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();

        unset($actions['delete'], $actions['create'], $actions['view']);

        return $actions;
    }

    // Метод регистрации
    public function actionRegister()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = new RegisterForm();
        if ($model->load(Yii::$app->request->post(), '')) {
            if ($user = $model->register()) {
                $email = $user->email;
                $token = $user->verification_token;

                Yii::$app->response->on(Response::EVENT_AFTER_SEND, function () use ($email, $token) {
                    ignore_user_abort(true);
                    if (function_exists('fastcgi_finish_request')) {
                        fastcgi_finish_request();
                    } else {
                        @ob_end_flush();
                        @flush();
                    }
                    (new EmailService())->sendVerificationEmail($email, $token);
                });

                return ['status' => 'success', 'user' => User::findOne(['id' => Yii::$app->user->id]), 'token' => $user->auth_key, 'id' => Yii::$app->user->id];
            } else {
                return ['status' => 'error', 'message' => 'Ошибка при сохранении пользователя', 'errorsValidation' => $model->errors];
            }
        }
        return ['status' => 'error', 'message' => 'Ошибка при получении данных', 'post' => Yii::$app->request->getBodyParams()];
    }

    // Метод Авторизации
    public function actionLogin()
    {
        $model = new LoginForm();

        Yii::$app->response->format = Response::FORMAT_JSON;

        if ($model->load(Yii::$app->request->post(), '')) {

            if ($model->login()) {
                $user = Yii::$app->user->identity;
                return ['status' => 'success', 'user' => User::findOne(['id' => Yii::$app->user->id]), 'token' => $user->auth_key];
            } else {
                return ['status' => 'error', 'message' =>  "Неверные данные", 'errorsValidation' => $model->errors];
            }
        }

        return ['status' => 'error', 'message' => 'Ошибка при получении данных', 'post' => Yii::$app->request->post()];
    }

    // Аунтификация по токену
    public function actionFindByToken()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if ($user_id = Yii::$app->user->id) {
            $user = User::findOne($user_id);

            if ($user->avatar_path) {
                $user->avatar_path = '/backend/uploads/' . $user->avatar_path;
            }

            if ($user->background_path) {
                $user->background_path = '/backend/uploads/' . $user->background_path;
            }

            return ['status' => 'success', 'user' => $user];
        } else {
            return ['status' => 'error', 'message' => 'Undefind token'];
        };
    }

    public function actionLogout()
    {
        Yii::$app->user->logout();

        return ['status' => 'success'];
    }

    /**
     * Подтверждение email
     */
    public function actionVerifyEmail()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $request = Yii::$app->request;

        $token = $request->get('token');

        if (!$token) {
            return [
                'status' => 'error',
                'message' => 'Токен не указан'
            ];
        }

        $user = User::findOne([
            'verification_token' => $token
        ]);

        if (!$user) {
            return [
                'status' => 'error',
                'message' => 'Неверный токен подтверждения'
            ];
        }

        // Проверка срока действия токена
        if (strtotime($user->verification_token_expires) < time()) {
            return [
                'status' => 'error',
                'message' => 'Срок действия токена истек'
            ];
        }

        // Подтверждаем email
        $user->email_verified = 1;
        $user->verification_token = null;
        $user->verification_token_expires = null;

        if ($user->save(false)) {
            return [
                'status' => 'success',
                'message' => 'Email успешно подтвержден'
            ];
        }

        return [
            'status' => 'error',
            'message' => 'Ошибка при подтверждении email'
        ];
    }
}
