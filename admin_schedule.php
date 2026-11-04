<?php

require_once "backend/auth_check.php";
require_once "backend/database.php";


/* EDIT MODE */

$editSchedule = null;

if (isset($_GET["edit"])) {

    $id = intval($_GET["edit"]);

    if ($id > 0) {

        $stmt = $pdo->prepare(
            "SELECT * FROM schedules WHERE id = ?"
        );

        $stmt->execute([$id]);

        $editSchedule = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}


/* ADD / UPDATE EXAM SCHEDULE */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $id = intval($_POST["id"] ?? 0);

    $program = trim($_POST["program"] ?? "");
    $yearLevel = trim($_POST["year_level"] ?? "");
    $subjectCourse = trim($_POST["subject_course"] ?? "");
    $examDate = trim($_POST["exam_date"] ?? "");
    $timeSlot = trim($_POST["time_slot"] ?? "");
    $roomNumber = trim($_POST["room_number"] ?? "");
    $proctor = trim($_POST["proctor"] ?? "");


    if (
        $program !== "" &&
        $yearLevel !== "" &&
        $subjectCourse !== "" &&
        $examDate !== "" &&
        $timeSlot !== "" &&
        $roomNumber !== "" &&
        $proctor !== ""
    ) {

        /* UPDATE */

        if ($id > 0) {

            $stmt = $pdo->prepare(
                "UPDATE schedules
                 SET
                    program = ?,
                    year_level = ?,
                    subject_course = ?,
                    exam_date = ?,
                    time_slot = ?,
                    room_number = ?,
                    proctor = ?
                 WHERE id = ?"
            );

            $stmt->execute([
                $program,
                $yearLevel,
                $subjectCourse,
                $examDate,
                $timeSlot,
                $roomNumber,
                $proctor,
                $id
            ]);

        }

        /* ADD */

        else {

            $stmt = $pdo->prepare(
                "INSERT INTO schedules
                (
                    program,
                    year_level,
                    subject_course,
                    exam_date,
                    time_slot,
                    room_number,
                    proctor
                )
                VALUES (?, ?, ?, ?, ?, ?, ?)"
            );

            $stmt->execute([
                $program,
                $yearLevel,
                $subjectCourse,
                $examDate,
                $timeSlot,
                $roomNumber,
                $proctor
            ]);
        }

        header("Location: admin_schedule.php");
        exit;
    }
}


/* DELETE EXAM SCHEDULE */

if (isset($_GET["delete"])) {

    $id = intval($_GET["delete"]);

    if ($id > 0) {

        $stmt = $pdo->prepare(
            "DELETE FROM schedules WHERE id = ?"
        );

        $stmt->execute([$id]);
    }

    header("Location: admin_schedule.php");
    exit;
}


/* GET ALL SCHEDULES */

$stmt = $pdo->query(
    "SELECT * FROM schedules
     ORDER BY exam_date ASC, time_slot ASC"
);

$schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
Manage Exam Schedule - SMCBI
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

    padding: 18px 25px;

    display: flex;

    justify-content: space-between;

    align-items: center;
}

header h1 {

    margin: 0;

    font-size: 22px;
}

.logout {

    background: #dc2626;

    color: white;

    padding: 9px 15px;

    border-radius: 6px;

    text-decoration: none;
}


/* CONTAINER */

.container {

    max-width: 1100px;

    margin: 30px auto;

    padding: 0 20px;
}


/* CARD */

.card {

    background: white;

    padding: 25px;

    border-radius: 10px;

    margin-bottom: 25px;

    box-shadow:
        0 2px 8px rgba(0,0,0,0.08);
}

.card h2 {

    margin-top: 0;

    color: #1d4ed8;
}


/* FORM */

.form-grid {

    display: grid;

    grid-template-columns:
        repeat(2, 1fr);

    gap: 15px;
}

.form-group {

    display: flex;

    flex-direction: column;
}

.form-group.full {

    grid-column: 1 / -1;
}

label {

    font-weight: bold;

    margin-bottom: 6px;
}

input {

    padding: 11px;

    border: 1px solid #ccc;

    border-radius: 6px;

    font-size: 14px;
}

button {

    margin-top: 18px;

    padding: 11px 20px;

    border: none;

    border-radius: 6px;

    background: #1d4ed8;

    color: white;

    cursor: pointer;

    font-size: 14px;
}

button:hover {

    background: #1e40af;
}

.cancel {

    display: inline-block;

    margin-left: 8px;

    padding: 11px 20px;

    background: #6b7280;

    color: white;

    border-radius: 6px;

    text-decoration: none;

    font-size: 14px;
}

.cancel:hover {

    background: #4b5563;
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


/* ACTION BUTTONS */

.edit {

    display: inline-block;

    background: #f59e0b;

    color: white;

    padding: 6px 10px;

    border-radius: 5px;

    text-decoration: none;

    font-size: 13px;

    margin-right: 5px;
}

.edit:hover {

    background: #d97706;
}

.delete {

    display: inline-block;

    background: #dc2626;

    color: white;

    padding: 6px 10px;

    border-radius: 5px;

    text-decoration: none;

    font-size: 13px;
}

.delete:hover {

    background: #b91c1c;
}


/* BACK */

.back {

    display: inline-block;

    margin-top: 10px;

    color: #1d4ed8;

    text-decoration: none;
}


/* MOBILE */

@media (max-width: 700px) {

    .form-grid {

        grid-template-columns: 1fr;
    }

    .form-group.full {

        grid-column: auto;
    }

}

</style>

</head>


<body>


<header>

<h1>
Manage Exam Schedule
</h1>

<a
    href="backend/logout.php"
    class="logout"
>
Logout
</a>

</header>


<div class="container">


<!-- ADD / EDIT SCHEDULE -->

<div class="card">

<h2>

<?php if ($editSchedule): ?>

✏️ Edit Exam Schedule

<?php else: ?>

📅 Add Exam Schedule

<?php endif; ?>

</h2>


<form method="POST">


<input
    type="hidden"
    name="id"
    value="<?= $editSchedule
        ? htmlspecialchars($editSchedule["id"])
        : "" ?>"
>


<div class="form-grid">


<div class="form-group">

<label>
Program
</label>

<input
    type="text"
    name="program"
    placeholder="Example: BSIT"
    value="<?= $editSchedule
        ? htmlspecialchars($editSchedule["program"])
        : "" ?>"
    required
>

</div>


<div class="form-group">

<label>
Year Level
</label>

<input
    type="text"
    name="year_level"
    placeholder="Example: 2B"
    value="<?= $editSchedule
        ? htmlspecialchars($editSchedule["year_level"])
        : "" ?>"
    required
>

</div>


<div class="form-group full">

<label>
Subject / Course
</label>

<input
    type="text"
    name="subject_course"
    placeholder="Example: IT 213 - Information Management"
    value="<?= $editSchedule
        ? htmlspecialchars($editSchedule["subject_course"])
        : "" ?>"
    required
>

</div>


<div class="form-group">

<label>
Exam Date
</label>

<input
    type="date"
    name="exam_date"
    value="<?= $editSchedule
        ? htmlspecialchars($editSchedule["exam_date"])
        : "" ?>"
    required
>

</div>


<div class="form-group">

<label>
Time Slot
</label>

<input
    type="text"
    name="time_slot"
    placeholder="Example: 8:00 AM - 10:00 AM"
    value="<?= $editSchedule
        ? htmlspecialchars($editSchedule["time_slot"])
        : "" ?>"
    required
>

</div>


<div class="form-group">

<label>
Room Number
</label>

<input
    type="text"
    name="room_number"
    placeholder="Example: Room 204"
    value="<?= $editSchedule
        ? htmlspecialchars($editSchedule["room_number"])
        : "" ?>"
    required
>

</div>


<div class="form-group">

<label>
Proctor
</label>

<input
    type="text"
    name="proctor"
    placeholder="Example: Juan Dela Cruz"
    value="<?= $editSchedule
        ? htmlspecialchars($editSchedule["proctor"])
        : "" ?>"
    required
>

</div>


</div>


<?php if ($editSchedule): ?>

<button type="submit">
Update Exam Schedule
</button>

<a
    href="admin_schedule.php"
    class="cancel"
>
Cancel
</a>

<?php else: ?>

<button type="submit">
Add Exam Schedule
</button>

<?php endif; ?>


</form>

</div>



<!-- EXISTING SCHEDULES -->

<div class="card">

<h2>
📋 Existing Exam Schedules
</h2>


<?php if (count($schedules) === 0): ?>

<p>
No exam schedules available.
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

<th>
Action
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

<td>

<a
    class="edit"
    href="admin_schedule.php?edit=<?= $schedule["id"] ?>"
>
Edit
</a>


<a
    class="delete"
    href="admin_schedule.php?delete=<?= $schedule["id"] ?>"
    onclick="return confirm('Delete this exam schedule?');"
>
Delete
</a>

</td>

</tr>

<?php endforeach; ?>


</tbody>

</table>

</div>


<?php endif; ?>


<a
    href="admin.php"
    class="back"
>
← Back to Admin Dashboard
</a>

</div>


</div>


</body>

</html>