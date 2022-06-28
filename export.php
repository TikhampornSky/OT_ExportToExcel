<?php 
    require_once './dbConfig.php';
    
    //filter the excel data                 
    function filterData(&$str){ 
        $str = preg_replace("/\t/", "\\t", $str);                   //preg_replace(patterns, replacements, input, limit, count)
        $str = preg_replace("/\r?\n/", "\\n", $str); 
        if(strstr($str, '"')) $str = '"' . str_replace('"', '""', $str) . '"'; 
        //$str = mb_convert_encoding($str, 'UTF-16LE', 'UTF-8');
    }

    function decimalHours($time) {
        $hms = explode(":", $time);
        return ($hms[0] + round($hms[1]/60,3));
    }
    function mapFunction($v)  {
        $v = $v;
        return $v;
    }

    $reasonCODE = array("งานต่อเนื่อง" => "C01", "วันหยุดประเพณี" => "C08", "ปฏิบัติงานแทนเพื่อนร่วมงาน" => "C07", "งานซ่อมบำรุง" => "C10", "งาน PM" => "C13", "งานเร่งด่วน" => "A14", 
                                    "TPM/AM" => "C12", "อื่นๆ" => "I99", "Project" => "C03", "อบรม/สัมนา/ประชุม" => "C05") ;

    //Excel file name for download
    $fileName = "OT_export_data-" . date('Ymd') . ".xls"; 

    //Column names
    $fields = array('Infotype', 'SCG EMP ID', 'Attendance Type', 'Start Date', 'End Date', 'Start Time', 'End Time', 'Attendance Hours', 
    'Attendance reason', 'Cost center', 'Previous day indicator') ;

    //Display column names as first row
    $excelData = implode("\t", array_values($fields)) . "\n"; 

    //Get record from the database ;
    $query = $con->query("SELECT * FROM transaction ORDER BY time_stamp DESC");

    if($query->num_rows > 0) {
        $i = 0 ;
        while ($row = $query->fetch_assoc()) {
            $i++;
            //-------------------------------------------------------------------------------------------------
            $Infotype = 2002 ;                                                                          //*
            //-------------------------------------------------------------------------------------------------
            $SCG_EMP_ID = $row['user_id'];                                                              //*
            //-------------------------------------------------------------------------------------------------
            $value ;
            if ($row['stamp_channel'] == "แตะบัตร" && $row['ot_type'] == "normal") {
                $value = "C010" ;           //แตะบัตรปกติ และ แตะบัตรกะ
            } else if ($row['stamp_channel'] == "แตะบัตร" && $row['ot_type'] == "lunch") {
                $value = "C020" ;           //แตะพักเที่ยง
            } else if ($row['stamp_channel'] == "ปฏิบัติงานภายนอก") {
                 $value = "A002" ;           //นอกสถานที่
            } else {
                $value = "error" ;
            }
            $Attendance_Type = $value;                                                                  //*
            //-------------------------------------------------------------------------------------------------
            $date1 = $row['date']; 
            $date2 = explode("-", $date1);
            if ($date2[2][0] == '0') {
                $date2[2] = $date2[2][1] ;
            }
            if ($date2[1][0] == '0') {
                $date2[1] = $date2[1][1] ;
            }
            $Start_Date = $date2[2] . "/" . $date2[1] . "/" . $date2[0] ;                               //*
            //-------------------------------------------------------------------------------------------------
            $var ; 
            $date = $row['date'];
            $time_start = $row['time_start'] ;
            $time_end = $row['time_end'] ;
            if ($time_start > $time_end) {
                $date1 = str_replace('-', '/', $date);
                $DateEnd = date('Y-m-d',strtotime($date1 . "+1 days"));
                $var = $DateEnd;                                        //บวกวันไปอีก 1
            } else {
                $var = $row['date'] ;
            }

            $date1 = $var; 
            $date2 = explode("-", $date1);
            if ($date2[2][0] == '0') {
                $date2[2] = $date2[2][1] ;
            }
            if ($date2[1][0] == '0') {
                $date2[1] = $date2[1][1] ;
            }
            $End_Date = $date2[2] . "/" . $date2[1] . "/" . $date2[0] ;                             //*
            //-------------------------------------------------------------------------------------------------
            $Start_Time ;
            $time = $row['time_start'] ;
            $time_get = explode(" ", $time)[1] ;
            //echo $time_get ;
            $hour1 = $time_get[0] ;
            $hour2 = $time_get[1] ;
            $minute1 = $time_get[3] ;
            $minute2 = $time_get[4] ;

            if ($hour1 == '0' && $hour2 != '0') {
                $Start_Time =  $hour2 . ":" . $minute1 . $minute2 ;                               //*
            } else {
                $Start_Time =  $hour1 . $hour2 . ":" . $minute1 . $minute2 ;                      //*
            }
            //-------------------------------------------------------------------------------------------------
            $End_Time ;
            $time = $row['time_end'] ;
            $time_get = explode(" ", $time)[1] ;
            //echo $time_get ;
            $hour1 = $time_get[0] ;
            $hour2 = $time_get[1] ;
            $minute1 = $time_get[3] ;
            $minute2 = $time_get[4] ;

            if ($hour1 == '0' && $hour2 != '0') {
                $End_Time = $hour2 . ":" . $minute1 . $minute2 ;                                 //*
            } else {
                $End_Time = $hour1 . $hour2 . ":" . $minute1 . $minute2 ;                        //*
            }
            //---------------------------------------------------------------------------------------------------
            $Attendance_Hours = $row['hour_range'];                                              //*
            //---------------------------------------------------------------------------------------------------
            $Attendance_reason ;
            $reason = $row['request_msg'] ;
            $b = array_map("mapFunction",$reasonCODE);
            if (isset($b[$reason])) {
                $Attendance_reason = $b[$reason] ;                                               //*
            } else {
                $Attendance_reason = "I99" ;            //อื่นๆ                                    //*
            }
            //---------------------------------------------------------------------------------------------------
            $detailR = " " ;
            $detail = $row['request_detail'];
            $reason = $row['request_msg'] ;
            $real_reason = "  " ;
            $arr = explode("-", $reason) ;
            if (isset($b[$reason])) {
                $detailR = $detail ;
                $real_reason = $reason ;                                                            //*
            } else if ($arr[0] == "other"){
                $detailR = $arr[1]. "  " . $detail ;                                                //*   
                $real_reason = "อื่นๆ" ;
            }
            //echo $real_reason ;
            //---------------------------------------------------------------------------------------------------
            $Cost_center = "  " ;                                                                 //*
            //---------------------------------------------------------------------------------------------------
            $mark = " " ;
            $start = $row['time_start'] ;
            $end = $row['time_end'] ;
            $time_start = explode(" ", $start)[1] ;
            $time_end = explode(" ", $end)[1] ;

            $start_num = decimalHours($time_start) ;
            $end_num = decimalHours($time_end) ;

            $midnight = decimalHours('00:00:00') ;
            $morning =  decimalHours('08:00:00') ;
            //echo "X" . $start_num . "  " . $end_num . " --> " . $midnight . "  " .  $morning;
            if ($midnight <= $start_num && $start_num <= $morning && $midnight <= $end_num && $end_num <= $morning) {
                $mark = "X";                                                                    //*
            }
            //---------------------------------------------------------------------------------------------------

            $rowData = array($Infotype, $SCG_EMP_ID, $Attendance_Type, $Start_Date, $End_Date, $Start_Time, $End_Time, $Attendance_Hours, $Attendance_reason, $Cost_center, $mark) ;
            array_walk($rowData, 'filterData');
            $excelData .= implode("\t", array_values($rowData)) . "\n" ;
        }
    } else {
        $excelData .= 'No record found...' . "\n" ;
    }


    //Header for download
    /*
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Type: application/force-download");
    header("Content-Type: application/octet-stream");
    header("Content-Type: application/download"); 
    header("Content-Disposition: attachment; filename=\"$fileName\""); 
    header("Content-Type: application/vnd.ms-excel; charset=UTF-8;"); 
    header('Content-Transfer-Encoding: UTF-8');
    //header('Content-Type: text/csv; charset=UTF-8;');
    */
    /*
    header("Content-Type: application/xls");
	header("Content-Disposition: attachment; filename=export.xls");
	header("Pragma: no-cache");
	header("Expires: 0");
    */
    header("Content-Disposition: attachment; filename=\"$fileName\"");
    header("Content-Type: application/vnd.ms-excel; charset=UTF-8");

    //Render excel data
    echo $excelData;

    exit;
?>