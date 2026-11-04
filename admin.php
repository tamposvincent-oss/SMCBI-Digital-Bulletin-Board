<?php

require_once "backend/auth_check.php";

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
Admin Dashboard - SMCBI Bulletin Board
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

.container {
    max-width: 1100px;

    margin: 30px auto;

    padding: 0 20px;
}

.welcome {
    background: white;

    padding: 20px;

    border-radius: 10px;

    margin-bottom: 20px;

    box-shadow:
        0 2px 8px rgba(0,0,0,0.08);
}

.cards {
    display: grid;

    grid-template-columns:
        repeat(
            auto-fit,
            minmax(250px, 1fr)
        );

    gap: 20px;
}

.card {
    background: white;

    padding: 25px;

    border-radius: 10px;

    box-shadow:
        0 2px 8px rgba(0,0,0,0.08);
}

.card h2 {
    margin-top: 0;
}

.card p {
    color: #666;
}

button {
    border: none;

    padding: 11px 18px;

    border-radius: 6px;

    background: #1d4ed8;

    color: white;

    cursor: pointer;
}

button:hover {
    background: #1e40af;
}

.back {
    display: inline-block;

    margin-top: 25px;

    color: #1d4ed8;

    text-decoration: none;
}

</style>

</head>

<body>

<header>

<h1>
SMCBI Admin Dashboard
</h1>

<a
    href="backend/logout.php"
    class="logout"
>
Logout
</a>

</header>

<div class="container">

<div class="welcome">

<h2>
Welcome, Admin!
</h2>

<p>
From this dashboard, you will be able to manage
announcements and exam schedules.
</p>

</div>

<div class="cards">

<div class="card">

<h2>
📢 Announcements
</h2>

<p>
Add, edit, and delete school announcements.
</p>

<button
    onclick="location.href='admin_announcements.php'"
>
Manage Announcements
</button>

</div>


<div class="card">

<h2>
📅 Exam Schedule
</h2>

<p>
Upload and manage examination schedules.
</p>

<button
    onclick="location.href='admin_schedule.php'"
>
Manage Schedule
</button>

</div>

</div>

<a
    href="index.php"
    class="back"
>
← Back to Student Bulletin Board
</a>

</div>

</body>

</html>