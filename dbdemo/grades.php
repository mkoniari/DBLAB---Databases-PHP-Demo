<?php
// Set page titles for header.php
$pageTitle = "Grades List";
$navBarTitle = "Databases PHP Demo - Grades";
include 'header.php';
?>

    <div class="row" id="row">
        <div class="col-md-12">
            <div class="card" id="card-container">
                 <div class="card-header d-flex justify-content-between align-items-center">
                    Grades List
                    <!-- Optional: Add link to create grades if you have such a page -->
                    <!-- <a href="create_grade.php" class="btn btn-success btn-sm"><i class="fa fa-plus"></i> Add New Grade</a> -->
                 </div>
                <div class="card-body" id="card">
                    <?php
                    include 'db_connection.php';
                    $conn = OpenCon();

                    // Join with students table to show student name - more user friendly
                    $query = "SELECT g.id as grade_id, g.course_name, g.grade, g.student_id, s.name, s.surname
                              FROM grades g
                              JOIN students s ON g.student_id = s.id
                              ORDER BY g.student_id"; 
                    $result = mysqli_query($conn, $query);

                    if (!$result) {
                         echo '<div class="alert alert-danger">Error fetching grades: ' . htmlspecialchars(mysqli_error($conn)) . '</div>';
                    } elseif (mysqli_num_rows($result) == 0) {
                        echo '<div class="alert alert-info">No grades found in the system.</div>';
                    } else {
                        echo '<div class="table-responsive">';
                        echo '<table class="table table-striped table-hover">'; // Added Bootstrap table classes
                        echo '<thead class="thead-light">'; // Added thead class
                        echo '<tr>';
                        echo '<th>Grade ID</th>';
                        echo '<th>Student Name</th>';
                        echo '<th>Course Name</th>';
                        echo '<th>Grade</th>';
                        // echo '<th>Student ID</th>'; // Maybe hide this if name is shown
                        echo '<th class="text-center">Actions</th>'; // Combined actions
                        echo '</tr>';
                        echo '</thead>';
                        echo '<tbody>';
                        while ($row = mysqli_fetch_assoc($result)) { // Use fetch_assoc
                            echo '<tr>';
                            // Use htmlspecialchars for all output from DB
                            echo '<td>' . htmlspecialchars($row['grade_id']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['surname']) . ', ' . htmlspecialchars($row['name']) . ' (ID: ' . htmlspecialchars($row['student_id']) . ')</td>';
                            echo '<td>' . htmlspecialchars($row['course_name']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['grade']) . '</td>';
                            // echo '<td>' . htmlspecialchars($row['student_id']) . '</td>';
                            echo '<td class="text-center">';
                            // Edit link
                            echo '<a href="./update_grade.php?id=' . urlencode($row['student_id']) . '&grade_id=' . urlencode($row['grade_id']) . '" class="btn btn-sm btn-info me-1" title="Edit Grade">'; // Added classes and margin
                            echo '<i class="fa fa-edit"></i>';
                            echo '</a>';
                            // Delete link
                            echo '<a href="./delete_grade.php?id=' . urlencode($row['student_id']) . '&grade_id=' . urlencode($row['grade_id']) . '" class="btn btn-sm btn-danger" title="Delete Grade" onclick="return confirm(\'Are you sure you want to delete this grade?\');">'; // Added confirmation
                            echo '<i class="fa fa-trash"></i>';
                            echo '</a>';
                            echo '</td>';
                            echo '</tr>';
                        }
                        echo '</tbody>';
                        echo '</table>';
                        echo '</div>';
                        mysqli_free_result($result); // Free result set
                    }
                    CloseCon($conn); // Close connection
                    ?>
                </div>
            </div>
        </div>
    </div>

<?php include 'footer.php'; ?>
