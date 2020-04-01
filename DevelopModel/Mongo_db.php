<?php

namespace DevelopModel;

/**
 * CodeIgniter MongoDB Active Record Library
 *
 * A library to interface with the NoSQL database MongoDB. For more information see http://www.mongodb.org
 *
 * @package   CodeIgniter
 * @copyright Copyright (c) 2010, Alex Bilbie.
 * @license   http://codeigniter.com/user_guide/license.html
 * @version   Version 0.3.7
 */
class Mongo_db {

    private $db                          = null;
    private $connection_string           = null;
    private $write_concern               = null;
    private $host                        = null;
    private $port                        = null;
    private $user                        = null;
    private $pass                        = null;
    private $db_name                     = null;
    private $persist                     = null;
    private $persist_key                 = null;
    private $query_safety                = 'safe';
    private $bulk                        = null;
    private $selects                     = array();
    public  $wheres                      = array(); // Public to make debugging easier
    private $sorts                       = array();
    public  $mongo_return                = null;
    private $mongo_supers_connect_error  = null;
    private $limit                       = 1000;
    private $offset                      = 0;

    private $options                     = array();

    /**
     * --------------------------------------------------------------------------------
     * CONSTRUCTOR
     * --------------------------------------------------------------------------------
     *
     * Automatically check if the Mongo PECL extension has been installed/enabled.
     * Generate the connection string and establish a connection to the MongoDB.
     */
    public function __construct(){
        $this->connection_string();
        $this->connect();
    }

    /**
     * --------------------------------------------------------------------------------
     * CONNECT TO MONGODB
     * --------------------------------------------------------------------------------
     *
     * Establish a connection to MongoDB using the connection string generated in
     * the connection_string() method.  If 'mongo_persist_key' was set to true in the
     * config file, establish a persistent connection.  We allow for only the 'persist'
     * option to be set because we want to establish a connection immediately.
     */
    private function connect(){
        try{
            $this->db = new \MongoDB\Driver\Manager($this->connection_string, $this->options);
            $this->write_concern = new \MongoDB\Driver\WriteConcern(\MongoDB\Driver\WriteConcern::MAJORITY, 100);

            return ($this);
        }catch(\MongoDB\Driver\ConnectionException $e){
            if($this->mongo_supers_connect_error){
                trigger_error("Unable to connect to MongoDB", E_USER_ERROR);
            }else{
                trigger_error("Unable to connect to MongoDB: {$e->getMessage()}", E_USER_ERROR);
            }
        }
    }

    /**
     * --------------------------------------------------------------------------------
     * BUILD CONNECTION STRING
     * --------------------------------------------------------------------------------
     *
     * Build the connection string from the config file.
     */
    private function connection_string(){
        $config_arr = include(BASE_PATH.'Conf/mongodb.php');

        $this->host         = $config_arr['mongo_host'];
        $this->port         = $config_arr['mongo_port'];
        $this->user         = $config_arr['mongo_user'];
        $this->pass         = $config_arr['mongo_pass'];
        $this->db_name      = $config_arr['mongo_db'];
        $this->persist      = $config_arr['mongo_persist'];
        $this->persist_key  = $config_arr['mongo_persist_key'];
        $this->query_safety = $config_arr['mongo_query_safety'];
        $this->mongo_return = $config_arr['mongo_return'];
        $this->mongo_return = $config_arr['mongo_return'];
        $host_db_flag         = (bool)$config_arr['host_db_flag'];
        $connection_string  = "mongodb://";

        $this->mongo_supers_connect_error = $config_arr['mongo_supers_connect_error'];

        if(empty($this->host)){
            trigger_error("The Host must be set to connect to MongoDB", E_USER_ERROR);
        }

        if(empty($this->db_name)){
            trigger_error("The Database must be set to connect to MongoDB", E_USER_ERROR);
        }

//        if(!empty($this->user) && !empty($this->pass)){
//            $connection_string .= "{$this->user}:{$this->pass}@";
//        }

        if(isset($this->port) && !empty($this->port)){
            $connection_string .= "{$this->host}:{$this->port}";
        }else{
            $connection_string .= "{$this->host}";
        }

        if($host_db_flag === true){
            $this->connection_string = trim($connection_string).'/'.$this->db_name;
        }else{
            $this->connection_string = trim($connection_string);
        }

        $this->options = array(
            'username'         => $this->user,
            'password'         => $this->pass,
            'db'               => $this->db_name,
            'connect'          => true, // true表示Mongo构造函数中建立连接。
            'connectTimeoutMS' => 5000, // 配置建立连接超时时间，单位是ms
            // 'replicaSet'       => false, // 配置replicaSet名称
        );

    }

    /**
     * --------------------------------------------------------------------------------
     * _clear
     * --------------------------------------------------------------------------------
     *
     * Resets the class variables to default settings
     */
    private function _clear(){
        $this->selects = array();
        $this->wheres  = array();
        $this->limit   = 100;
        $this->offset  = 0;
        $this->sorts   = array();
    }

    /**
     * --------------------------------------------------------------------------------
     * Switch_db
     * --------------------------------------------------------------------------------
     *
     * Switch from default database to a different db
     * @param       $database
     * @return bool
     */
    public function switch_db($database){
        if(empty($database)){
            trigger_error("未选择数据库", E_USER_ERROR);
        }
        $this->connection_string = str_replace($this->db_name, $database, $this->connection_string);
        $this->db_name           = $database;
        try{
            $this->db = new \MongoDB\Driver\Manager($this->connection_string, $this->options);

            return true;
        }catch(\Exception $e){
            trigger_error("Unable to switch Mongo Databases: {$e->getMessage()}", E_USER_ERROR);
        }
    }


    /**
     * --------------------------------------------------------------------------------
     * SELECT FIELDS
     * --------------------------------------------------------------------------------
     *
     * Determine which fields to include OR which to exclude during the query process.
     * Currently, including and excluding at the same time is not available, so the
     * $includes array will take precedence over the $excludes array.  If you want to
     * only choose fields to exclude, leave $includes an empty array().
     *
     * @usage: $this->mongo_db->select(array('foo', 'bar'))->get('foobar');
     */
    public function select($includes = array(), $excludes = array()){
        if(!is_array($includes)){
            $includes = array();
        }

        if(!is_array($excludes)){
            $excludes = array();
        }

        if(!empty($includes)){
            foreach($includes as $col){
                $this->selects[$col] = 1;
            }
        }else{
            foreach($excludes as $col){
                $this->selects[$col] = 0;
            }
        }

        return ($this);
    }

    /**
     * --------------------------------------------------------------------------------
     * WHERE PARAMETERS
     * --------------------------------------------------------------------------------
     *
     * Get the documents based on these search parameters.  The $wheres array should
     * be an associative array with the field as the key and the value as the search
     * criteria.
     *
     * @usage : $this->mongo_db->where(array('foo' => 'bar'))->get('foobar');
     */
    public function where($wheres = array()){
        foreach($wheres as $wh => $val){
            $this->wheres[$wh] = $val;
        }

        return ($this);
    }

    /**
     * --------------------------------------------------------------------------------
     * OR_WHERE PARAMETERS
     * --------------------------------------------------------------------------------
     *
     * Get the documents where the value of a $field may be something else
     *
     * @usage : $this->mongo_db->or_where(array( array('foo'=>'bar', 'bar'=>'foo' ))->get('foobar');
     */
    public function or_where($wheres = array()){
        if(count($wheres) > 0){
            if(!isset($this->wheres['$or']) || !is_array($this->wheres['$or'])){
                $this->wheres['$or'] = array();
            }

            foreach($wheres as $wh => $val){
                $this->wheres['$or'][] = array($wh => $val);
            }
        }

        return ($this);
    }

    /**
     * --------------------------------------------------------------------------------
     * WHERE_IN PARAMETERS
     * --------------------------------------------------------------------------------
     *
     * Get the documents where the value of a $field is in a given $in array().
     *
     * @usage : $this->mongo_db->where_in('foo', array('bar', 'zoo', 'blah'))->get('foobar');
     */
    public function where_in($field, $in = array()){
        $this->_where_init($field);
        $this->wheres[$field]['$in'] = $in;

        return ($this);
    }

    /**
     * --------------------------------------------------------------------------------
     * WHERE_IN_ALL PARAMETERS
     * --------------------------------------------------------------------------------
     *
     * Get the documents where the value of a $field is in all of a given $in array().
     *
     * @usage : $this->mongo_db->where_in('foo', array('bar', 'zoo', 'blah'))->get('foobar');
     */
    public function where_in_all($field, $in = array()){
        $this->_where_init($field);
        $this->wheres[$field]['$all'] = $in;

        return ($this);
    }

    /**
     * --------------------------------------------------------------------------------
     * WHERE_NOT_IN PARAMETERS
     * --------------------------------------------------------------------------------
     *
     * Get the documents where the value of a $field is not in a given $in array().
     *
     * @usage : $this->mongo_db->where_not_in('foo', array('bar', 'zoo', 'blah'))->get('foobar');
     */
    public function where_not_in($field, $in = array()){
        $this->_where_init($field);
        $this->wheres[$field]['$nin'] = $in;

        return ($this);
    }

    /**
     * --------------------------------------------------------------------------------
     * WHERE GREATER THAN PARAMETERS
     * --------------------------------------------------------------------------------
     *
     * Get the documents where the value of a $field is greater than $x
     *
     * @usage : $this->mongo_db->where_gt('foo', 20);
     */
    public function where_gt($field, $x){
        $this->_where_init($field);
        $this->wheres[$field]['$gt'] = $x;

        return ($this);
    }

    /**
     * --------------------------------------------------------------------------------
     * WHERE GREATER THAN OR EQUAL TO PARAMETERS
     * --------------------------------------------------------------------------------
     *
     * Get the documents where the value of a $field is greater than or equal to $x
     * @usage : $this->mongo_db->where_gte('foo', 20);
     */
    public function where_gte($field, $x){
        $this->_where_init($field);
        $this->wheres[$field]['$gte'] = $x;

        return ($this);
    }

    /**
     * --------------------------------------------------------------------------------
     * WHERE LESS THAN PARAMETERS
     * --------------------------------------------------------------------------------
     *
     * Get the documents where the value of a $field is less than $x
     *
     * @usage : $this->mongo_db->where_lt('foo', 20);
     */
    public function where_lt($field, $x){
        $this->_where_init($field);
        $this->wheres[$field]['$lt'] = $x;

        return ($this);
    }

    /**
     * --------------------------------------------------------------------------------
     * WHERE LESS THAN OR EQUAL TO PARAMETERS
     * --------------------------------------------------------------------------------
     *
     * Get the documents where the value of a $field is less than or equal to $x
     *
     * @usage : $this->mongo_db->where_lte('foo', 20);
     */
    public function where_lte($field, $x){
        $this->_where_init($field);
        $this->wheres[$field]['$lte'] = $x;

        return ($this);
    }

    /**
     * --------------------------------------------------------------------------------
     * WHERE BETWEEN PARAMETERS
     * --------------------------------------------------------------------------------
     *
     * Get the documents where the value of a $field is between $x and $y
     *
     * @usage : $this->mongo_db->where_between('foo', 20, 30);
     */
    public function where_between($field, $x, $y){
        $this->_where_init($field);
        $this->wheres[$field]['$gte'] = $x;
        $this->wheres[$field]['$lte'] = $y;

        return ($this);
    }

    /**
     * --------------------------------------------------------------------------------
     * WHERE BETWEEN AND NOT EQUAL TO PARAMETERS
     * --------------------------------------------------------------------------------
     *
     * Get the documents where the value of a $field is between but not equal to $x and $y
     *
     * @usage : $this->mongo_db->where_between_ne('foo', 20, 30);
     */

    public function where_between_ne($field, $x, $y){
        $this->_where_init($field);
        $this->wheres[$field]['$gt'] = $x;
        $this->wheres[$field]['$lt'] = $y;

        return ($this);
    }

    /**
     * --------------------------------------------------------------------------------
     * WHERE NOT EQUAL TO PARAMETERS
     * --------------------------------------------------------------------------------
     *
     * Get the documents where the value of a $field is not equal to $x
     *
     * @usage : $this->mongo_db->where_not_equal('foo', 1)->get('foobar');
     */
    public function where_ne($field, $x){
        $this->_where_init($field);
        $this->wheres[$field]['$ne'] = $x;

        return ($this);
    }

    /**
     * --------------------------------------------------------------------------------
     * WHERE NOT EQUAL TO PARAMETERS
     * --------------------------------------------------------------------------------
     *
     * Get the documents nearest to an array of coordinates (your collection must have a geospatial index)
     *
     * @usage : $this->mongo_db->where_near('foo', array('50','50'))->get('foobar');
     */
    public function where_near($field = '', $co = array()){
        $this->__where_init($field);
        $this->where['$near'] = $co;

        return ($this);
    }

    /**
     * --------------------------------------------------------------------------------
     * LIKE PARAMETERS
     * --------------------------------------------------------------------------------
     *
     * Get the documents where the (string) value of a $field is like a value. The defaults
     * allow for a case-insensitive search.
     *
     * @param $flags
     *   Allows for the typical regular expression flags:
     *   i = case insensitive
     *   m = multiline
     *   x = can contain comments
     *   l = locale
     *   s = dotall, "." matches everything, including newlines
     *   u = match unicode
     *
     * @param $enable_start_wildcard
     *   If set to anything other than TRUE, a starting line character "^" will be prepended
     *   to the search value, representing only searching for a value at the start of
     *   a new line.
     *
     * @param $enable_end_wildcard
     *   If set to anything other than TRUE, an ending line character "$" will be appended
     *   to the search value, representing only searching for a value at the end of
     *   a line.
     *
     * @usage : $this->mongo_db->like('foo', 'bar', 'im', FALSE, TRUE);
     */
    public function like($field, $value = "", $flags = "i", $enable_start_wildcard = true, $enable_end_wildcard = true){
        $field = (string)trim($field);
        $this->where_init($field);
        $value = (string)trim($value);
        $value = quotemeta($value);

        if($enable_start_wildcard !== true){
            $value = "^".$value;
        }

        if($enable_end_wildcard !== true){
            $value .= "$";
        }

        $regex                = "/$value/$flags";
        $this->wheres[$field] = new \MongoRegex($regex);

        return ($this);
    }

    /**
     * --------------------------------------------------------------------------------
     * ORDER BY PARAMETERS
     * --------------------------------------------------------------------------------
     *
     * Sort the documents based on the parameters passed. To set values to descending order,
     * you must pass values of either -1, FALSE, 'desc', or 'DESC', else they will be
     * set to 1 (ASC).
     *
     * @usage : $this->mongo_db->where_between('foo', 20, 30);
     */
    public function order_by($fields = array()){
        foreach($fields as $col => $val){
            if($val == -1 || $val === false || strtolower($val) == 'desc'){
                $this->sorts[$col] = -1;
            }else{
                $this->sorts[$col] = 1;
            }
        }

        return ($this);
    }

    /**
     * --------------------------------------------------------------------------------
     * LIMIT DOCUMENTS
     * --------------------------------------------------------------------------------
     *
     * Limit the result set to $x number of documents
     *
     * @usage : $this->mongo_db->limit($x);
     */
    public function limit($x = 99999){
        if($x !== null && is_numeric($x) && $x >= 1){
            $this->limit = (int)$x;
        }

        return ($this);
    }

    /**
     * --------------------------------------------------------------------------------
     * OFFSET DOCUMENTS
     * --------------------------------------------------------------------------------
     *
     * Offset the result set to skip $x number of documents
     *
     * @usage : $this->mongo_db->offset($x);
     */
    public function offset($x = 0){
        if($x !== null && is_numeric($x) && $x >= 1){
            $this->offset = (int)$x;
        }

        return ($this);
    }

    /**
     * --------------------------------------------------------------------------------
     * GET_WHERE
     * --------------------------------------------------------------------------------
     *
     * Get the documents based upon the passed parameters
     *
     * @usage : $this->mongo_db->get_where('foo', array('bar' => 'something'));
     */
    public function get_where($collection = "", $where = array()){
        return ($this->where($where)->get($collection));
    }

    /**
     * --------------------------------------------------------------------------------
     * GET
     * --------------------------------------------------------------------------------
     *
     * Get the documents based upon the passed parameters
     *
     * @usage : $this->mongo_db->get('foo', array('bar' => 'something'));
     */
    public function get($collection = ""){
        if(empty($collection)){
            trigger_error("In order to retreive documents from MongoDB, a collection name must be passed", E_USER_ERROR);
        }

        $options = [];
        if($this->offset){
            $options['skip'] = ($this->offset - 1) * $this->limit;
        }

        if($this->limit){
            $options['limit'] = $this->limit;
        }
        if($this->sorts){
            $options['sort'] = $this->sorts;
        }

        if($this->selects){
            $options['projection'] = $this->selects;
        }

        $query  = new \MongoDB\Driver\Query($this->wheres, $options);
        $cursor = $this->db->executeQuery($this->db_name.".".$collection, $query);

        $this->_clear();

        $returns = array();

        foreach($cursor as $doc){
            $returns[] = $doc;
        }

        if($this->mongo_return == 'object'){
            return (object)$returns;
        }else{
            return $returns;
        }


    }

    /**
     * --------------------------------------------------------------------------------
     * COUNT
     * --------------------------------------------------------------------------------
     *
     * Count the documents based upon the passed parameters
     *
     * @usage : $this->mongo_db->get('foo');
     */
    public function count($collection = ""){
        if(empty($collection)){
            trigger_error("In order to retreive a count of documents from MongoDB, a collection name must be passed", E_USER_ERROR);
        }

        $command = new \MongoDB\Driver\Command(['count' => $collection, 'query' => $this->wheres]);
        $result  = $this->db->executeCommand($this->db_name, $command);
        $res     = $result->toArray();
        $cnt     = 0;
        $this->_clear();
        if($res){
            $cnt = $res[0]->n;
        }

        return $cnt;
    }

    /**
     * --------------------------------------------------------------------------------
     * INSERT
     * --------------------------------------------------------------------------------
     *
     * Insert a new document into the passed collection
     *
     * @usage : $this->mongo_db->insert('foo', $data = array());
     */
    public function insert($collection = "", $insert = array()){
        if(empty($collection)){
            trigger_error("No Mongo collection selected to insert into", E_USER_ERROR);
        }

        if(count($insert) == 0 || !is_array($insert)){
            trigger_error("Nothing to insert into Mongo collection or insert is not an array", E_USER_ERROR);
        }

        try{
            $bulk   = new \MongoDB\Driver\BulkWrite();
            $result = $bulk->insert($insert);
            $id     = '';
            foreach($result as $key => $val){
                if($key == 'oid'){
                    $id = $val;
                }
            }

            if($id){
                $result = $this->db->executeBulkWrite($this->db_name.".".$collection, $bulk);
                if($result->getInsertedCount()){
                    return $id;
                }else{
                    return false;
                }
            }else{
                return false;
            }

        }catch(\MongoDB\Driver\InvalidArgumentException $e){
            trigger_error("Insert of data into MongoDB failed: {$e->getMessage()}", E_USER_ERROR);
        }
    }

    /**
     * --------------------------------------------------------------------------------
     * UPDATE
     * --------------------------------------------------------------------------------
     *
     * Updates a single document
     *
     * @usage: $this->mongo_db->update('foo', $data = array());
     */
    public function update($collection = "", $data = array(), $upsert = false){
        if(empty($collection)){
            trigger_error("No Mongo collection selected to update", E_USER_ERROR);
        }

        if(count($data) == 0 || !is_array($data)){
            trigger_error("Nothing to update in Mongo collection or update is not an array", E_USER_ERROR);
        }
        if(empty($this->wheres)){
            trigger_error("Update Record Must Be With Condition Of Where", E_USER_ERROR);
        }

        try{
            $bulk = new \MongoDB\Driver\BulkWrite();
            $bulk->update($this->wheres, ['$set' => $data], ['multi' => true, 'upsert' => $upsert]);
            $result = $this->db->executeBulkWrite("$this->db_name.$collection", $bulk, $this->write_concern);
            $this->_clear();

            return $result->getModifiedCount();
        }catch(\MongoDB\Driver\InvalidArgumentException $e){
            trigger_error("Update of data into MongoDB failed: {$e->getMessage()}", E_USER_ERROR);
        }

    }

    /**
     * --------------------------------------------------------------------------------
     * DELETE
     * --------------------------------------------------------------------------------
     *
     * delete document from the passed collection based upon certain criteria
     *
     * @usage : $this->mongo_db->delete('foo', $data = array());
     */
    public function delete($collection = "", $where = array()){
        if(empty($collection)){
            trigger_error("No Mongo collection selected to delete from", E_USER_ERROR);
        }

        if(is_array($where) && count($where) > 0){
            $this->wheres = $where;
        }

        if(empty($this->wheres)){
            trigger_error("Delete Data Must Be With Condition Of Where", E_USER_ERROR);
        }

        try{
            $bulk   = new \MongoDB\Driver\BulkWrite();
            $result = $this->bulk->delete($where, ['limit' => 1]);
            $result = $this->db->executeBulkWrite("$this->db_name.$collection", $bulk, $this->write_concern);
            $this->_clear();

            return $result->getDeletedCount();
        }catch(\MongoDB\Driver\InvalidArgumentException $e){
            trigger_error("Delete of data into MongoDB failed: {$e->getMessage()}", E_USER_ERROR);
        }

    }

    /**
     * --------------------------------------------------------------------------------
     * DELETE_ALL
     * --------------------------------------------------------------------------------
     *
     * Delete all documents from the passed collection based upon certain criteria
     *
     * @usage : $this->mongo_db->delete_all('foo', $data = array());
     */
    public function delete_all($collection = "", $where = array()){
        if(empty($collection)){
            trigger_error("No Mongo collection selected to delete from", E_USER_ERROR);
        }

        if(is_array($where) && count($where) > 0){
            $this->wheres = $where;
        }

        if(empty($this->wheres)){
            trigger_error("Delete Data Must Be With Condition Of Where", E_USER_ERROR);
        }

        try{
            $bulk   = new \MongoDB\Driver\BulkWrite();
            $result = $this->bulk->delete($where, ['limit' => 1]);
            $result = $this->db->executeBulkWrite("$this->db_name.$collection", $bulk, $this->write_concern);
            $this->_clear();

            return $result->getDeletedCount();
        }catch(\MongoDB\Driver\InvalidArgumentException $e){
            trigger_error("Delete of data into MongoDB failed: {$e->getMessage()}", E_USER_ERROR);
        }

    }

    /**
     * --------------------------------------------------------------------------------
     * WHERE INITIALIZER
     * --------------------------------------------------------------------------------
     *
     * Prepares parameters for insertion in $wheres array().
     */
    private function _where_init($param){
        if(!isset($this->wheres[$param])){
            $this->wheres[$param] = array();
        }
    }

}
