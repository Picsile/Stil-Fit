<?php

namespace app\controllers;

use app\models\Board;
use app\models\Generation;
use app\models\Tag;
use Yii;
use app\models\Post;
use app\models\User;
use app\models\ViewedPost;
use Exception;
use yii\db\Expression;
use yii\db\Query;
use yii\filters\auth\HttpBearerAuth;
use yii\rest\ActiveController;
use yii\web\Response;

class ViewController extends ActiveController
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

        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
            'except' => ['options'],
            'optional' => [
                'posts-system-board',
                'posts-for-board',
                'search-posts',
                'similar',
                'outfits-with-item',
                'post',
                'board',
                'profile',
                'posts-by-ids',
                'boards',
                'tags',
                'file',
                'generations',
            ],
        ];

        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();

        unset($actions['delete'], $actions['create'], $actions['view']);

        return $actions;
    }

    // Преобразовать данные поста в короткий вид
    private function getPostPreviewData($post)
    {
        $mainImage = $post->mainImage;
        $userId = Yii::$app->user->id;

        return [
            'id' => $post->id,
            'title' => $post->title,
            'description' => $post->description,
            'main_image' => [
                'id' => $mainImage->id,
                'path_preview' => '/backend/uploads/' . $mainImage->path_preview,
                'width' => $mainImage->width,
                'height' => $mainImage->height,
            ],
            'author' => [
                'id' => $post->user->id,
                'login' => $post->user->login,
                'avatar_path' => $post->user->avatar_path ? '/backend/uploads/' . $post->user->avatar_path : null,
            ],
            'likes_count' => $post->likes_count,
            'is_liked' => $post->getLikes()->where(['user_id' => $userId])->exists() ? true : false,
            'is_saved' => $post->getBoards()->where(['user_id' => $userId])->exists() ? true : false,
            'saved_board_ids' => $userId ? $post->getBoards()->where(['user_id' => $userId])->select('board.id')->column() : [],
        ];
    }

    // Получаем видимые посты по userId
    private function getVisiblePosts($userId)
    {
        return Post::find()->andWhere([
            'and',
            ['status_id' => 1],
            [
                'or',
                ['user_id' => $userId],
                ['visible_id' => 1]
            ]
        ]);
    }

    private function getViewedPostIds($userId)
    {
        if (!$userId) {
            return [];
        }

        return ViewedPost::find()
            ->select('post_id')
            ->where(['user_id' => $userId])
            ->column();
    }

    private function markPostsAsViewed($userId, $posts)
    {
        if (!$userId || empty($posts)) {
            return;
        }

        $postIds = array_values(array_unique(array_map(static fn($post) => (int)$post->id, $posts)));
        if (empty($postIds)) {
            return;
        }

        $existingIds = ViewedPost::find()
            ->select('post_id')
            ->where(['user_id' => $userId, 'post_id' => $postIds])
            ->column();

        $newIds = array_values(array_diff($postIds, $existingIds));
        if (empty($newIds)) {
            return;
        }

        $rows = array_map(static fn($postId) => [$userId, $postId], $newIds);
        Yii::$app->db->createCommand()
            ->batchInsert(ViewedPost::tableName(), ['user_id', 'post_id'], $rows)
            ->execute();
    }

    private function getHybridFeed($tagQuery, $generalQuery, $userId, $limit)
    {
        $viewedIds = $this->getViewedPostIds($userId);

        // 1. Новые посты по тегам
        $posts = (clone $tagQuery)
            ->andWhere(['not in', 'post.id', $viewedIds ?: [0]])
            ->limit($limit)
            ->all();

        if (count($posts) < $limit) {
            $remaining = (int)$limit - count($posts);
            $selectedIds = array_map(static fn($post) => (int)$post->id, $posts);
            $excludeIds = array_merge($viewedIds, $selectedIds);

            // 2. Новые посты без учета тегов (добивка)
            $extraPosts = (clone $generalQuery)
                ->andWhere(['not in', 'post.id', $excludeIds ?: [0]])
                ->limit($remaining)
                ->all();

            $posts = array_merge($posts, $extraPosts);
        }

        // 3. Если всё еще не хватает — значит посмотрели вообще всё. Чистим историю.
        if (count($posts) < $limit) {
            ViewedPost::deleteAll(['user_id' => $userId]);

            $selectedIds = array_map(static fn($post) => (int)$post->id, $posts);
            $remaining = (int)$limit - count($posts);

            // Повторяем поиск по тегам после сброса
            $restartTagPosts = (clone $tagQuery)
                ->andWhere(['not in', 'post.id', $selectedIds ?: [0]])
                ->limit($remaining)
                ->all();

            $posts = array_merge($posts, $restartTagPosts);

            if (count($posts) < $limit) {
                $remaining = (int)$limit - count($posts);
                $selectedIds = array_map(static fn($post) => (int)$post->id, $posts);

                $restartGeneralPosts = (clone $generalQuery)
                    ->andWhere(['not in', 'post.id', $selectedIds ?: [0]])
                    ->limit($remaining)
                    ->all();

                $posts = array_merge($posts, $restartGeneralPosts);
            }
        }

        $this->markPostsAsViewed($userId, $posts);

        return $posts;
    }

    // Получение постов по разделу
    public function actionPosts($boardId, $offset = 0, $limit = 20, $userId = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            // Определяем пользователя (запрошенный или текущий)
            if (!$userId) {
                $userId = Yii::$app->user->id;
            }

            $posts = [];

            // Посты для конкретной доски (рекомендации)
            if (is_numeric($boardId)) {
                return $this->actionPostsForBoard($boardId, $offset, $limit, 1);
            } else {
                // Посты по категориям
                switch ($boardId) {
                    case 'top':
                        // Ранжирование: "Лайки / (Часы + 2)^1.8"
                        $score = new Expression("(post.likes_count + 1) / POW(TIMESTAMPDIFF(HOUR, post.created_at, NOW()) + 2, 1.8)");
                        $topQuery = Post::find()
                            ->where(['post.status_id' => 1, 'post.visible_id' => 1])
                            ->orderBy(new Expression($score . " DESC, post.created_at DESC"));

                        $posts = $this->getHybridFeed($topQuery, $topQuery, $userId, (int)$limit);
                        break;

                    case 'foryou':
                        if (!$userId) {
                            return $this->actionPosts('top', $offset, $limit);
                        }

                        // Последние взаимодействия (лайки, избранное, доски)
                        $likesIds = (new Query())->select('post_id')->from('like')->where(['user_id' => $userId])->orderBy(['post_id' => SORT_DESC])->limit(15)->column();
                        $favoritesIds = (new Query())->select('post_id')->from('favorites')->where(['user_id' => $userId])->orderBy(['post_id' => SORT_DESC])->limit(15)->column();

                        $boardIds = (new Query())->select('id')->from('board')->where(['user_id' => $userId])->column();
                        $boardPostIds = [];
                        foreach ($boardIds as $bId) {
                            $ids = (new Query())->select('post_id')->from('board_post')->where(['board_id' => $bId])->orderBy(['post_id' => SORT_DESC])->limit(15)->column();
                            $boardPostIds = array_merge($boardPostIds, $ids);
                        }

                        $allInteractedIds = array_unique(array_merge($likesIds, $favoritesIds, $boardPostIds));

                        // Теги из взаимодействий
                        $tagIds = (new Query())->select('tag_id')->from('post_tag')->where(['post_id' => $allInteractedIds ?: [0]])->distinct()->column();

                        // Если нет истории, показываем топ
                        if (empty($tagIds)) {
                            return $this->actionPosts('top', $offset, $limit);
                        }

                        $score = new Expression("(post.likes_count + 1) / POW(TIMESTAMPDIFF(HOUR, post.created_at, NOW()) + 2, 1.8)");

                        // 1. Посты по тегам
                        $tagQuery = Post::find()
                            ->innerJoin('post_tag', 'post_tag.post_id = post.id')
                            ->where(['post_tag.tag_id' => $tagIds])
                            ->andWhere(['post.status_id' => 1, 'post.visible_id' => 1])
                            ->groupBy('post.id')
                            ->orderBy(new Expression($score . " DESC, post.created_at DESC"));

                        // 2. Общий поток для добивки
                        $generalQuery = Post::find()
                            ->where(['post.status_id' => 1, 'post.visible_id' => 1])
                            ->orderBy(new Expression($score . " DESC, post.created_at DESC"));

                        $posts = $this->getHybridFeed($tagQuery, $generalQuery, $userId, (int)$limit);
                        break;

                    case 'likes':
                        $posts = $this->getVisiblePosts($userId)
                            ->innerJoin('like l', 'l.post_id = post.id')
                            ->where(['l.user_id' => $userId])
                            ->orderBy(['l.post_id' => SORT_DESC])
                            ->limit($limit)
                            ->offset($offset)
                            ->all();
                        break;

                    case 'favorites':
                        $posts = $this->getVisiblePosts($userId)
                            ->innerJoin('favorites f', 'f.post_id = post.id')
                            ->where(['f.user_id' => $userId])
                            ->orderBy(['f.post_id' => SORT_DESC])
                            ->limit($limit)
                            ->offset($offset)
                            ->all();
                        break;

                    case 'my-posts':
                        $posts = $this->getVisiblePosts($userId)
                            ->where(['user_id' => $userId])
                            ->orderBy(['id' => SORT_DESC])
                            ->limit($limit)
                            ->offset($offset)
                            ->all();
                        break;
                }
            }

            return [
                'status' => 'success',
                'posts' => array_map([$this, 'getPostPreviewData'], $posts)
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Ошибка при получении ленты постов: ' . $e->getMessage()
            ];
        }
    }

    // Похожие посты для доски
    public function actionPostsForBoard($boardId, $offset = 0, $limit = 20, $isSimilar = 0)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $userId = Yii::$app->user->id;

            // Получаем доску
            $board = Board::find()
                ->where(['id' => $boardId])
                ->andWhere(['or', ['user_id' => $userId], ['visible_id' => 1]])
                ->one();

            // Если доска не найдена, возвращаем ошибку
            if (!$board) throw new Exception('Доска не найдена');

            if ((int)$isSimilar === 1) {
                $allBoardPostIds = (new Query())
                    ->select('post_id')
                    ->from('board_post')
                    ->where(['board_id' => $boardId])
                    ->column();

                $lastBoardPostIds = (new Query())
                    ->select('post_id')
                    ->from('board_post')
                    ->where(['board_id' => $boardId])
                    ->orderBy(['id' => SORT_DESC])
                    ->limit(15)
                    ->column();

                if (empty($lastBoardPostIds)) {
                    return [
                        'status' => 'success',
                        'posts' => []
                    ];
                }

                $tagIds = (new Query())
                    ->select('tag_id')
                    ->from('post_tag')
                    ->where(['post_id' => $lastBoardPostIds])
                    ->distinct()
                    ->column();

                if (empty($tagIds)) {
                    return [
                        'status' => 'success',
                        'posts' => []
                    ];
                }

                $relevanceExpr = new Expression('COUNT(post_tag.tag_id)');
                $tagQuery = Post::find()
                    ->innerJoin('post_tag', 'post_tag.post_id = post.id')
                    ->where(['post_tag.tag_id' => $tagIds])
                    ->andWhere(['post.status_id' => 1, 'post.visible_id' => 1])
                    ->andWhere(['not in', 'post.id', $allBoardPostIds])
                    ->groupBy('post.id')
                    ->orderBy(new Expression($relevanceExpr . ' DESC, post.created_at DESC'));

                $generalQuery = Post::find()
                    ->where(['post.status_id' => 1, 'post.visible_id' => 1])
                    ->andWhere(['not in', 'post.id', $allBoardPostIds])
                    ->orderBy(['post.created_at' => SORT_DESC]);

                if ($userId) {
                    $posts = $this->getHybridFeed($tagQuery, $generalQuery, $userId, (int)$limit);
                } else {
                    $posts = $tagQuery
                        ->limit($limit)
                        ->offset($offset)
                        ->all();
                }
            } else {
                $posts = Post::find()
                    ->innerJoin('board_post', 'board_post.post_id = post.id')
                    ->where(['board_post.board_id' => $boardId])
                    ->andWhere(['post.status_id' => 1])
                    ->orderBy(['board_post.created_at' => SORT_DESC])
                    ->limit($limit)
                    ->offset($offset)
                    ->all();
            }

            return [
                'status' => 'success',
                'posts' => array_map([$this, 'getPostPreviewData'], $posts)
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Ошибка получения постов доски: ' . $e->getMessage()
            ];
        }
    }

    // Поиск по строке
    public function actionSearchPosts($query, $offset = 0, $limit = 20)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $trimmedQuery = trim((string)$query);
            if ($trimmedQuery === '') {
                return [
                    'status' => 'success',
                    'posts' => [],
                ];
            }

            $queryLength = mb_strlen($trimmedQuery);
            $safeQueryLength = max($queryLength, 1);

            $relevanceExpr = new Expression(
                "(
                    (
                        (CHAR_LENGTH(LOWER(IFNULL(post.title, ''))) - CHAR_LENGTH(REPLACE(LOWER(IFNULL(post.title, '')), LOWER(:searchQuery), ''))) / :queryLength
                    ) * 3
                    +
                    (
                        (CHAR_LENGTH(LOWER(IFNULL(post.description, ''))) - CHAR_LENGTH(REPLACE(LOWER(IFNULL(post.description, '')), LOWER(:searchQuery), ''))) / :queryLength
                    ) * 2
                    +
                    COALESCE(SUM(
                        (CHAR_LENGTH(LOWER(IFNULL(t.title, ''))) - CHAR_LENGTH(REPLACE(LOWER(IFNULL(t.title, '')), LOWER(:searchQuery), ''))) / :queryLength
                    ), 0)
                )"
            );

            $posts = Post::find()
                ->where(['post.status_id' => 1, 'post.visible_id' => 1])
                ->joinWith('postTags.tag t', false)
                ->andWhere([
                    'or',
                    ['like', 'post.title', $trimmedQuery],
                    ['like', 'post.description', $trimmedQuery],
                    ['like', 't.title', $trimmedQuery]
                ])
                ->with(['user', 'mainImage'])
                ->groupBy('post.id')
                ->addSelect([
                    'post.*',
                    'relevance' => $relevanceExpr,
                ])
                ->addParams([
                    ':searchQuery' => $trimmedQuery,
                    ':queryLength' => $safeQueryLength,
                ])
                ->orderBy([
                    'relevance' => SORT_DESC,
                    'post.created_at' => SORT_DESC,
                ])
                ->offset($offset)
                ->limit($limit)
                ->all();

            return [
                'status' => 'success',
                'posts' => array_map([$this, 'getPostPreviewData'], $posts)
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Ошибка поиска: ' . $e->getMessage()
            ];
        }
    }

    // Поиск похожих постов
    public function actionSimilar($id, $offset = 0, $limit = 20)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $post = Post::findOne($id);
            if (!$post) {
                return ['status' => 'error', 'message' => 'Пост не найден'];
            }

            // Получаем теги текущего поста
            $tagIds = $post->getPostTags()->select('tag_id')->column();

            $query = Post::find()
                ->where(['post.status_id' => 1, 'post.visible_id' => 1])
                ->with(['user', 'mainImage'])
                ->andWhere(['!=', 'post.id', $id]);

            if (!empty($tagIds)) {
                // Ищем посты с такими же тегами
                $query->joinWith('postTags pt', false)
                    ->andWhere(['pt.tag_id' => $tagIds])
                    ->groupBy('post.id')
                    ->orderBy(['COUNT(pt.tag_id)' => SORT_DESC, 'post.created_at' => SORT_DESC]); // Сортируем по количеству совпадений
            } else {
                $query->orderBy(['post.created_at' => SORT_DESC]);
            }

            $posts = $query->offset($offset)->limit($limit)->all();

            // Если совпадений по тегам нет, отдаём последние посты как fallback
            if (empty($posts) && !empty($tagIds)) {
                $posts = Post::find()
                    ->where(['post.status_id' => 1, 'post.visible_id' => 1])
                    ->andWhere(['!=', 'post.id', $id])
                    ->with(['user', 'mainImage'])
                    ->orderBy(['post.created_at' => SORT_DESC])
                    ->offset($offset)
                    ->limit($limit)
                    ->all();
            }

            return [
                'status' => 'success',
                'posts' => array_map([$this, 'getPostPreviewData'], $posts)
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Ошибка получения похожих постов: ' . $e->getMessage()
            ];
        }
    }

    // Образы с вещью
    public function actionOutfitsWithItem($id, $offset = 0, $limit = 20)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        try {
            $posts = Post::find()
                ->where(['post.status_id' => 1, 'post.visible_id' => 1, 'post.type_id' => 2])
                ->innerJoin('outfit_item oi', 'oi.outfit_id = post.id')
                ->andWhere(['oi.item_id' => $id])
                ->with(['user', 'mainImage'])
                ->offset($offset)
                ->limit($limit)
                ->all();

            return [
                'status' => 'success',
                'outfits' => array_map([$this, 'getPostPreviewData'], $posts)
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Ошибка получения образов: ' . $e->getMessage()
            ];
        }
    }

    // Получаем сам пост
    public function actionGetPost($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $post = Post::find()
                ->where(['id' => $id, 'status_id' => 1])
                ->with(['user', 'postImages.image', 'postTags.tag', 'itemLinks'])
                ->one();

            if (!$post) {
                return ['status' => 'error', 'message' => 'Пост не найден'];
            }

            $userId = Yii::$app->user->id;
            $isLiked = $userId ? $post->getLikes()->where(['user_id' => $userId])->exists() : false;
            $isSaved = $userId ? $post->getFavorites()->where(['user_id' => $userId])->exists() : false;

            $images = [];
            foreach ($post->postImages as $pi) {
                if ($pi->image) {
                    $images[] = [
                        'id' => $pi->image->id,
                        'path' => '/backend/uploads/' . $pi->image->path,
                        'width' => $pi->image->width,
                        'height' => $pi->image->height
                    ];
                }
            }

            $tags = array_map(fn($pt) => [
                'id' => $pt->tag->id,
                'title' => $pt->tag->title
            ], $post->postTags);

            $links = array_map(fn($il) => [
                'id' => $il->post_id,
                'url' => $il->link
            ], $post->itemLinks);

            // Загружаем items для образов
            $items = [];
            if ($post->type_id == 2) {
                $outfitItems = $post->getOutfitItems0()->with(['item.user', 'item.mainImage'])->all();
                foreach ($outfitItems as $oi) {
                    if ($oi->item) {
                        $items[] = $this->getPostPreviewData($oi->item);
                    }
                }
                $items = array_filter($items); // Убираем null
            }

            return [
                'status' => 'success',
                'post' => [
                    'id' => $post->id,
                    'type_id' => $post->type_id,
                    'visible' => $post->visible_id,
                    'images' => $images,
                    'title' => $post->title,
                    'description' => $post->description,
                    'tags' => $tags,
                    'links' => $links,
                    'items' => $items,
                    'likes_count' => (int)$post->likes_count,
                    'created_at' => $post->created_at,
                    'author' => [
                        'id' => $post->user->id,
                        'login' => $post->user->login,
                        'avatar_path' => $post->user->avatar_path ? '/backend/uploads/' . $post->user->avatar_path : null,
                    ],
                    'isLiked' => $isLiked,
                    'isSaved' => $isSaved,
                ]
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Ошибка получения поста: ' . $e->getMessage()
            ];
        }
    }

    // Получение постов по массиву ID
    public function actionGetPostsByIds()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $request = Yii::$app->request;

        try {
            $ids = $request->post('ids', []);

            if (empty($ids) || !is_array($ids)) {
                return [
                    'status' => 'success',
                    'posts' => []
                ];
            }

            $posts = Post::find()
                ->where(['id' => $ids, 'status_id' => 1, 'visible_id' => 1])
                ->with(['user', 'mainImage'])
                ->all();

            return [
                'status' => 'success',
                'posts' => array_map([$this, 'getPostPreviewData'], $posts)
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Ошибка получения постов: ' . $e->getMessage()
            ];
        }
    }

    // Получить генерации
    public function actionGetGenerations($offset = 0, $limit = 5): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            if (Yii::$app->user->isGuest) {
                return [
                    'status' => 'success',
                    'generations' => [],
                ];
            }

            $generationsData = Generation::find()
                ->where(['user_id' => Yii::$app->user->id])
                ->orderBy(['created_at' => SORT_DESC])
                ->limit($limit)
                ->offset($offset)
                ->all();

            $generations = array_map(
                function ($generationData) {
                    // Получаем изображения из generation_tasks
                    $generatedImages = array_map(
                        function ($task) {
                            if ($task->image_id && $task->image) {
                                return [
                                    'id' => $task->image->id,
                                    'path' => '/backend/uploads/' . $task->image->path,
                                    'width' => $task->image->width,
                                    'height' => $task->image->height,
                                ];
                            }
                            return null;
                        },
                        $generationData->generationTasks
                    );

                    // Фильтруем null значения
                    $generatedImages = array_values(array_filter($generatedImages));

                    return [
                        'id' => $generationData->id,
                        'visible_id' =>  $generationData->visible_id,
                        'prompt' =>  $generationData->prompt,
                        'ratio' =>  $generationData->ratio->value,
                        'resolution' =>  $generationData->resolution->value,
                        'quantity' =>  $generationData->quantity,
                        'appearances'  => [],
                        'items' => array_map(
                            fn($item) => $this->getPostPreviewData($item->post),
                            $generationData->generationItems
                        ),
                        'generated_images'  => $generatedImages,
                        'status' => $generationData->status ? $generationData->status->title : 'unknown',
                        'error' => $generationData->error ?? null,
                        'created_at' => Yii::$app->formatter->asDatetime($generationData->created_at, 'php:d.m.Y H:i'),
                    ];
                },
                $generationsData
            );

            return [
                'status' => 'success',
                'generations' => $generations,
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Ошибка при получении генераций пользователя: ' . $e->getMessage()
            ];
        }
    }

    // Получить доску
    public function actionGetBoard($boardId)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $board = Board::find()
                ->where(['id' => $boardId])
                ->with(['user'])
                ->one();

            if (!$board) {
                return ['status' => 'error', 'message' => 'Доска не найдена'];
            }

            $userId = Yii::$app->user->id;
            if ($board->visible_id === 2 && $board->user_id !== $userId) {
                return ['status' => 'error', 'message' => 'Нет доступа к доске'];
            }

            return [
                'status' => 'success',
                'board' => [
                    'id' => $board->id,
                    'title' => $board->title,
                    'visible_id' => $board->visible_id,
                    'author' => [
                        'id' => $board->user->id,
                        'login' => $board->user->login,
                        'avatar_path' => $board->user->avatar_path ? '/backend/uploads/' . $board->user->avatar_path : null,
                    ],
                    'created_at' => $board->created_at,
                ]
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Ошибка получения доски: ' . $e->getMessage()
            ];
        }
    }

    // Получить профиль пользователя
    public function actionGetProfile($userId)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $user = User::find()
                ->where(['id' => $userId])
                ->one();

            if (!$user) {
                return ['status' => 'error', 'message' => 'Пользователь не найден'];
            }

            // Количество постов пользователя
            $postsCount = Post::find()
                ->where(['user_id' => $userId, 'status_id' => 1])
                ->count();

            // Количество лайков на посты пользователя
            $likesCount = (new Query())
                ->from('like')
                ->innerJoin('post', 'post.id = like.post_id')
                ->where(['post.user_id' => $userId, 'post.status_id' => 1])
                ->count();

            return [
                'status' => 'success',
                'profile' => [
                    'id' => $user->id,
                    'login' => $user->login,
                    'avatar_path' => $user->avatar_path ? '/backend/uploads/' . $user->avatar_path : null,
                    'background_path' => $user->background_path ? '/backend/uploads/' . $user->background_path : null,
                    'posts_count' => (int)$postsCount,
                    'likes_count' => (int)$likesCount,
                ]
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Ошибка получения профиля: ' . $e->getMessage()
            ];
        }
    }

    // Получение досок по id пользователя
    public function actionGetBoards($userId): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $boardsData = Board::find()
                ->where(['user_id' => $userId])
                ->with([
                    'boardPosts' => function ($query) {
                        $query->orderBy(['created_at' => SORT_DESC]);
                    },
                    'boardPosts.post.mainImage'
                ])
                ->orderBy(['created_at' => SORT_DESC])
                ->all();

            $boards = array_map(
                function ($boardData) {
                    return [
                        'id' => $boardData->id,
                        'title' =>  $boardData->title,
                        'visible_id' => $boardData->visible_id,
                        'images' => array_map(function ($boardPost) {
                            $image = $boardPost->post->mainImage;
                            return [
                                'id' => $image->id,
                                'path_preview' => '/backend/uploads/' . $image->path_preview,
                                'width' => $image->width,
                                'height' => $image->height,
                            ];
                        }, $boardData->boardPosts),
                        'created_at' => Yii::$app->formatter->asDatetime($boardData->created_at, 'php:d.m.Y H:i'),
                    ];
                },
                $boardsData
            );

            return [
                'status' => 'success',
                'boards' => $boards,
                "a" => $boardsData,
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Ошибка при получении досок пользователя: ' . $e->getMessage(),
                "a" => $boardsData,
            ];
        }
    }

    // Получение тегов по запросу
    public function actionGetTags($query)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $tags = Tag::find()
                ->where(['like', 'title', $query])
                ->orderBy(['id' => SORT_DESC])
                ->limit(6)
                ->all();

            return [
                'status' => 'success',
                'tags' => array_map(fn($tag) => [
                    'id' => $tag->id,
                    'title' => $tag->title
                ], $tags)
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Ошибка получения тегов: ' . $e->getMessage()
            ];
        }
    }

    // Получения файла
    public function actionGetFile($url)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $path = Yii::getAlias('@webroot/uploads/') . basename($url);

            if (!file_exists($path) || !is_file($path)) {
                throw new Exception("Файл не найден по пути: " . $path);
            }

            return Yii::$app->response->sendFile($path);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}
