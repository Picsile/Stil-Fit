<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "generation_appearance".
 *
 * @property int $id
 * @property int $generation_id
 * @property int $image_id
 *
 * @property Generation $generation
 * @property Image $image
 */
class GenerationAppearance extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'generation_appearance';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['generation_id', 'image_id'], 'required'],
            [['generation_id', 'image_id'], 'integer'],
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
            'image_id' => 'Image ID',
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
     * Gets query for [[Image]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getImage()
    {
        return $this->hasOne(Image::class, ['id' => 'image_id']);
    }

}
