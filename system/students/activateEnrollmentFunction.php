<?php

session_start();
require_once ("../../helper/dbHelper.php");
require_once ("../../helper/authHelper.php");

$permittedRole = ["lecturer", "admin"];

if (!authorization($permittedRole, $_SESSION["UserId"])) {
    header('location: ../auth/logout.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST["activate"])) {
    activateEnrollmentController();
} else {
    $_SESSION["error"] = "Tidak menemukan permintaan yang valid!";

    header("location: dataStudent.php");
    exit;
}

function activateEnrollmentController()
{
    try {
        $enrollmentId = htmlspecialchars($_POST["activate"]);

        $connection = getConnection(); // Mengasumsikan Anda memiliki fungsi bernama getConnection() untuk membuat koneksi PDO

        $stmt = $connection->prepare("SELECT Status FROM enrollments WHERE EnrollmentId = :enrollmentId");
        $stmt->bindParam(':enrollmentId', $enrollmentId);
        $stmt->execute();

        $enrollment = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($enrollment) {
            if ($enrollment['Status'] == 1) {
                throw new Exception("Enrollment sudah aktif!");
            }

            $updateStmt = $connection->prepare("UPDATE enrollments SET Status = 1 WHERE EnrollmentId = :enrollmentId");
            $updateStmt->bindParam(':enrollmentId', $enrollmentId);
            $updateStmt->execute();

            $_SESSION["success"] = "Enrollment berhasil diaktifkan kembali!";
        } else {
            throw new Exception("ID enrollment tidak valid!");
        }

        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    } catch (Exception $e) {
        $_SESSION["error"] = "Error: " . $e->getMessage();
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }
}
?>