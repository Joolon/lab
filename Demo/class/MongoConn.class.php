<?php

class MongoConn extends MongoClient{

    const DEFAULT_HOST = "61.145.158.170" ;
    public static  $conn = '';
    public $db = '';
    public $coll = '';

    public static $error = '';


    protected $data             =   NULL;
    // 查询表达式参数
    protected $options          =   array();

    /**
     * 获得实例
     */
    public static function getInstance(){
        if (self::$conn instanceof self) {
            return self::$conn;
        }
        self::$conn = new self(self::DEFAULT_HOST);
        return self::$conn;
    }

    /**
     * 选择要操作的目标库
     * @param $dbName
     * @return $this
     */
    public function db($dbName){
        $this->db = self::$conn->selectDB($dbName);
        return $this;
    }

    /**
     * 选择指定的集合
     * @param $collName
     * @return $this
     */
    public function coll($collName){
        $this->coll = $this->db->$collName;

        return $this;
    }


    /**
     * 查询条件
     * @param array $where  一维数组（可含有多个键值对）
     * @return $this
     */
    public function where($where){
        $this->options['where'] = $where;
        return $this;
    }



    /**
     * 查找一条记录
     * @param string $fields
     * @return array
     */
    public function getFindOne($fields = ''){
        if(empty($this->options['where']))
            $this->options['where'] = array();

        $this->data = $this->coll->find($this->options['where']);

        $newData = array();
        while ($this->data->hasNext()) {
            $newData = $this->data->getNext();
            break;
        }
        if($fields){
            $fields = explode(',',$fields);
            $newDataTmp = array();
            foreach($fields as $field){
                if(isset($newData[$field])){
                    $newDataTmp[$field] = $newData[$field];
                }
            }
        }

        return $newData;
    }


    /**
     * 查找满足查询条件的集合
     * @param string $fields
     * @param string $index
     * @return array
     */
    public function getFindList($fields = '',$index = ''){
        if(empty($this->options['where']))
            $this->options['where'] = array();

        $this->data = $this->coll->find($this->options['where']);

        $newData = array();
        while ($this->data->hasNext()) {
            $newData[] = $this->data->getNext();
        }
//        print_r($newData);exit;
        if($fields){
            $fields = explode(',',$fields);
            $newDataTmp = array();
            foreach($newData as $value){
                $tmp = array();
                foreach($fields as $field){
                    if(isset($value[$field]))
                        $tmp[$field] = $value[$field];
                }
                if($index AND $value[$index]){
                    $newDataTmp[$value[$index]] = $tmp;
                }else{
                    $newDataTmp[] = $tmp;
                }
            }
            $newData = $newDataTmp;

        }

        return $newData;
    }

    /**
     * 更新 满足条件的第一条数据（数据不存在则插入）
     * @param $update
     * @return bool
     */
    public function updateOne($update){
        try{
            $res = $this->coll->update($this->options['where'],array('$set' => $update),array('upsert' => true));

            if($res['updatedExisting']){
                return true;
            }else{
                self::$error = '更新失败';
                echo self::$error;
                return false;
            }

        }catch (Exception $e){
            self::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * 更新 满足条件的数据集合（数据不存在则插入）
     * @param $update
     * @return bool
     */
    public function updateList($update){
        try{
            $res = $this->coll->update($this->options['where'],array('$set' => $update),array('upsert' => true,'multi' => true));

            if($res['updatedExisting']){
                return true;
            }else{
                self::$error = '更新失败';
                echo self::$error;
                return false;
            }

        }catch (Exception $e){
            self::$error = $e->getMessage();
            return false;
        }
    }


    public function add($add){
        $res =  $this->coll->insert($add);

        var_dump($res);exit;

    }




    /**
     * 获得错误提示
     * @return string
     */
    public static function getError(){
        return self::$error;
    }


}


$mongoDB = MongoConn::getInstance()
    ->db('inventory')
    ->coll('orderWait')
    ->where(array('sku' => 'ABC123'))
    ->getFindList('sku,quantity','sku');

//print_r($mongoDB);
//exit;

$mongoDB = MongoConn::getInstance()
    ->db('inventory')
    ->coll('orderWait')
    ->where(array('sku' => 'ABC123'))
    ->updateOne(array('quantity' => 2234));

echo MongoConn::getError();

//var_dump($mongoDB);exit;
$mongoDB = MongoConn::getInstance()
    ->db('inventory')
    ->coll('orderWait')
    ->add(array('sku' => 'ABC123467','qty' => 12));

echo MongoConn::getError();

var_dump($mongoDB);exit;










