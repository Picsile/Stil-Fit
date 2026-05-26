<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "generation_image".
 *
 * @property int $id
 * @property int $generation_task_id
 * @property int $image_id
 *
 * @property GenerationTask $generationTask
 * @property Image $image
 */
class GenerationImage extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'generation_image';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['generation_task_id', 'image_id'], 'required'],
            [['generation_task_id', 'image_id'], 'integer'],
            [['image_id'], 'exist', 'skipOnError' => true, 'targetClass' => Image::class, 'targetAttribute' => ['image_id' => 'id']],
            [['generation_task_id'], 'exist', 'skipOnError' => true, 'targetClass' => GenerationTask::class, 'targetAttribute' => ['generation_task_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'generation_task_id' => 'Generation Task ID',
            'image_id' => 'Image ID',
        ];
    }

    /**
     * Gets query for [[GenerationTask]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGenerationTask()
    {
        return $this->hasOne(GenerationTask::class, ['id' => 'generation_task_id']);
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
