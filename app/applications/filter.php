<?php
if (isset($_POST['id']) && isset($_POST['by'])) {
  session_start();
  require '../../includes/db-config.php';

  $by = $_POST['by'];
  $id = intval($_POST['id']);
  $sub_center_name = "";
  if ($by == 'departments') {
    $courseIds = $conn->query("SELECT GROUP_CONCAT(ID) as ID FROM Courses WHERE Course_Type_ID = $id AND University_ID = " . $_SESSION['university_id']);
    if ($courseIds->num_rows > 0) {
      $courseIds = $courseIds->fetch_assoc();
      $courseIds = $courseIds['ID'];
      $_SESSION['filterByDepartment'] = !empty($courseIds) ? " AND Students.Course_ID IN ($courseIds)" : " AND Students.ID IS NULL";
    } else {
      $_SESSION['filterByDepartment'] = " AND Students.ID IS NULL";
    }
  } elseif ($by == 'sub_courses') {
    $_SESSION['filterBySubCourses'] = " AND Students.Sub_Course_ID = $id";
  } elseif ($by == 'users') {
    $user = $conn->query("SELECT Role FROM Users WHERE ID = $id");
    $user = $user->fetch_assoc();

    $role = $user['Role'];
    $role_query = " AND Students.Added_For = $id";

    if ($role == 'Counsellor') {
      $center_list = array($id);
      $sub_center_list = array();

      $sub_counsellors = $conn->query("SELECT `User_ID` FROM University_User WHERE Reporting IN (" . implode(",", $center_list) . ") AND University_ID = " . $_SESSION['university_id']);
      while ($sub_counsellor = $sub_counsellors->fetch_assoc()) {
        $center_list[] = $sub_counsellor['User_ID'];
      }

      $get_all_center = $conn->query("SELECT DISTINCT(Code) as Code FROM Alloted_Center_To_Counsellor WHERE University_ID = '" . $_SESSION['university_id'] . "' AND Counsellor_ID = $id");
      if ($get_all_center->num_rows > 0) {
        while ($gac = $get_all_center->fetch_assoc()) {
          $center_list[] = $gac['Code'];
        }
        $center_lists = "(" . implode(",", $center_list) . ")";
        if ($_SESSION['unique_center'] == 1) {
          $role_query = " AND Students.Added_For IN $center_lists";
        } else {
          $get_sub_center_list = $conn->query("SELECT User_ID FROM University_User WHERE Reporting IN $center_lists");
          if ($get_sub_center_list->num_rows > 0) {
            while ($gscl = $get_sub_center_list->fetch_assoc()) {
              $sub_center_list[] = $gscl['User_ID'];
            }
            $all_list = array_merge($center_list, $sub_center_list);
            $all_lists = "(" . implode(",", $all_list) . ")";
            $role_query = " AND Students.Added_For IN $all_lists";
          } else {
            $role_query = " AND Students.Added_For IN $center_lists";
          }
        }
      }
    } elseif ($role === 'Sub-Counsellor') {
      $center_list = array($id);
      $sub_center_list = array();
      $get_all_center = $conn->query("SELECT DISTINCT(Code) as Code FROM Alloted_Center_To_SubCounsellor WHERE University_ID = '" . $_SESSION['university_id'] . "' AND Sub_Counsellor_ID = $id");
      if ($get_all_center->num_rows > 0) {
        while ($gac = $get_all_center->fetch_assoc()) {
          $center_list[] = $gac['Code'];
        }
        $center_lists = "('" . implode("','", ($center_list)) . "')";
        if ($_SESSION['unique_center'] == 1) {
          $role_query = " AND Students.Added_For IN $center_lists";
        } else {
          $get_sub_center_list = $conn->query("SELECT User_ID FROM University_User WHERE Reporting IN $center_lists");
          if ($get_sub_center_list->num_rows > 0) {
            while ($gscl = $get_sub_center_list->fetch_assoc()) {
              $sub_center_list[] = $gscl['User_ID'];
            }
            $all_list = array_merge($center_list, $sub_center_list);
            $all_lists = "(" . implode(",", $all_list) . ")";
            $role_query = " AND Students.Added_For IN $all_lists";
          } else {
            $role_query = " AND Students.Added_For IN $center_lists";
          }
        }
      }
    } elseif ($role === 'Center') {
      $center_list[] = $id;
      $get_sub_center_list = $conn->query("SELECT User_ID FROM University_User WHERE Reporting = $id AND University_ID = " . $_SESSION['university_id']);
      if ($get_sub_center_list->num_rows > 0) {
        while ($gscl = $get_sub_center_list->fetch_assoc()) {
          $sub_center_list[] = $gscl['User_ID'];
        }
        $all_list = array_merge($center_list, $sub_center_list);
        $all_lists = "(" . implode(",", ($all_list)) . ")";
        $role_query = " AND Students.Added_For IN $all_lists";
      }
      $subCenter = array();
      $subCenter = $conn->query("SELECT * FROM Center_SubCenter WHERE Center=$id");
      if ($subCenter->num_rows > 0) {

        $subCenterArrId = array();
        $subCenterArrIdzz = array();
        while ($subCenterArr = $subCenter->fetch_assoc()) {
          $subCenterArrId[] = $subCenterArr['Sub_Center'];
          $subCenterArrIdzz[] = $subCenterArr['Sub_Center'];
        }

        $subCenterArrIdzz[] = $id;
        $centerSubCenterIds = "(" . implode(",", $subCenterArrIdzz) . ")";

        $role_query = " AND Students.Added_For IN $centerSubCenterIds AND Students.University_ID = " . $_SESSION['university_id'];

        $subCenter_list = "(" . implode(",", $subCenterArrId) . ")";
        $sub_centers = $conn->query("SELECT `ID`, `Code`, `Name`, `Role` FROM Users  WHERE ID IN $subCenter_list");
        $sub_center_name .= "<option value=''>Select Sub Center</option>";
        while ($subCenterListArr = $sub_centers->fetch_assoc()) {
          $sub_center_name .= "<option value='" . $subCenterListArr['ID'] . "'>" . $subCenterListArr['Name'] . "</option>";
        }
      } else {
        $sub_center_name = "<option value=''>No Record found!</option>";
      }
    } elseif ($role == 'Sub-Center') {
      $role_query = " AND Students.Added_For = $id AND Students.University_ID = " . $_SESSION['university_id'];
    }
    $_SESSION['filterByUser'] = $role_query;
  } elseif ($by == 'date') {
    $startDate = date("Y-m-d 00:00:00", strtotime($_POST['startDate']));
    $endDate = date("Y-m-d 23:59:59", strtotime($_POST['endDate']));

    $_SESSION['filterByDate'] = " AND Students.Created_At BETWEEN '$startDate' AND '$endDate'";
  } elseif ($by == 'application_status') {
    if ($id == 1) {
      $_SESSION['filterByStatus'] = " AND Document_Verified IS NOT NULL ";
    } elseif ($id == 2) {
      $_SESSION['filterByStatus'] = " AND Payment_Received IS NOT NULL ";
    } elseif ($id == 3) {
      $_SESSION['filterByStatus'] = " AND Document_Verified IS NOT NULL AND Payment_Received IS NOT NULL ";
    }
  } elseif ($by == 'vartical_type') {


    $vartical_type_sql = $conn->query("SELECT ID FROM Users WHERE  Vertical_type='$id' AND Status=1");
    while($row = $vartical_type_sql->fetch_array()){
      $center_id_arr[] = $row['ID'];
    }
    $center_ids = implode(',', $center_id_arr);
    
    $_SESSION['filterByVerticalType'] = " AND Students.Added_For IN ($center_ids)";
    // unset($_SESSION['RoleQuery']);
  }
  echo json_encode(['status' => true, 'subCenterName' => $sub_center_name]);
}
