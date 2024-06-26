<?php
require_once ("../../helper/dbHelper.php");
date_default_timezone_set('Asia/Jakarta');


// Check if it's a POST request and attendance action is requested
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['attendance'])) {
    // Get the card ID from the POST parameters
    $faceId = $_POST['faceId'];

    // Call the function to validate the card ID and update attendance
    $result = checkAndUpdateAttendance($faceId);

    http_response_code($result["code"]);
    echo json_encode(array("message" => $result["message"]));

} else {
    // Method not allowed for other request types
    http_response_code(405); // Method Not Allowed
    echo json_encode(array("error" => "Method not allowed."));
}

/**
 * Validates the face ID, checks student enrollment, and updates attendance accordingly.
 * 
 * @param string $faceId The face ID to be validated.
 * @return array A message indicating the result of the attendance update.
 */
function checkAndUpdateAttendance($faceId)
{
    // Initialize the database connection and response message
    $connection = null;
    $responseMessage = '';

    try {
        // Connect to the database
        $connection = getConnection();

        // Check if the face ID is registered to any student
        $sqlCheckStudent = "SELECT students.StudentId, Users.Name, users.Status 
                            FROM students 
                            INNER JOIN users ON students.StudentId = users.StudentId 
                            WHERE Face = :faceId";
        $stmtCheckStudent = $connection->prepare($sqlCheckStudent);
        $stmtCheckStudent->bindParam(':faceId', $faceId);
        $stmtCheckStudent->execute();
        $student = $stmtCheckStudent->fetch(PDO::FETCH_ASSOC);

        // If student is found
        if ($student) {
            // Check if the student's status is inactive
            if ($student['Status'] == 0) {
                return ["code" => 200, "message" => "Mahasiswa " . $student['Name'] . " sudah tidak aktif."];
            }

            // Get the current date and time
            $currentDate = date('Y-m-d');
            $currentTime = date('H:i:s');

            // Check if the student is enrolled in any class on the current day
            $sqlCheckEnrollment = "SELECT enrollments.Status AS EnrollmentStatus, schedules.ScheduleId, courses.Name, courses.Status AS CourseStatus, schedules.StartTime
                                   FROM enrollments
                                   INNER JOIN schedules ON enrollments.CourseId = schedules.CourseId
                                   INNER JOIN courses ON enrollments.CourseId = courses.CourseId
                                   WHERE enrollments.StudentId = :studentId
                                   AND schedules.Date = :currentDate
                                   AND schedules.StartTime <= :currentTime
                                   AND schedules.EndTime >= :currentTime";
            $stmtCheckEnrollment = $connection->prepare($sqlCheckEnrollment);
            $stmtCheckEnrollment->bindParam(':studentId', $student['StudentId']);
            $stmtCheckEnrollment->bindParam(':currentDate', $currentDate);
            $stmtCheckEnrollment->bindParam(':currentTime', $currentTime);
            $stmtCheckEnrollment->execute();
            $enrollment = $stmtCheckEnrollment->fetch(PDO::FETCH_ASSOC);

            // If the student is enrolled in a class on the current day
            if ($enrollment) {
                // Check if the enrollment is active
                if ($enrollment['EnrollmentStatus'] == 0) {
                    return ["code" => 200, "message" => "Mahasiswa telah dinonaktifkan dari kelas " . $enrollment["Name"] . "."];
                }

                // Check if the course is available
                if ($enrollment['CourseStatus'] == 0) {
                    return ["code" => 200, "message" => "Mata kuliah " . $enrollment["Name"] . " tidak tersedia saat ini."];
                }

                // Check if attendance has already been recorded for the student today
                $sqlCheckAttendance = "SELECT *
                                       FROM attendances
                                       WHERE StudentId = :studentId
                                       AND ScheduleId = :scheduleId
                                       AND (FaceTimeIn IS NOT NULL OR FingerprintTimeIn IS NOT NULL OR CardTimeIn IS NOT NULL)";
                $stmtCheckAttendance = $connection->prepare($sqlCheckAttendance);
                $stmtCheckAttendance->bindParam(':studentId', $student['StudentId']);
                $stmtCheckAttendance->bindParam(':scheduleId', $enrollment['ScheduleId']);
                $stmtCheckAttendance->execute();
                $existingAttendance = $stmtCheckAttendance->fetch(PDO::FETCH_ASSOC);

                if ($existingAttendance) {
                    return ["code" => 200, "message" => $student["Name"] . " telah masuk kelas " . $enrollment["Name"] . "."];
                } else {
                    // Update attendance in the attendances table
                    $attendanceDate = date('Y-m-d H:i:s');
                    $attendanceStatus = ($currentTime <= date('H:i:s', strtotime($enrollment['StartTime'] . '+15 minutes'))) ? 1 : 3; // Mark as present if within 15 minutes, otherwise mark as late

                    // Query to update attendance
                    $sqlUpdateAttendance = "UPDATE attendances
                                            SET FaceTimeIn = :attendanceDate, Status = :attendanceStatus
                                            WHERE StudentId = :studentId AND ScheduleId = :scheduleId";
                    $stmtUpdateAttendance = $connection->prepare($sqlUpdateAttendance);
                    $stmtUpdateAttendance->bindParam(':attendanceDate', $attendanceDate);
                    $stmtUpdateAttendance->bindParam(':attendanceStatus', $attendanceStatus);
                    $stmtUpdateAttendance->bindParam(':studentId', $student['StudentId']);
                    $stmtUpdateAttendance->bindParam(':scheduleId', $enrollment['ScheduleId']);
                    $stmtUpdateAttendance->execute();

                    // Return success message
                    $responseMessage = "Kehadiran " . $student["Name"] . " di kelas " . $enrollment["Name"] . " telah dicatat.";

                    if ($attendanceStatus == 3) {
                        $responseMessage .= " Mahasiswa terlambat.";
                    }

                    return ["code" => 200, "message" => $responseMessage];

                }
            } else {
                // Student is not enrolled in any class on the current day or arrived more than 15 minutes after the class starts
                return ["code" => 500, "message" => $student["Name"] . " tidak memiliki jadwal kelas sekarang."];

            }
        } else {
            // Face ID is not registered to any student
            return ["code" => 500, "message" => "ID wajah tidak terkait dengan mahasiswa mana pun."];
        }
    } catch (PDOException $e) {
        // Log database connection or query execution errors
        return ["code" => 500, "message" => "Terjadi Kesalahan Di Database"];

    } catch (Exception $e) {
        // Log general exceptions
        return ["code" => 500, "message" => "Terjadi Kesalahan Di Sistem"];
    } finally {
        // Close the database connection if open
        if ($connection) {
            $connection = null;
        }
    }
}