<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "generation".
 *
 * @property int $id
 * @property int $user_id
 * @property int $visible_id
 * @property string $prompt
 * @property int $resolution_id
 * @property int $ratio_id
 * @property int $quantity
 * @property int $status_id
 * @property string|null $error
 * @property string $created_at
 *
 * @property GenerationAppearance[] $generationAppearances
 * @property GenerationItem[] $generationItems
 * @property GenerationTask[] $generationTasks
 * @property Post[] $posts
 * @property GenerationRatio $ratio
 * @property GenerationResolution $resolution
 * @property GenerationModel $model
 * @property GenerationStatus $status
 * @property User $user
 * @property Visible $visible
 */
class Generation extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'generation';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['error'], 'default', 'value' => null],
            [['user_id', 'visible_id', 'prompt', 'resolution_id', 'ratio_id', 'quantity', 'status_id'], 'required'],
            [['user_id', 'visible_id', 'resolution_id', 'ratio_id', 'quantity', 'status_id'], 'integer'],
            [['prompt', 'error'], 'string'],
            [['created_at'], 'safe'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
            [['ratio_id'], 'exist', 'skipOnError' => true, 'targetClass' => GenerationRatio::class, 'targetAttribute' => ['ratio_id' => 'id']],
            [['resolution_id'], 'exist', 'skipOnError' => true, 'targetClass' => GenerationResolution::class, 'targetAttribute' => ['resolution_id' => 'id']],
            [['status_id'], 'exist', 'skipOnError' => true, 'targetClass' => GenerationStatus::class, 'targetAttribute' => ['status_id' => 'id']],
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
            'visible_id' => 'Visible ID',
            'prompt' => 'Prompt',
            'resolution_id' => 'Resolution ID',
            'ratio_id' => 'Ratio ID',
            'quantity' => 'Quantity',
            'status_id' => 'Status ID',
            'error' => 'Error',
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
        return $this->hasMany(GenerationAppearance::class, ['generation_id' => 'id']);
    }

    /**
     * Gets query for [[GenerationItems]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGenerationItems()
    {
        return $this->hasMany(GenerationItem::class, ['generation_id' => 'id']);
    }

    /**
     * Gets query for [[GenerationTasks]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGenerationTasks()
    {
        return $this->hasMany(GenerationTask::class, ['generation_id' => 'id']);
    }

    /**
     * Gets query for [[Posts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPosts()
    {
        return $this->hasMany(Post::class, ['id' => 'post_id'])->viaTable('generation_item', ['generation_id' => 'id']);
    }

    /**
     * Gets query for [[Ratio]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRatio()
    {
        return $this->hasOne(GenerationRatio::class, ['id' => 'ratio_id']);
    }

    /**
     * Gets query for [[Resolution]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getResolution()
    {
        return $this->hasOne(GenerationResolution::class, ['id' => 'resolution_id']);
    }

    /**
     * Gets query for [[Status]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStatus()
    {
        return $this->hasOne(GenerationStatus::class, ['id' => 'status_id']);
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
