<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "board_post".
 *
 * @property int $board_id
 * @property int $post_id
 * @property string $created_at
 *
 * @property Board $board
 * @property Post $post
 */
class BoardPost extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'board_post';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['board_id', 'post_id'], 'required'],
            [['board_id', 'post_id'], 'integer'],
            [['created_at'], 'safe'],
            [['board_id', 'post_id'], 'unique', 'targetAttribute' => ['board_id', 'post_id']],
            [['post_id'], 'exist', 'skipOnError' => true, 'targetClass' => Post::class, 'targetAttribute' => ['post_id' => 'id']],
            [['board_id'], 'exist', 'skipOnError' => true, 'targetClass' => Board::class, 'targetAttribute' => ['board_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'board_id' => 'Board ID',
            'post_id' => 'Post ID',
            'created_at' => 'Created At',
        ];
    }

    /**
     * Gets query for [[Board]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBoard()
    {
        return $this->hasOne(Board::class, ['id' => 'board_id']);
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
