<?php

/**
 * 数据库工具类
 * time 2019-06-19
 * author xfdada
 */
class Mysql{
    protected $host;
    protected $user;
    protected $password;
    protected $link;

    public function __construct($host,$user,$password,$dbname)
    {
        $this->host= $host;
        $this->user = $user;
        $this->password = $password;
        $this->link = mysqli_connect($this->host,$this->user,$this->password,$dbname) or die('数据库连接失败!');
        mysqli_set_charset($this->link,"utf8");
    }
    //查询所有数据

    /**
     * @param $table string 表名
     * @param $prams array 查询参数如 ['name'=>'天子','phone'=>1399624431]
     * @filed string 'id,name,age' 需要获取的字段
     * return 返回数据数组
     */
    public function select($table,$prams='',$filed='*'){

        $sql = "select ".$filed." from ".$table;
        if ($prams!=''){
            $where = ' where';
            $count = 1;
            foreach ($prams as $k=>$v){
                if($count>=2){
                    $where.=' and '."{$k}='{$v}'";
                }
                else{
                    $where.=" {$k}='{$v}'";
                }
                $count++;
            }
            $sql.=' '.$where;
        }
        $res = mysqli_query($this->link,$sql);
        $list = [];
        while ($arr = mysqli_fetch_assoc($res)) {
           $list[] = $arr;
        }
        mysqli_close($this->link);
        return $list;
    }

    /**
     *  获取一条数据
     * @param $table string 表名
     * @param $prams array 查询参数如 ['name'=>'天子','phone'=>1399624431]
     * @filed string 'id,name,age' 需要获取的字段
     * return 返回数据数组
     */
    public function getOne($table,$prams='',$field='*'){
        $sql = "select ".$field." from ".$table;
        if ($prams!=''){
            $where = ' where';
            $count = 1;
            foreach ($prams as $k=>$v){
                if($count>=2){
                    $where.=' and '."{$k}='{$v}'";
                }
                else{
                    $where.=" {$k}='{$v}'";
                }
                $count++;
            }
            $sql.=' '.$where.' limit 1';
        }
        $res = mysqli_query($this->link,$sql);
        mysqli_close($this->link);
        return mysqli_fetch_assoc($res);
    }

    /**
     * 分页查询数据
     * @param $table string 表名
     * @param $prams array 查询参数如 ['name'=>'天子','phone'=>1399624431]
     * @filed string 'id,name,age' 需要获取的字段
     * @param $rows 每页多少条
     * @param $page 页码
     * return  array
     */
    public function Paginate($table,$prams='',$filed='*',$rows=10,$page=1){

        $sql = "select ".$filed." from ".$table;
        $where = '';
        if ($prams!=''){
            $where = ' where';
            $count = 1;
            foreach ($prams as $k=>$v){
                if($count>=2){
                    $where.=' and '."{$k}='{$v}'"; //拼装sql语句
                }
                else{
                    $where.=" {$k}='{$v}'";
                }
                $count++;
            }
            $sql.=' '.$where;
        }
        $data['total'] =mysqli_fetch_array(mysqli_query($this->link,"select count(*) from ".$table.' '.$where))[0] ;//记录总条数
        $data['current'] = $page;//当前页
        $data['pages'] = ceil($data['total']/$rows);//计算页数
        $data['rows'] = $rows;//每页条数
        $offset = ($page-1)*$rows;//偏移量
        $sql.=" limit {$offset},{$rows}";
        $res = mysqli_query($this->link,$sql);
        $list = [];
        while ($arr = mysqli_fetch_assoc($res)) {
            $list[] = $arr;
        }
        $data['data'] = $list;
        return $data;
    }

    /**
     * 删除一条数据
     * @param $table 必填项
     * @param $prams 必填项
     * @return int|mysqli_affected_rows 返回受影响的条数
     */
    public function delete($table,$prams){
        $sql = "delete from ".$table;
         $where = ' where';
            $count = 1;
            foreach ($prams as $k=>$v){
                if($count>=2){
                    $where.=' and '."{$k}='{$v}'";
                }
                else{
                    $where.=" {$k}='{$v}'";
                }
                $count++;
            }
            $sql.=' '.$where;
            mysqli_query($this->link,$sql )or die(mysqli_error());
            $status = mysqli_affected_rows($this->link);//返回受影响的条数
            mysqli_close($this->link);
            return $status;
    }

    /**
     * @param $table  string 数据表名
     * @param $prams  array 条件
     * @param $data  array   更新的数据
     * return 返回受影响的条数
     */
    public function update($table,$prams,$data){
        $sql = "update {$table}";
        $value = ' set';
        $i = 1;//用于计算判断是否有多个值
        foreach ($data as $k=>$v){
            if($i>=2){
                $value.=','."{$k}='{$v}'";
            }
            else{
                $value.=" {$k}='{$v}'";
            }
            $i++;
        }
        $sql.=$value;
        $where = ' where';
        $count = 1;
        foreach ($prams as $k=>$v){
            if($count>=2){
                $where.=' and '."{$k}='{$v}'";
            }
            else{
                $where.=" {$k}='{$v}'";
            }
            $count++;
        }
        $sql.=' '.$where;
        echo $sql;
       mysqli_query($this->link,$sql )or die(mysqli_error());
        $status = mysqli_affected_rows($this->link);//返回受影响的条数
        mysqli_close($this->link);
        return $status;
    }

    /**
     * 插入一条数据
     * @param $table string
     * @param $data array
     * @return int
     */
    public function insert($table,$data){
        $sql = "insert into ".$table;
        $field = "(";
        $value = "(";
        $i=0;
        foreach($data as $k=>$v){
            if($i==count($data)-1){
                $field .= "{$k} )";
                $value .= "'{$v}')";
            }
            else{
                $field .= "{$k}, ";
                $value .= "'{$v}',";
            }
            $i++;
        }
        $sql.=$field." value ".$value;
        mysqli_query($this->link,$sql )or die(mysqli_error());
        $status = mysqli_affected_rows($this->link);//返回受影响的条数
        mysqli_close($this->link);
        return $status;
    }

    /** 插入一条数据获取其id值
     * @param $table string
     * @param $data array
     * @return int|string
     */
    public function insertId($table,$data){
        $sql = "insert into ".$table;
        $field = "(";
        $value = "(";
        $i=0;
        foreach($data as $k=>$v){
            if($i==count($data)-1){
                $field .= "{$k} )";
                $value .= "'{$v}')";
            }
            else{
                $field .= "{$k}, ";
                $value .= "'{$v}',";
            }
            $i++;
        }
        $sql.=$field." value ".$value;
        mysqli_query($this->link,$sql )or die(mysqli_error());
        $id = mysqli_insert_id($this->link);//返回受影响的条数
        mysqli_close($this->link);
        return $id;
    }

    /** 数据自增长
     * @param $table string 表名
     * @param $prams array 参数
     * @param $field string 自增字段
     * @param int $value 自增值 默认为1
     * @return bool
     */
    public function setInc($table,$prams,$field,$value=1){
        $sql = "select ".$field." from ".$table;
        $where = ' where';
        $count = 1;
        foreach ($prams as $k=>$v){
            if($count>=2){
                $where.=' and '."{$k}='{$v}'";
            }
            else{
                $where.=" {$k}='{$v}'";
            }
            $count++;
        }
        $sql.=' '.$where.' limit 1';
        $res = mysqli_query($this->link,$sql);
        $res =  mysqli_fetch_assoc($res);
        $status = $this->update($table,$prams,[$field=>$res[$field]+=$value]);
        mysqli_close($this->link);
        if ($status==1){
            return true;
        }
        return false;
    }


    /** 数据自减
     * @param $table string 表名
     * @param $prams array 参数
     * @param $field string 自减字段
     * @param int $value 自减值 默认为1
     * @return bool
     */
    public function setDec($table,$prams,$field,$value=1){
        $sql = "select ".$field." from ".$table;
        $where = ' where';
        $count = 1;
        foreach ($prams as $k=>$v){
            if($count>=2){
                $where.=' and '."{$k}='{$v}'";
            }
            else{
                $where.=" {$k}='{$v}'";
            }
            $count++;
        }
        $sql.=' '.$where.' limit 1';
        $res = mysqli_query($this->link,$sql);
        $res =  mysqli_fetch_assoc($res);
        $vals = $res[$field]-=$value<=0?0:$res[$field]-=$value;
        $status = $this->update($table,$prams,[$field=>$vals]);
        mysqli_close($this->link);
        if ($status==1){
            return true;
        }
        return false;
    }

}
