<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "image".
 *
 * @property int $id
 * @property string $path
 * @property string $path_preview
 * @property int $width
 * @property int $height
 * @property string $created_at
 *
 * @property GenerationAppearance[] $generationAppearances
 * @property GenerationImage[] $generationImages
 * @property GenerationTask[] $generationTasks
 * @property PostImage[] $postImages
 * @property Post[] $posts
 */
class Image extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'image';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['path', 'path_preview', 'width', 'height'], 'required'],
            [['width', 'height'], 'integer'],
            [['created_at'], 'safe'],
            [['path', 'path_preview'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'path' => 'Path',
            'path_preview' => 'Path Preview',
            'width' => 'Width',
            'height' => 'Height',
            'created_at' => 'Created At',
        ];
    }

    /**
     * Gets query for [[GenerationAppearances]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGenerationAppearances()
    {
        return $this->hasMany(GenerationAppearance::class, ['image_id' => 'id']);
    }

    /**
     * Gets query for [[GenerationImages]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGenerationImages()
    {
        return $this->hasMany(GenerationImage::class, ['image_id' => 'id']);
    }

    /**
     * Gets query for [[GenerationTasks]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGenerationTasks()
    {
        return $this->hasMany(GenerationTask::class, ['image_id' => 'id']);
    }

    /**
     * Gets query for [[PostImages]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPostImages()
    {
        return $this->hasMany(PostImage::class, ['image_id' => 'id']);
    }

    /**
     * Gets query for [[Posts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPosts()
    {
        return $this->hasMany(Post::class, ['main_image_id' => 'id']);
    }

}
