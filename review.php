<?php
    require_once './dbConfig.php';
    
    $dateStart = $_GET['dateStart'] ;
    $dateEnd = $_GET['dateEnd'] ;
    $type = $_GET['type'] ;

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

    if ($type == "only") {
        $query = $con->query("SELECT * FROM transaction WHERE `date` >= '$dateStart' AND `date` <= '$dateEnd' AND `IsReport` = 0 
                            AND (`approve_status` = 'approve' OR `approve_status` = 'edit') ORDER BY time_stamp DESC");
    } else {
        $query = $con->query("SELECT * FROM transaction WHERE `date` >= '$dateStart' AND `date` <= '$dateEnd' 
                            AND (`approve_status` = 'approve' OR `approve_status` = 'edit') ORDER BY time_stamp DESC");
    }

    if (isset($_POST["save"])) {
        header("Location: export.php?dateStart=$dateStart&dateEnd=$dateEnd&type=$type");
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>        <!-- Link to jquery-->
    <script>
        $(document).ready(function(){
            
        });
    </script>
    <title> OT Data </title>
    <meta charset="utf-8">
    <link rel="stylesheet" href="./style.css">
</head>

<body>
    <div>
        <h1> OT Data </h1>
    </div>
    <div class="container">
        <div class="grid-container">
            <div class="Abouttime">
                <p> ระบุช่วงเวลาที่ต้องการ </p>
                <div class="grid-container">
                    <div>
                        <lable> วันเริ่มต้น  : </label>
                        <input type="date" id="dateStart" name="dateStart" placeholder="dd/mm/YYYY" class= "DateInput" style="-webkit-appearance: none; -moz-appearance: none; background-color: #d6d6d6;" 
                            value="<?php echo $dateStart ;?>" readonly />
                    </div>
                    <div>
                        <lable> วันสิ้นสุด   : </label>
                        <input type="date" id="dateEnd" name="dateEnd" placeholder="dd/mm/YYYY" class= "DateInput" style="-webkit-appearance: none; -moz-appearance: none; background-color: #d6d6d6;" 
                            value="<?php echo $dateEnd ; ?>" readonly />
                    </div>
                </div>
            </div>
            <div class="Abouttype">
                <p> รูปแบบที่ต้องการ </p>
                <div class="grid-container">
                    <div class="option-radio">
                        <input name="yesno" class="DateInput" style="background-color: #d6d6d6;" readonly
                            value="<?php 
                                if ($type == "only") {
                                    echo "เอาเฉพาะข้อมูลที่ไม่เคย Export" ;
                                } else {
                                    echo "เอาข้อมูลทั้งหมด" ;
                                }
                            ?>"/> 
                    </div>
                </div>
            </div>
        </div>

        <div class="grid-container2">    
            <form method="POST">
                <button class="btn-export" title="Click to export" name="save" type="submit"> Export </button>
            </form>
            <button class="btn-back" name = "back" onclick="goBack()"> Edit </button>
            <script>
                function goBack() {
                    window.history.back();
                }   
            </script>
        </div>

        <table class="tabledata" >
            <thead>
            <tr> 
                <!-- <th> transaction ID </th> -->               <!-- For Debug -->
                <th> Infotype </th>
                <th> SCG EMP ID </th>
                <th> Attendance Type </th>
                <th> Start Date </th>
                <th> End Date </th>
                <th> Start Time </th>
                <th> End Time </th>
                <th> Attendance Hours </th>
                <th> Attendance reason </th>
                <th> Cost center </th>
                <th> Previous day indicator </th>
            </tr>
            </thead>

            <tbody>
                <?php
                if($query->num_rows > 0) {
                    $i = 0 ;
                    while ($row = $query->fetch_assoc()) {
                        $i++;
                ?>

                <tr>
                    <!-- <td> <?php echo $row['transaction_id']; ?> </td> -->          <!-- For Debug -->
                    <td> 2002 </td>
                    <td> <?php echo $row['user_id']; ?> </td>
                    <td> <?php 
                            $value ;
                            if ($row['stamp_channel'] == "แตะบัตร" && $row['ot_type'] == "normal") {
                                $value = "C010" ;           //แตะบัตรปกติ และ แตะบัตรกะ
                            } else if ($row['stamp_channel'] == "แตะบัตร" && $row['ot_type'] == "lunch") {
                                $value = "C020" ;           //แตะพักเที่ยง
                            } else if ($row['stamp_channel'] == "ปฏิบัติงานภายนอก") {
                                $value = "A002" ;           //นอกสถานที่
                            } else {
                                $value = "undefined" ;
                            }
                            echo $value ;
                        ?> 
                    </td>
                    <td> <?php 
                            $date1 = $row['date']; 
                            $date2 = explode("-", $date1);
                            if ($date2[2][0] == '0') {
                                $date2[2] = $date2[2][1] ;
                            }
                            if ($date2[1][0] == '0') {
                                $date2[1] = $date2[1][1] ;
                            }
                            echo $date2[2] . "/" . $date2[1] . "/" . $date2[0] ;
                        ?> 
                    </td>
                    <td> <?php 
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
                            echo $date2[2] . "/" . $date2[1] . "/" . $date2[0] ;
                            ?> 
                    </td>
                    <td> <?php 
                            $time = $row['time_start'] ;
                            $time_get = explode(" ", $time)[1] ;
                            //echo $time_get ;
                            $hour1 = $time_get[0] ;
                            $hour2 = $time_get[1] ;
                            $minute1 = $time_get[3] ;
                            $minute2 = $time_get[4] ;

                            if ($hour1 == '0' && $hour2 != '0') {
                                echo $hour2 . ":" . $minute1 . $minute2 ;
                            } else {
                                echo $hour1 . $hour2 . ":" . $minute1 . $minute2 ;
                            }
                        ?> 
                    </td>
                    <td> <?php 
                            $time = $row['time_end'] ;
                            $time_get = explode(" ", $time)[1] ;
                            //echo $time_get ;
                            $hour1 = $time_get[0] ;
                            $hour2 = $time_get[1] ;
                            $minute1 = $time_get[3] ;
                            $minute2 = $time_get[4] ;

                            if ($hour1 == '0' && $hour2 != '0') {
                                echo $hour2 . ":" . $minute1 . $minute2 ;
                            } else {
                                echo $hour1 . $hour2 . ":" . $minute1 . $minute2 ;
                            }
                        ?> 
                    </td>
                    <td> <?php echo $row['hour_range']; ?> </td>
                    <td> <?php
                            $reason = $row['request_msg'] ;
                            $b = array_map("mapFunction",$reasonCODE);
                            if (isset($b[$reason])) {
                                echo $b[$reason] ;
                            } else {
                                echo "I99" ;            //อื่นๆ
                            }
                        ?>
                    </td>
                    <td>   </td>
                    <td> <?php
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
                                echo "X";
                            } 
                        ?> 
                    </td>
                </tr>
                
                <?php
                    }
                }else {
                ?> 
                    <tr><td colspan="11"> No data found... </td><tr>
                <?php
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>