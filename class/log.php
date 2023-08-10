<?php

class Log extends Model
{
    public function getCurrentData($table_name,$table_field,$table_id)
    {
        $sql = "SELECT * FROM $table_name WHERE  $table_field = '$table_id' LIMIT 1";
        $result = $this->runQuery($sql);
        return $result[0];
    }

    public function logData($current_data,$insert_data,Array $option, Array $exempt = [])
    {
        $result       = $this->Insert("log_table",array("username"=>$_SESSION['username_sess'],"table_name"=>$option['table_name'],"table_id"=>$option['table_id'],"table_alias"=>$option['table_alias'],"created"=>date("Y-m-d h:i:s")),[]);
        $insert_id    = $this->lastInsertId();
        // $insert_id    = $this->getInsert_id($this->conn);
        if($result > 0) {
            $difference = array_diff($insert_data,$current_data);
            foreach($difference as $key=>$value)
            {
                if(!in_array($key,$exempt))
                {
                    $this->Insert("log_details",array("log_id"=>$insert_id,"field_name"=>$key,"previous_data"=>$current_data[$key],"current_data"=>$value,"field_alias"=>""),[]);
                }
            }
        }
    }

    public function lastInsertId()
    {
        return $this->runQuery("SELECT max(id) AS id FROM log_table")[0]['id'];
    }
}