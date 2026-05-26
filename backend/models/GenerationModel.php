<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * Модель GenerationModel
 *
 * @property int $id
 * @property string $name
 * @property string $api_name
 * @property string $description
 * @property int $is_active
 * @property int $created_at
 * @property int $updated_at
 */
class GenerationModel extends ActiveRecord
{
    public static function tableName()
    {
        return 'generation_model';
    }

    public function rules()
    {
        return [
            [['name', 'api_name'], 'required'],
            [['description'], 'string'],
            [['is_active'], 'integer'],
            [['name', 'api_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * Получить список активных моделей для выбора
     */
    public static function getActiveModels()
    {
        return self::find()
            ->where(['is_active' => 1])
            ->all();
    }
}
