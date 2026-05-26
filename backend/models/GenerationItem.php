<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "generation_item".
 *
 * @property int $generation_id
 * @property int $post_id
 *
 * @property Generation $generation
 * @property Post $post
 */
class GenerationItem extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'generation_item';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['generation_id', 'post_id'], 'required'],
            [['generation_id', 'post_id'], 'integer'],
            [['generation_id', 'post_id'], 'unique', 'targetAttribute' => ['generation_id', 'post_id']],
            [['generation_id'], 'exist', 'skipOnError' => true, 'targetClass' => Generation::class, 'targetAttribute' => ['generation_id' => 'id']],
            [['post_id'], 'exist', 'skipOnError' => true, 'targetClass' => Post::class, 'targetAttribute' => ['post_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'generation_id' => 'Generation ID',
            'post_id' => 'Post ID',
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
     * Gets query for [[Post]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPost()
    {
        return $this->hasOne(Post::class, ['id' => 'post_id']);
    }

}
