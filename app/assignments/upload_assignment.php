<?php
require '../../includes/db-config.php';
if (isset($_POST['submit'])) {
    session_start();
    $student_id = $conn->real_escape_string($_SESSION['ID']);
    $subject_id = $conn->real_escape_string($_POST['subject_id']);
    $assignment_id = $conn->real_escape_string($_POST['assignment_id']);
    $uploaded_type = $conn->real_escape_string($_POST['uploaded_type']);

    $targetDir = '../../uploads/assignments/';
    if ($_FILES["assignment_file"]["error"] == UPLOAD_ERR_OK) {
        $fileName = basename($_FILES["assignment_file"]["name"]);
        $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedTypes = array('pdf', 'jpeg', 'jpg', 'png', 'gif');
        if (!in_array($fileType, $allowedTypes)) {
            echo "Only PDF, JPEG, or JPG files are allowed.";
            exit;
        }
        $fileNameNew = uniqid() . '.' . $fileType;
        $targetFilePath = $targetDir . $fileNameNew;
        if (move_uploaded_file($_FILES["assignment_file"]["tmp_name"], $targetFilePath)) {
            $created_date = date('Y-m-d H:i:s');

            $existingData = $conn->query("SELECT * FROM submitted_assignment WHERE assignment_id='$assignment_id' AND student_id='$student_id' AND subject_id='$subject_id'");

            if ($existingData->num_rows > 0) {
                $updated_query = $conn->query("UPDATE submitted_assignment SET file_name='$targetFilePath', created_date='$created_date' WHERE assignment_id='$assignment_id' AND subject_id='$subject_id' AND student_id='$student_id'");
                if ($updated_query) {
                    echo json_encode(['status' => 200, 'message' => 'Assignment Updated And Uploaded Successfully!']);
                    header("Location:../../student/lms/assignments");
                } else {
                    echo json_encode(['status' => 400, 'message' => 'Something went wrong!']);
                }
            } else {
                $insert_query = $conn->query("INSERT INTO submitted_assignment (assignment_id, subject_id, student_id,uploaded_type, file_name, created_date) VALUES ('$assignment_id', '$subject_id', '$student_id', '$uploaded_type', '$targetFilePath', '$created_date')");
                if ($insert_query) {
                    echo json_encode(['status' => 200, 'message' => 'Assignment Uploaded Successfully!']);
                    header("Location:../../student/lms/assignments");
                } else {
                    echo json_encode(['status' => 400, 'message' => 'Something went wrong!']);
                }
            }
        } else {
            echo "Error moving uploaded file.";
        }
    } else {
        echo "File upload error.";
    }
}
