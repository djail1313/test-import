<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "transactions".
 *
 * @property string $id
 * @property string $customer_name
 * @property string $date_purchase
 * @property string $amount_due
 * @property string $discount
 * @property string $gst
 * @property string $total_price_before_disc
 * @property string $created_date
 * @property string $last_modified_date
 */
class Transactions extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'transactions';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'customer_name'], 'required'],
            [['date_purchase', 'created_date', 'last_modified_date'], 'safe'],
            [['amount_due', 'discount', 'gst', 'total_price_before_disc'], 'number'],
            [['id'], 'string', 'max' => 64],
            [['customer_name'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'customer_name' => 'Customer Name',
            'date_purchase' => 'Date Purchase',
            'amount_due' => 'Amount Due',
            'discount' => 'Discount',
            'gst' => 'Gst',
            'total_price_before_disc' => 'Total Price Before Disc',
            'created_date' => 'Created Date',
            'last_modified_date' => 'Last Modified Date',
        ];
    }
}
