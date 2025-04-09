<?php
// Set page titles for header.php
$pageTitle = "Create New Student";
$navBarTitle = "Databases PHP Demo - Create Student";
// $homeLink = "index.php"; // You can override the default home link here if needed
include 'header.php';
?>

    <div class="row" id="row">
        <div class="col-md-12">
            <form class="form-horizontal" name="student-form" method="POST">
                <div class="form-group col-sm-3 mb-3">
                    <label class = "form-label">Name</label>
                    <input class = "form-control" placeholder="Enter first name" name="name" required>
                    
                </div>
                <div class="form-group col-sm-3 mb-3">
                    <label class = "form-label">Surname</label>
                    <input class = "form-control" placeholder="Enter last name" name="surname" required>
                </div>
                <div class="form-group col-sm-3 mb-3">
                    <label class = "form-label">Email address</label>
                    <input type="email" class = "form-control" placeholder="e.g. you@example.com" name="email" required> <!-- Changed type to email for basic validation -->
                </div>
                <button class = "btn btn-primary btn-submit-custom" type="submit" name="submit_creds">Submit</button>
                <!-- Changed formaction to a simple link for clarity if it's just navigation -->
                <a href="students.php" class="btn btn-secondary btn-submit-custom">Back to Students</a> <!-- Changed link to students.php -->
            </form>
        </div>

        <!-- Moved PHP processing logic outside the form structure but within the row -->
        <div class="col-md-12 mt-3"> <!-- Added margin-top for spacing -->
            <?php
                // Include the database connection only when needed
                if(isset($_POST['submit_creds'])){
                    include_once 'db_connection.php'; // Use include_once for db connection
                    $conn = OpenCon(); // Establish connection

                    // Get data using null coalescing operator and trim whitespace
                    $first_name = trim($_POST['name'] ?? '');
                    $last_name = trim($_POST['surname'] ?? '');
                    $email = trim($_POST['email'] ?? '');

                    // Server-side validation
                    $errors = [];
                    if (empty($first_name)) {
                        $errors[] = "Name is required.";
                    }
                    if (empty($last_name)) {
                         $errors[] = "Surname is required.";
                    }
                    if (empty($email)) {
                         $errors[] = "Email is required.";
                    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $errors[] = "Invalid email format.";
                    }

                    // Check for existing email (optional but good practice)
                    // Note: This adds another DB query. Consider if needed for your application.
                    if (empty($errors) && !empty($email)) {
                        $stmt_check = $conn->prepare("SELECT id FROM students WHERE email = ?");
                        if ($stmt_check) {
                            $stmt_check->bind_param("s", $email);
                            $stmt_check->execute();
                            $stmt_check->store_result(); // Needed for num_rows
                            if ($stmt_check->num_rows > 0) {
                                $errors[] = "Email address already exists.";
                            }
                            $stmt_check->close();
                        } else {
                             $errors[] = "Error checking email uniqueness: " . htmlspecialchars($conn->error);
                        }
                    }


                    // If no errors, proceed with insertion
                    if (empty($errors)) {
                        // Use prepared statements to prevent SQL injection
                        $stmt = $conn->prepare("INSERT INTO students (name, surname, email) VALUES (?, ?, ?)");
                        // Check if prepare() failed
                        if ($stmt === false) {
                            echo '<div class="alert alert-danger" role="alert">Error preparing statement: ' . htmlspecialchars($conn->error) . '</div>';
                        } else {
                            // Bind parameters (s = string)
                            $stmt->bind_param("sss", $first_name, $last_name, $email);

                            // Execute the statement
                            if ($stmt->execute()) {
                                echo '<div class="alert alert-success" role="alert">Record created successfully - Redirecting to students list in 5 seconds...<br>';
                                echo '<a href="./students.php">Click here to go back now</a></div>';
                                // Use the delayer function defined in footer.php
                                echo '<script type="text/javascript">setTimeout(function() { delayer("./students.php"); }, 5000);</script>';

                                // Clear form fields after successful submission (optional, JS redirect handles this)
                                $_POST = array(); // Clear POST array to prevent resubmission issues on refresh

                            } else {
                                echo '<div class="alert alert-danger" role="alert">Error while creating record: ' . htmlspecialchars($stmt->error) . '</div>';
                            }
                            // Close the statement
                            $stmt->close();
                        }
                    } else {
                        // Display validation errors
                        echo '<div class="alert alert-danger" role="alert"><strong>Error(s):</strong><br><ul>';
                        foreach ($errors as $error) {
                            echo '<li>' . htmlspecialchars($error) . '</li>';
                        }
                        echo '</ul></div>';
                    }
                    CloseCon($conn); // Close connection
                }
            ?>
        </div>
    </div>

<?php include 'footer.php'; ?>
