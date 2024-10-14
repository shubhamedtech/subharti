<?php
## Database configuration
include '../../includes/db-config.php';
session_start();
## Read value
$draw = $_POST['draw'];
$row = $_POST['start'];
$rowperpage = $_POST['length'];
if (isset($_POST['order'])) {
    $columnIndex = $_POST['order'][0]['column'];
    $columnName = $_POST['columns'][$columnIndex]['data'];
    $columnSortOrder = $_POST['order'][0]['dir'];
}
$searchValue = mysqli_real_escape_string($conn, $_POST['search']['value']);
if (isset($columnSortOrder)) {
    $orderby = "ORDER BY $columnName $columnSortOrder";
} else {
    $orderby = "ORDER BY Students.id ASC";
}
// Admin Query
$query = "";
## Search 
$searchQuery = "";
if ($searchValue != '') {
    $searchQuery = " AND (Students.Enrollment_No like '%" . $searchValue . "%' OR Students.First_Name like '%" . $searchValue . "%' OR Syllabi.Name like '%" . $searchValue . "%' OR Sub_Courses.Name like '%" . $searchValue . "%' OR Sub_Courses.Short_Name like '%" . $searchValue . "%')";
}

## Total number of records without filtering
$all_count = $conn->query("SELECT COUNT(Students.id) as allcount FROM Students 
LEFT JOIN Sub_Courses ON Sub_Courses.ID = Students.Sub_Course_ID 
LEFT JOIN submitted_assignment ON Students.ID = submitted_assignment.student_id 
LEFT JOIN Syllabi ON Syllabi.Sub_Course_ID = Sub_Courses.ID 
LEFT JOIN Universities ON Students.University_ID = Universities.ID
LEFT JOIN student_assignment_result ON submitted_assignment.id = student_assignment_result.assignment_id
LEFT JOIN student_assignment ON Students.ID = student_assignment.Assignment_id
WHERE Students.status != 2 $query");
$records = mysqli_fetch_assoc($all_count);
$totalRecords = $records['allcount'];


## Total number of record with filtering
$filter_count = $conn->query("SELECT COUNT(Students.id) as filtered FROM Students 
    LEFT JOIN Sub_Courses ON Sub_Courses.ID = Students.Sub_Course_ID 
    LEFT JOIN submitted_assignment ON Students.ID = submitted_assignment.student_id 
    LEFT JOIN Syllabi ON Syllabi.Sub_Course_ID = Sub_Courses.ID 
    LEFT JOIN Universities ON Students.University_ID = Universities.ID
    LEFT JOIN student_assignment_result ON submitted_assignment.id = student_assignment_result.assignment_id
    LEFT JOIN student_assignment ON Students.ID = student_assignment.Assignment_id
    WHERE Students.status != 2 $searchQuery $query");
$records = mysqli_fetch_assoc($filter_count);
$totalRecordwithFilter = $records['filtered'];


## Fetch records
$result_record = "SELECT CASE 
WHEN Universities.ID = 47 THEN 'BVOC PROGRAM'
WHEN Universities.ID = 48 THEN 'SKILL PROGRAM'
ELSE 'Unknown University'
END AS universityname,
Students.`ID` as student_id, CONCAT_WS(' ', Students.First_Name, Students.Middle_Name, Students.Last_Name) AS student_name,
    Students.Enrollment_No, Sub_Courses.`Name` as sub_course_name, Sub_Courses.`Short_Name` as sub_course_short_name,
    Syllabi.`Name` as subject_name, Syllabi.ID as subject_id, student_assignment.Assignment_id as assignment_id, Syllabi.`Code` as subject_code, Syllabi.Semester AS semester,submitted_assignment.created_date,submitted_assignment.file_name,COALESCE(submitted_assignment.uploaded_type,student_assignment_result.uploaded_type,'NULL') AS uploaded_type,submitted_assignment.id,student_assignment_result.obtained_mark,COALESCE(student_assignment_result.status,'NOT EVALUATED') AS eva_status,student_assignment_result.remark,
CASE 
WHEN student_assignment.Assignment_id IS NULL THEN 'NOT CREATED'
ELSE 'CREATED' 
END AS assignment_status,
CASE 
WHEN submitted_assignment.id IS NULL THEN 'NOT SUBMITTED'
ELSE 'SUBMITTED'
END AS student_status, 
Students.`status`
    FROM Students
    LEFT JOIN Sub_Courses ON Sub_Courses.ID = Students.Sub_Course_ID
    LEFT JOIN Syllabi ON Syllabi.Sub_Course_ID = Sub_Courses.ID
    LEFT JOIN student_assignment ON Syllabi.ID = student_assignment.subject_id
    /*LEFT JOIN student_assignment ON Students.ID = student_assignment.Assignment_id*/
    LEFT JOIN submitted_assignment ON Students.ID = submitted_assignment.student_id AND Syllabi.ID = student_assignment.subject_id
    LEFT JOIN student_assignment_result ON submitted_assignment.id = student_assignment_result.assignment_id
    LEFT JOIN Universities ON Students.University_ID = Universities.ID
    WHERE Students.status != 2 $searchQuery $query $orderby
    LIMIT " . $row . "," . $rowperpage;
$results = mysqli_query($conn, $result_record);
$data = array();
while ($row = mysqli_fetch_assoc($results)) {
    $data[] = array(
        "student_name" => $row["student_name"],
        "enrollment_no" => $row["Enrollment_No"],
        "universityname" => $row["universityname"],
        "sub_course_name" => $row["sub_course_name"],
        "subject_name" => $row["subject_name"],
        "subject_code" => $row["subject_code"],
        "status" => $row["status"],
        "semester" => $row["semester"],
        "obtained_mark" => $row["obtained_mark"],
        "remark" => $row["remark"],
        "created_date" => $row["created_date"],
        "student_status" => $row["student_status"],
        "assignment_status" => $row["assignment_status"],
        "uploaded_type" => $row["uploaded_type"],
        "file_name" => $row["file_name"],
        "eva_status" => $row["eva_status"],
        "id" => $row["id"],
        "student_id" => $row["student_id"],
        "subject_id" => $row["subject_id"],
        "assignment_id" => $row["assignment_id"],
    );
}

## Response
$response = array(
    "draw" => intval($draw),
    "iTotalRecords" => $totalRecords,
    "iTotalDisplayRecords" => $totalRecordwithFilter,
    "aaData" => $data
);
echo json_encode($response);
