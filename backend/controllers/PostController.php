<?php

namespace app\controllers;

use app\models\Image;
use app\models\Post;
use app\models\PostImage;
use app\models\PostTag;
use app\models\Tag;
use app\models\ItemLink;
use app\models\OutfitItem;
use app\models\ReasonDelete;
use app\models\UploadForm;
use BcMath\Number;
use Exception;
use Throwable;
use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\rest\ActiveController;
use yii\web\Response;
use yii\web\UploadedFile;

class PostController extends ActiveController
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

    // Публикация образа
    public function actionCreatePost()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            // Post
            $data = Yii::$app->request->post();
            $post = new Post();

            $post->type_id = $data['type_id'];
            $post->user_id = Yii::$app->user->id;
            $post->title = $data['title'];
            $post->description = $data['description'];
            $post->category_id = 1;
            $post->visible_id = $data['visible_id'];
            $post->status_id = 4;

            if (!$post->save()) {
                throw new Exception('Ошибка сохранения поста: ' . json_encode($post->errors));
            }

            // Images
            $uploadForm = new UploadForm();
            $images = UploadedFile::getInstancesByName('images');

            foreach ($images as $image) {
                $imageModel = new Image();
                $uploadForm->imageFile = $image;

                // Получаем размеры изображения
                [$width, $height] = @getimagesize($image->tempName);
                $imageModel->width = $width;
                $imageModel->height = $height;

                if (
                    ($imageModel->path_preview = $uploadForm->uploadPreview('uploads'))
                    && ($imageModel->path = $uploadForm->upload('uploads'))
                ) {

                    if ($imageModel->save()) {
                        $postImageModel = new PostImage();
                        $postImageModel->post_id = $post->id;
                        $postImageModel->image_id = $imageModel->id;

                        if (!$postImageModel->save()) {
                            throw new Exception(json_encode($postImageModel->errors));
                        }

                        // Устанавливаем начальные изображения
                        if (!$post->main_image_id) {
                            $post->main_image_id = $imageModel->id;
                            $post->save();
                        }
                    } else {
                        return ['status' => 'error', 'message' => $imageModel->errors, 'width' => $width, 'height' => $height, '$path' => $image];
                    }
                }
            }

            // Tags
            foreach ($data['tags'] as $tag) {

                // Tag create
                $tagModel = new Tag();

                if (!$tagModel = Tag::findOne(['title' => $tag])) {
                    $tagModel = new Tag();
                    $tagModel->title = $tag;
                    $tagModel->save();
                }

                $postTagModel = new PostTag();
                $postTagModel->post_id = $post->id;
                $postTagModel->tag_id = $tagModel->id;
                $postTagModel->save();
            }

            // Links (только для вещей)
            if (isset($data['links']) && is_array($data['links'])) {
                foreach ($data['links'] as $url) {
                    if (empty($url)) continue;

                    $itemLinkModel = new ItemLink();
                    $itemLinkModel->post_id = $post->id;
                    $itemLinkModel->link = $url;

                    if (!$itemLinkModel->save()) {
                        throw new Exception('Ошибка сохранения ссылки: ' . json_encode($itemLinkModel->errors));
                    }
                }
            }

            // Items (для образов)
            if (isset($data['item_ids']) && is_array($data['item_ids'])) {
                foreach ($data['item_ids'] as $itemId) {
                    if (empty($itemId)) continue;

                    $outfitItemModel = new OutfitItem();
                    $outfitItemModel->outfit_id = $post->id;
                    $outfitItemModel->item_id = (int)$itemId;

                    if (!$outfitItemModel->save()) {
                        throw new Exception('Ошибка сохранения вещи в образе: ' . json_encode($outfitItemModel->errors));
                    }
                }
            }

            return [
                'status' => 'success',
                'postId' => $post->id,
            ];
        } catch (Exception $e) {
            if (isset($post) && !$post->isNewRecord) {
                $post->delete();
            }

            return [
                'status' => 'error',
                'message' => 'Ошибка загрузки вещи: ' . $e->getMessage()
            ];
        }
    }

    // Удаление поста (Только для автора)
    public function actionDeletePost()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $postId = Yii::$app->request->post('postId');
            $userId = Yii::$app->user->id;

            $post = Post::findOne(['id' => $postId, 'user_id' => $userId]);

            if ($post) {
                $post->status_id = 2;
                if ($post->save(false)) {
                    $reasonDelete = new ReasonDelete();
                    $reasonDelete->post_id = $postId;
                    $reasonDelete->user_id = $userId;
                    $reasonDelete->reason = 'Удалено автором';
                    if (!$reasonDelete->save()) {
                        throw new Exception('Ошибка удаления поста: ' . json_encode($reasonDelete->errors, JSON_UNESCAPED_UNICODE));
                    }
                } else {
                    throw new Exception('Ошибка удаления поста: ' . json_encode($post->errors, JSON_UNESCAPED_UNICODE));
                }
            } else {
                throw new Exception('Пост не найден');
            }

            return [
                'status' => 'success',
            ];
        } catch (Throwable $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }
}
