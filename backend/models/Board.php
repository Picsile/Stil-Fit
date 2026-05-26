<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "board".
 *
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property int $visible_id
 * @property int $status_id
 * @property string $created_at
 *
 * @property BoardPost[] $boardPosts
 * @property Post[] $posts
 * @property Status $status
 * @property User $user
 * @property Visible $visible
 */
class Board extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'board';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'title', 'visible_id', 'status_id'], 'required'],
            [['user_id', 'visible_id', 'status_id'], 'integer'],
            [['created_at'], 'safe'],
            [['title'], 'string', 'max' => 255],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
            [['status_id'], 'exist', 'skipOnError' => true, 'targetClass' => Status::class, 'targetAttribute' => ['status_id' => 'id']],
            [['visible_id'], 'exist', 'skipOnError' => true, 'targetClass' => Visible::class, 'targetAttribute' => ['visible_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'title' => 'Title',
            'visible_id' => 'Visible ID',
            'status_id' => 'Status ID',
            'created_at' => 'Created At',
        ];
    }

    /**
     * Gets query for [[BoardPosts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBoardPosts()
    {
        return $this->hasMany(BoardPost::class, ['board_id' => 'id']);
    }

    /**
     * Gets query for [[Posts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPosts()
    {
        return $this->hasMany(Post::class, ['id' => 'post_id'])->viaTable('board_post', ['board_id' => 'id']);
    }

    /**
     * Gets query for [[Status]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStatus()
    {
        return $this->hasOne(Status::class, ['id' => 'status_id']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * Gets query for [[Visible]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVisible()
    {
        return $this->hasOne(Visible::class, ['id' => 'visible_id']);
    }

}
