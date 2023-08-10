<?php
class engine extends Model
{
    public $id    = "";
    public $draw   = "";
    public $start  = "";
    public $length = "";
    public $search = "";
    public $order  = "";
    public $dirs    = "";
    public $head   = "";
    public $column   = "";

    public function __construct(){
        parent::__construct();
      
        if(!file_exists('logger')) {
            mkdir('logger');
        }
    }

  

    public function global_generic_report_table($data, $filter_with_table, $columner,$filter = "",$pk)
    {//filter is an EXTRA FILTER
		// $table_name    = $table_name;
        $this->draw    = isset( $data['draw']) ?   $data['draw']  : "";
        $this->start   =  isset($data['start']) ?  $data['start']  : "";
        $this->length  =  isset($data['length']) ?  $data['length']  : "";
        $this->search  = isset( $data['search']['value']) ? $data['search']['value']   : "";
        $this->order   =  isset($data['order'][0]['column']) ? $data['order'][0]['column']   : "";
        $this->dirs    = isset( $data['order'][0]['dir']) ?  $data['order'][0]['dir']  : "";
        $this->column  =  isset( $data['columns'] ) ? $data['columns']  : "";
        $start_date    =  isset($data['start_date']) ? $data['start_date']   : "";
        $end_date      =  isset($data['end_date']) ?  $data['end_date']  : "";
        $order_db            = isset($columner[$this->order]['db']) ? $columner[$this->order]['db'] : "";
		
		$columner     = $columner;
        $fields       = $this->prepare_column($columner);
		
        $sql = "SELECT $fields FROM $filter_with_table  AND ".$this->prepareSearch($columner,$this->search)." order by ".$order_db." ".$this->dirs."  LIMIT ".$this->start.", ".$this->length;
		
		$result   = $this->global_runQuery($sql);
		
		$sql_without_limit = "SELECT {$columner[0]['db']} FROM $filter_with_table AND ".$this->prepareSearch($columner,$this->search)." order by ". $order_db ." ".$this->dirs;
 		
 		//logger
 		file_put_contents("logger/datatable_debug.php", "SQL:  $sql ::||:: WITHOUT LIMIT: $sql_without_limit" . " ::||::" . json_encode($data) );
		
		$output = $this->display_data($result,$columner,$sql_without_limit,$pk,$this->start, 'global');
        
        return json_encode($output);
    }
    
    public function generic_report_table($data, $filter_with_table, $columner,$filter = "", $pk, $groupby_db ="")
    {//filter is an EXTRA FILTER
		// $table_name    = $table_name;
     	$this->draw    = isset( $data['draw']) ?   $data['draw']  : "";
        $this->start   =  isset($data['start']) ?  $data['start']  : "";
        $this->length  =  isset($data['length']) ?  $data['length']  : "";
        $this->search  = isset( $data['search']['value']) ? $data['search']['value']   : "";
        $this->order   =  isset($data['order'][0]['column']) ? $data['order'][0]['column']   : "";

        //SORT IN DESCENDING ORDER ON FIRST DRAW
        if($this->draw == 1) {
	        $this->dirs    = "desc";
	    }
	    else {
	        $this->dirs    = isset( $data['order'][0]['dir']) ?  $data['order'][0]['dir']  : "";
	    }
        $this->column  =  isset( $data['columns'] ) ? $data['columns']  : "";
        $start_date    =  isset($data['start_date']) ? $data['start_date']   : "";
        $end_date      =  isset($data['end_date']) ?  $data['end_date']  : "";
        $order_db            = isset($columner[$this->order]['db']) ? $columner[$this->order]['db'] : "";
		
		$columner     = $columner;
        $fields       = $this->prepare_column($columner);
		
        $sql = "SELECT $fields FROM $filter_with_table  AND ".$this->prepareSearch($columner,$this->search). " " .$groupby_db ." order by ".$order_db." ".$this->dirs."  LIMIT ".$this->start.", ".$this->length;
		
		$result   = $this->runQuery($sql);
		
		$sql_without_limit = "SELECT {$columner[0]['db']} FROM $filter_with_table AND ".$this->prepareSearch($columner,$this->search). " " .$groupby_db ." order by ".$order_db." ".$this->dirs;
 		
 		//logger
 		file_put_contents("logger/datatable_debug.php", "SQL:  $sql ::||:: WITHOUT LIMIT: $sql_without_limit" );
 		//
		//file_put_contents("logger/datatable_debug.php", json_encode($data) );

		$output = $this->display_data($result,$columner,$sql_without_limit,$pk,$this->start);
        
        return json_encode($output);
    }

    public function generic_select_report_table($data, $select, $filter_with_table, $columner,$filter = "",$pk, $groupby_db = "")
    {//filter is an EXTRA FILTER
		// $table_name    = $table_name;
     	$this->draw    = isset( $data['draw']) ?   $data['draw']  : "";
        $this->start   =  isset($data['start']) ?  $data['start']  : "";
        $this->length  =  isset($data['length']) ?  $data['length']  : "";
        $this->search  = isset( $data['search']['value']) ? $data['search']['value']   : "";
        $this->order   =  isset($data['order'][0]['column']) ? $data['order'][0]['column']   : "";

        //SORT IN DESCENDING ORDER ON FIRST DRAW
        if($this->draw == 1) {
	        $this->dirs    = "desc";
	    }
	    else {
	        $this->dirs    = isset( $data['order'][0]['dir']) ?  $data['order'][0]['dir']  : "";
	    }
        $this->column  =  isset( $data['columns'] ) ? $data['columns']  : "";
        $start_date    =  isset($data['start_date']) ? $data['start_date']   : "";
        $end_date      =  isset($data['end_date']) ?  $data['end_date']  : "";
        $order_db            = isset($columner[$this->order]['db']) ? $columner[$this->order]['db'] : "";
		
		$columner     = $columner;
        $fields       = $this->prepare_column($columner);
		
        $sql = "SELECT $select FROM $filter_with_table  AND ".$this->prepareSearch($columner,$this->search). " " .$groupby_db ." order by ".$order_db." ".$this->dirs."  LIMIT ".$this->start.", ".$this->length;
		
		$result   = $this->runQuery($sql);
		
		$sql_without_limit = "SELECT $pk FROM $filter_with_table AND ".$this->prepareSearch($columner,$this->search). " " .$groupby_db . " order by ".$order_db." ".$this->dirs;
 		
 		//logger
 		file_put_contents("logger/datatable_debug.php", "SQL:  $sql ::||:: WITHOUT LIMIT: $sql_without_limit" );
 		//
		//file_put_contents("logger/datatable_debug.php", json_encode($data) );

		$output = $this->display_data($result,$columner,$sql_without_limit,$pk,$this->start);
        
        return json_encode($output);
    }

	public function getitemlabel($tablename,$table_col,$table_val,$ret_val) {
	   
	    $label = "";
	    $table_filter = " where ".$table_col."='".$table_val."' LIMIT 1";

	    $query = "select ".$ret_val." from ".$tablename.$table_filter;
	      //echo $query;
	    $result = $this->myconn->query($query);
	    
	    if($result->num_rows){
	        $row =  $result->fetch_assoc();
	        $label = $row[$ret_val];
	    }
	    return $label;
    }


	public function generic_table($data,$table_name,$columner,$filter = "",$pk)
    {
		$table_name    = $table_name;
        $this->draw    = $data['draw'];
        $this->start   = $data['start'];
        $this->length  = $data['length'];
        $this->search  = $data['search']['value'];
        $this->order   = $data['order'][0]['column'];
        $this->dirs    = $data['order'][0]['dir'];
        $this->column  = $data['columns'];
        $start_date    = isset($data['start_date']) ? $data['start_date'] : '';
        $end_date      = isset($data['end_date']) ? $data['end_date'] : '';
	
		$columner     = $columner;
        $fields       = $this->prepare_column($columner);
		
        $sql = "SELECT $fields FROM $table_name WHERE ".$this->prepareSearch($columner,$this->search).$this->date_filter($start_date,$end_date).$filter." order by ".$columner[$this->order]['db']." ".$this->dirs."  LIMIT ".$this->start.", ".$this->length;
		
		$result   = $this->runQuery($sql);
		
		$sql_without_limit = "SELECT {$columner[0]['db']} FROM $table_name WHERE ".$this->prepareSearch($columner,$this->search).$this->date_filter($start_date,$end_date).$filter." order by ".$columner[$this->order]['db']." ".$this->dirs;
        
		
		$output = $this->display_data($result,$columner,$sql_without_limit,$pk,$this->start);
		file_put_contents("logger/datatable_debug.php", "SQL:  $sql ::||:: WITHOUT LIMIT: $sql_without_limit" . " ::||::" . json_encode($data) );
     
        return json_encode($output);
    }
	
	public function generic_multi_table($data,$table_name,$columner,$filter = "",$pk,$join_arr,$join_type = "JOIN")
    {
		$table_name    = $table_name;
        $this->draw    = $data['draw'];
        $this->start   = $data['start'];
        $this->length  = $data['length'];
        $this->search  = $data['search']['value'];
        $this->order   = $data['order'][0]['column'];
        $this->dirs    = $data['order'][0]['dir'];
        $this->column  = $data['columns'];
        $start_date    = isset($data['start_date']) ? $data['start_date'] : '';
        $end_date      = isset($data['end_date']) ? $data['end_date'] : '';
	
		$join          = $this->joinTables($join_arr);
		$columner     = $columner;
        $fields       = $this->prepare_column($columner);
		
        $sql = "SELECT $fields FROM $table_name  $join_type ".$join." WHERE ".$this->prepareSearch($columner,$this->search).$this->date_filter($start_date,$end_date).$filter." order by ".$columner[$this->order]['db']." ".$this->dirs."  LIMIT ".$this->start.", ".$this->length;
		

		$result   = $this->runQuery($sql);
		
		$sql_without_limit = "SELECT {$columner[0]['db']} FROM $table_name $join_type ".$join." WHERE ".$this->prepareSearch($columner,$this->search).$this->date_filter($start_date,$end_date).$filter." order by ".$columner[$this->order]['db']." ".$this->dirs;
        
        file_put_contents("logger/datatable_debug.php", "SQL:  $sql ::||:: WITHOUT LIMIT: $sql_without_limit" . " ::||::" . json_encode($data) );
		
		$output = $this->display_data($result,$columner,$sql_without_limit,$pk,$this->start);
        
        return json_encode($output);
    }
	
	
	public function joinTables($arr)
	{
		$str = "";
		foreach($arr as $rw)
		{
			
			foreach($rw as $key => $val)
			{
				 $str .= $key." ON ";
				foreach($val as $k)
				{
					$str .= $k."=";
				}
			}
			
		}
		
		$str =  rtrim($str,"=");
		return $str;
	}
	
public function display_data($result,$columner,$sql_without_limit,$pk,$start, $connection = "")
{
	$pagination = ($connection == "") ? $this->runQuery($sql_without_limit, false)  : $this->global_runQuery($sql_without_limit, false)  ;

	$count = is_array($result) ? count($result) : 0;
	$big_data   = array("draw"=>$this->draw,"recordsFiltered"=>$pagination,"recordsTotal"=>$count);

	if($count > 0)
	{
		
		$rw        = array();
		$serial_no = $start;
		foreach($result as $row)
		{
			$serial_no++;
			$cnt      = 0;
			foreach($columner as $inner_rw)
			{
				if(isset($columner[$cnt]['db']))
				{
					$data  = $row[$columner[$cnt]['db']];
					$index = $columner[$cnt]['dt'];
				}

				// added by AJ
				$pk_breakdown = explode('.', $pk);
				$primary_key = (sizeof($pk_breakdown) > 1) ? $pk_breakdown[1] : $pk;
				$id = $columner[$cnt]['db']."-".$row[$primary_key];
				//end
				// $id = $columner[$cnt]['db']."-".$row[$pk];
				
				if($cnt == 0)
				{
					$data = $serial_no;
				}
				else
				{
					// using the callback function :: formatter
					$data = (isset($columner[$cnt]['formatter']))?$columner[$cnt]['formatter']($data,$row):$data;

					$data = (isset($columner[$cnt]['edit']))?$this->doHtml($data,$columner[$cnt],$id,$row):$data;
				}
				

				$rw[$index] = array($data);
				$cnt++;
			}
			$big_data['data'][] = $rw;
		}
	}else
	{

		$big_data['data'] = array();
	}

	return $big_data;
}
	public function doHtml($data,$column_data,$id,$row)
	{
		$type = $column_data['edit'];
		$r    = "<div><span>{$data}</span>";
		if($type == 'text' || $type == 'date' || $type == 'tel' || $type == 'number' || $type == 'email')
		{
			$r .= "<input type='hidden' name='{$column_data['db']}' value='{$id}'  />
			<input style='display:none; border:1px solid red' autocomplete='off' type='{$type}' name='{$column_data['db']}'  />";
		}
		elseif($type == 'select')
		{
			$r .= "<input type='hidden' name='{$column_data['db']}' value='{$id}'  />
			<select class='form-control' name='{$column_data['db']}'  style='display:none; border:1px solid red' >
			{$column_data['options']($row)} 
			</select>";
		}
		
		$r .= "</div>";
		return $r;
	}
	
	public function prepare_column($col)
	{
		$coll = "";
		foreach($col as $rr)
		{
			$coll .= $rr['db'].",";
		}
		return substr($coll,0,-1);
	}
	
	public function date_filter($start,$end,$column = "created")
	{
		$date = "";
		if($start != "" && $end != "")
		{
			$date = " and ($column between '$start 00:00' and '$end 23:59')";
		}
		return $date;
	}
	public function prepareSearch($array_search,$search)
    {
        $len = count($array_search);
        $columns = "";
        if($search != "")
        {
            for($x=0; $x<$len; $x++)
            {
                $columns .= $array_search[$x]['db']." LIKE '%".$search."%'  OR ";
            }
        }
		
		for($x=0; $x<$len; $x++)
		{
			if( isset($array_search[$x]['search']['value']) && $array_search[$x]['search']['value'] != "")
			{
				$columns .= $array_search[$x]['name']." LIKE '%".$array_search[$x]['search']['value']."%' AND ";
			}
		}
		$columns = substr($columns,0,-4);
        
        
        return $columns == ""?" 1 = 1 ":"(".$columns.") AND 1 = 1 ";
//        return $columns == ""?" 1 = 1 ":$columns." AND 1 = 1 ";
    }
    
    public function runQuery($sql,$object = true)
	{
		// if you are performig a UPDATE query; you will need to set $object == false
		$result = $this->myconn->query($sql);
		$count  = $this->myconn->affected_rows;

		if($object)
		{
			if($count > 0)
			{
				$data = array();
				while($row = $result->fetch_assoc())
				{
					$data[] = $row;
				}
				return $data;
			}else
			{
				return null;
			}
		}else
		{
			file_put_contents('logger/runQuery.php', $sql.PHP_EOL , FILE_APPEND | LOCK_EX);
			return $count;
		}
	}

	public function global_runQuery($sql,$object = true)
	{
		// if you are performig a UPDATE query; you will need to set $object == false
		$result = $this->global_conx->query($sql);
		$count  = $this->global_conx->affected_rows;
		if($object)
		{
			if($count > 0)
			{
				$data = array();
				while($row = $result->fetch_assoc())
				{
					$data[] = $row;
				}
				return $data;
			}else
			{
				return null;
			}
		}else
		{
			return $count;
		}
	}
    
}



