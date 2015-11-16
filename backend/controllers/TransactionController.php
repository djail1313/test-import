<?php
namespace backend\controllers;

use yii\rest\ActiveController;

class TransactionController extends ActiveController
{
	public $modelClass = 'common\models\Transactions';
    public $reservedParams = ['sort','q'];

	public function behaviors()
    {
        return \yii\helpers\ArrayHelper::merge(parent::behaviors(), [
            // Setting up Origin, for allowing access from different host
            'corsFilter' => [
                'class' => \yii\filters\Cors::className(),
                'cors' => [
                    'Origin' => ['http://test-frontend.dev', 'http://test-frontend.dev/'],
                    'Access-Control-Request-Method' => ['POST', 'PUT', 'GET', 'HEADER'],
                    'Access-Control-Allow-Credentials' => true,
                    'Access-Control-Max-Age' => 3600,
                    'Access-Control-Expose-Headers' => [
                        'X-Pagination-Current-Page',
                        'X-Pagination-Page-Count',
                        'X-Pagination-Per-Page',
                        'X-Pagination-Total-Count',
                        'Link',
                    ],
                ],
            ],
        ]);
    }
	
    public function actions() {
        $actions = parent::actions();
        // Override indexAction
        $actions['index']['prepareDataProvider'] = [$this, 'indexDataProvider'];
        // Remove create action, then override
        unset($actions['create']);
        return $actions;
    }

    public function actionCreate(){
        // folder temp save uploaded file
        $path = '/var/www/html/test/frontend/uploads/';
        if(isset($_FILES['file'])){
            $file = $_FILES['file'];
            echo json_encode($file);
            $explode = explode(".", $file['name']);
            $path .= $explode[0];
            if(!file_exists($path)) mkdir($path, 0777);
            move_uploaded_file($file['tmp_name'], $path.'/'.$file['name']);
        } else {
            $params = \Yii::$app->request->post();
            if($params['type'] == 'object'){
                $this->_createByObject();
            } elseif($params['type'] == 'file'){
                $path .= $params['name'];
                $dir = scandir($path, 1);
                $content = '';
                foreach ($dir as $fname) {
                    if($fname != '.' && $fname != '..'){
                        $fname = $path.'/'.$fname;    
                        $file_size = filesize($fname);
                        $handle = fopen($fname, 'rb') or die("error opening file");
                        $content = fread($handle, $file_size).$content or die("error reading file");
                        fclose($handle);
                        unlink($fname);
                    }
                }
                $file = json_decode(utf8_encode($content));
                if(json_last_error()){
                    $handle = fopen($path."/".$params['name'], 'wb') or die("error creating/opening merged file");
                    fwrite($handle, $content) or die("error writing to merged file");
                    fclose($handle);
                    $this->_createByCSV($path."/".$params['name']);
                    unlink($path."/".$params['name']);               
                } else {
                    $this->_createByJSON($file);                
                }
                rmdir($path);   
            }
        }
    }


    // Import JSON to database
    private function _createByJSON($data){
        foreach ($data as $value) {
            $model = \common\models\Transactions::find()->where(['id'=>$value->Id])->one();
            // Checking if the transactions ID if exist on the database
            if($model !== null) continue;
                // Checking if the required field is have NULL value, if NULL then not Insert to Database
            if($value->Id === NULL || $value->CustomerName === NULL || $value->Amount_due__c === NULL || $value->Discount__c === NULL || $value->GST__c === NULL) continue;
            $model = new \common\models\Transactions();
            $model->id = $value->Id;
            $model->customer_name = $value->CustomerName;
            $model->date_purchase = date("Y-m-d",$value->DatePurchase);
            $model->amount_due = $value->Amount_due__c;
            $model->discount = $value->Discount__c;
            $model->gst = $value->GST__c;
            $model->total_price_before_disc = $value->Amount_due__c+$value->Discount__c-$value->GST__c;
            $model->created_date = $value->CreatedDate;
            $model->last_modified_date = $value->LastModifiedDate;
            $model->save();
        }
    }

    // Import CSV to database
    private function _createByCSV($file){
        $file = fopen($file, "r");
        while (!feof($file)) {
            $fgetcsv = fgetcsv($file);
            if($fgetcsv[0] != 'Id'){
                $model = \common\models\Transactions::find()->where(['id'=>$fgetcsv[0]])->one();
                // Checking if the transactions ID if exist on the database
                if($model !== null) continue;
                // Checking if the required field is have NULL value, if NULL then not Insert to Database 
                if($fgetcsv[0] === NULL || $fgetcsv[1] === NULL || $fgetcsv[3] === NULL || $fgetcsv[4] === NULL || $fgetcsv[5] === NULL) continue;
                $model = new \common\models\Transactions();
                $model->id = $fgetcsv[0];
                $model->customer_name = $fgetcsv[1];
                $model->date_purchase = date("Y-m-d",$fgetcsv[2]);
                $model->amount_due = $fgetcsv[3];
                $model->discount = $fgetcsv[4];
                $model->gst = $fgetcsv[5];
                $model->total_price_before_disc = $model->amount_due+$model->discount-$model->gst;
                $model->created_date = $fgetcsv[6];
                $model->last_modified_date = $fgetcsv[7];
                $model->save();
            }
        }
        fclose($file);
    }

    // Insert single Object
    private function _createByObject($params){
        if(isset($params['Transactions'])){
            $model = new \common\models\Transactions();
            $params['Transactions'] = (array)json_decode($params['Transactions']);
            if(json_last_error() == JSON_ERROR_NONE){
                if ($model->load($params) && $model->save()) {
                    echo json_encode($model->findOne($model->id)->attributes);
                } else {
                    echo json_encode(array(
                        'name' => 'Failed insert',
                        'message' => 'Insert failed, try again and check the parameter',
                        'code' => 23131
                    ));
                }
            } else {
                $this->_errorJson();
            }
        } else {
            $this->_errorJson();
        }
    }

    private function _errorJson(){
        echo json_encode(array(
            'name' => 'Not JSON format',
            'message' => 'Input Transactions must be JSON format, and must be in Transactions post',
            'code' => 23130
        ));
    }

    // Override index request
    public function indexDataProvider() {
        $params = \Yii::$app->request->queryParams;

        $model = new $this->modelClass;
        $modelAttr = $model->attributes;

        $search = [];

        if (!empty($params)) {
            foreach ($params as $key => $value) {
                if(!is_scalar($key) or !is_scalar($value)) {
                    throw new BadRequestHttpException('Bad Request');
                }
                if (!in_array(strtolower($key), $this->reservedParams) && \yii\helpers\ArrayHelper::keyExists($key, $modelAttr, false)) {
                    $search[$key] = $value;
                }
            }
        }
        $searchByAttr['TransactionSearch'] = $search;
        $searchModel = new \common\models\TransactionSearch();    
        return $searchModel->search($searchByAttr);     
    }

} 