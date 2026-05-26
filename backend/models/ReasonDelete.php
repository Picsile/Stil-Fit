<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "reason_delete".
 *
 * @property int $post_id
 * @property string $reason
 * @property int $user_id
 * @property string $delete_at
 *
 * @property Post $post
 * @property User $user
 */
class ReasonDelete extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'reason_delete';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['post_id', 'reason', 'user_id'], 'required'],
            [['post_id', 'user_id'], 'integer'],
            [['reason'], 'string'],
            [['delete_at'], 'safe'],
            [['post_id'], 'unique'],
            [['post_id'], 'exist', 'skipOnError' => true, 'targetClass' => Post::class, 'targetAttribute' => ['post_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'post_id' => 'Post ID',
            'reason' => 'Reason',
            'user_id' => 'User ID',
            'delete_at' => 'Delete At',
        ];
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

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

}
