<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "store_offers".
 *
 * @property int $id
 * @property int $quantity_fitcoin
 * @property int $price
 *
 * @property Transaction[] $transactions
 */
class StoreOffers extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'store_offers';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['quantity_fitcoin', 'price'], 'required'],
            [['quantity_fitcoin', 'price'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'quantity_fitcoin' => 'Quantity Fitcoin',
            'price' => 'Price',
        ];
    }

    /**
     * Gets query for [[Transactions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTransactions()
    {
        return $this->hasMany(Transaction::class, ['offer_id' => 'id']);
    }

}
