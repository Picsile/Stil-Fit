<?php

namespace app\controllers;

use app\models\Board;
use app\models\Generation;
use app\models\Tag;
use Yii;
use app\models\Post;
use app\models\PostTag;
use app\models\User;
use app\models\ViewedPost;
use Throwable;
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

    // --- Приватные методы

    // Получить полный url файла
    private function getUrlPreview(string $path): string
    {
        return '/backend/uploads/' . $path;
    }

    // Преобразовать данные поста в короткий вид
    private function getPostPreviewData(object $post): array
    {
        $mainImage = $post->mainImage;
        $userId = Yii::$app->user->id;

        return [
            'id' => $post->id,
            'title' => $post->title,
            'description' => $post->description,
            'main_image' => [
                'id' => $mainImage->id,
                'path_preview' => $this->getUrlPreview($mainImage->path_preview),
                'width' => $mainImage->width,
                'height' => $mainImage->height,
            ],
            'author' => [
                'id' => $post->user->id,
                'login' => $post->user->login,
                'avatar_path' => $post->user->avatar_path ? $this->getUrlPreview($post->user->avatar_path) : null,
            ],
            'likes_count' => $post->likes_count,
            'is_liked' => $post->getLikes()->where(['user_id' => $userId])->exists() ? true : false,
            'is_saved' => $post->getBoards()->where(['user_id' => $userId])->exists() ? true : false,
            'saved_board_ids' => $userId ? $post->getBoards()->where(['user_id' => $userId])->select('board.id')->column() : [],
        ];
    }

    // --- Публичные методы (Сложная логика)

    // Получение постов системной доски
    public function actionPostsSystemBoard(string $boardId, $offset = 0, $limit = 20, $userId = null): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $posts = [];
            if (!$userId) $userId = Yii::$app->user->id;

            switch ($boardId) {
                case 'top':
                    $posts = Post::find()
                        ->active()
                        ->sortByRelevance()
                        ->offset((int)$offset)
                        ->limit((int)$limit)
                        ->all();
                    break;

                case 'foryou':
                    $viewedIds = ViewedPost::find()
                        ->select('post_id')
                        ->where(['user_id' => $userId])
                        ->column();

                    $posts = Post::find()
                        ->andWhere(['status_id' => 1, 'visible_id' => 1])
                        ->andWhere(['not in', 'id', $viewedIds])
                        ->sortByRelevance()
                        ->limit((int)$limit)
                        ->all();

                    if (empty($posts)) {
                        ViewedPost::deleteAll(['user_id' => $userId]);

                        $posts = Post::find()
                            ->active()
                            ->sortByRelevance()
                            ->limit((int)$limit)
                            ->all();
                    }
                    $this->markAsViewed($posts);
                    break;

                case 'likes':
                case 'favorites':
                case 'my-posts':
                    if ($boardId === 'my-posts') {
                        $query = Post::find()
                            ->andWhere(['post.user_id' => $userId, 'post.status_id' => [1, 4]])
                            ->select(['post.*', 'post.created_at AS action_date']);
                    } else {
                        $query = Post::find()->activeOrOwnerModeration($userId);

                        if ($boardId === 'likes') {
                            $query->innerJoin('`like`', '`like`.post_id = post.id')
                                ->andWhere(['`like`.user_id' => $userId])
                                ->select(['post.*', '`like`.created_at AS action_date']);
                        } else {
                            $query->innerJoin('`favorite`', '`favorite`.post_id = post.id')
                                ->andWhere(['`favorite`.user_id' => $userId])
                                ->select(['post.*', '`favorite`.created_at AS action_date']);
                        }
                    }

                    $posts = $query
                        ->orderBy(['action_date' => SORT_DESC])
                        ->offset((int)$offset)
                        ->limit((int)$limit)
                        ->all();
                    break;
            }

            return [
                'status' => 'success',
                'posts' => array_map([$this, 'getPostPreviewData'], $posts),
            ];
        } catch (Throwable $e) {
            return [
                'status' => 'error',
                'message' => 'Ошибка при получении постов системной доски: ' . $e->getMessage()
            ];
        }
    }

    // Получение постов для доски
    public function actionPostsForBoard(string $boardId, $limit = 20): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $posts = [];
            $userId = Yii::$app->user->id;

            $nativePostIds = Post::find()
                ->select('post.id')
                ->innerJoin('board_post', 'board_post.post_id = post.id')
                ->where(['board_post.board_id' => $boardId, 'post.status_id' => 1, 'post.visible_id' => 1])
                ->column();

            $viewedIds = ViewedPost::find()
                ->select('post_id')
                ->where(['user_id' => $userId])
                ->column();

            $excludeIds = array_merge($nativePostIds, $viewedIds);

            $tagIds = PostTag::find()
                ->select('tag_id')
                ->innerJoin('board_post', 'board_post.post_id = post_tag.post_id')
                ->where(['board_post.board_id' => $boardId])
                ->limit(15)
                ->column();

            if (!empty($tagIds)) {
                $posts = Post::find()
                    ->active()
                    ->innerJoin('post_tag', 'post_tag.post_id = post.id')
                    ->where(['post_tag.tag_id' => $tagIds])
                    ->andWhere(['not in', 'post.id', $excludeIds])
                    ->sortByRelevance()
                    ->limit((int)$limit)
                    ->all();
            }

            if (count($posts) < $limit) {
                $extraPosts = Post::find()
                    ->active()
                    ->andWhere(['not in', 'id', array_merge($excludeIds, array_column($posts, 'id'))])
                    ->sortByRelevance()
                    ->limit((int)$limit - count($posts))
                    ->all();
                $posts = array_merge($posts, $extraPosts);
            }

            if (empty($posts)) {
                ViewedPost::deleteAll(['user_id' => $userId]);

                $posts = Post::find()
                    ->innerJoin('viewed_post', 'viewed_post.post_id = post.id')
                    ->innerJoin('board_post', 'board_post.post_id = post.id')
                    ->where(['viewed_post.user_id' => $userId, 'board_post.board_id' => $boardId])
                    ->orderBy(['viewed_post.created_at' => SORT_ASC])
                    ->limit((int)$limit)
                    ->all();

                if (empty($posts)) {
                    ViewedPost::deleteAll(['user_id' => $userId]);
                }
            }
            $this->markAsViewed($posts);

            return [
                'status' => 'success',
                'posts' => array_map([$this, 'getPostPreviewData'], $posts),
            ];
        } catch (Throwable $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    // Получение постов из доски
    public function actionPostsInBoard(string $boardId, $offset = 0, $limit = 20)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $posts = Post::find()
                ->activeOrOwnerModeration(Yii::$app->user->id)
                ->innerJoin('board_post', 'board_post.post_id = post.id')
                ->andWhere(['board_post.board_id' => $boardId])
                ->orderBy(['board_post.created_at' => SORT_DESC])
                ->offset((int)$offset)
                ->limit((int)$limit)
                ->all();

            return [
                'status' => 'success',
                'posts' => array_map([$this, 'getPostPreviewData'], $posts),
            ];
        } catch (Throwable $e) {
            return [
                'status' => 'error',
                'message' => 'Ошибка при получении постов доски: ' . $e->getMessage()
            ];
        }
    }

    // Отмечаем что видели
    private function markAsViewed(array $posts)
    {
        try {
            $data = [];
            $now = date('Y-m-d H:i:s');

            foreach ($posts as $post) {
                $data[] = [
                    'user_id' => Yii::$app->user->id,
                    'post_id' => $post->id,
                    'created_at' => $now,
                ];
            }

            if (!empty($data)) {
                Yii::$app->db->createCommand()
                    ->batchInsert('viewed_post', ['user_id', 'post_id', 'created_at'], $data)
                    ->execute();
            }
        } catch (Throwable $e) {
            Yii::error("Ошибка отметки просмотра: " . $e->getMessage());
        }
    }

    // Поиск по строке
    public function actionSearchPosts(string $query, $offset = 0, $limit = 20)
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
        } catch (Throwable $e) {
            return [
                'status' => 'error',
                'message' => 'Ошибка поиска: ' . $e->getMessage()
            ];
        }
    }

    // --- Публичные методы

    // Получение досок по id пользователя
    public function actionBoards(int $userId): array
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
            ];
        } catch (Throwable $e) {
            return [
                'status' => 'error',
                'message' => 'Ошибка при получении досок пользователя: ' . $e->getMessage(),
            ];
        }
    }

    // Поиск похожих постов
    public function actionSimilar(int $postId, $offset = 0, $limit = 20)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $post = Post::find()->active()->andWhere(['id' => $postId])->one();
            if (!$post) {
                return ['status' => 'error', 'message' => 'Пост не найден'];
            }

            // Получаем теги текущего поста
            $tagIds = $post->getPostTags()->select('tag_id')->column();

            $query = Post::find()
                ->where(['post.status_id' => 1, 'post.visible_id' => 1])
                ->with(['user', 'mainImage'])
                ->andWhere(['!=', 'post.id', $postId]);

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
                    ->andWhere(['!=', 'post.id', $postId])
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
        } catch (Throwable $e) {
            return [
                'status' => 'error',
                'message' => 'Ошибка получения похожих постов: ' . $e->getMessage()
            ];
        }
    }

    // Образы с вещью
    public function actionOutfitsWithItem(int $itemId, $offset = 0, $limit = 20)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        try {
            $posts = Post::find()
                ->where(['post.status_id' => 1, 'post.visible_id' => 1, 'post.type_id' => 2])
                ->innerJoin('outfit_item oi', 'oi.outfit_id = post.id')
                ->andWhere(['oi.item_id' => $itemId])
                ->with(['user', 'mainImage'])
                ->offset($offset)
                ->limit($limit)
                ->all();

            return [
                'status' => 'success',
                'outfits' => array_map([$this, 'getPostPreviewData'], $posts)
            ];
        } catch (Throwable $e) {
            return [
                'status' => 'error',
                'message' => 'Ошибка получения образов: ' . $e->getMessage()
            ];
        }
    }

    // Получаем сам пост
    public function actionPost(int $postId)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $post = Post::find()
                ->where(['id' => $postId, 'status_id' => 1])
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
                        'path' => $this->getUrlPreview($pi->image->path),
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
                $items = array_filter($items);
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
                        'avatar_path' => $post->user->avatar_path ? $this->getUrlPreview($post->user->avatar_path) : null,
                    ],
                    'isLiked' => $isLiked,
                    'isSaved' => $isSaved,
                ]
            ];
        } catch (Throwable $e) {
            return [
                'status' => 'error',
                'message' => 'Ошибка получения поста: ' . $e->getMessage()
            ];
        }
    }

    // Получение постов по массиву ID
    public function actionPostsByIds()
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
        } catch (Throwable $e) {
            return [
                'status' => 'error',
                'message' => 'Ошибка получения постов: ' . $e->getMessage()
            ];
        }
    }

    // Получить генерации
    public function actionGenerations($offset = 0, $limit = 5): array
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
                                    'path' => $this->getUrlPreview($task->image->path),
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
        } catch (Throwable $e) {
            return [
                'status' => 'error',
                'message' => 'Ошибка при получении генераций пользователя: ' . $e->getMessage()
            ];
        }
    }

    // Получить доску
    public function actionBoard($boardId)
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
        } catch (Throwable $e) {
            return [
                'status' => 'error',
                'message' => 'Ошибка получения доски: ' . $e->getMessage()
            ];
        }
    }

    // Получить профиль пользователя
    public function actionProfile($userId)
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
                    'avatar_path' => $user->avatar_path ? $this->getUrlPreview($user->avatar_path) : null,
                    'background_path' => $user->background_path ? $this->getUrlPreview($user->background_path) : null,
                    'posts_count' => (int)$postsCount,
                    'likes_count' => (int)$likesCount,
                ]
            ];
        } catch (Throwable $e) {
            return [
                'status' => 'error',
                'message' => 'Ошибка получения профиля: ' . $e->getMessage()
            ];
        }
    }

    // Получение тегов по запросу
    public function actionTags($query)
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
        } catch (Throwable $e) {
            return [
                'status' => 'error',
                'message' => 'Ошибка получения тегов: ' . $e->getMessage()
            ];
        }
    }

    // Получения файла
    public function actionFile($url)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $path = Yii::getAlias('@webroot/uploads/') . basename($url);

            if (!file_exists($path) || !is_file($path)) {
                throw new Throwable("Файл не найден по пути: " . $path);
            }

            return Yii::$app->response->sendFile($path);
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }
}
