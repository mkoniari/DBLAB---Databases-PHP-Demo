<?php
// Set variables for the header
$pageTitle = "Databases PHP Demo - Home";
$navBarTitle = "Databases PHP Demo - Landing Page";
// $homeLink = "index.php"; // Default in header.php is fine

// Include the header
include 'header.php';

// Include database connection - needed for the grade display sections
include_once 'db_connection.php'; // Use include_once to prevent re-declaration if header/footer also need it later
$conn = OpenCon(); // Open the connection once for the page
?>

<!-- Start of page-specific content -->
<!-- Note: The surrounding <div class="container"> is provided by header.php/footer.php -->

<div class="row" id="row">
    <div class="col-md-4">
        <div class="card" id="card-container-layout">
            <div class="card-body" id="card">
                <h4 class="card-title">View Students</h4>
                <p class="card-text" id="paragraph">Simple Query to database to show all students</p>
                <a class="btn btn-primary" id="show-btn" href="students.php">Show</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card" id="card-container-layout">
            <div class="card-body" id="card">
                <h4 class="card-title">View Grades</h4>
                <p class="card-text" id="paragraph">Simple Query to database to show students' grades<br></p>
                <a class="btn btn-primary" id="show-btn" href="grades.php">Show</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card" id="card-container-layout">
            <div class="card-body" id="card">
                <h4 class="card-title">Create Student</h4>
                <p class="card-text" id="paragraph">Enter a new student into the database<br></p>
                <a class="btn btn-primary" id="show-btn" href="create_student.php">Create</a>
            </div>
        </div>
    </div>
</div>

<div class="row" id="row-2">
    <div class="col-md-6">
        <div class="card" id="card-container-layout">
            <div class="card-body" id="card">
                <h4 class="card-title">Best Dribbling Grade</h4>
                <p class="card-text" id="paragraph">
                    <?php
                        // Connection $conn is already open

                        $query_dri = "SELECT g.grade, s.name, s.surname
                        FROM students s INNER JOIN grades g ON g.student_id = s.id
                        WHERE g.course_name = 'DRI'
                        ORDER BY g.grade DESC LIMIT 1";

                        $result_dri = mysqli_query($conn, $query_dri);

                        // Added more robust checking
                        if ($result_dri) {
                            if($best_dribbling_grade = mysqli_fetch_row($result_dri)){
                                echo 'The best Dribbling grade is ' . htmlspecialchars($best_dribbling_grade[0]) . ' and belongs to ' . htmlspecialchars($best_dribbling_grade[1]) . ' ' . htmlspecialchars($best_dribbling_grade[2]);
                                mysqli_free_result($result_dri); // Free result
                            } else {
                                echo 'There does not exist a student with a Dribbling Grade';
                            }
                        } else {
                             echo 'Error executing query: ' . htmlspecialchars(mysqli_error($conn));
                        }
                    ?>
                </p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card" id="card-container-layout">
            <div class="card-body" id="card">
                <h4 class="card-title">Best Shooting Grade</h4>
                <p class="card-text" id="paragraph">
                    <?php
                        // Reuse the $conn variable opened earlier

                        $query_sho = "SELECT g.grade, s.name, s.surname
                                      FROM students s INNER JOIN grades g ON g.student_id = s.id
                                      WHERE g.course_name = 'SHO'
                                      ORDER BY g.grade DESC LIMIT 1";
                        $result_sho = mysqli_query($conn, $query_sho); // Execute the query

                        // Check if the query was successful and returned a row
                        if ($result_sho) {
                            if ($best_shooting_grade = mysqli_fetch_row($result_sho)) {
                                // Use htmlspecialchars for security
                                echo 'The best Shooting grade is ' . htmlspecialchars($best_shooting_grade[0]) . ' and belongs to ' . htmlspecialchars($best_shooting_grade[1]) . ' ' . htmlspecialchars($best_shooting_grade[2]);
                                mysqli_free_result($result_sho); // Free the result set
                            } else {
                                echo 'There does not exist a student with a Shooting Grade';
                            }
                        } else {
                            // Check if the query failed
                             echo 'Error executing query: ' . htmlspecialchars(mysqli_error($conn));
                        }

                        // Close the connection *once* after all queries on the page are done.
                        CloseCon($conn);

                    ?>
                </p>
            </div>
        </div>
    </div>
</div>
<!-- End of page-specific content -->

<?php
// Include the footer
include 'footer.php';
?>
