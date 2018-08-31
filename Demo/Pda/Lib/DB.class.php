<?php

/**
 * 数据库连接类
 * Class DBClass
 */
class DBClass
{
    public $link = null;

    /**
     * 使用构造函数连接数据库
     * DBClass constructor.
     * @param string $host
     * @param string $root
     * @param string $password
     * @param string $dbname
     */
    public function __construct($host = "61.145.158.170:6307", $root = "frode", $password = "xs0HvB5E33", $dbname = "test")
    {
        if (!$this->link = @mysql_connect($host, $root, $password)) {
            echo "database connected failure" . mysql_error();
            exit;
        }
        mysql_query("SET NAMES utf8");
        if (!@mysql_select_db($dbname)) {
            echo "数据库连接失败";
            die();
        }
    }

    /**
     * 执行查询语句
     * @param $sql
     * @param bool $replaceDbTable
     * @return resource
     */
    public function query($sql, $replaceDbTable = true)
    {
        if (isset($_SESSION['dbNow']) && $_SESSION['dbNow'] == 'old' && $replaceDbTable) $sql = $this->changeDbTable($sql);
        $this->logStr($sql);    //sql str log,add by Frode (2015.03.30)

        /***** S: add by Frode (2014.07.22) *****/
        $s = time();
        $r = @mysql_query($sql);
        $ut = time() - $s;
        if ($ut > 10) {
            $sessionTrueName = isset($_SESSION['truename']) ? $_SESSION['truename'] : '';
            $fp = fopen("/frodeLog.txt", "a");
            $str = $ut . ' ----- ' . date('Y-m-d H:i:s') . ' -------- ' . $sessionTrueName . ' ------------- ' . $sql . ' ------------- ' . $_SERVER['REQUEST_URI'] . "\r\n\r\n";
            fwrite($fp, $str);
            fclose($fp);
        }
        return $r;
    }

    /**
     * 执行查询语句之外的操作 例如:添加，修改，删除
     * @param $sql
     * @param bool $replaceDbTable
     * @return resource
     */
    public function execute($sql, $replaceDbTable = true)
    {
        if (isset($_SESSION['dbNow']) && $_SESSION['dbNow'] == 'old' && $replaceDbTable) $sql = $this->changeDbTable($sql);    //change db,add by Frode (2015.03.30)
        $this->logStr($sql);    //sql str log,add by Frode (2015.03.30)

        /***** S: add by Frode (2014.07.22) *****/
        $s = time();
        if (@function_exists("mysql_unbuffered_query")) {
            $result = @mysql_unbuffered_query($sql);
        } else {
            $result = @mysql_query($sql);
        }
        $ut = time() - $s;
        if ($ut > 10) {
            $sessionTrueName = isset($_SESSION['truename']) ? $_SESSION['truename'] : '';
            $fp = fopen("/frodeLog.txt", "a");
            $str = $ut . ' ----- ' . date('Y-m-d H:i:s') . ' -------- ' . $sessionTrueName . ' ------------- ' . $sql . ' ------------- ' . $_SERVER['REQUEST_URI'] . "\r\n\r\n";
            fwrite($fp, $str);
            fclose($fp);
        }
        /***** E: add by Frode (2014.07.22) *****/
        return $result;
    }

    /**
     * 执行更新语句
     * @param $sql
     * @return int
     */
    public function update($sql)
    {
        if (@function_exists("mysql_unbuffered_query")) {
            $result = @mysql_unbuffered_query($sql);
        } else {
            $result = @mysql_query($sql);
        }
        $rows = mysql_affected_rows($this->link);
        return $rows;
    }

    /**
     * 获得表的记录的行数
     * @param $result
     * @return int
     */
    public function num_rows($result)
    {
        if ($result) {
            return @mysql_num_rows($result);
        } else {
            return 0;
        }
    }

    public function counts($sql)
    {
        $query = @mysql_query($sql);
        if ($rs = @mysql_fetch_array($query)) {
            $total = $rs[0];
        } else {
            $total = 0;
        }
        return $total;
    }

    /**
     * 返回对象数据
     * @param $result
     * @return object|stdClass
     */
    public function fetch_object($result)
    {
        return @mysql_fetch_object($result);
    }

    /*************************
     * 返回关联数据
     *************************/
    public function fetch_assoc($result)
    {
        return @mysql_fetch_assoc($result);
    }

    /**
     * 返回关联数据
     * @param $result
     * @param string $type
     * @return array
     */
    public function fetch_array($result, $type = 'MYSQL_BOTH')
    {
        return @mysql_fetch_array($result, $type);
    }

    /**
     * 关闭相关与数据库的信息链接
     * @param $result
     * @return bool
     */
    public function free_result($result)
    {
        return @mysql_free_result($result);
    }

    public function close()
    {
        return @mysql_close();
    }

    /**
     * 其他操作例如结果集放入数组中
     * @param $result
     * @return array
     */
    public function getResultArray($result)
    {
        $array = array();
        $i = 0;
        while ($row = @mysql_fetch_assoc($result)) {
            $array[$i] = $row;
            $i++;
        }
        @mysql_free_result($result);
        return $array;
    }

    public function changeDbTable($sql)
    {
        if (stripos($sql, 'ebay_order')) {
            if (stripos($sql, ' ebay_order ')) $sql = str_ireplace(' ebay_order ', ' ebay_order_HistoryRcd ', $sql);
            if (stripos($sql, ' ebay_order.')) $sql = str_ireplace(' ebay_order.', ' ebay_order_HistoryRcd.', $sql);
            if (stripos($sql, ',ebay_order.')) $sql = str_ireplace(',ebay_order.', ',ebay_order_HistoryRcd.', $sql);
            if (stripos($sql, '`ebay_order`')) $sql = str_ireplace('`ebay_order`', '`ebay_order_HistoryRcd`', $sql);
            if (stripos($sql, 'ebay_orderdetail')) {
                if (stripos($sql, ' ebay_orderdetail ')) $sql = str_ireplace(' ebay_orderdetail ', ' ebay_orderdetail_HistoryRcd ', $sql);
                if (stripos($sql, ' ebay_orderdetail.')) $sql = str_ireplace(' ebay_orderdetail.', ' ebay_orderdetail_HistoryRcd.', $sql);
                if (stripos($sql, ',ebay_orderdetail.')) $sql = str_ireplace(',ebay_orderdetail.', ',ebay_orderdetail_HistoryRcd.', $sql);
                if (stripos($sql, '`ebay_orderdetail`')) $sql = str_ireplace('`ebay_orderdetail`', '`ebay_orderdetail_HistoryRcd`', $sql);
                if (stripos($sql, ' ebay_orderdetail(')) $sql = str_ireplace(' ebay_orderdetail(', ' ebay_orderdetail_HistoryRcd(', $sql);
            }
            if (stripos($sql, 'ebay_orderslog')) {
                if (stripos($sql, ' ebay_orderslog ')) $sql = str_ireplace(' ebay_orderslog ', ' ebay_orderslog_HistoryRcd ', $sql);
                if (stripos($sql, ' ebay_orderslog.')) $sql = str_ireplace(' ebay_orderslog.', ' ebay_orderslog_HistoryRcd.', $sql);
                if (stripos($sql, ',ebay_orderslog.')) $sql = str_ireplace(',ebay_orderslog.', ',ebay_orderslog_HistoryRcd.', $sql);
                if (stripos($sql, '`ebay_orderslog`')) $sql = str_ireplace('`ebay_orderslog`', '`ebay_orderslog_HistoryRcd`', $sql);
                if (stripos($sql, ' ebay_orderslog(')) $sql = str_ireplace(' ebay_orderslog(', ' ebay_orderslog_HistoryRcd(', $sql);
            }
        }
        if (stripos($sql, 'ebay_iostore')) {
            if (stripos($sql, ' ebay_iostore ')) $sql = str_ireplace(' ebay_iostore ', ' ebay_iostore_HistoryRcd ', $sql);
            if (stripos($sql, ' ebay_iostore.')) $sql = str_ireplace(' ebay_iostore.', ' ebay_iostore_HistoryRcd.', $sql);
            if (stripos($sql, ',ebay_iostore.')) $sql = str_ireplace(',ebay_iostore.', ',ebay_iostore_HistoryRcd.', $sql);
            if (stripos($sql, '`ebay_iostore`')) $sql = str_ireplace('`ebay_iostore`', '`ebay_iostore_HistoryRcd`', $sql);
            if (stripos($sql, 'ebay_iostoredetail')) {
                if (stripos($sql, ' ebay_iostoredetail ')) $sql = str_ireplace(' ebay_iostoredetail ', ' ebay_iostoredetail_HistoryRcd ', $sql);
                if (stripos($sql, ' ebay_iostoredetail.')) $sql = str_ireplace(' ebay_iostoredetail.', ' ebay_iostoredetail_HistoryRcd.', $sql);
                if (stripos($sql, ',ebay_iostoredetail.')) $sql = str_ireplace(',ebay_iostoredetail.', ',ebay_iostoredetail_HistoryRcd.', $sql);
                if (stripos($sql, '`ebay_iostoredetail`')) $sql = str_ireplace('`ebay_iostoredetail`', '`ebay_iostoredetail_HistoryRcd`', $sql);
                if (stripos($sql, ' ebay_iostoredetail(')) $sql = str_ireplace(' ebay_iostoredetail(', ' ebay_iostoredetail_HistoryRcd(', $sql);
            }
        }
        if (stripos($sql, 'ebay_message')) {
            if (stripos($sql, ' ebay_message ')) $sql = str_ireplace(' ebay_message ', ' ebay_message_HistoryRcd ', $sql);
            if (stripos($sql, ' ebay_message.')) $sql = str_ireplace(' ebay_message.', ' ebay_message_HistoryRcd.', $sql);
            if (stripos($sql, ',ebay_message.')) $sql = str_ireplace(',ebay_message.', ',ebay_message_HistoryRcd.', $sql);
            if (stripos($sql, '`ebay_message`')) $sql = str_ireplace('`ebay_message`', '`ebay_message_HistoryRcd`', $sql);
        }
        if (stripos($sql, 'ali_message')) {
            if (stripos($sql, ' ali_message ')) $sql = str_ireplace(' ali_message ', ' ali_message_HistoryRcd ', $sql);
            if (stripos($sql, ' ali_message.')) $sql = str_ireplace(' ali_message.', ' ali_message_HistoryRcd.', $sql);
            if (stripos($sql, ',ali_message.')) $sql = str_ireplace(',ali_message.', ',ali_message_HistoryRcd.', $sql);
            if (stripos($sql, '`ali_message`')) $sql = str_ireplace('`ali_message`', '`ali_message_HistoryRcd`', $sql);
        }
        return $sql;
    }

    public function logStr($sql)
    {
        $table_name = '';
        $sql_type = '';
        $url = $_SERVER['PHP_SELF'];
        $sql1 = preg_replace("/[\s]+/is", " ", trim($sql));
        $sql_arr = explode(' ', $sql1);
        $issave = false;
        $config_uTableName = array('ebay_order', 'ebay_onhandle');
        $config_dTableName = array('ebay_order', 'ebay_goods');
        //保存u操作
        if (strtolower($sql_arr[0]) == 'update') {
            if (in_array(strtolower($sql_arr[1]), $config_uTableName)) {
                $table_name = $sql_arr[1];
                $sql_type = $sql_arr[0];
                $issave = true;
            }
        } elseif (strtolower($sql_arr[0]) == 'delete') {
            if (in_array(strtolower($sql_arr[2]), $config_dTableName)) {
                $table_name = $sql_arr[2];
                $sql_type = $sql_arr[0];
                $issave = true;
            }
        }
        if ($issave) {
            $inSql = 'INSERT sql_log1(run_url,table_name,sql_str,sql_type) VALUES("' . mysql_escape_string($url) . '","' . $table_name . '","' . mysql_escape_string($sql) . '","' . $sql_type . '")';
            @mysql_query($inSql);
        }
    }
}


/**
 * DB 描述信息: db操作类
 */
class DB
{
    public static $dbcon = null;

    private static function init()
    {
        if (!self::$dbcon) {
            $dbcon = new DBClass();
            self::$dbcon = $dbcon;
        } else {
            $dbcon = self::$dbcon;
        }
        return $dbcon;
    }


    private static function exec($sql)
    {
        $dbcon = self::init();
        $rs = $dbcon->execute($sql);
        return $rs;
    }

    private static function getArray($rs)
    {
        $dbcon = self::init();
        return $dbcon->getResultArray($rs);
    }

    /**
     * 单表查找多个数据
     * @param string $table
     * @param string $where
     * @param string $fields
     * @param string $limit
     * @return mixed
     */
    public static function Select($table, $where, $fields = '*', $limit = '')
    {
        if ($limit) {
            $sql = "SELECT $fields FROM $table WHERE $where LIMIT $limit";
        } else {
            $sql = "SELECT $fields FROM $table WHERE $where";
        }
        $arr = self::getArray(self::exec($sql));
        return $arr;
    }

    /**
     * 单表查找单个数据
     * @param string $table
     * @param string $where
     * @param string $fields
     * @return mixed
     */
    public static function Find($table, $where, $fields = '*')
    {
        $arr = self::Select($table, $where, $fields, 1);
        if ($arr) {
            return $arr[0];
        } else {
            return false;
        }
    }

    /**
     * 添加数据
     * @param string $table
     * @param string $data
     * @return boolean
     */
    public static function Add($table, $data)
    {
        $arr = current($data);
        if (is_array($arr)) {
            $keys = array_keys($arr);
        } else {
            $keys = array_keys($data);
            $data = array($data);
        }
        $_temp = array();
        foreach ($data as $row) {
            $t = array();
            foreach ($keys as $num => $key) {
                if (version_compare(PHP_VERSION, '5.3.0', 'ge')) {
                    $t[$num] = addslashes($row[$key]);
                } else {
                    $t[$num] = mysql_real_escape_string($row[$key]);
                }
            }
            $_temp[] = "('" . implode("','", $t) . "')";
        }
        $key_str = "`" . implode("`,`", $keys) . "`";
        $val_str = implode(",", $_temp);
        $sql = "INSERT INTO $table ($key_str) VALUES $val_str";
        $rs = self::exec($sql);
        return @mysql_insert_id();
    }

    /**
     * 更新表
     * @param string $table
     * @param string $update_data
     * @param string $where
     * @return mixed
     */
    public static function Update($table, $update_data, $where)
    {
        $_temp = array();
        foreach ($update_data as $key => $value) {
            if (version_compare(PHP_VERSION, '5.3.0', 'ge')) {
                $value = addslashes($value);
            } else {
                $value = mysql_real_escape_string($value);
            }
            $_temp[] = "`$key`='$value'";
        }
        $sql = "UPDATE $table SET " . implode(",", $_temp) . " WHERE $where";
        $rs = self::exec($sql);
        return mysql_affected_rows();
    }

    /**
     * 删除
     * @param string $table
     * @param string $where
     * @return mixed
     */
    public static function Delete($table, $where)
    {
        $sql = "DELETE FROM $table WHERE $where";
        $rs = self::exec($sql);
        return $rs;
    }

    public static function QuerySQL($sql)
    {
        $sql = trim($sql);
        $rs = self::exec($sql);
        $arr = explode(" ", $sql);
        $action = strtoupper($arr[0]);
        switch ($action) {
            case 'SELECT':
                return self::getArray($rs);
                break;
            case 'INSERT':
                return mysql_insert_id();
                break;
            case 'UPDATE':
                return mysql_affected_rows();
                break;
            case 'DELETE':
                return mysql_affected_rows();
                break;
            default:
                return false;
                break;
        }
    }

    public static function GetResultSet($sql)
    {
        $dbcon = self::init();
        return $dbcon->query($sql);
    }

}
