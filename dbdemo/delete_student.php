<?php
// Set page titles for header.php
$pageTitle = "Delete Student";
$navBarTitle = "Databases PHP Demo - Delete Student";
include 'header.php';

include 'db_connection.php'; // Include connection file
$conn = OpenCon(); // Establish connection

$student_id = null;
$student_name = '';
$student_surname = '';
$error_message = '';
$success_message = '';
$show_confirmation = false;

// --- Input Validation & Fetching Data ---
if (isset($_GET['id']) && filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    $student_id = (int)$_GET['id'];

    // Fetch student details using prepared statement
    $stmt_select = $conn->prepare("SELECT name, surname FROM students WHERE id = ?");
    if ($stmt_select) {
        $stmt_select->bind_param("i", $student_id);
        $stmt_select->execute();
        $result_select = $stmt_select->get_result();
        if ($row = $result_select->fetch_assoc()) {
            $student_name = htmlspecialchars($row['name']);
            $student_surname = htmlspecialchars($row['surname']);
            $show_confirmation = true; // Student found, show confirmation form
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

// --- Handle Deletion on POST ---
if ($show_confirmation && isset($_POST['submit_del']) && $student_id !== null) {
    // Use prepared statement for DELETE
    // Consider foreign key constraints (ON DELETE CASCADE or SET NULL might be needed in DB schema)
    $stmt_delete = $conn->prepare("DELETE FROM students WHERE id = ?");
    if ($stmt_delete) {
        $stmt_delete->bind_param("i", $student_id);

        if ($stmt_delete->execute()) {
            $success_message = 'Record deleted successfully - Redirecting back in 5 seconds...<br>';
            $success_message .= '<a href="./students.php">Click here to go back to all students now</a>';
            // Add JavaScript redirect using function from footer.php
            echo '<script type="text/javascript">setTimeout(function() { delayer("./students.php"); }, 5000);</script>';
            $show_confirmation = false; // Hide form after successful deletion
        } else {
            // Provide more specific error if possible (e.g., foreign key constraint)
            $error_message = "Error while deleting record: " . htmlspecialchars($stmt_delete->error);
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
                // Optionally provide a back link if there was an error finding the student
                if (!$show_confirmation && strpos($error_message, 'ID') !== false) {
                     echo '<a href="students.php" class="btn btn-secondary mt-2">Back to Students</a>';
                }
            }
            if (!empty($success_message)) {
                echo '<div class="alert alert-success mt-3" role="alert">' . $success_message . '</div>';
            }
            ?>

            <?php if ($show_confirmation): ?>
                <form class="form-horizontal mt-3" name="student-delete-form" method="POST">
                     <div class="form-group col-sm-6 mb-3"> <!-- Adjusted width -->
                        <label class="form-label">Are you sure you want to delete student <br><b><?= $student_name . ' ' . $student_surname ?>?</b></label>
                        <p class="text-muted"><small>(Note: This action might also delete associated grades depending on database setup.)</small></p>
                    </div>
                    <button class="btn btn-danger btn-submit-custom" type="submit" name="submit_del">Delete</button>
                    <a href="students.php" class="btn btn-secondary btn-submit-custom">Cancel</a> <!-- Use link for Cancel -->
                </form>
            <?php endif; ?>
        </div>
    </div>

<?php include 'footer.php'; ?>
