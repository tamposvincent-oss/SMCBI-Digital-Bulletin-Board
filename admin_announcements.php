<?php

require_once "backend/auth_check.php";
require_once "backend/database.php";

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
        Manage Announcements - SMCBI
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

        .container {
            max-width: 1100px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 25px;

            box-shadow:
                0 2px 8px rgba(0,0,0,0.08);
        }

        h2 {
            margin-top: 0;
        }

        label {
            display: block;
            margin-top: 15px;
            margin-bottom: 6px;
            font-weight: bold;
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 11px;

            border: 1px solid #ccc;
            border-radius: 6px;

            font-family: Arial, sans-serif;
        }

        textarea {
            min-height: 130px;
            resize: vertical;
        }

        button {
            border: none;
            padding: 11px 18px;
            border-radius: 6px;

            background: #1d4ed8;
            color: white;

            cursor: pointer;
            margin-top: 18px;
        }

        button:hover {
            background: #1e40af;
        }

        .edit-btn {
            background: #f59e0b;
            margin-right: 5px;
        }

        .edit-btn:hover {
            background: #d97706;
        }

        .delete-btn {
            background: #dc2626;
        }

        .delete-btn:hover {
            background: #b91c1c;
        }

        .cancel-btn {
            background: #6b7280;
            margin-left: 5px;
        }

        .cancel-btn:hover {
            background: #4b5563;
        }

        .back {
            display: inline-block;
            margin-bottom: 20px;

            color: #1d4ed8;
            text-decoration: none;
        }

        .announcement {
            border: 1px solid #ddd;
            padding: 18px;
            border-radius: 8px;
            margin-top: 15px;
        }

        .announcement h3 {
            margin-top: 0;
        }

        .announcement img {
            max-width: 250px;
            max-height: 180px;
            object-fit: cover;

            margin-top: 10px;

            border-radius: 6px;
        }

        .current-file {
            background: #f3f4f6;
            padding: 10px;
            border-radius: 6px;
            margin-top: 8px;
        }

        .message {
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 15px;
            display: none;
        }

        .success {
            background: #dcfce7;
            color: #166534;
        }

        .error {
            background: #fee2e2;
            color: #991b1b;
        }

    </style>

</head>

<body>


<header>

    <h1>
        SMCBI Announcement Management
    </h1>

    <a
        href="backend/logout.php"
        style="
            color:white;
            text-decoration:none;
            background:#dc2626;
            padding:9px 15px;
            border-radius:6px;
        "
    >
        Logout
    </a>

</header>


<div class="container">


    <a
        href="admin.php"
        class="back"
    >
        ← Back to Admin Dashboard
    </a>


    <!-- ADD / EDIT ANNOUNCEMENT -->

    <div class="card">

        <h2 id="formTitle">
            📢 Add Announcement
        </h2>


        <div
            id="message"
            class="message"
        ></div>


        <form
            id="announcementForm"
            enctype="multipart/form-data"
        >

            <!-- ANNOUNCEMENT ID -->

            <input
                type="hidden"
                name="id"
                id="announcementId"
                value=""
            >


            <label>
                Title
            </label>

            <input
                type="text"
                name="title"
                id="announcementTitle"
                placeholder="Enter announcement title"
                required
            >


            <label>
                Category
            </label>

            <select
                name="category"
                id="announcementCategory"
                required
            >

                <option value="">
                    Select category
                </option>

                <option value="General">
                    General
                </option>

                <option value="Academic">
                    Academic
                </option>

                <option value="Exam">
                    Exam
                </option>

                <option value="Event">
                    Event
                </option>

                <option value="Important">
                    Important
                </option>

            </select>


            <label>
                Announcement
            </label>

            <textarea
                name="content"
                id="announcementContent"
                placeholder="Write your announcement here..."
                required
            ></textarea>


            <label>
                Image (Optional)
            </label>

            <input
                type="file"
                name="image"
                id="announcementImage"
                accept=".jpg,.jpeg,.png,.webp"
            >


            <label>
                Attachment File (Optional)
            </label>

            <input
                type="file"
                name="file"
                id="announcementFile"
            >


            <div id="currentFiles"></div>


            <button
                type="submit"
                id="submitButton"
            >
                Add Announcement
            </button>


            <button
                type="button"
                id="cancelButton"
                class="cancel-btn"
                style="display:none;"
                onclick="cancelEdit()"
            >
                Cancel
            </button>

        </form>

    </div>


    <!-- EXISTING ANNOUNCEMENTS -->

    <div class="card">

        <h2>
            📋 Existing Announcements
        </h2>

        <div id="announcementList">
            Loading announcements...
        </div>

    </div>


</div>


<script>


const form =
    document.getElementById(
        "announcementForm"
    );


const list =
    document.getElementById(
        "announcementList"
    );


const message =
    document.getElementById(
        "message"
    );


const formTitle =
    document.getElementById(
        "formTitle"
    );


const submitButton =
    document.getElementById(
        "submitButton"
    );


const cancelButton =
    document.getElementById(
        "cancelButton"
    );


const announcementId =
    document.getElementById(
        "announcementId"
    );


const announcementTitle =
    document.getElementById(
        "announcementTitle"
    );


const announcementCategory =
    document.getElementById(
        "announcementCategory"
    );


const announcementContent =
    document.getElementById(
        "announcementContent"
    );


const currentFiles =
    document.getElementById(
        "currentFiles"
    );


let announcements = [];


/*
LOAD ANNOUNCEMENTS
*/

async function loadAnnouncements() {

    try {

        const response =
            await fetch(
                "backend/announcements.php"
            );


        const result =
            await response.json();


        if (!result.success) {

            list.innerHTML =
                "Unable to load announcements.";

            return;
        }


        announcements =
            result.data;


        if (announcements.length === 0) {

            list.innerHTML =
                "No announcements yet.";

            return;
        }


        list.innerHTML = "";


        announcements.forEach(
            item => {

                const div =
                    document.createElement(
                        "div"
                    );


                div.className =
                    "announcement";


                let imageHTML = "";


                if (item.image_path) {

                    imageHTML = `

                        <img
                            src="${item.image_path}"
                            alt="Announcement image"
                        >

                    `;

                }


                let fileHTML = "";


                if (item.file_path) {

                    fileHTML = `

                        <p>
                            📎

                            <a
                                href="${item.file_path}"
                                target="_blank"
                            >
                                ${escapeHTML(
                                    item.file_name
                                )}
                            </a>
                        </p>

                    `;

                }


                div.innerHTML = `

                    <h3>
                        ${escapeHTML(
                            item.title
                        )}
                    </h3>

                    <p>
                        <strong>
                            Category:
                        </strong>

                        ${escapeHTML(
                            item.category
                        )}
                    </p>

                    <p>
                        ${escapeHTML(
                            item.content
                        )}
                    </p>

                    ${imageHTML}

                    ${fileHTML}

                    <br>

                    <button
                        type="button"
                        class="edit-btn"
                        onclick="
                            editAnnouncement(
                                ${item.id}
                            )
                        "
                    >
                        ✏️ Edit
                    </button>

                    <button
                        type="button"
                        class="delete-btn"
                        onclick="
                            deleteAnnouncement(
                                ${item.id}
                            )
                        "
                    >
                        Delete
                    </button>

                `;


                list.appendChild(div);

            }
        );


    } catch (error) {

        list.innerHTML =
            "Error connecting to server.";

        console.error(error);

    }

}


/*
EDIT ANNOUNCEMENT
*/

function editAnnouncement(id) {

    const item =
        announcements.find(
            announcement =>
                Number(announcement.id) ===
                Number(id)
        );


    if (!item) {

        alert(
            "Announcement not found."
        );

        return;
    }


    announcementId.value =
        item.id;


    announcementTitle.value =
        item.title;


    announcementCategory.value =
        item.category;


    announcementContent.value =
        item.content;


    formTitle.textContent =
        "✏️ Edit Announcement";


    submitButton.textContent =
        "Update Announcement";


    cancelButton.style.display =
        "inline-block";


    let currentHTML = "";


    if (item.image_path) {

        currentHTML += `

            <div class="current-file">

                <strong>
                    Current Image:
                </strong>

                <br>

                <img
                    src="${item.image_path}"
                    alt="Current announcement image"
                    style="
                        max-width:200px;
                        max-height:140px;
                        margin-top:8px;
                        border-radius:6px;
                    "
                >

                <p>
                    Leave the image field empty
                    to keep the current image.
                </p>

            </div>

        `;

    }


    if (item.file_path) {

        currentHTML += `

            <div class="current-file">

                <strong>
                    Current Attachment:
                </strong>

                <br>

                📎

                <a
                    href="${item.file_path}"
                    target="_blank"
                >
                    ${escapeHTML(
                        item.file_name
                    )}
                </a>

                <p>
                    Leave the file field empty
                    to keep the current attachment.
                </p>

            </div>

        `;

    }


    currentFiles.innerHTML =
        currentHTML;


    window.scrollTo({
        top: 0,
        behavior: "smooth"
    });

}


/*
CANCEL EDIT
*/

function cancelEdit() {

    form.reset();


    announcementId.value =
        "";


    formTitle.textContent =
        "📢 Add Announcement";


    submitButton.textContent =
        "Add Announcement";


    cancelButton.style.display =
        "none";


    currentFiles.innerHTML =
        "";

}


/*
ADD / UPDATE ANNOUNCEMENT
*/

form.addEventListener(
    "submit",
    async function(event) {

        event.preventDefault();


        const formData =
            new FormData(form);


        try {

            const response =
                await fetch(
                    "backend/announcements.php",
                    {
                        method: "POST",
                        body: formData
                    }
                );


            const result =
                await response.json();


            if (result.success) {

                showMessage(
                    result.message,
                    true
                );


                cancelEdit();


                await loadAnnouncements();


            } else {

                showMessage(
                    result.message,
                    false
                );

            }


        } catch (error) {

            showMessage(
                "Server connection error.",
                false
            );

            console.error(error);

        }

    }
);


/*
DELETE ANNOUNCEMENT
*/

async function deleteAnnouncement(id) {

    const confirmed =
        confirm(
            "Are you sure you want to delete this announcement?"
        );


    if (!confirmed) {

        return;

    }


    const data =
        new URLSearchParams();


    data.append(
        "id",
        id
    );


    try {

        const response =
            await fetch(
                "backend/announcements.php",
                {
                    method: "DELETE",
                    body: data
                }
            );


        const result =
            await response.json();


        if (result.success) {

            loadAnnouncements();

        } else {

            alert(
                result.message
            );

        }


    } catch (error) {

        alert(
            "Unable to delete announcement."
        );

        console.error(error);

    }

}


/*
MESSAGE
*/

function showMessage(
    text,
    success
) {

    message.textContent =
        text;


    message.className =
        "message " +
        (
            success
                ? "success"
                : "error"
        );


    message.style.display =
        "block";


    setTimeout(
        function() {

            message.style.display =
                "none";

        },
        3000
    );

}


/*
SECURITY
*/

function escapeHTML(text) {

    const div =
        document.createElement(
            "div"
        );


    div.textContent =
        text ?? "";


    return div.innerHTML;

}


/*
LOAD ON PAGE OPEN
*/

loadAnnouncements();

</script>


</body>
</html>