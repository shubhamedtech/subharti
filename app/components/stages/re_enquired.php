<?php
if (isset($_POST['id'])) {
  include '../../../includes/db-config.php';
  session_start();

  $id = mysqli_real_escape_string($conn, $_POST['id']);

  $check = $conn->query("SELECT ID FROM Stages WHERE ID = $id");
  if ($check->num_rows > 0) {

    $check_for_first = $conn->query("SELECT ID FROM Stages WHERE ID = $id AND Is_First = 1");
    if ($check_for_first->num_rows > 0) {
      echo json_encode(['status' => 302, 'message' => "Can't set Initial Stage as Re-Enquired Stage!"]);
      exit();
    }

    $check_for_last = $conn->query("SELECT ID FROM Stages WHERE ID = $id AND Is_Last = 1");
    if ($check_for_last->num_rows > 0) {
      echo json_encode(['status' => 302, 'message' => "Can't set Final Stage as Re-Enquired Stage!"]);
      exit();
    }

    $update_all = $conn->query("UPDATE Stages SET Is_ReEnquired = 0");
    if ($update_all) {
      $update = $conn->query("UPDATE Stages SET Is_ReEnquired = 1 WHERE ID = $id");
      if ($update) {
        echo json_encode(['status' => 200, 'message' => 'Initial stage set successfully!']);
      } else {
        echo json_encode(['status' => 302, 'message' => 'Something went wrong!']);
      }
    }
  } else {
    echo json_encode(['status' => 302, 'message' => 'Stage not found!']);
  }
}
