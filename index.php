<?php
    require_once './dbConfig.php';
    //include("dbConfig.php");
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

    $query = $con->query("SELECT * FROM transaction WHERE `approve_status` = 'approve' ORDER BY time_stamp DESC ");

    if (isset($_POST["save"])) {
        $dateStart = $_POST['dateStart'] ;
        $dateEnd = $_POST['dateEnd'] ;
        header("Location: export.php?dateStart=$dateStart&dateEnd=$dateEnd");
    }

    if (isset($_POST["review"])) {
        $dateStart = $_POST['dateStart'] ;
        $dateEnd = $_POST['dateEnd'] ;
        header("Location: review.php?dateStart=$dateStart&dateEnd=$dateEnd");
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title> Export OT Data </title>
    <meta charset="utf-8">
    <link rel="stylesheet" href="./style.css">
</head>

<body>
    <div>
        <h1> Export OT Data </h1>
    </div>

    <form name="myForm" class="forms-my" onsubmit = "return clickExport()" method="POST">
        <div class="grid-container">
            <div class="Abouttime">
                <p> ระบุช่วงเวลาที่ต้องการ </p>
                <div class="grid-container">
                    <div>
                        <lable> วันเริ่มต้น  : </label>
                        <input type="date" id="dateStart" name="dateStart" placeholder="dd/mm/YYYY" class= "DateInput" style="-webkit-appearance: none; -moz-appearance: none;" 
                        value="<?php 
                            $date = date('Y-m-d'); 
                            $date1 = str_replace('-', '/', $date);
                            $DateEnd = date('Y-m-d',strtotime($date1 . "-7 days"));
                            $DatetimeEndInput = strtotime($DateEnd);
                            $DatetimeEndInput = date('Y-m-d',$DatetimeEndInput);
                            echo $DatetimeEndInput;
                        ?>" />
                    </div>
                    <div>
                        <lable> วันสิ้นสุด   : </label>
                        <input type="date" id="dateEnd" name="dateEnd" placeholder="dd/mm/YYYY" class= "DateInput" style="-webkit-appearance: none; -moz-appearance: none;" 
                        value="<?php echo date('Y-m-d'); ?>" />
                    </div>
                </div>
            </div>
            <div class="Abouttype">
                <p> รูปแบบที่ต้องการ </p>
                <div class="grid-container">
                    <div class="option-radio">
                        <input type="radio" name="yesno" value="only" class="typeData"> เอาเฉพาะข้อมูลที่ไม่เคย Export
                    </div>
                    <div class="option-radio">
                        <input type="radio" name="yesno" value="all" class="typeData"> เอาข้อมูลทั้งหมด
                    </div>
                    <p style="display: none; color:red;" id="warn-type"> กรุณาเลือกรูปแบบที่ท่านต้องการ! </p>
                </div>
            </div>
        </div>
        <button class="btn-export" title="Click to export" name="save" type="submit"> Export </button>
        <button class="btn-export" title="Click to review" name="review" type="submit"> Review </button>
    </form>

    <div class="container">
        <script>
            function clickExport() {
                var type = document.querySelector('input[name="yesno"]:checked') ;
                var check1 = false;
                if (type == null) {
                    //console.log("undefined") ;
                    document.getElementById("warn-type").style.display = "inline-block";
                    check1 = false ;
                } else {
                    //console.log(type.value) ;
                    document.getElementById("warn-type").style.display = "none";
                    check1 = true ;
                }
                
                var dateStart = document.getElementById("dateStart").value ;
                var dateEnd = document.getElementById("dateEnd").value ;
                console.log(dateStart) ;
                console.log(dateEnd) ;
                return check1 ;
            }
        </script>
    </div>
</body>
</html>
