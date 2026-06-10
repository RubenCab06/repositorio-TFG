<?php
session_start();
$conn = new mysqli("localhost", "root", "", "agroconnect");
$check = $conn->query("SHOW TABLES LIKE 'sesiones_activas'");
if ($check && $check->num_rows > 0) {
    $sid = session_id();
    $stmt = $conn->prepare("DELETE FROM sesiones_activas WHERE session_id=?");
    $stmt->bind_param("s", $sid);
    $stmt->execute();
}
session_destroy();
header("Location: login.php");
exit();
?>
