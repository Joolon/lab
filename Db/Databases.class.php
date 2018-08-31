<?php

/**
 * Created by JoLon.
 * User: JoLon
 * Date: 2016/10/12
 * Time: 15:59
 */
namespace Db;

class Databases
{
    private static $_instance = null;
    private $sql = null;

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    public static function getInstance()
    {
        if (!self::$_instance instanceof self) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    /**
     * SELECT方法参数可变，调用时可以传入多于定义个数的参数
     * @param null $columns
     * @return $this
     */
    public function select($columns = NULL)
    {
        $columns = func_get_args();// 获取函数所有参数
        if (empty($columns)) {// 查询所有列
            $str = '*';
        } else {
            $str = mysql_escape_string(implode(",", $columns));
        }
        $this->sql = "SELECT $str ";
        return $this;
    }

    public function from($table_name)
    {
        $this->sql = $this->sql . " FROM $table_name ";
        return $this;
    }

    public function where($column, $oper, $value)
    {
        switch ($oper) {
            case 'LIKE':
                $this->sql = $this->sql . " WHERE $column $oper '%$value%' ";
                break;

            case 'IN':
                $in_str = "('" . implode("','", $value) . "')";
                $this->sql = $this->sql . " WHERE $column IN $in_str ";
                break;

            default :
                $this->sql = $this->sql . " WHERE $column $oper '$value' ";
        }
        return $this;
    }

    public function and_where($column, $oper, $value)
    {
        $this->sql = $this->sql . " AND $column $oper $value";
        return $this;
    }

    public function execue($database)
    {
        $conn = mysql_connect('localhost', 'root', 'root');
        mysql_select_db($database, $conn);
        mysql_query("SET NAMES UTF-8");
        return $this;
    }

    public function current()
    {
        $this->sql .= ' LIMIT 1';
        $datas = mysql_query($this->sql);
        if ($datas) {
            $res = mysql_fetch_row($datas);
        }
        return $res;
    }

    public static function query($sql)
    {

    }

    public function compile()
    {
        return $this->sql;
    }

    public function begin()
    {
        mysql_query("BEGIN");
    }

    public function commit()
    {
        mysql_query("COMMIT");
    }

    public function rollback()
    {
        mysql_query("ROLLBACK");
    }

}