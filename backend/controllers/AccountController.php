<?php

namespace app\controllers;

use app\models\Like;
use app\models\Post;
use app\models\Board;
use app\models\BoardPost;
use app\models\Favorites;
use app\models\ReasonDelete;
use app\models\UploadForm;
use app\models\User;
use Exception;
use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\rest\ActiveController;
use yii\web\Response;
use yii\web\UploadedFile;

/**
 * AccountPostController implements the CRUD actions for Post model.
 */
class AccountController extends ActiveController
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
            'except' => ['options'],
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

    public function actionLike()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $data = Yii::$app->request->post();
        $postId = $data['postId'];
        $post = Post::findOne(['id' => $postId]);
        $userId = Yii::$app->user->id;

        $like = Like::findOne(['user_id' => $userId, 'post_id' => $postId]);

        try {
            if ($like) {
                if ($like->delete()) {
                    $post->likes_count--;
                    $post->save(false);
                    return [
                        'status' => 'success',
                        'isLiked' => false,
                    ];
                }
            } else {
                $like = new Like();
                $like->user_id = $userId;
                $like->post_id = $postId;

                $post->likes_count++;
                $post->save(false);

                if ($like->save()) {
                    return [
                        'status' => 'success',
                        'isLiked' => true,
                    ];
                }
            }
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Ошибка при обработке лайка: ' . $e->getMessage()
            ];
        }
    }

    public function actionCreateBoard()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $data = Yii::$app->request->post();

            $postId = $data['post_id'];
            $board = new Board();
            $board->user_id = Yii::$app->user->id;
            $board->title = $data['title'];
            $board->visible_id = $data['visible_id'];
            $board->status_id = 1;

            if ($board->save()) {

                if ($postId) {
                    $boardPost = new BoardPost();
                    $boardPost->board_id = $board->id;
                    $boardPost->post_id = $postId;

                    if (!$boardPost->save()) {
                        throw new Exception('Ошибка при добавлении поста в доску: ' . json_encode($boardPost->errors));
                    }
                }

                return [
                    'status' => 'success',
                    'board' => [
                        'id' => $board->id,
                        'title' => $board->title,
                        'visible_id' => $board->visible_id,
                        'images' => $postId ?
                            [[
                                'id' => $boardPost->post->mainImage->id,
                                'path_preview' => '/backend/uploads/' . $boardPost->post->mainImage->path_preview,
                                'width' => $boardPost->post->mainImage->width,
                                'height' => $boardPost->post->mainImage->height,
                            ]] : [],
                        'created_at' => Yii::$app->formatter->asDatetime($board->created_at, 'php:d.m.Y H:i'),
                    ]
                ];
            } else {
                throw new Exception('Ошибка при создании доски: ' . json_encode($board->errors));
            }
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }

    public function actionDeleteBoard()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $data = Yii::$app->request->getBodyParams();
            $boardId = $data['boardId'];
            $userId = Yii::$app->user->id;

            $board = Board::findOne(['id' => $boardId, 'user_id' => $userId]);

            if (!$board) {
                throw new Exception('Доска не найдена или у вас нет прав на её удаление.');
            }

            BoardPost::deleteAll(['board_id' => $boardId]);

            if ($board->delete()) {
                return [
                    'status' => 'success',
                ];
            } else {
                throw new Exception('Ошибка при удалении доски: ' . json_encode($board->errors));
            }
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }

    public function actionSavePost()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $data = Yii::$app->request->post();

        $boardId = $data['boardId'];
        $postId = $data['postId'];
        $userId = Yii::$app->user->id;

        try {
            if ($boardId == 'favorites') {
                $favorites = Favorites::findOne(['user_id' => $userId, 'post_id' => $postId]);

                if (!$favorites) {
                    $favorites = new Favorites();
                    $favorites->user_id = $userId;
                    $favorites->post_id = $postId;
                    $favorites->save();
                } else {
                    $favorites->delete();
                }
            } else {
                $board = Board::findOne(['id' => $boardId, 'user_id' => $userId]);

                if ($board) {
                    $boardPost = BoardPost::findOne(['board_id' => $boardId, 'post_id' => $postId]);

                    if (!$boardPost) {
                        $boardPost = new BoardPost();
                        $boardPost->board_id = $boardId;
                        $boardPost->post_id = $postId;
                        $boardPost->save();
                    } else {
                        $boardPost->delete();
                    }
                } else {
                    throw new Exception('Доска не найдена или у вас нет прав.');
                }
            }

            return [
                'status' => 'success',
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }

    // Обновление аватарки
    public function actionUpdateAvatar()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        try {
            $user = User::findOne(['id' => Yii::$app->user->id]);

            $avatarFile = UploadedFile::getInstanceByName('avatarFile');
            if ($avatarFile) {

                $uploadForm = new UploadForm();
                $uploadForm->imageFile = $avatarFile;

                if ($path = $uploadForm->uploadPreview('uploads')) {

                    $user->avatar_path = $path;

                    if ($user->save(false)) {
                        return [
                            'status' => 'success',
                            'avatar_path' => '/backend/uploads/' . $path
                        ];
                    }
                } else {
                    return [$path];
                }
                throw new Exception('Ошибка загрузки файла');
            }
            throw new Exception('Файл не найден');
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
                '1' => (string) $avatarFile,
            ];
        }
    }

    // Обновление фонового изображения
    public function actionUpdateBgImage()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        try {
            $user = User::findOne(['id' => Yii::$app->user->id]);

            $avatarFile = UploadedFile::getInstanceByName('bgImageFile');
            if ($avatarFile) {

                $uploadForm = new UploadForm();
                $uploadForm->imageFile = $avatarFile;

                if ($path = $uploadForm->upload('uploads')) {

                    $user->background_path = $path;

                    if ($user->save(false)) {
                        return [
                            'status' => 'success',
                            'background_path' => '/backend/uploads/' . $path
                        ];
                    }
                } else {
                    return [$path];
                }
                throw new Exception('Ошибка загрузки файла');
            }
            throw new Exception('Файл не найден');
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
                '1' => (string) $avatarFile,
            ];
        }
    }
}
