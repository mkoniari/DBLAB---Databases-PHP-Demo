<?php
// Set page titles for header.php
$pageTitle = "Add New Grade";
$navBarTitle = "Databases PHP Demo - Add Grade";
include 'header.php';

include_once 'db_connection.php'; // Use include_once
$conn = OpenCon(); // Establish connection

// Initialize variables
$student_id = null;
$student_name = '';
$student_surname = '';
$error_message = '';
$success_message = '';
$show_form = false;

// --- Validate Student ID from GET and Fetch Student Info ---
if (isset($_GET['student_id']) && filter_var($_GET['student_id'], FILTER_VALIDATE_INT)) {
    $student_id = (int)$_GET['student_id'];

    // Fetch student details to display confirmation
    $stmt_select = $conn->prepare("SELECT name, surname FROM students WHERE id = ?");
    if ($stmt_select) {
        $stmt_select->bind_param("i", $student_id);
        $stmt_select->execute();
        $result_select = $stmt_select->get_result();
        if ($row = $result_select->fetch_assoc()) {
            $student_name = htmlspecialchars($row['name']);
            $student_surname = htmlspecialchars($row['surname']);
            $show_form = true; // Student found, show the form
        } else {
            $error_message = "Error: Student with ID " . $student_id . " not found.";
        }
        $stmt_select->close();
    } else {
        $error_message = "Error preparing student lookup: " . htmlspecialchars($conn->error);
    }
} else {
    $error_message = "Invalid or missing student ID provided in the URL.";
}

// --- Handle Form Submission (POST) ---
if ($show_form && isset($_POST['submit_grade'])) {
    // Retrieve student_id from hidden field (more reliable during POST)
    $posted_student_id = filter_input(INPUT_POST, 'student_id', FILTER_VALIDATE_INT);

    // Double-check if the posted student ID matches the one from GET, or if it's valid
    if ($posted_student_id !== $student_id || $posted_student_id === null) {
         $error_message = "Student ID mismatch or missing. Please try again.";
         $show_form = false; // Prevent further processing if ID is wrong
    } else {
        // Get data using null coalescing operator and trim whitespace
        $course_name = trim($_POST['course_name'] ?? '');
        $grade_value = trim($_POST['grade'] ?? '');

        // Server-side validation
        $errors = [];
        if (empty($course_name)) {
            $errors[] = "Course Name is required.";
        }
        if ($grade_value === '') { // Check specifically for empty string as 0 is valid
             $errors[] = "Grade is required.";
        } elseif (!is_numeric($grade_value)) {
             $errors[] = "Grade must be a numeric value.";
        } elseif ($grade_value < 0 || $grade_value > 100) { // Example range validation
             $errors[] = "Grade must be between 0 and 100 (inclusive).";
        }

        // If no validation errors, proceed with insertion
        if (empty($errors)) {
            // Use prepared statements to prevent SQL injection
            $stmt_insert = $conn->prepare("INSERT INTO grades (student_id, course_name, grade) VALUES (?, ?, ?)");

            if ($stmt_insert === false) {
                $error_message = 'Error preparing statement: ' . htmlspecialchars($conn->error);
            } else {
                // Bind parameters (i = integer, s = string, d = double/decimal)
                // Use 'd' for grade if it can have decimals, 's' if it's stored as VARCHAR
                $stmt_insert->bind_param("isd", $posted_student_id, $course_name, $grade_value);

                // Execute the statement
                if ($stmt_insert->execute()) {
                    $success_message = 'Grade added successfully for ' . $student_name . ' ' . $student_surname . '. Redirecting to grades list in 5 seconds...<br>';
                    $success_message .= '<a href="./grades.php">Click here to go back now</a>';
                    // Use the delayer function defined in footer.php
                    echo '<script type="text/javascript">setTimeout(function() { delayer("./grades.php"); }, 5000);</script>';

                    // Clear form fields after successful submission (optional, JS redirect handles this)
                    $_POST = array(); // Clear POST array
                    $show_form = false; // Hide form after success

                } else {
                    // Check for specific errors like duplicate entry if you have unique constraints
                    if ($conn->errno == 1062) { // Error code for duplicate entry
                         $error_message = 'Error: A grade for this course might already exist for this student.';
                    } else {
                        $error_message = 'Error while adding grade: ' . htmlspecialchars($stmt_insert->error);
                    }
                }
                // Close the statement
                $stmt_insert->close();
            }
        } else {
            // Format validation errors for display
            $error_html = '<strong>Error(s):</strong><br><ul>';
            foreach ($errors as $error) {
                $error_html .= '<li>' . htmlspecialchars($error) . '</li>';
            }
            $error_html .= '</ul>';
            $error_message = $error_html; // Assign formatted errors to the message variable
        }
    } // End check for valid posted_student_id
} // End POST handling

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
                 // Add a back button if the form isn't showing due to an initial error
                 if (!$show_form && strpos($error_message, 'ID') !== false) {
                     echo '<a href="students.php" class="btn btn-secondary mt-2">Back to Students List</a>';
                }
            }
            ?>

            <?php if ($show_form): ?>
                <div class="form-group col-sm-6 mb-3 mt-3"> <!-- Adjusted width -->
                    <label class="form-label">Adding Grade for Student: <br><b><?= $student_name . ' ' . $student_surname ?> (ID: <?= $student_id ?>)</b></label>
                    <hr>
                </div>

                <form class="form-horizontal" name="grade-form" method="POST">
                    <!-- Hidden field to pass student_id during POST -->
                    <input type="hidden" name="student_id" value="<?= htmlspecialchars($student_id) ?>">

                    <div class="form-group col-sm-4 mb-3"> <!-- Adjusted width -->
                        <label class="form-label">Course Name</label>
                        <input type="text" class="form-control" name="course_name" placeholder="Enter course name" value="<?= isset($_POST['course_name']) ? htmlspecialchars($_POST['course_name']) : '' ?>" required>
                    </div>
                    <div class="form-group col-sm-3 mb-3"> <!-- Adjusted width -->
                        <label class="form-label">Grade</label>
                        <input type="number" step="any" min="0" max="100" class="form-control" name="grade" placeholder="e.g. 85.5" value="<?= isset($_POST['grade']) ? htmlspecialchars($_POST['grade']) : '' ?>" required> <!-- Use type="number" -->
                    </div>

                    <button class="btn btn-primary btn-submit-custom" type="submit" name="submit_grade">Add Grade</button>
                    <!-- Link back to the students list or grades list -->
                    <a href="students.php" class="btn btn-secondary btn-submit-custom">Cancel</a>
                </form>
            <?php endif; ?>

        </div>
    </div>

<?php include 'footer.php'; ?>
