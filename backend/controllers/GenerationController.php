<?php

namespace app\controllers;

use app\components\ProxyCurl;
use app\models\Generation;
use app\models\GenerationAppearance;
use app\models\GenerationImage;
use app\models\GenerationItem;
use app\models\GenerationResolution;
use app\models\GenerationTask;
use app\models\Image;
use app\models\UploadForm;
use Exception;
use yii\web\Response;
use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\helpers\Json;
use yii\imagine\Image as ImagineImage;
use yii\rest\ActiveController;
use yii\web\UploadedFile;

class GenerationController extends ActiveController
{
    public $modelClass = '';
    public $enableCsrfValidation = false;
    private const COST_PER_IMAGE_2K = 5;
    private const COST_PER_IMAGE_4K = 8;

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

    private function getGenerationCost(int $quantity, int $resolutionId): int
    {
        $costPerImage = $resolutionId === 2
            ? self::COST_PER_IMAGE_4K
            : self::COST_PER_IMAGE_2K;

        return max(1, $quantity) * $costPerImage;
    }

    /**
     * Создание генерации
     */
    public function actionCreateGeneration()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $data = Yii::$app->request->post();
            $user = Yii::$app->user->identity;
            $generationCost = $this->getGenerationCost((int)$data['quantity'], (int)$data['resolution_id']);

            // Проверка баланса
            if ($user->quantity_fitcoins < $generationCost) {
                return [
                    'status' => 'error',
                    'message' => 'Недостаточно токенов для генерации'
                ];
            }

            // Списание токенов
            $user->quantity_fitcoins -= $generationCost;
            $user->save(false);

            // Создание генерации
            $generation = new Generation();
            $generation->user_id = $user->id;
            $generation->visible_id = $data['visible_id'];
            $generation->prompt = $data['prompt'];
            $generation->ratio_id = $data['ratio_id'];
            $generation->resolution_id = $data['resolution_id'];
            $generation->quantity = $data['quantity'];
            $generation->status_id = 1;

            if (!$generation->save()) {
                $user->quantity_fitcoins += $generationCost;
                $user->save(false);
                return [
                    'status' => 'error',
                    'message' => 'Ошибка сохранения генерации',
                    'errorsValidation' => $generation->errors
                ];
            }

            // Сохранение вещей
            $itemIds = $data['itemIds'] ?? [];
            foreach ($itemIds as $itemId) {
                $generationItem = new GenerationItem();
                $generationItem->generation_id = $generation->id;
                $generationItem->post_id = (int)$itemId;
                $generationItem->save();
            }

            // Сохранение фото внешности
            $uploadForm = new UploadForm();
            $appearances = UploadedFile::getInstancesByName('appearances');
            if (!empty($appearances)) {
                foreach ($appearances as $appearanceFile) {
                    $imageModel = new Image();
                    $uploadForm->imageFile = $appearanceFile;

                    [$width, $height] = @getimagesize($appearanceFile->tempName);
                    $imageModel->width = $width;
                    $imageModel->height = $height;

                    if (
                        ($imageModel->path_preview = $uploadForm->uploadPreview('uploads'))
                        && ($imageModel->path = $uploadForm->upload('uploads'))
                    ) {
                        if ($imageModel->save()) {
                            $generationAppearance = new GenerationAppearance();
                            $generationAppearance->generation_id = $generation->id;
                            $generationAppearance->image_id = $imageModel->id;
                            $generationAppearance->save();
                        }
                    }
                }
            }

            // Отправка в APIMart - создаём N задач
            $quantity = (int)$generation->quantity;
            $successfulTasks = 0;
            $errors = [];

            // Загружаем картинки один раз, переиспользуем URL для всех задач
            $uploadResult = $this->uploadGenerationImages($generation);
            if (isset($uploadResult['error'])) {
                $generation->status_id = 3;
                $generation->error = $uploadResult['error'];
                $generation->save(false);

                $user->quantity_fitcoins += $generationCost;
                $user->save(false);

                return [
                    'status' => 'error',
                    'message' => $uploadResult['error'],
                ];
            }
            $imageUrls = $uploadResult['urls'];

            for ($i = 0; $i < $quantity; $i++) {
                $apiResponse = $this->sendToApiMart($generation, $imageUrls);

                if (isset($apiResponse['code']) && $apiResponse['code'] === 200 && isset($apiResponse['data'][0]['task_id'])) {
                    $taskId = $apiResponse['data'][0]['task_id'];

                    // Сохраняем task в generation_tasks
                    $generationTask = new GenerationTask();
                    $generationTask->generation_id = $generation->id;
                    $generationTask->task_id = $taskId;
                    $generationTask->status = 'pending';

                    if ($generationTask->save()) {
                        $successfulTasks++;
                    } else {
                        $errors[] = 'Проблема с соединением, попробуйте позже';
                    }
                } else {
                    $errors[] = 'Проблема с соединением, попробуйте позже';
                }
            }

            // Если хотя бы одна задача создана успешно
            if ($successfulTasks > 0) {
                return [
                    'status' => 'success',
                    'generation' => [
                        'id' => $generation->id,
                        'status' => 'Generating',
                        'total_tasks' => $quantity,
                        'successful_tasks' => $successfulTasks
                    ],
                    'quantity_fitcoins' => (int)$user->quantity_fitcoins,
                    'errors' => !empty($errors) ? $errors : null
                ];
            } else {
                // Все задачи провалились
                $generation->status_id = 3;
                $generation->error = implode('; ', $errors);
                $generation->save(false);

                $user->quantity_fitcoins += $generationCost;
                $user->save(false);

                return [
                    'status' => 'error',
                    'message' => 'Проблема с соединением, попробуйте позже',
                    'errors' => $errors
                ];
            }
        } catch (Exception $e) {
            if (isset($user) && isset($generation)) {
                $generationCost = $this->getGenerationCost((int)$generation->quantity, (int)$generation->resolution_id);
                $user->quantity_fitcoins += $generationCost;
                $user->save(false);
            }
            return [
                'status' => 'error',
                'message' => 'Ошибка создания генерации: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Загрузка картинок генерации (вещи + внешность) в APIMart один раз
     *
     * @return array{urls?: string[], error?: string}
     */
    private function uploadGenerationImages(Generation $generation): array
    {
        $apiKey = Yii::$app->params['apimartApiKey'] ?? '';
        if ($apiKey === '') {
            return ['error' => 'Проблема с соединением, попробуйте позже'];
        }

        $imageUrls = [];

        foreach ($generation->generationItems as $item) {
            if (!isset($item->post->mainImage->path)) {
                return ['error' => 'Добавленная в образ вещь не имеет изображения'];
            }

            $filePath = Yii::getAlias('@webroot') . '/uploads/' . $item->post->mainImage->path;
            if (!file_exists($filePath)) {
                return ['error' => 'Файл изображения вещи не найден'];
            }

            $uploadedUrl = $this->uploadImageToApiMart($filePath, $apiKey);
            if ($uploadedUrl === null) {
                return ['error' => 'Проблема с соединением, попробуйте позже'];
            }

            $imageUrls[] = $uploadedUrl;
        }

        foreach ($generation->generationAppearances as $appearance) {
            if (!isset($appearance->image->path)) continue;

            $filePath = Yii::getAlias('@webroot') . '/uploads/' . $appearance->image->path;
            if (!file_exists($filePath)) continue;

            $uploadedUrl = $this->uploadImageToApiMart($filePath, $apiKey);
            if ($uploadedUrl !== null) {
                $imageUrls[] = $uploadedUrl;
            }
        }

        return ['urls' => $imageUrls];
    }

    /**
     * Отправка генерации в APIMart
     *
     * @param string[] $imageUrls Уже загруженные URL картинок
     */
    private function sendToApiMart(Generation $generation, array $imageUrls = []): array
    {
        $apiKey = Yii::$app->params['apimartApiKey'] ?? '';
        if ($apiKey === '') {
            return ['status' => 'error', 'message' => 'Проблема с соединением, попробуйте позже'];
        }

        $url = 'https://api.apimart.ai/v1/images/generations';

        $payload = [
            'model' => 'gpt-image-2',
            'prompt' => $generation->prompt,
            'size' => $generation->ratio->value,
            'resolution' => $generation->resolution->value,
            'n' => (int)$generation->quantity,
        ];

        if (!empty($imageUrls)) {
            $payload['image_urls'] = $imageUrls;
        }

        // Логирование для отладки
        Yii::info('APIMart payload: ' . Json::encode($payload), 'generation');

        // Временно возвращаем payload для отладки
        // return ['debug' => $payload];

        $curl = curl_init($url);
        curl_setopt_array($curl, ProxyCurl::apply([
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => Json::encode($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey,
            ],
        ]));

        $response = curl_exec($curl);
        $curlErrno = curl_errno($curl);
        $curlErr = curl_error($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        Yii::error('APIMart payload: ' . Json::encode($payload), __METHOD__);
        Yii::error('APIMart HTTP ' . $httpCode . ' errno=' . $curlErrno . ' err=' . $curlErr . ' response=' . substr((string)$response, 0, 2000), __METHOD__);

        if ($curlErrno !== 0) {
            return ['status' => 'error', 'message' => 'Перебои в работе сервиса, повторите попытку позже'];
        }

        $result = json_decode((string)$response, true);

        if (!is_array($result)) {
            return ['status' => 'error', 'message' => 'Сервер вернул невалидный JSON'];
        }

        return $result;
    }

    /**
     * Проверка статуса генерации
     */
    public function actionCheckGeneration($generation_id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $generation = Generation::findOne($generation_id);
            if (!$generation) {
                return [
                    'status' => 'error',
                    'message' => 'Генерация не найдена'
                ];
            }

            // Получаем все задачи генерации
            $tasks = GenerationTask::find()
                ->where(['generation_id' => $generation->id])
                ->all();

            if (empty($tasks)) {
                return [
                    'status' => 'error',
                    'message' => 'Задачи генерации не найдены'
                ];
            }

            $newImages = [];
            $completedCount = 0;
            $failedCount = 0;
            $processingCount = 0;

            foreach ($tasks as $task) {
                // Если задача уже завершена (есть image_id), добавляем изображение и пропускаем проверку
                if ($task->status === 'completed' && $task->image_id && $task->image) {
                    $completedCount++;
                    $newImages[] = [
                        'id' => $task->image->id,
                        'path' => '/backend/uploads/' . $task->image->path,
                        'width' => $task->image->width,
                        'height' => $task->image->height,
                    ];
                    continue;
                }

                // Если задача провалилась, пропускаем
                if ($task->status === 'failed') {
                    $failedCount++;
                    continue;
                }

                // Проверяем статус задачи в APIMart
                $statusResponse = $this->checkApiMartTaskStatus($task->task_id);

                if (isset($statusResponse['code']) && $statusResponse['code'] === 200) {
                    $status = $statusResponse['data']['status'] ?? '';

                    if ($status === 'completed') {
                        $taskResult = $statusResponse['data']['result'] ?? null;

                        if (isset($taskResult['images'][0]['url'][0])) {
                            $imageUrl = $taskResult['images'][0]['url'][0];
                            $fileData = @file_get_contents($imageUrl);

                            if ($fileData !== false) {
                                $ext = pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION);
                                if (!$ext || !in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'webp'])) {
                                    $ext = 'png';
                                }

                                $webroot = Yii::getAlias('@webroot');
                                $path = md5(uniqid()) . '.' . $ext;
                                $fullPath = $webroot . '/uploads/' . $path;

                                if (file_put_contents($fullPath, $fileData)) {
                                    $imageModel = new Image();
                                    $imageModel->path = $path;

                                    $previewName = md5(uniqid()) . '.jpg';
                                    $previewFullPath = $webroot . '/uploads/' . $previewName;

                                    ImagineImage::thumbnail($fullPath, 400, null)
                                        ->save($previewFullPath, ['format' => 'jpg', 'quality' => 80]);
                                    $imageModel->path_preview = $previewName;

                                    [$width, $height] = @getimagesize($fullPath);
                                    $imageModel->width = $width ?: 0;
                                    $imageModel->height = $height ?: 0;

                                    if ($imageModel->save()) {
                                        // Обновляем задачу
                                        $task->status = 'completed';
                                        $task->image_id = $imageModel->id;
                                        $task->save(false);

                                        // Добавляем в список новых изображений
                                        $newImages[] = [
                                            'id' => $imageModel->id,
                                            'path' => '/backend/uploads/' . $imageModel->path,
                                            'width' => $imageModel->width,
                                            'height' => $imageModel->height,
                                        ];

                                        $completedCount++;
                                    }
                                }
                            }
                        }
                    } elseif ($status === 'failed' || $status === 'cancelled') {
                        $errorMessage = $statusResponse['data']['error']['message'] ?? 'Генерация завершилась со статусом: ' . $status;
                        $task->status = 'failed';
                        $task->error = $errorMessage;
                        $task->save(false);
                        $failedCount++;
                    } else {
                        // pending или processing
                        $processingCount++;
                    }
                } else {
                    $processingCount++;
                }
            }

            // Определяем общий статус генерации
            $totalTasks = count($tasks);

            if ($completedCount === $totalTasks) {
                // Все задачи завершены
                $generation->status_id = 2;
                $generation->save(false);

                return [
                    'status' => 'success',
                    'generation' => [
                        'id' => $generation->id,
                        'status' => 'Completed',
                        'images' => $newImages
                    ]
                ];
            } elseif ($failedCount === $totalTasks) {
                // Все задачи провалились
                $generation->status_id = 3;
                $generation->error = 'Все задачи генерации завершились с ошибкой';
                $generation->save(false);

                // Возвращаем токены
                $refundCost = $this->getGenerationCost((int)$generation->quantity, (int)$generation->resolution_id);
                $generation->user->quantity_fitcoins += $refundCost;
                $generation->user->save(false);

                return [
                    'status' => 'error',
                    'message' => 'Все задачи генерации завершились с ошибкой'
                ];
            } else {
                // Генерация в процессе
                return [
                    'status' => 'processing',
                    'generation' => [
                        'id' => $generation->id,
                        'status' => 'Generating',
                        'images' => $newImages
                    ]
                ];
            }
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Ошибка проверки генерации: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Загружает изображение в APIMart и возвращает его URL
     */
    private function uploadImageToApiMart(string $filePath, string $apiKey): ?string
    {
        $curl = curl_init('https://api.apimart.ai/v1/uploads/images');
        curl_setopt_array($curl, ProxyCurl::apply([
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => ['file' => new \CURLFile($filePath)],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $apiKey,
            ],
        ]));

        $response = curl_exec($curl);
        $curlErrno = curl_errno($curl);
        $curlErr = curl_error($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        Yii::error('uploadImage: file=' . basename($filePath) . ' HTTP=' . $httpCode . ' errno=' . $curlErrno . ' err=' . $curlErr . ' response=' . substr((string)$response, 0, 500), __METHOD__);

        if ($curlErrno !== 0) {
            return null;
        }

        $result = json_decode((string)$response, true);

        return $result['url'] ?? null;
    }

    private function checkApiMartTaskStatus(string $taskId): array
    {
        $apiKey = Yii::$app->params['apimartApiKey'] ?? '';
        if ($apiKey === '') {
            return ['status' => 'error', 'message' => 'Проблема с соединением, попробуйте позже'];
        }

        $url = 'https://api.apimart.ai/v1/tasks/' . $taskId;

        $curl = curl_init($url);
        curl_setopt_array($curl, ProxyCurl::apply([
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $apiKey,
            ],
        ]));

        $response = curl_exec($curl);
        $curlErrno = curl_errno($curl);
        $curlErr = curl_error($curl);

        if ($curlErrno !== 0) {
            return ['status' => 'error', 'message' => 'Перебои в работе сервиса, повторите попытку позже'];
        }

        $result = json_decode((string)$response, true);

        if (!is_array($result)) {
            return ['status' => 'error', 'message' => 'Сервер вернул невалидный JSON'];
        }

        return $result;
    }
}
