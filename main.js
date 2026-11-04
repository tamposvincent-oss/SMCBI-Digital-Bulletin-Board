

// ==========================================
// DIGITAL BULLETIN BOARD
// MAIN JAVASCRIPT
// ==========================================

let scheduleData = [];

let announcements = [];


// ==========================================
// PAGE TITLES
// ==========================================

const pageTitles = {

    dashboard: "Dashboard",

    schedule: "Exam Schedule",

    announcements: "Announcements",

    academic: "Academic"

};


// ==========================================
// PAGE NAVIGATION
// ==========================================

const navButtons =
    document.querySelectorAll(".nav-btn");


const pages =
    document.querySelectorAll(".page");


navButtons.forEach(button => {

    button.addEventListener("click", function () {

        const target =
            this.getAttribute("data-section");

        openPage(target);

    });

});


function openPage(pageName) {

    navButtons.forEach(button => {

        button.classList.remove("active");

        if (
            button.getAttribute("data-section")
            === pageName
        ) {

            button.classList.add("active");

        }

    });


    pages.forEach(page => {

        page.classList.remove("active");

    });


    const selectedPage =
        document.getElementById(pageName);


    if (selectedPage) {

        selectedPage.classList.add("active");

    }


    const pageTitle =
        document.getElementById("pageTitle");


    if (pageTitle) {

        pageTitle.textContent =
            pageTitles[pageName];

    }

}


// ==========================================
// DATE AND TIME
// ==========================================

function updateDateTime() {

    const now =
        new Date();


    const dateElement =
        document.getElementById("currentDate");


    const timeElement =
        document.getElementById("currentTime");


    if (dateElement) {

        dateElement.textContent =
            now.toLocaleDateString(
                "en-US",
                {
                    weekday: "long",
                    month: "long",
                    day: "numeric",
                    year: "numeric"
                }
            );

    }


    if (timeElement) {

        timeElement.textContent =
            now.toLocaleTimeString(
                "en-US"
            );

    }

}


updateDateTime();


setInterval(
    updateDateTime,
    1000
);


// ==========================================
// EXCEL FILE UPLOAD
// ==========================================

const excelFile =
    document.getElementById(
        "excelFile"
    );


if (excelFile) {

    excelFile.addEventListener(
        "change",
        function () {

            const file =
                this.files[0];


            if (!file) {

                return;

            }


            const fileName =
                document.getElementById(
                    "fileName"
                );


            const selectedFile =
                document.getElementById(
                    "selectedFile"
                );


            const uploadStatus =
                document.getElementById(
                    "uploadStatus"
                );


            fileName.textContent =
                file.name;


            selectedFile.textContent =
                "Selected: " +
                file.name;


            uploadStatus.textContent =
                "Reading Excel file...";


            uploadStatus.style.color =
                "#2563eb";


            if (
                typeof XLSX ===
                "undefined"
            ) {

                uploadStatus.textContent =
                    "❌ Excel reader is not loaded. Please check your internet connection.";

                uploadStatus.style.color =
                    "red";

                return;

            }


            const reader =
                new FileReader();


            reader.onload =
                function (event) {

                    try {

                        const data =
                            new Uint8Array(
                                event.target.result
                            );


                        const workbook =
                            XLSX.read(
                                data,
                                {
                                    type: "array",
                                    cellDates: true
                                }
                            );


                        if (
                            !workbook.SheetNames ||
                            workbook.SheetNames.length === 0
                        ) {

                            throw new Error(
                                "No worksheet found."
                            );

                        }


                        const sheetName =
                            workbook.SheetNames[0];


                        const worksheet =
                            workbook.Sheets[
                                sheetName
                            ];


                        const rows =
                            XLSX.utils.sheet_to_json(
                                worksheet,
                                {
                                    header: 1,
                                    defval: ""
                                }
                            );


                        if (
                            rows.length === 0
                        ) {

                            throw new Error(
                                "The Excel file is empty."
                            );

                        }


                        const headerIndex =
                            findHeaderRow(rows);


                        if (
                            headerIndex === -1
                        ) {

                            throw new Error(
                                "Could not find the Excel headers. Make sure the file contains Program, Year Level, Subject / Course, Exam Date, Time Slot, Room Number, and Proctor."
                            );

                        }


                        const headers =
                            rows[
                                headerIndex
                            ].map(header =>
                                normalizeHeader(
                                    header
                                )
                            );


                        scheduleData = [];


                        for (
                            let i =
                                headerIndex + 1;

                            i <
                            rows.length;

                            i++
                        ) {

                            const row =
                                rows[i];


                            if (
                                !rowHasData(
                                    row
                                )
                            ) {

                                continue;

                            }


                            const record = {};


                            headers.forEach(
                                (
                                    header,
                                    index
                                ) => {

                                    if (
                                        header !== ""
                                    ) {

                                        record[
                                            header
                                        ] =
                                            row[
                                                index
                                            ] ?? "";

                                    }

                                }
                            );


                            if (
                                Object.values(
                                    record
                                ).some(
                                    value =>
                                        String(
                                            value
                                        ).trim() !== ""
                                )
                            ) {

                                scheduleData.push(
                                    record
                                );

                            }

                        }


                        if (
                            scheduleData.length ===
                            0
                        ) {

                            throw new Error(
                                "No examination schedule records were found."
                            );

                        }


                        displaySchedule(
                            scheduleData
                        );


                        uploadStatus.textContent =
                            "✓ File uploaded successfully! " +
                            scheduleData.length +
                            " schedule(s) loaded.";


                        uploadStatus.style.color =
                            "green";

                    }

                    catch (error) {

                        console.error(
                            "Excel error:",
                            error
                        );


                        uploadStatus.textContent =
                            "❌ " +
                            error.message;


                        uploadStatus.style.color =
                            "red";

                    }

                };


            reader.onerror =
                function () {

                    uploadStatus.textContent =
                        "❌ Cannot read the selected file.";

                    uploadStatus.style.color =
                        "red";

                };


            reader.readAsArrayBuffer(
                file
            );

        }
    );

}


// ==========================================
// FIND HEADER ROW
// ==========================================

function findHeaderRow(rows) {

    for (
        let i = 0;
        i < rows.length;
        i++
    ) {

        const row =
            rows[i].map(
                cell =>
                    normalizeHeader(cell)
            );


        const hasProgram =
            row.includes("program");


        const hasYearLevel =
            row.includes(
                "year level"
            );


        const hasSubject =
            row.includes(
                "subject / course"
            ) ||
            row.includes(
                "subject/course"
            );


        const hasExamDate =
            row.includes(
                "exam date"
            );


        if (
            hasProgram &&
            hasYearLevel &&
            hasSubject &&
            hasExamDate
        ) {

            return i;

        }

    }


    return -1;

}


// ==========================================
// NORMALIZE HEADER
// ==========================================

function normalizeHeader(value) {

    return String(
        value ?? ""
    )
        .trim()
        .replace(
            /\s+/g,
            " "
        )
        .toLowerCase();

}


// ==========================================
// CHECK ROW DATA
// ==========================================

function rowHasData(row) {

    if (!row) {

        return false;

    }


    return row.some(
        value =>
            value !== "" &&
            value !== null &&
            value !== undefined
    );

}


// ==========================================
// DISPLAY SCHEDULE
// ==========================================

function displaySchedule(
    data
) {

    const tableBody =
        document.getElementById(
            "scheduleBody"
        );


    if (!tableBody) {

        return;

    }


    tableBody.innerHTML = "";


    if (
        data.length === 0
    ) {

        tableBody.innerHTML = `

            <tr>

                <td colspan="7">

                    <div class="empty">

                        <span>
                            🔍
                        </span>

                        <h3>
                            No Schedule Found
                        </h3>

                        <p>
                            No examination schedule
                            matches your search.
                        </p>

                    </div>

                </td>

            </tr>

        `;


        updateScheduleCount(
            0
        );


        return;

    }


    data.forEach(
        row => {

            const program =
                getValue(
                    row,
                    "program"
                );


            const yearLevel =
                getValue(
                    row,
                    "year level"
                );


            const subject =
                getValue(
                    row,
                    "subject / course",
                    "subject/course"
                );


            const examDate =
                getValue(
                    row,
                    "exam date"
                );


            const timeSlot =
                getValue(
                    row,
                    "time slot"
                );


            const roomNumber =
                getValue(
                    row,
                    "room number"
                );


            const proctor =
                getValue(
                    row,
                    "proctor"
                );


            const tr =
                document.createElement(
                    "tr"
                );


            tr.innerHTML = `

                <td>
                    ${escapeHTML(
                        program
                    )}
                </td>

                <td>
                    ${escapeHTML(
                        yearLevel
                    )}
                </td>

                <td>
                    ${escapeHTML(
                        subject
                    )}
                </td>

                <td>
                    ${escapeHTML(
                        formatDate(
                            examDate
                        )
                    )}
                </td>

                <td>
                    ${escapeHTML(
                        timeSlot
                    )}
                </td>

                <td>
                    ${escapeHTML(
                        roomNumber
                    )}
                </td>

                <td>
                    ${escapeHTML(
                        proctor
                    )}
                </td>

            `;


            tableBody.appendChild(
                tr
            );

        }
    );


    updateScheduleCount(
        data.length
    );

}


// ==========================================
// GET VALUE
// ==========================================

function getValue(
    row,
    ...possibleNames
) {

    const keys =
        Object.keys(row);


    for (
        const name
        of possibleNames
    ) {

        const foundKey =
            keys.find(
                key =>
                    key
                    .trim()
                    .toLowerCase() ===
                    name
                    .trim()
                    .toLowerCase()
            );


        if (
            foundKey !==
            undefined
        ) {

            return row[
                foundKey
            ];

        }

    }


    return "";

}


// ==========================================
// FORMAT DATE
// ==========================================

function formatDate(
    value
) {

    if (
        value === null ||
        value === undefined ||
        value === ""
    ) {

        return "";

    }


    if (
        value instanceof Date
    ) {

        return value.toLocaleDateString(
            "en-US",
            {
                month: "short",
                day: "numeric",
                year: "numeric"
            }
        );

    }


    return String(
        value
    );

}


// ==========================================
// SEARCH SCHEDULE
// ==========================================

const searchInput =
    document.getElementById(
        "scheduleSearch"
    );


if (searchInput) {

    searchInput.addEventListener(
        "input",
        function () {

            const search =
                this.value
                    .toLowerCase()
                    .trim();


            if (
                search === ""
            ) {

                displaySchedule(
                    scheduleData
                );

                return;

            }


            const filtered =
                scheduleData.filter(
                    row => {

                        const text =
                            Object
                            .values(row)
                            .join(" ")
                            .toLowerCase();


                        return text.includes(
                            search
                        );

                    }
                );


            displaySchedule(
                filtered
            );

        }
    );

}


// ==========================================
// FILTER BUTTONS
// ==========================================

const filterButtons =
    document.querySelectorAll(
        ".filter-btn"
    );


filterButtons.forEach(
    button => {

        button.addEventListener(
            "click",
            function () {

                filterButtons.forEach(
                    btn =>
                        btn.classList
                            .remove(
                                "active"
                            )
                );


                this.classList.add(
                    "active"
                );


                const filter =
                    this.getAttribute(
                        "data-filter"
                    );


                if (
                    filter ===
                    "all"
                ) {

                    displaySchedule(
                        scheduleData
                    );

                    return;

                }


                const today =
                    new Date();


                const filtered =
                    scheduleData.filter(
                        row => {

                            const dateValue =
                                getValue(
                                    row,
                                    "exam date"
                                );


                            const examDate =
                                parseExamDate(
                                    dateValue
                                );


                            if (
                                !examDate
                            ) {

                                return false;

                            }


                            if (
                                filter ===
                                "today"
                            ) {

                                return isSameDay(
                                    examDate,
                                    today
                                );

                            }


                            if (
                                filter ===
                                "tomorrow"
                            ) {

                                const tomorrow =
                                    new Date(
                                        today
                                    );


                                tomorrow.setDate(
                                    today.getDate() +
                                    1
                                );


                                return isSameDay(
                                    examDate,
                                    tomorrow
                                );

                            }


                            return true;

                        }
                    );


                displaySchedule(
                    filtered
                );

            }
        );

    }
);


// ==========================================
// PARSE EXAM DATE
// ==========================================

function parseExamDate(
    value
) {

    if (!value) {

        return null;

    }


    if (
        value instanceof Date
    ) {

        return value;

    }


    const date =
        new Date(
            String(value)
        );


    if (
        isNaN(
            date.getTime()
        )
    ) {

        return null;

    }


    return date;

}


// ==========================================
// SAME DAY
// ==========================================

function isSameDay(
    date1,
    date2
) {

    return (

        date1.getFullYear() ===
        date2.getFullYear()

        &&

        date1.getMonth() ===
        date2.getMonth()

        &&

        date1.getDate() ===
        date2.getDate()

    );

}


// ==========================================
// UPDATE SCHEDULE COUNT
// ==========================================

function updateScheduleCount(
    count
) {

    const countElement =
        document.getElementById(
            "scheduleCount"
        );


    const totalElement =
        document.getElementById(
            "totalSchedules"
        );


    if (
        countElement
    ) {

        countElement.textContent =
            count +
            (
                count === 1
                    ? " schedule"
                    : " schedules"
            );

    }


    if (
        totalElement
    ) {

        totalElement.textContent =
            count;

    }

}


// ==================================================
// ANNOUNCEMENT SYSTEM
// ==================================================


// ==========================================
// LOAD ANNOUNCEMENTS
// ==========================================

function loadAnnouncements() {

    try {

        const saved =
            localStorage.getItem(
                "digitalBulletinAnnouncements"
            );


        if (saved) {

            announcements =
                JSON.parse(saved);

        }

    }

    catch (error) {

        console.error(
            "Could not load announcements:",
            error
        );

        announcements = [];

    }


    displayAnnouncements();

    updateAnnouncementCount();

}


loadAnnouncements();


// ==========================================
// SAVE ANNOUNCEMENTS
// ==========================================

function saveAnnouncements() {

    try {

        localStorage.setItem(
            "digitalBulletinAnnouncements",
            JSON.stringify(
                announcements
            )
        );

    }

    catch (error) {

        alert(
            "The announcement could not be saved. The uploaded image or file may be too large for browser storage."
        );

        console.error(
            error
        );

    }

}


// ==========================================
// ANNOUNCEMENT FORM
// ==========================================

const announcementForm =
    document.getElementById(
        "announcementForm"
    );


if (announcementForm) {

    announcementForm.addEventListener(
        "submit",
        function (event) {

            event.preventDefault();


            const title =
                document.getElementById(
                    "announcementTitle"
                ).value.trim();


            const category =
                document.getElementById(
                    "announcementCategory"
                ).value;


            const text =
                document.getElementById(
                    "announcementText"
                ).value.trim();


            const imageInput =
                document.getElementById(
                    "announcementImage"
                );


            const fileInput =
                document.getElementById(
                    "announcementFile"
                );


            if (
                !title ||
                !text
            ) {

                alert(
                    "Please enter the announcement title and details."
                );

                return;

            }


            const imageFile =
                imageInput.files[0];


            const attachmentFile =
                fileInput.files[0];


            if (
                imageFile &&
                attachmentFile
            ) {

                readAnnouncementFiles(
                    imageFile,
                    attachmentFile,
                    title,
                    category,
                    text
                );

            }

            else if (imageFile) {

                readAnnouncementFiles(
                    imageFile,
                    null,
                    title,
                    category,
                    text
                );

            }

            else if (attachmentFile) {

                readAnnouncementFiles(
                    null,
                    attachmentFile,
                    title,
                    category,
                    text
                );

            }

            else {

                createAnnouncement(
                    title,
                    category,
                    text,
                    null,
                    null
                );

            }

        }
    );

}


// ==========================================
// READ ANNOUNCEMENT FILES
// ==========================================

function readAnnouncementFiles(
    imageFile,
    attachmentFile,
    title,
    category,
    text
) {

    let imageData = null;

    let attachmentData = null;


    const filesToRead = [];


    if (imageFile) {

        filesToRead.push({
            type: "image",
            file: imageFile
        });

    }


    if (attachmentFile) {

        filesToRead.push({
            type: "attachment",
            file: attachmentFile
        });

    }


    let completed = 0;


    filesToRead.forEach(item => {

        const reader =
            new FileReader();


        reader.onload =
            function (event) {

                if (
                    item.type ===
                    "image"
                ) {

                    imageData = {
                        name: item.file.name,
                        type: item.file.type,
                        data: event.target.result
                    };

                }


                if (
                    item.type ===
                    "attachment"
                ) {

                    attachmentData = {
                        name: item.file.name,
                        type: item.file.type,
                        data: event.target.result
                    };

                }


                completed++;


                if (
                    completed ===
                    filesToRead.length
                ) {

                    createAnnouncement(
                        title,
                        category,
                        text,
                        imageData,
                        attachmentData
                    );

                }

            };


        reader.onerror =
            function () {

                alert(
                    "There was a problem reading the selected file."
                );

            };


        reader.readAsDataURL(
            item.file
        );

    });

}


// ==========================================
// CREATE ANNOUNCEMENT
// ==========================================

function createAnnouncement(
    title,
    category,
    text,
    image,
    attachment
) {

    const announcement = {

        id:
            Date.now(),

        title:
            title,

        category:
            category,

        text:
            text,

        image:
            image,

        attachment:
            attachment,

        date:
            new Date().toLocaleString(
                "en-US",
                {
                    month: "long",
                    day: "numeric",
                    year: "numeric",
                    hour: "numeric",
                    minute: "2-digit"
                }
            )

    };


    announcements.unshift(
        announcement
    );


    saveAnnouncements();

    displayAnnouncements();

    updateAnnouncementCount();


    announcementForm.reset();


    document.getElementById(
        "imagePreview"
    ).innerHTML = "";


    document.getElementById(
        "attachmentPreview"
    ).innerHTML = "";


    alert(
        "✓ Announcement posted successfully!"
    );

}


// ==========================================
// DISPLAY ANNOUNCEMENTS
// ==========================================

function displayAnnouncements(
    searchText = ""
) {

    const list =
        document.getElementById(
            "announcementList"
        );


    const empty =
        document.getElementById(
            "noAnnouncements"
        );


    if (!list) {

        return;

    }


    list.innerHTML = "";


    const search =
        searchText
            .toLowerCase()
            .trim();


    const filtered =
        announcements.filter(
            announcement => {

                const content =
                    (
                        announcement.title +
                        " " +
                        announcement.category +
                        " " +
                        announcement.text
                    )
                    .toLowerCase();


                return content.includes(
                    search
                );

            }
        );


    if (
        filtered.length === 0
    ) {

        empty.style.display =
            "block";

        return;

    }


    empty.style.display =
        "none";


    filtered.forEach(
        announcement => {

            const card =
                document.createElement(
                    "div"
                );


            card.className =
                "bulletin-card";


            let imageHTML = "";


            if (
                announcement.image
            ) {

                imageHTML = `

                    <img
                        class="bulletin-image"
                        src="${announcement.image.data}"
                        alt="${escapeHTML(
                            announcement.image.name
                        )}">

                `;

            }


            let fileHTML = "";


            if (
                announcement.attachment
            ) {

                fileHTML = `

                    <a
                        class="bulletin-file"
                        href="${announcement.attachment.data}"
                        download="${escapeHTML(
                            announcement.attachment.name
                        )}">

                        📎
                        ${escapeHTML(
                            announcement.attachment.name
                        )}

                    </a>

                `;

            }


            card.innerHTML = `

                <div class="bulletin-top">

                    <div>

                        <h3 class="bulletin-title">

                            ${escapeHTML(
                                announcement.title
                            )}

                        </h3>

                        <span class="bulletin-date">

                            Posted:
                            ${escapeHTML(
                                announcement.date
                            )}

                        </span>

                    </div>


                    <div>

                        <span class="bulletin-category">

                            ${escapeHTML(
                                announcement.category
                            )}

                        </span>

                        <br><br>

                        <button
                            class="delete-announcement"
                            onclick="deleteAnnouncement(
                                ${announcement.id}
                            )">

                            🗑 Delete

                        </button>

                    </div>

                </div>


                <div class="bulletin-content">

                    ${escapeHTML(
                        announcement.text
                    )}

                </div>


                ${imageHTML}


                ${fileHTML}

            `;


            list.appendChild(
                card
            );

        }
    );

}


// ==========================================
// DELETE ANNOUNCEMENT
// ==========================================

function deleteAnnouncement(
    id
) {

    const confirmed =
        confirm(
            "Are you sure you want to delete this announcement?"
        );


    if (!confirmed) {

        return;

    }


    announcements =
        announcements.filter(
            announcement =>
                announcement.id !== id
        );


    saveAnnouncements();

    displayAnnouncements();

    updateAnnouncementCount();

}


// ==========================================
// ANNOUNCEMENT SEARCH
// ==========================================

const announcementSearch =
    document.getElementById(
        "announcementSearch"
    );


if (announcementSearch) {

    announcementSearch.addEventListener(
        "input",
        function () {

            displayAnnouncements(
                this.value
            );

        }
    );

}


// ==========================================
// UPDATE ANNOUNCEMENT COUNT
// ==========================================

function updateAnnouncementCount() {

    const total =
        document.getElementById(
            "totalAnnouncements"
        );


    if (total) {

        total.textContent =
            announcements.length;

    }

}


// ==========================================
// IMAGE PREVIEW
// ==========================================

const announcementImage =
    document.getElementById(
        "announcementImage"
    );


if (announcementImage) {

    announcementImage.addEventListener(
        "change",
        function () {

            const file =
                this.files[0];


            const preview =
                document.getElementById(
                    "imagePreview"
                );


            preview.innerHTML = "";


            if (!file) {

                return;

            }


            const reader =
                new FileReader();


            reader.onload =
                function (event) {

                    preview.innerHTML = `

                        <img
                            src="${event.target.result}"
                            alt="Image Preview">

                    `;

                };


            reader.readAsDataURL(
                file
            );

        }
    );

}


// ==========================================
// FILE PREVIEW
// ==========================================

const announcementFile =
    document.getElementById(
        "announcementFile"
    );


if (announcementFile) {

    announcementFile.addEventListener(
        "change",
        function () {

            const file =
                this.files[0];


            const preview =
                document.getElementById(
                    "attachmentPreview"
                );


            preview.innerHTML = "";


            if (!file) {

                return;

            }


            preview.innerHTML = `

                📎 Selected file:

                <strong>
                    ${escapeHTML(
                        file.name
                    )}
                </strong>

            `;

        }
    );

}


// ==========================================
// ESCAPE HTML
// ==========================================

function escapeHTML(
    value
) {

    return String(
        value ?? ""
    )
        .replace(
            /&/g,
            "&amp;"
        )
        .replace(
            /</g,
            "&lt;"
        )
        .replace(
            />/g,
            "&gt;"
        )
        .replace(
            /"/g,
            "&quot;"
        )
        .replace(
            /'/g,
            "&#039;"
        );

}


// ==========================================
// SYSTEM LOADED
// ==========================================

console.log(
    "Digital Bulletin Board loaded successfully."
);