<?php

include '../db.php';


$result=mysqli_query($conn,
"SELECT school_code FROM school_settings LIMIT 1");


$school=mysqli_fetch_assoc($result);

$school_code=$school['school_code'];


$class_id=$_GET['class_id'];


// Generate Registration Number

$class=mysqli_fetch_assoc(mysqli_query(
$conn,
"SELECT class_name FROM class WHERE class_id='$class_id'"
));


$class_name=strtolower($class['class_name']);


if(strpos($class_name,'form one')!==false){

    $prefix="11";

}elseif(strpos($class_name,'form two')!==false){

    $prefix="12";

}elseif(strpos($class_name,'form three')!==false){

    $prefix="13";

}else{

    $prefix="14";

}


$year=date("y");


$query=mysqli_query($conn,

"SELECT registration_no 
FROM student
WHERE registration_no LIKE '$prefix/$school_code/%/$year'
ORDER BY student_id DESC
LIMIT 1");


if(mysqli_num_rows($query)>0){

$row=mysqli_fetch_assoc($query);

$parts=explode("/",$row['registration_no']);

$num=intval($parts[2])+1;


}else{

$num=1;

}


echo $prefix."/".$school_code."/".str_pad($num,4,"0",STR_PAD_LEFT)."/".$year;


?>