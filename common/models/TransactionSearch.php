<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Transactions;

/**
 * TransactionSearch represents the model behind the search form about `common\models\Transactions`.
 */
class TransactionSearch extends Transactions
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'customer_name', 'date_purchase', 'created_date', 'last_modified_date'], 'safe'],
            [['amount_due', 'discount', 'gst', 'total_price_before_disc'], 'number'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Transactions::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'date_purchase' => $this->date_purchase,
            'amount_due' => $this->amount_due,
            'discount' => $this->discount,
            'gst' => $this->gst,
            'total_price_before_disc' => $this->total_price_before_disc,
            'created_date' => $this->created_date,
            'last_modified_date' => $this->last_modified_date,
        ]);

        $query->andFilterWhere(['like', 'id', $this->id])
            ->andFilterWhere(['like', 'customer_name', $this->customer_name]);

        return $dataProvider;
    }
}
