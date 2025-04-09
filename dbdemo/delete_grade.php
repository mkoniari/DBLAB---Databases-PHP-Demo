<?php
// Set page titles for header.php
$pageTitle = "Delete Grade";
$navBarTitle = "Databases PHP Demo - Delete Grade";
include 'header.php';

include 'db_connection.php'; // Include connection file
$conn = OpenCon(); // Establish connection

$student_id = null;
$grade_id = null;
$course_name = '';
$grade_value = '';
$student_name = '';
$student_surname = '';
$error_message = '';
$success_message = '';
$show_confirmation = false;

// --- Input Validation & Fetching Data ---
if (isset($_GET['id']) && filter_var($_GET['id'], FILTER_VALIDATE_INT) &&
    isset($_GET['grade_id']) && filter_var($_GET['grade_id'], FILTER_VALIDATE_INT))
{
    $student_id = (int)$_GET['id'];
    $grade_id = (int)$_GET['grade_id'];

    // Fetch grade and student details using prepared statement
    $stmt_select = $conn->prepare("SELECT g.course_name, g.grade, s.name, s.surname
                                   FROM grades as g
                                   JOIN students as s ON g.student_id = s.id
                                   WHERE s.id = ? AND g.id = ?");
    if ($stmt_select) {
        $stmt_select->bind_param("ii", $student_id, $grade_id);
        $stmt_select->execute();
        $result_select = $stmt_select->get_result();

        if ($row = $result_select->fetch_assoc()) {
            $course_name = htmlspecialchars($row['course_name']);
            $grade_value = htmlspecialchars($row['grade']);
            $student_name = htmlspecialchars($row['name']);
            $student_surname = htmlspecialchars($row['surname']);
            $show_confirmation = true; // Grade found, show confirmation
        } else {
            $error_message = "Error: Grade or associated student not found (Student ID: $student_id, Grade ID: $grade_id).";
        }
        $stmt_select->close();
    } else {
        $error_message = "Error preparing select statement: " . htmlspecialchars($conn->error);
    }

} else {
    $error_message = "Invalid or missing student ID or grade ID provided.";
}

// --- Handle Deletion on POST ---
if ($show_confirmation && isset($_POST['submit_del']) && $grade_id !== null) {
    // Use prepared statement for DELETE
    $stmt_delete = $conn->prepare("DELETE FROM grades WHERE id = ?");
    if ($stmt_delete) {
        $stmt_delete->bind_param("i", $grade_id);

        if ($stmt_delete->execute()) {
            $success_message = 'Grade record deleted successfully - Redirecting back in 5 seconds...<br>';
            $success_message .= '<a href="./grades.php">Click here to go back to all grades now</a>';
            // Add JavaScript redirect using function from footer.php
            echo '<script type="text/javascript">setTimeout(function() { delayer("./grades.php"); }, 5000);</script>';
            $show_confirmation = false; // Hide form after successful deletion
        } else {
            $error_message = "Error while deleting grade record: " . htmlspecialchars($stmt_delete->error);
        }
        $stmt_delete->close();
    } else {
         $error_message = "Error preparing delete statement: " . htmlspecialchars($conn->error);
    }
}

CloseCon($conn); // Close connection
?>

    <div class="row" id="row">
        <div class="col-md-12">
            <?php
            // Display messages
            if (!empty($error_message)) {
                echo '<div class="alert alert-danger mt-3" role="alert">' . $error_message . '</div>';
                 if (!$show_confirmation && (strpos($error_message, 'ID') !== false || strpos($error_message, 'found') !== false)) {
                     echo '<a href="grades.php" class="btn btn-secondary mt-2">Back to Grades</a>';
                }
            }
            if (!empty($success_message)) {
                echo '<div class="alert alert-success mt-3" role="alert">' . $success_message . '</div>';
            }
            ?>

            <?php if ($show_confirmation): ?>
                <form class="form-horizontal mt-3" name="grade-delete-form" method="POST">
                     <div class="form-group col-sm-6 mb-3"> <!-- Adjusted width -->
                        <label class="form-label">Are you sure you want to delete the grade <b><?= $grade_value ?></b> for course <b><?= $course_name ?></b> from student <b><?= $student_name . ' ' . $student_surname ?></b>?</label>
                    </div>
                    <button class="btn btn-danger btn-submit-custom" type="submit" name="submit_del">Delete</button>
                    <a href="grades.php" class="btn btn-secondary btn-submit-custom">Cancel</a> <!-- Use link for Cancel -->
                </form>
            <?php endif; ?>
        </div>
    </div>

<?php include 'footer.php'; ?>
