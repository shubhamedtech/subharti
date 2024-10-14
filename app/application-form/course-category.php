<?php

if (isset($_GET['id'], $_GET['userId'])) {
  require '../../includes/db-config.php';
  session_start();

  $sub_course_id = intval($_GET['id']);
  $userId = intval($_GET['userId']);
  //print_r($sub_course_id);die;
  if (empty($sub_course_id)) {
    echo '<option value="">Please add sub-course</option>';
    exit();
  }
  $course_categories = array();

  if (!empty($course_categories) && is_array($course_categories)) {
    $option = "<option>Select Choose Category</option>";
    foreach ($course_categories as $course_category) {
      $course_category1 = $course_category;
      $option .= '<option value="' . $course_category1 . '">' . $course_category1 . '</option>';
    }
  } else {
    $option = "<option>No Categories found</option>";
  }

  echo $option;
}
