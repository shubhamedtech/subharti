<?php
ini_set('display_errors', 1);

## Database configuration
include '../../includes/db-config.php';
session_start();
## Read value
// echo "<pre>"; print_r($_POST);die;
$draw = $_POST['draw'];
$row = $_POST['start'];
$rowperpage = $_POST['length']; // Rows display per page
if (isset($_POST['order'])) {
  $columnIndex = $_POST['order'][0]['column']; // Column index
  $columnName = $_POST['columns'][$columnIndex]['data']; // Column name
  $columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
}

$searchValue = mysqli_real_escape_string($conn, $_POST['search']['value']); // Search value

if (isset($columnSortOrder)) {
  $orderby = "ORDER BY $columnName $columnSortOrder";
} else {
  $orderby = "ORDER BY Wallets.ID DESC";
}

$university_query = " AND Wallets.University_ID =" . $_SESSION['university_id'];

$role_query = str_replace('{{ table }}', 'Wallets', $_SESSION['RoleQuery']);
$role_query = str_replace('{{ column }}', 'Added_By', $role_query);

## Search 
$searchQuery = " ";

if ($searchValue != '') {
  if (strcasecmp($searchValue, 'added') == 0) {
    $searchQuery = " AND Wallets.Status = 1 ";
  } else if (strcasecmp($searchValue, 'Failled') == 0) {
    $searchQuery = " AND Wallets.Status = 2 ";
  } else if (strcasecmp($searchValue, 'pending') == 0) {
    $searchQuery = " AND Wallets.Status = 0 ";
  } else if (strcasecmp($searchValue, 'Online') == 0) {
    $searchQuery = " AND Wallets.Type = 2 ";
  } else if (strcasecmp($searchValue, 'Offline') == 0) {
    $searchQuery = " AND Wallets.Type = 1";
  } else {
    $userQuery = $conn->query("SELECT ID, Code, Name FROM Users WHERE Name LIKE '%" . $searchValue . "%' OR Code  LIKE '%" . $searchValue . "%'");
    $userArr = mysqli_fetch_array($userQuery);
    if (isset($userArr['ID'])) {
      $searchQuery = "AND Wallets.Added_By = " . $userArr['ID'];
    } else {
      $searchQuery = " AND (CONCAT(TRIM(CONCAT(Students.First_Name, ' ', Students.Middle_Name, ' ', Students.Last_Name)), ' (', IF(Students.Unique_ID='' OR Students.Unique_ID IS NULL, RIGHT(CONCAT('000000', Students.ID), 6), Students.Unique_ID), ')') LIKE '%" . $searchValue . "%' OR Transaction_ID like '%" . $searchValue . "%'  OR Bank like '%" . $searchValue . "%'  OR Gateway_ID like '%" . $searchValue . "%' OR Amount like '%" . $searchValue . "%' OR Payment_Mode like '%" . $searchValue . "%' OR Universities.Short_Name like '%" . $searchValue . "%' OR Universities.Name like '%" . $searchValue . "%' OR Universities.Vertical like '%" . $searchValue . "%')";
    }
  }
}
// echo $searchQuery;
$filterQueryUser = "";
if (isset($_SESSION['filterByUser'])) {
  $filterQueryUser = $_SESSION['filterByUser'];
}

$filterByDate = "";
if (isset($_SESSION['filterByDate'])) {
  $filterByDate = $_SESSION['filterByDate'];
}
// echo "<pre>";print_r($_SESSION);die;
$searchQuery .= $filterQueryUser . $filterByDate;

## Total number of records without filtering

$all_count = $conn->query("SELECT COUNT(Wallets.ID) as allcount FROM Wallets WHERE (Type = 1 OR Type = 2) $university_query $role_query");
$records = mysqli_fetch_assoc($all_count);
$totalRecords = $records['allcount'];

## Total number of record with filtering
$filter_count = $conn->query("SELECT COUNT(Wallets.ID) as filtered FROM Wallets LEFT JOIN Universities ON Wallets.University_ID = Universities.ID LEFT JOIN Students ON Wallets.Added_For = Students.ID WHERE (Type = 1 OR Type = 2) $university_query $searchQuery $role_query");
$records = mysqli_fetch_assoc($filter_count);
$totalRecordwithFilter = $records['filtered'];

## Fetch records
$result_record = "SELECT Wallets.ID, Wallets.Transaction_Date, Wallets.Type, Wallets.Transaction_ID, Wallets.Gateway_ID, Wallets.Amount, Wallets.File, Wallets.Payment_Mode, Wallets.Bank, Wallets.Added_For, Wallets.Added_By, Wallets.Status, CONCAT(Universities.Short_Name, ' (', Universities.Vertical, ')') as University FROM Wallets LEFT JOIN Universities ON Wallets.University_ID = Universities.ID LEFT JOIN Students ON Wallets.Added_For = Students.ID WHERE (Type = 1 OR Type = 2) $university_query $searchQuery $role_query $orderby LIMIT " . $row . "," . $rowperpage;
$empRecords = mysqli_query($conn, $result_record);
$data = array();

while ($row = mysqli_fetch_assoc($empRecords)) {
  // Added_For
  if ($row['Payment_Mode'] == "Settelment By Sub-Center" && $_SESSION['university_id']==48) {

    $subUser = $conn->query("SELECT ID,Added_By as Sub_Center  FROM Wallet_Payments WHERE Transaction_ID = '".$row['Transaction_ID']."'");

    $subUserArr  = mysqli_fetch_array($subUser);
    $sub_centerId =  $subUserArr['Sub_Center'];
    $users = $conn->query("SELECT ID, Code, Name FROM Users WHERE ID = $sub_centerId");
  } else {
    $users = $conn->query("SELECT ID, Code, Name FROM Users WHERE ID = " . $row['Added_By']);
  }

  $user = mysqli_fetch_array($users);


  // $user = $conn->query("SELECT ID, Code, Name FROM Users WHERE ID = " . $row['Added_By'] . " ");
  // $user = $conn->query("SELECT ID, Code, Name FROM Users WHERE ID = " . $row['Added_By'] . " AND Role != 'Sub-Center'");
  // if ($user->num_rows == 0) {
  //   $user = $conn->query("SELECT Users.ID, Code, Name FROM Users LEFT JOIN Center_SubCenter ON Users.ID = Center_SubCenter.Center WHERE `Sub_Center` = " . $row['Added_By']);
  // }
  // $user = mysqli_fetch_array($user);

  // RM
  $rm['Name'] = "";
  if (!empty($user)) {
    // RM
    $rm = $conn->query("SELECT CONCAT(Users.Name, ' (', Users.Code, ')') as Name FROM Alloted_Center_To_Counsellor LEFT JOIN Users ON Alloted_Center_To_Counsellor.Counsellor_ID = Users.ID AND Alloted_Center_To_Counsellor.University_ID = " . $_SESSION['university_id'] . " WHERE Alloted_Center_To_Counsellor.Code = " . $user['ID'] . " AND Alloted_Center_To_Counsellor.University_ID = " . $_SESSION['university_id']);
    if ($rm->num_rows > 0) {
      $rm = mysqli_fetch_array($rm);
    } else {
      $rm = $user;
    }
  }


  $students = $conn->query("SELECT CONCAT(TRIM(CONCAT(Students.First_Name, ' ', Students.Middle_Name, ' ', Students.Last_Name)), ' (', IF(Students.Unique_ID='' OR Students.Unique_ID IS NULL, RIGHT(CONCAT('000000', Students.ID), 6), Students.Unique_ID), ')') as Student_Name , Students.ID as std_ID FROM Invoices LEFT JOIN Students ON Invoices.Student_ID = Students.ID WHERE `User_ID` = " . $row['Added_By'] . " AND Invoice_No = '" . $row['Transaction_ID'] . "'  AND Invoices.University_ID = " . $_SESSION['university_id'] . " ");
  $student_name = array();
  while ($student = mysqli_fetch_assoc($students)) {
    $student_name[] = $student['std_ID'];
  }

  $file_type = "";
  if (!empty($row['File'])) {
    $extension = explode(".", $row['File']);
    $extension = end($extension);
    $file_type = strcasecmp($extension, 'pdf') == 0 ? "pdf" : "image";
  }

  $data[] = array(
    "File" => empty($row['File']) ? '' : $row['File'],
    "File_Type" => $file_type,
    "Transaction_ID" => empty($row['Transaction_ID']) ? '' : $row['Transaction_ID'],
    "Transaction_Date" => empty($row['Transaction_Date']) ? '' : date("d-m-Y", strtotime($row['Transaction_Date'])),
    "Gateway_ID" => empty($row['Gateway_ID']) ? '' : $row['Gateway_ID'],
    "Bank" => empty($row['Bank']) ? '' : $row['Bank'],
    // "Amount" => empty($row['Amount']) ? '' : $row['Amount'],
    "Amount" => empty($row['Amount']) ? '' : "&#8377; " . number_format($row['Amount'], 2),
    "Payment_Mode" => empty($row['Payment_Mode']) ? '' : $row['Payment_Mode'],
    "University" => empty($row['University']) ? '' : $row['University'],
    "Center_Code" => $user['Code'],
    "Center_Name" => $user['Name'],
    "RM" => $rm['Name'],
    "Status" => $row['Status'],
    "ID" => $row['ID'],
    "Student" => $student_name,
    "Type" => $row['Type'] == 1 ? "Offline" : "Online",
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
