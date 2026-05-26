<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "outfit_item".
 *
 * @property int $outfit_id
 * @property int $item_id
 *
 * @property Post $item
 * @property Post $outfit
 */
class OutfitItem extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'outfit_item';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['outfit_id', 'item_id'], 'required'],
            [['outfit_id', 'item_id'], 'integer'],
            [['outfit_id', 'item_id'], 'unique', 'targetAttribute' => ['outfit_id', 'item_id']],
            [['item_id'], 'exist', 'skipOnError' => true, 'targetClass' => Post::class, 'targetAttribute' => ['item_id' => 'id']],
            [['outfit_id'], 'exist', 'skipOnError' => true, 'targetClass' => Post::class, 'targetAttribute' => ['outfit_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'outfit_id' => 'Outfit ID',
            'item_id' => 'Item ID',
        ];
    }

    /**
     * Gets query for [[Item]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getItem()
    {
        return $this->hasOne(Post::class, ['id' => 'item_id']);
    }

    /**
     * Gets query for [[Outfit]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOutfit()
    {
        return $this->hasOne(Post::class, ['id' => 'outfit_id']);
    }

}
