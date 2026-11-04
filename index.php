<?php

require_once "backend/database.php";


/* GET ANNOUNCEMENTS */

$announcementStmt = $pdo->query(
    "SELECT * FROM announcements ORDER BY created_at DESC"
);

$announcements = $announcementStmt->fetchAll(PDO::FETCH_ASSOC);


/* SEARCH AND FILTER */

$search = trim($_GET["search"] ?? "");
$programFilter = trim($_GET["program"] ?? "");
$yearFilter = trim($_GET["year_level"] ?? "");


$sql = "SELECT * FROM schedules WHERE 1=1";

$params = [];


/* GENERAL SEARCH */

if ($search !== "") {

    $sql .= "
        AND (
            program LIKE ?
            OR year_level LIKE ?
            OR subject_course LIKE ?
            OR room_number LIKE ?
            OR proctor LIKE ?
        )
    ";

    $searchValue = "%" . $search . "%";

    $params[] = $searchValue;
    $params[] = $searchValue;
    $params[] = $searchValue;
    $params[] = $searchValue;
    $params[] = $searchValue;
}


/* PROGRAM FILTER */

if ($programFilter !== "") {

    $sql .= " AND program = ?";

    $params[] = $programFilter;
}


/* YEAR LEVEL FILTER */

if ($yearFilter !== "") {

    $sql .= " AND year_level = ?";

    $params[] = $yearFilter;
}


$sql .= "
    ORDER BY exam_date ASC, time_slot ASC
";


$scheduleStmt = $pdo->prepare($sql);

$scheduleStmt->execute($params);

$schedules = $scheduleStmt->fetchAll(PDO::FETCH_ASSOC);


/* GET PROGRAMS */

$programStmt = $pdo->query(
    "SELECT DISTINCT program
     FROM schedules
     ORDER BY program ASC"
);

$programs = $programStmt->fetchAll(PDO::FETCH_COLUMN);


/* GET YEAR LEVELS */

$yearStmt = $pdo->query(
    "SELECT DISTINCT year_level
     FROM schedules
     ORDER BY year_level ASC"
);

$yearLevels = $yearStmt->fetchAll(PDO::FETCH_COLUMN);

?>


<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>
SMCBI Digital Bulletin Board
</title>


<style>

* {
    box-sizing: border-box;
}

body {

    margin: 0;

    font-family: Arial, sans-serif;

    background: #f3f4f6;

    color: #222;
}


/* HEADER */

header {

    background: #1d4ed8;

    color: white;

    padding: 20px;

    text-align: center;
}

header h1 {

    margin: 0;

    font-size: 28px;
}

header p {

    margin: 6px 0 0;
}


/* NAVIGATION */

nav {

    background: white;

    padding: 12px;

    text-align: center;

    box-shadow:
        0 2px 6px rgba(0,0,0,0.08);
}

nav a {

    text-decoration: none;

    color: #1d4ed8;

    margin: 0 15px;

    font-weight: bold;
}


/* CONTAINER */

.container {

    max-width: 1100px;

    margin: 30px auto;

    padding: 0 20px;
}


/* SECTION */

.section {

    background: white;

    padding: 25px;

    margin-bottom: 25px;

    border-radius: 10px;

    box-shadow:
        0 2px 8px rgba(0,0,0,0.08);
}

.section h2 {

    margin-top: 0;

    color: #1d4ed8;
}


/* ANNOUNCEMENT */

.announcement {

    border: 1px solid #ddd;

    padding: 18px;

    border-radius: 8px;

    margin-bottom: 15px;
}

.announcement h3 {

    margin-top: 0;
}

.category {

    display: inline-block;

    background: #1d4ed8;

    color: white;

    padding: 5px 10px;

    border-radius: 5px;

    font-size: 12px;
}

.announcement img {

    max-width: 100%;

    max-height: 300px;

    display: block;

    margin-top: 15px;

    border-radius: 8px;
}

.announcement a {

    color: #1d4ed8;

    font-weight: bold;
}


/* SEARCH AREA */

.search-box {

    background: #f9fafb;

    border: 1px solid #ddd;

    padding: 18px;

    border-radius: 8px;

    margin-bottom: 20px;
}

.search-grid {

    display: grid;

    grid-template-columns:
        2fr 1fr 1fr auto;

    gap: 10px;
}

.search-grid input,
.search-grid select {

    padding: 11px;

    border: 1px solid #ccc;

    border-radius: 6px;

    font-size: 14px;
}

.search-button {

    border: none;

    padding: 11px 18px;

    border-radius: 6px;

    background: #1d4ed8;

    color: white;

    cursor: pointer;
}

.search-button:hover {

    background: #1e40af;
}

.clear-button {

    display: inline-block;

    margin-top: 10px;

    color: #dc2626;

    text-decoration: none;

    font-size: 14px;
}


/* TABLE */

.table-container {

    overflow-x: auto;
}

table {

    width: 100%;

    border-collapse: collapse;
}

th,
td {

    border: 1px solid #ddd;

    padding: 10px;

    text-align: left;
}

th {

    background: #1d4ed8;

    color: white;
}


/* EMPTY */

.empty {

    color: #777;

    text-align: center;

    padding: 20px;
}


/* FOOTER */

footer {

    background: #1d4ed8;

    color: white;

    text-align: center;

    padding: 18px;

    margin-top: 30px;
}


/* MOBILE */

@media (max-width: 750px) {

    .search-grid {

        grid-template-columns: 1fr;
    }

}

</style>

</head>


<body>


<header>

<h1>
SMCBI Digital Bulletin Board
</h1>

<p>
School Information System
</p>

</header>


<nav>

<a href="#announcements">
Announcements
</a>

<a href="#schedule">
Exam Schedule
</a>

</nav>


<div class="container">


<!-- ANNOUNCEMENTS -->

<div
    class="section"
    id="announcements"
>

<h2>
📢 School Announcements
</h2>


<?php if (count($announcements) === 0): ?>

<p class="empty">
No announcements available.
</p>


<?php else: ?>


<?php foreach ($announcements as $announcement): ?>

<div class="announcement">

<h3>
<?= htmlspecialchars($announcement["title"]) ?>
</h3>


<span class="category">

<?= htmlspecialchars($announcement["category"]) ?>

</span>


<p>

<?= nl2br(
    htmlspecialchars($announcement["content"])
) ?>

</p>


<?php if (!empty($announcement["image_path"])): ?>

<img
    src="<?= htmlspecialchars($announcement["image_path"]) ?>"
    alt="Announcement Image"
>

<?php endif; ?>


<?php if (!empty($announcement["file_path"])): ?>

<p>

📎

<a
    href="<?= htmlspecialchars($announcement["file_path"]) ?>"
    target="_blank"
>

<?= htmlspecialchars(
    $announcement["file_name"]
) ?>

</a>

</p>

<?php endif; ?>


<small>

Posted:
<?= htmlspecialchars($announcement["created_at"]) ?>

</small>

</div>

<?php endforeach; ?>


<?php endif; ?>

</div>



<!-- EXAM SCHEDULE -->

<div
    class="section"
    id="schedule"
>

<h2>
📅 Exam Schedule
</h2>


<!-- SEARCH -->

<div class="search-box">

<form
    method="GET"
    action="index.php#schedule"
>

<div class="search-grid">


<input
    type="text"
    name="search"
    placeholder="Search program, subject, room, proctor..."
    value="<?= htmlspecialchars($search) ?>"
>


<select name="program">

<option value="">
All Programs
</option>


<?php foreach ($programs as $program): ?>

<option
    value="<?= htmlspecialchars($program) ?>"
    <?= $programFilter === $program ? "selected" : "" ?>
>

<?= htmlspecialchars($program) ?>

</option>

<?php endforeach; ?>

</select>


<select name="year_level">

<option value="">
All Year Levels
</option>


<?php foreach ($yearLevels as $yearLevel): ?>

<option
    value="<?= htmlspecialchars($yearLevel) ?>"
    <?= $yearFilter === $yearLevel ? "selected" : "" ?>
>

<?= htmlspecialchars($yearLevel) ?>

</option>

<?php endforeach; ?>

</select>


<button
    type="submit"
    class="search-button"
>
🔎 Search
</button>


</div>


<?php if (
    $search !== "" ||
    $programFilter !== "" ||
    $yearFilter !== ""
): ?>

<a
    href="index.php#schedule"
    class="clear-button"
>
Clear Search
</a>

<?php endif; ?>


</form>

</div>


<!-- RESULTS -->


<?php if (count($schedules) === 0): ?>

<p class="empty">

No exam schedules found.

</p>


<?php else: ?>


<div class="table-container">

<table>

<thead>

<tr>

<th>
Program
</th>

<th>
Year Level
</th>

<th>
Subject / Course
</th>

<th>
Exam Date
</th>

<th>
Time
</th>

<th>
Room
</th>

<th>
Proctor
</th>

</tr>

</thead>


<tbody>


<?php foreach ($schedules as $schedule): ?>

<tr>

<td>
<?= htmlspecialchars($schedule["program"]) ?>
</td>

<td>
<?= htmlspecialchars($schedule["year_level"]) ?>
</td>

<td>
<?= htmlspecialchars($schedule["subject_course"]) ?>
</td>

<td>
<?= htmlspecialchars($schedule["exam_date"]) ?>
</td>

<td>
<?= htmlspecialchars($schedule["time_slot"]) ?>
</td>

<td>
<?= htmlspecialchars($schedule["room_number"]) ?>
</td>

<td>
<?= htmlspecialchars($schedule["proctor"]) ?>
</td>

</tr>

<?php endforeach; ?>


</tbody>

</table>

</div>


<?php endif; ?>

</div>


</div>


<footer>

SMCBI Digital Bulletin Board

</footer>


</body>

</html>