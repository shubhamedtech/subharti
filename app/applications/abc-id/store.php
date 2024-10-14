<?php
  if(isset($_POST['id']) && $_POST['abc_id']){
    require '../../../includes/db-config.php';
    session_start();

    $id = intval($_POST['id']);
    
if (!empty($_POST['abc_id']) && isset($_POST['abc_id'])) {
    $abc_id = mysqli_real_escape_string($conn, $_POST['abc_id']);
    if (strlen($abc_id) != 12) {
      echo json_encode(['status' => 400, 'message' => 'ABC ID must be exactly 12 characters in length!']);
      exit();
    }
  }

    if(empty($abc_id)){
      echo json_encode(['status'=>400, 'message'=>'ABC ID is required.']);
      exit();
    }

    $update = $conn->query("UPDATE Students SET ABC_ID = '$abc_id' WHERE ID = $id");
    if($update){
      echo json_encode(['status'=>200, 'message'=>'ABC_ID updated successfully!']);
    }else{
      echo json_encode(['status'=>400, 'message'=>'Something went wrong!']);
    }
  }
?>