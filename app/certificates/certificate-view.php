<?php


ini_set('display_errors',1);
require '../../includes/db-config.php';

session_start();

//$id=$_GET['student_id'];
$id = intval($_POST['student_id']);

//University_ID = 48 AND
$students_temps_result = $conn->query("SELECT Students.*,Sub_Courses.Min_Duration as total_duration,Modes.Name as mode,Sub_Courses.Name as course,Courses.Name as program_Type FROM Students left join Sub_Courses on Sub_Courses.ID=Students.Sub_Course_ID left join Modes on Students.University_ID=Modes.University_ID left join Courses on Students.Course_ID=Courses.ID  WHERE Students.ID = '".$id."' ");    //AND Duration = $sem 
if($students_temps_result->num_rows > 0){
    $students_temps = $students_temps_result->fetch_assoc();
}else{
    $students_temps['duration'] = '';
}

$durMonthYear="";
if($students_temps['mode']=="Monthly"){
    $durMonthYear="Months";
}elseif($students_temps['mode']=="Sem"){
    $durMonthYear="Semesters";
}else{
    $durMonthYear="Years";
}
$total_duration=0;
if($students_temps['University_ID']==47 ){//|| $students_temps['mode']=="Sem"
    $total_duration=0; 
    if (str_contains($students_temps['total_duration'], '"')) { 
        $a=str_replace('"', '', $students_temps['total_duration']);
        $total_duration=(int)$a;
    }else{
        $total_duration=(int)$students_temps['total_duration'];
    }
}else{
    if (str_contains($students_temps['Duration'], '/')) { 
        $a=explode("/",$students_temps['Duration']);
        $total_duration=(int)$a[0];
    }else{
        $total_duration=(int)$students_temps['Duration'];
    }
}

$courseCategory="";
if (str_contains($students_temps['Course_Category'], '_')) { 
    $a=str_replace('_',' ', $students_temps['Course_Category']);
    $courseCategory=ucfirst($a);
}else{
    $courseCategory=ucfirst($students_temps['Course_Category']);
}

if(str_contains($students_temps['Course_Category'], 'advance_diploma')){
    $certificate="Adv. Certification Skill Diploma";  
}else{
    $certificate=" Certified Skill Diploma";  

}
$hours=0;

if($total_duration==3 && $durMonthYear=="Months"){
    $certificate="Certification Course";
    $hours=160;
}elseif($total_duration==6 && $durMonthYear=="Months"){
    $certificate="Certified Skill Diploma";   
    $hours=320;
}elseif($total_duration==11 && $durMonthYear=="Months"){
    $hours=960;
    
    

}elseif ($total_duration==6 && $durMonthYear=="Semester"){
    $hours='NA';
}

$a=implode(' ', array_slice(explode(' ', $students_temps['course']), 0, 6));
$b=implode(' ', array_slice(explode(' ', $students_temps['course']), 6));

$name=$students_temps['First_Name']." ".$students_temps['Middle_Name']." ".$students_temps['Last_Name'];
$f_name=$students_temps['Father_Name'];
$father_name = "Son/Daughter of Mr. $f_name";
$Enrol_no=$students_temps['Enrollment_No'];
use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfReader;
    
ob_end_clean(); 

require_once('../../extras/TCPDF/tcpdf.php');
require_once('../../extras/vendor/setasign/fpdf/fpdf.php');
require_once('../../extras/vendor/setasign/fpdi/src/autoload.php');

$pdf = new FPDI('L', 'mm', array(299, 210));



$pdf->AddPage();

$pdf->AddFont('GreatVibes-Regular','','GreatVibes-Regular.php');
$pdf->AddFont('OpenSans-Regular','','OpenSans-VariableFont_wdth,wght.php');

// $pdf->image('bg.png', 70, 109.5, 150, 20);
// $pdf->image('bg.png', 157.5, 138, 50, 10);
// $pdf->image('bg.png', 57.5, 145, 200, 10);
// $pdf->image('bg.png', 97.6, 163, 30, 10);
// $pdf->image('bg.png', 173, 163, 70, 10);

$name = array_filter(explode(" ", $name));
$htmlName = "";
$counter = 1;
foreach($name as $n){
    if($counter>1)
        $htmlName .= ' ';
    $firstLetter = substr($n, 0, 1);
    $restLetters = substr($n, 1);

    $htmlName .= '<font style="font-size:12px">'.strtoupper($firstLetter).'</font>'.strtolower($restLetters);
    $counter++;
}
// Create new TCPDF object
$tcpdf = new TCPDF('L', 'mm', array(299, 210));
$tcpdf->AddPage();
$tcpdf->SetPrintHeader(false);
$tcpdf->SetPrintFooter(false);
$tcpdf->setFont($font_family='montserratb',$font_variant='',$font_size=12);
$tcpdf->SetTextColor(6,64,101);
$tcpdf->writeHTMLCell(0, 0, 3.8, 101.8, $htmlName." bearing Enrollment No ".$Enrol_no, 0, 0, false, true, 'C');
// $tcpdf->SetTextColor(0,0,0);
// $tcpdf->setFont($font_family='montserratb',$font_variant='',$font_size=13);
// $tcpdf->writeHTMLCell(250, 0,140.5, 100, $father_name, 0, 0, false, true, 'L');
$tcpdf->SetTextColor(0,0,0);
$tcpdf->setFont($font_family='montserratb',$font_variant='',$font_size=10);
$tcpdf->writeHTMLCell(250, 0, 156.5, 116, $certificate, 0, 0, false, true, 'L');
$tcpdf->setFont($font_family='worksanssemib',$font_variant='',$font_size=10);
$tcpdf->writeHTMLCell(0, 0, 0, 125, strtoupper($students_temps['course']), 0, 0, false, true, 'C');
$tcpdf->setFont($font_family='montserratb',$font_variant='',$font_size=10);
$tcpdf->writeHTMLCell(40, 7, 102, 144, 'AY  2023 - 24', 0, 0, false, true, 'L');
$tcpdf->writeHTMLCell(100, 7, 200, 144,"$total_duration"." ".$durMonthYear." /". $hours." hours ", 0, 0, false, true, 'L');
$tcpdf->writeHTMLCell(100, 7,42.2, 180," 02-05-2024 ", 0, 0, false, true, 'L');
// Generate a temporary file to store the TCPDF output
$temp_pdf_path = tempnam(sys_get_temp_dir(), 'tcpdf_');
$tcpdf->Output($temp_pdf_path, 'F');

// Import the generated TCPDF content into the FPDI document
$pdf->setSourceFile($temp_pdf_path);
$tpl = $pdf->importPage(1);
$pdf->useTemplate($tpl, 0, 0);

// Clean up the temporary file
unlink($temp_pdf_path);


//save file
$filename=$students_temps['Unique_ID']."_".time().".pdf";
// return the generated output 
$pdf->Output('../../uploads/certificates/'.$filename,"F");
$student_id=$id;
$enrollment_no=$students_temps['Enrollment_No'];
$file_path="uploads/certificates/".$filename;
$file_type="pdf";
$status=1;
$created_by=$_SESSION['ID'];
$created_at=date("Y-m-d:H:i:s");
$add = $conn->query("INSERT INTO `certificates`(`student_id`, `enrollment_no`, `file_path`, `file_type`, `status`,`created_by`, `created_at`) VALUES ('".$student_id."', '".$enrollment_no."', '".$file_path."', '".$file_type."','".$status."', '".$created_by."', '".$created_at."' ) ");

if($add){
    echo json_encode(['status'=>200, 'message'=> "Certificate generated succefully!!"]);
  }else{
    echo json_encode(['status'=>400, 'message'=>'Something went to wrong!!']);
  }







  
