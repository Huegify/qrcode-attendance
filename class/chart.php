<?php

class Chart extends Model
{
    public function Monthly_Attendance($data)
    {   
        $filter = "1=1 ";

        if(!isset($data['from']) || $data['from'] == null){
            $filter .= " AND MONTH(a.day_taken) IN (".date('m').")";
            $filter .= " AND YEAR(a.day_taken) IN (".date('Y').")";
        }else{
            $filter .= " AND a.week_taken IN ('".$data['from']."')";
        }

        $query = "SELECT DAY(a.day_taken) AS `day`,MONTH(a.day_taken) AS `month`,YEAR(a.day_taken) AS `year`,
            count(a.staff_id) AS daily_attendance FROM attendance_log a INNER JOIN staff AS s ON s.staff_id = a.staff_id
            WHERE $filter GROUP BY DAY(a.day_taken) ";

        $stmt = $this->runQuery($query);

        // var_dump($stmt);

        return json_encode($stmt);        
    }

    public function Weekly_Attendance($data)
    {
        $filter = "1=1 ";

        if(!isset($data['from']) || $data['from'] == null){
            $filter .= " AND MONTH(a.day_taken) IN (".date('m').")";
            $filter .= " AND YEAR(a.day_taken) IN (".date('Y').")";
        }else{
            $filter .= " AND a.week_taken IN ('".$data['from']."')";
        }

        $query = "SELECT DAY(a.day_taken) AS `day`,MONTH(a.day_taken) AS `month`,YEAR(a.day_taken) AS `year`,
            count(a.staff_id) AS daily_attendance FROM attendance_log a INNER JOIN staff AS s ON s.staff_id = a.staff_id
            WHERE $filter GROUP BY DAY(a.day_taken) ";

        $stmt = $this->runQuery($query);

        return json_encode($stmt);
        
    }
    
}
