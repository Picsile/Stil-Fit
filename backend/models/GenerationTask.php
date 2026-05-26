<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "generation_task".
 *
 * @property int $id
 * @property int $generation_id
 * @property string $task_id
 * @property string|null $status
 * @property int|null $image_id
 * @property string|null $error
 *
 * @property Generation $generation
 * @property GenerationImage[] $generationImages
 * @property Image $image
 */
class GenerationTask extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'generation_task';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['image_id', 'error'], 'default', 'value' => null],
            [['status'], 'default', 'value' => 'pending'],
            [['generation_id', 'task_id'], 'required'],
            [['generation_id', 'image_id'], 'integer'],
            [['error'], 'string'],
            [['task_id', 'status'], 'string', 'max' => 255],
            [['generation_id'], 'exist', 'skipOnError' => true, 'targetClass' => Generation::class, 'targetAttribute' => ['generation_id' => 'id']],
            [['image_id'], 'exist', 'skipOnError' => true, 'targetClass' => Image::class, 'targetAttribute' => ['image_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'generation_id' => 'Generation ID',
            'task_id' => 'Task ID',
            'status' => 'Status',
            'image_id' => 'Image ID',
            'error' => 'Error',
        ];
    }

    /**
     * Gets query for [[Generation]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGeneration()
    {
        return $this->hasOne(Generation::class, ['id' => 'generation_id']);
    }

    /**
     * Gets query for [[GenerationImages]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGenerationImages()
    {
        return $this->hasMany(GenerationImage::class, ['generation_task_id' => 'id']);
    }

    /**
     * Gets query for [[Image]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getImage()
    {
        return $this->hasOne(Image::class, ['id' => 'image_id']);
    }

}
