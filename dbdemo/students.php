<?php
// --- Determine Mode and Set Titles ---
// Check if a valid student_id is provided in the URL for "Add Grade" mode
$is_add_grade_mode = isset($_GET['student_id']) && filter_var($_GET['student_id'], FILTER_VALIDATE_INT);

if ($is_add_grade_mode) {
    $pageTitle = "Add New Grade";
    $navBarTitle = "Databases PHP Demo - Add Grade";
} else {
    $pageTitle = "Students List";
    $navBarTitle = "Databases PHP Demo - Students";
}
include 'header.php'; // Include header AFTER setting titles

include_once 'db_connection.php'; // Use include_once
$conn = OpenCon(); // Establish connection

// Initialize variables
$student_id = null;
$student_name = '';
$student_surname = '';
$error_message = '';
$success_message = '';
$show_form = false; // Default to showing the list, will be set true if student_id is valid
$students_list = []; // Array to hold student list data

// --- Handle "Add Grade" Mode (student_id provided) ---
if ($is_add_grade_mode) {
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
            $error_message = "Error: Student with ID " . htmlspecialchars($student_id) . " not found.";
            // Don't show the form if student not found
        }
        $stmt_select->close();
    } else {
        $error_message = "Error preparing student lookup: " . htmlspecialchars($conn->error);
    }

    // --- Handle Form Submission (POST) only in "Add Grade" mode ---
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
                    $stmt_insert->bind_param("isd", $posted_student_id, $course_name, $grade_value);

                    // Execute the statement
                    if ($stmt_insert->execute()) {
                        $success_message = 'Grade added successfully for ' . $student_name . ' ' . $student_surname . '. Redirecting to grades list in 3 seconds...<br>';
                        $success_message .= '<a href="./grades.php">Click here to go back now</a>';
                        // Use the delayer function defined in footer.php (assuming it exists)
                        echo '<script type="text/javascript">setTimeout(function() { if(typeof delayer === "function") { delayer("./grades.php"); } else { window.location.href="./grades.php"; } }, 3000);</script>';

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
}
// --- Handle "List Students" Mode (no valid student_id provided) ---
else {
    // Fetch the list of students using prepared statements for consistency and security
    $stmt_list = $conn->prepare("SELECT id, name, surname, email FROM students ORDER BY id");
    if ($stmt_list) {
        $stmt_list->execute();
        $result_list = $stmt_list->get_result();
        while ($row = $result_list->fetch_assoc()) {
            // Sanitize output before storing
            foreach ($row as $key => $value) {
                $row[$key] = htmlspecialchars($value ?? ''); // Ensure nulls are handled
            }
            $students_list[] = $row; // Store sanitized rows in an array
        }
        $stmt_list->close();
    } else {
         // Set an error message if fetching the list fails
         $error_message = "Error fetching student list: " . htmlspecialchars($conn->error);
    }
}

CloseCon($conn); // Close connection AFTER all DB operations are done
?>

    <div class="row" id="row">
        <div class="col-md-12">

            <?php
            // Display messages (applies to both modes)
            if (!empty($success_message)) {
                echo '<div class="alert alert-success mt-3" role="alert">' . $success_message . '</div>';
            }
            // Display errors, potentially adding a back button if an error occurred in "Add Grade" mode
            if (!empty($error_message)) {
                echo '<div class="alert alert-danger mt-3" role="alert">' . $error_message . '</div>';
                 // Add a back button if showing an error specifically in "Add Grade" mode (e.g., student not found, ID mismatch)
                 if ($is_add_grade_mode && !$show_form) { // Error occurred, and we are not showing the form
                     echo '<a href="students.php" class="btn btn-secondary mt-2">Back to Students List</a>';
                 }
            }
            ?>

            <?php if ($show_form): // --- Display "Add Grade" Form --- ?>
                <div class="form-group col-lg-6 col-md-8 col-sm-12 mb-3 mt-3"> <!-- Responsive width -->
                    <label class="form-label">Adding Grade for Student: <br><b><?= $student_name . ' ' . $student_surname ?> (ID: <?= $student_id ?>)</b></label>
                    <hr>
                </div>

                <form class="form-horizontal" name="grade-form" method="POST" action="students.php?student_id=<?= htmlspecialchars($student_id) ?>"> <!-- Keep student_id in action URL -->
                    <!-- Hidden field to pass student_id during POST -->
                    <input type="hidden" name="student_id" value="<?= htmlspecialchars($student_id) ?>">

                    <div class="form-group col-lg-5 col-md-6 col-sm-10 mb-3"> <!-- Responsive width -->
                        <label class="form-label" for="course_name">Course Name</label>
                        <input type="text" id="course_name" class="form-control" name="course_name" placeholder="Enter course name" value="<?= isset($_POST['course_name']) ? htmlspecialchars($_POST['course_name']) : '' ?>" required>
                    </div>
                    <div class="form-group col-lg-3 col-md-4 col-sm-6 mb-3"> <!-- Responsive width -->
                        <label class="form-label" for="grade">Grade</label>
                        <input type="number" id="grade" step="any" min="0" max="100" class="form-control" name="grade" placeholder="e.g. 85.5" value="<?= isset($_POST['grade']) ? htmlspecialchars($_POST['grade']) : '' ?>" required> <!-- Use type="number" -->
                    </div>

                    <button class="btn btn-primary btn-submit-custom" type="submit" name="submit_grade">Add Grade</button>
                    <!-- Link back to the students list -->
                    <a href="students.php" class="btn btn-secondary btn-submit-custom">Cancel</a>
                </form>

            <?php elseif (!$is_add_grade_mode): // --- Display Student List (only if not in add grade mode) --- ?>

                <h2 class="mt-3 mb-3">Students List</h2>
                <p><a href="create_student.php" class="btn btn-success"><i class="fa fa-plus"></i> Add New Student</a></p> <!-- Link to add student -->

                <?php if (empty($students_list) && empty($error_message)): // Check if list is empty AND no error occurred fetching it ?>
                    <div class="alert alert-info mt-3" role="alert">
                        No Students found in the database.
                    </div>
                <?php elseif (!empty($students_list)): ?>
                    <div class="table-responsive mt-3">
                        <table class="table table-striped table-hover">
                            <thead class="table-light"> <!-- Added class for thead styling -->
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Surname</th>
                                    <th>Email</th>
                                    <th colspan="3" class="text-center">Actions</th> <!-- Combine action headers -->
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($students_list as $student): ?>
                                    <tr>
                                        <td><?= $student['id'] ?></td>
                                        <td><?= $student['name'] ?></td>
                                        <td><?= $student['surname'] ?></td>
                                        <td><?= $student['email'] ?></td>
                                        <td class="text-center">
                                            <!-- Link to Add Grade for this student -->
                                            <a href="students.php?student_id=<?= $student['id'] ?>" class="btn btn-info btn-sm" title="Add Grade">
                                                <i class="fa fa-plus"></i> Grade
                                            </a>
                                        </td>
                                        <td class="text-center">
                                            <!-- Link to Edit Student -->
                                            <a href="./update_student.php?id=<?= $student['id'] ?>" class="btn btn-warning btn-sm" title="Edit Student">
                                                <i class="fa fa-edit"></i> Edit
                                            </a>
                                        </td>
                                        <td class="text-center">
                                            <!-- Link to Delete Student (with confirmation) -->
                                            <a href="./delete_student.php?id=<?= $student['id'] ?>" class="btn btn-danger btn-sm" title="Delete Student" onclick="return confirm('Are you sure you want to delete <?= addslashes($student['name'] . ' ' . $student['surname']) ?>? This action cannot be undone.');">
                                                <i class="fa fa-trash"></i> Delete
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
                 <!-- Add button again at the bottom for convenience -->
                 <p class="mt-3"><a href="add_student.php" class="btn btn-success"><i class="fa fa-plus"></i> Add New Student</a></p>

            <?php endif; // End main if/else for form vs list ?>

        </div>
    </div>

<?php include 'footer.php'; ?>
