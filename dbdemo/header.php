<?php
// header.php
// session_start(); // Uncomment if using sessions

// Set default values if variables aren't passed from the including page
$pageTitle = isset($pageTitle) ? $pageTitle : "Databases PHP Demo";
$navBarTitle = isset($navBarTitle) ? $navBarTitle : "Databases PHP Demo";
$homeLink = isset($homeLink) ? $homeLink : "index.php"; // Default home link
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
</head>

<body>
    <nav class="navbar navbar-light navbar-expand-md" id="nav-bar">
        <div id="navbar-div" class="container-fluid">
            <a class="navbar-brand" id="nav-bar-text"><?= htmlspecialchars($navBarTitle) ?></a>
            <a id="navbar-items" href="<?= htmlspecialchars($homeLink) ?>">
                <i class="fa fa-home "></i> Home
            </a>
        </div>
    </nav>

    <div class="container"> <!-- Start the main container (closed in footer.php) -->
