<?php
require $_SERVER['DOCUMENT_ROOT'] . '/includes/db-config.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $assignment_id = intval($_POST['assignment_id']);
    $marks = $_POST['marks'];
    $reason = $_POST['reason'];
    $uploaded_type = $_POST['uploaded_type'];
    $status = $_POST['status'];
    $sql = "INSERT INTO student_assignment_result (assignment_id,obtained_mark,remark,uploaded_type,status) VALUES (?, ?, ?, ?,?)";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("issss", $assignment_id, $marks, $reason, $uploaded_type, $status);
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            echo json_encode(['status' => 200, 'message' => 'Result Proper Uploaded Successfully!']);
        } else {
            echo json_encode(['status' => 400, 'message' => 'Something went wrong while inserting the file path into the database.']);
        }
    } else {
        echo "Error: " . $conn->error;
    }
}
