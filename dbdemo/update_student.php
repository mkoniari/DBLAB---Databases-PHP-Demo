<?php
// Set page titles for header.php
$pageTitle = "Update Student";
$navBarTitle = "Databases PHP Demo - Update Student";
include 'header.php';

include 'db_connection.php'; // Include connection file
$conn = OpenCon(); // Establish connection

$student_id = null;
$current_name = '';
$current_surname = '';
$current_email = '';
$error_message = '';
$success_message = '';
$show_form = false;

// --- Input Validation & Fetching Current Data ---
if (isset($_GET['id']) && filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    $student_id = (int)$_GET['id'];

    // Fetch current student details using prepared statement
    $stmt_select = $conn->prepare("SELECT name, surname, email FROM students WHERE id = ?");
    if ($stmt_select) {
        $stmt_select->bind_param("i", $student_id);
        $stmt_select->execute();
        $result_select = $stmt_select->get_result();
        if ($row = $result_select->fetch_assoc()) {
            $current_name = htmlspecialchars($row['name']);
            $current_surname = htmlspecialchars($row['surname']);
            $current_email = htmlspecialchars($row['email']);
            $show_form = true; // Student found, show the form
        } else {
            $error_message = "Error: Student with ID " . $student_id . " not found.";
        }
        $stmt_select->close();
    } else {
        $error_message = "Error preparing select statement: " . htmlspecialchars($conn->error);
    }

} else {
    $error_message = "Invalid or missing student ID provided.";
}

// --- Handle Update on POST ---
if ($show_form && isset($_POST['submit_upd']) && $student_id !== null) {
    // Get and sanitize input data
    $new_name = trim($_POST['name'] ?? ''); // Use null coalescing operator
    $new_surname = trim($_POST['surname'] ?? '');
    $new_email = trim($_POST['email'] ?? '');

    // Basic Validation
    if (empty($new_name) || empty($new_surname) || empty($new_email)) {
        $error_message = "All fields (New Name, New Surname, New Email) are required.";
    } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format provided.";
    } else {
        // Use prepared statement for UPDATE
        $stmt_update = $conn->prepare("UPDATE students SET name = ?, surname = ?, email = ? WHERE id = ?");
        if ($stmt_update) {
            $stmt_update->bind_param("sssi", $new_name, $new_surname, $new_email, $student_id);

            if ($stmt_update->execute()) {
                $success_message = 'Record updated successfully - Redirecting back in 5 seconds...<br>';
                $success_message .= '<a href="./students.php">Click here to go back to all students now</a>';
                // Update current values for display if needed, or just redirect
                $current_name = htmlspecialchars($new_name);
                $current_surname = htmlspecialchars($new_surname);
                $current_email = htmlspecialchars($new_email);
                // Add JavaScript redirect
                echo '<script type="text/javascript">setTimeout(function() { delayer("./students.php"); }, 5000);</script>';
                // Optionally hide form after success: $show_form = false; (but redirecting is better)
            } else {
                $error_message = "Error while updating record: " . htmlspecialchars($stmt_update->error);
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
                // Display success message above the form or confirmation area
                echo '<div class="alert alert-success mt-3" role="alert">' . $success_message . '</div>';
            }
            if (!empty($error_message)) {
                echo '<div class="alert alert-danger mt-3" role="alert">' . $error_message . '</div>';
                 if (!$show_form && strpos($error_message, 'ID') !== false) {
                     echo '<a href="students.php" class="btn btn-secondary mt-2">Back to Students</a>';
                }
            }
            ?>

            <?php if ($show_form): ?>
                <div class="form-group col-sm-6 mb-3 mt-3"> <!-- Adjusted width -->
                    <label class="form-label">Change information for student: <br><b><?= $current_name . ' ' . $current_surname ?></b> (<?= $current_email ?>)</label>
                    <hr>
                </div>

                <form class="form-horizontal" name="student-update-form" method="POST">
                    <div class="form-group col-sm-4 mb-3"> <!-- Adjusted width -->
                        <label class="form-label">New Name</label>
                        <input class="form-control" name="name" placeholder="Enter new name" value="<?= $current_name ?>" required>
                    </div>
                    <div class="form-group col-sm-4 mb-3"> <!-- Adjusted width -->
                        <label class="form-label">New Surname</label>
                        <input class="form-control" name="surname" placeholder="Enter new surname" value="<?= $current_surname ?>" required>
                    </div>
                    <div class="form-group col-sm-4 mb-3"> <!-- Adjusted width -->
                        <label class="form-label">New Email address</label>
                        <input type="email" class="form-control" name="email" placeholder="Enter new email@example.com" value="<?= $current_email ?>" required>
                    </div>
                    <button class="btn btn-primary btn-submit-custom" type="submit" name="submit_upd">Update</button>
                    <a href="students.php" class="btn btn-secondary btn-submit-custom">Back</a> <!-- Use link for Back -->
                </form>
            <?php endif; ?>

        </div>
    </div>

<?php include 'footer.php'; ?>
