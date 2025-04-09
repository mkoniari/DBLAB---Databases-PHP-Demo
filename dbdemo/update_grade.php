<?php
// Set page titles for header.php
$pageTitle = "Update Grade";
$navBarTitle = "Databases PHP Demo - Update Grade";
include 'header.php';

include 'db_connection.php'; // Include connection file
$conn = OpenCon(); // Establish connection

$student_id = null;
$grade_id = null;
$current_course_name = '';
$current_grade_value = '';
$student_name = '';
$student_surname = '';
$error_message = '';
$success_message = '';
$show_form = false;

// --- Input Validation & Fetching Current Data ---
if (isset($_GET['id']) && filter_var($_GET['id'], FILTER_VALIDATE_INT) &&
    isset($_GET['grade_id']) && filter_var($_GET['grade_id'], FILTER_VALIDATE_INT))
{
    $student_id = (int)$_GET['id'];
    $grade_id = (int)$_GET['grade_id'];

    // Fetch current grade and student details using prepared statement
    $stmt_select = $conn->prepare("SELECT g.course_name, g.grade, s.name, s.surname
                                   FROM grades as g
                                   JOIN students as s ON g.student_id = s.id
                                   WHERE s.id = ? AND g.id = ?");
    if ($stmt_select) {
        $stmt_select->bind_param("ii", $student_id, $grade_id);
        $stmt_select->execute();
        $result_select = $stmt_select->get_result();

        if ($row = $result_select->fetch_assoc()) {
            $current_course_name = htmlspecialchars($row['course_name']);
            $current_grade_value = htmlspecialchars($row['grade']);
            $student_name = htmlspecialchars($row['name']);
            $student_surname = htmlspecialchars($row['surname']);
            $show_form = true; // Grade found, show the form
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

// --- Handle Update on POST ---
if ($show_form && isset($_POST['submit_upd']) && $grade_id !== null) {
    // Get and sanitize input data
    $new_grade = trim($_POST['grade'] ?? '');

    // Basic Validation (Check if numeric and within a reasonable range if applicable)
    if (!is_numeric($new_grade)) {
         $error_message = "Grade must be a numeric value.";
    } elseif ($new_grade < 0 || $new_grade > 100) { // Example range validation
         $error_message = "Grade must be between 0 and 100.";
    } else {
        // Use prepared statement for UPDATE
        $stmt_update = $conn->prepare("UPDATE grades SET grade = ? WHERE id = ?");
        if ($stmt_update) {
            $stmt_update->bind_param("ii", $new_grade, $grade_id);

            if ($stmt_update->execute()) {
                $success_message = 'Grade record updated successfully - Redirecting back in 5 seconds...<br>';
                $success_message .= '<a href="./grades.php">Click here to go back to all grades now</a>';
                // Update current value for display if needed
                $current_grade_value = htmlspecialchars($new_grade);
                // Add JavaScript redirect
                echo '<script type="text/javascript">setTimeout(function() { delayer("./grades.php"); }, 5000);</script>';
                // Optionally hide form: $show_form = false;
            } else {
                $error_message = "Error while updating grade record: " . htmlspecialchars($stmt_update->error);
            }
            $stmt_update->close();
        } else {
             $error_message = "Error preparing update statement: " . htmlspecialchars($conn->error);
        }
    }
}

CloseCon($conn); // Close connection
?>

    <div class="row" id="row">
        <div class="col-md-12">

            <?php
            // Display messages first
            if (!empty($success_message)) {
                echo '<div class="alert alert-success mt-3" role="alert">' . $success_message . '</div>';
            }
             if (!empty($error_message)) {
                echo '<div class="alert alert-danger mt-3" role="alert">' . $error_message . '</div>';
                 if (!$show_form && (strpos($error_message, 'ID') !== false || strpos($error_message, 'found') !== false)) {
                     echo '<a href="grades.php" class="btn btn-secondary mt-2">Back to Grades</a>';
                }
            }
            ?>

            <?php if ($show_form): ?>
                <div class="form-group col-sm-6 mb-3 mt-3"> <!-- Adjusted width -->
                    <label class="form-label">Change grade for course <b><?= $current_course_name ?></b> (Current: <?= $current_grade_value ?>) for student: <br><b><?= $student_name . ' ' . $student_surname ?></b></label>
                    <hr>
                </div>

                <form class="form-horizontal" name="grade-update-form" method="POST">
                    <div class="form-group col-sm-3 mb-3">
                        <label class="form-label">New Grade</label>
                        <input type="number" step="any" class="form-control" name="grade" placeholder="e.g. 85.5" value="<?= $current_grade_value ?>" required> <!-- Use type="number" -->
                    </div>
                    <button class="btn btn-primary btn-submit-custom" type="submit" name="submit_upd">Update Grade</button>
                    <a href="grades.php" class="btn btn-secondary btn-submit-custom">Back</a> <!-- Use link for Back -->
                </form>
            <?php endif; ?>

        </div>
    </div>

<?php include 'footer.php'; ?>
