<?php

include '../db.php';


// Get school code
$school_query = mysqli_query($conn,
"SELECT school_code FROM school_settings LIMIT 1");


if(mysqli_num_rows($school_query)==0){

    die("School code not configured");

}


$school = mysqli_fetch_assoc($school_query);

$school_code = $school['school_code'];



// ===============================
// Generate Admission Number
// ===============================

function generateAdmissionNo($conn)
{

    $year=date("Y");


    $query=mysqli_query($conn,

    "SELECT admission_no 
     FROM student
     WHERE admission_no LIKE 'ADM/$year/%'
     ORDER BY student_id DESC
     LIMIT 1");


    if(mysqli_num_rows($query)>0)
    {

        $row=mysqli_fetch_assoc($query);

        $parts=explode("/",$row['admission_no']);

        $number=(int)$parts[2]+1;

    }
    else
    {

        $number=1;

    }



    do{

        $admission="ADM/".$year."/".str_pad($number,3,"0",STR_PAD_LEFT);


        $check=mysqli_query($conn,

        "SELECT student_id 
         FROM student
         WHERE admission_no='$admission'");


        $number++;


    }while(mysqli_num_rows($check)>0);



    return $admission;

}





// ===============================
// Generate Registration Number
// ===============================

function generateRegistrationNo($conn,$class_id,$school_code)
{


    $class=mysqli_fetch_assoc(mysqli_query($conn,

    "SELECT class_name
     FROM class
     WHERE class_id='$class_id'"));



    $class_name=strtolower($class['class_name']);



    if(strpos($class_name,'form one')!==false)
    {

        $prefix="11";

    }
    elseif(strpos($class_name,'form two')!==false)
    {

        $prefix="12";

    }
    elseif(strpos($class_name,'form three')!==false)
    {

        $prefix="13";

    }
    else
    {

        $prefix="14";

    }



    $year=date("y");



    // get last number
    $query=mysqli_query($conn,

    "SELECT registration_no
     FROM student
     WHERE registration_no LIKE '$prefix/$school_code/%/$year'
     ORDER BY student_id DESC
     LIMIT 1");



    if(mysqli_num_rows($query)>0)
    {

        $row=mysqli_fetch_assoc($query);

        $parts=explode("/",$row['registration_no']);

        $number=(int)$parts[2]+1;

    }
    else
    {

        $number=1;

    }




    do{


        $registration=

        $prefix."/".$school_code."/".

        str_pad($number,4,"0",STR_PAD_LEFT).

        "/".$year;



        $check=mysqli_query($conn,

        "SELECT student_id
         FROM student
         WHERE registration_no='$registration'");



        $number++;



    }while(mysqli_num_rows($check)>0);



    return $registration;


}





// ===============================
// Update Students
// ===============================


$result=mysqli_query($conn,

"SELECT student_id,class_id
 FROM student
 WHERE admission_no IS NULL
 OR admission_no=''
 OR registration_no IS NULL
 OR registration_no=''
 ORDER BY student_id");



$count=0;



while($student=mysqli_fetch_assoc($result))
{


    $student_id=$student['student_id'];

    $class_id=$student['class_id'];



    $admission=generateAdmissionNo($conn);


    $registration=generateRegistrationNo(
        $conn,
        $class_id,
        $school_code
    );



    $update=mysqli_query($conn,


    "UPDATE student SET

    admission_no='$admission',

    registration_no='$registration'

    WHERE student_id='$student_id'");



    if(!$update)
    {

        echo mysqli_error($conn);
        exit();

    }



    echo "Updated Student ID: ".$student_id.
    " | ".
    $admission.
    " | ".
    $registration.
    "<br>";



    $count++;

}



echo "<hr>";

echo "<h3>$count Students Updated Successfully</h3>";

?>