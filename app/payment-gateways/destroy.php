<?php
if ($_SERVER['REQUEST_METHOD'] == 'DELETE' && isset($_GET['id'])) {
  require '../../includes/db-config.php';
  session_start();

  $id = intval($_GET['id']);

  $check = $conn->query("SELECT ID FROM Payment_Gateways WHERE ID = $id");
  if ($check->num_rows > 0) {
    $delete = $conn->query("DELETE FROM Payment_Gateways WHERE ID = $id");
    if ($delete) {
      echo json_encode(['status' => 200, 'message' => 'Payment Gateway deleted successfully!']);
    } else {
      echo json_encode(['status' => 302, 'message' => 'Something went wrong!']);
    }
  } else {
    echo json_encode(['status' => 302, 'message' => 'Payment Gateway not exists!']);
  }
}
