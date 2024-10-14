<?php
  if(isset($_POST['name']) && isset($_POST['university_id']) && isset($_POST['id'])){
    require '../../../includes/db-config.php';
    session_start();

    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $university_id = intval($_POST['university_id']);
    $id = intval($_POST['id']);
    
    if(empty($name) || empty($university_id)){
      echo json_encode(['status'=>403, 'message'=>'All fields are mandatory!']);
      exit();
    }

    $check = $conn->query("SELECT ID FROM Schemes WHERE Name LIKE '$name' AND University_ID = $university_id AND ID <> $id");
    if($check->num_rows>0){
      echo json_encode(['status'=>400, 'message'=> $name.' already exists!']);
      exit();
    }
    
    $add = $conn->query("UPDATE `Schemes` SET `Name` = '$name' WHERE ID = $id");
    if($add){
      echo json_encode(['status'=>200, 'message'=>$name.' added successlly!']);
    }else{
      echo json_encode(['status'=>400, 'message'=>'Something went wrong!']);
    }
  }
?>
