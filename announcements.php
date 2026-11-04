<?php

session_start();

require_once "database.php";

header("Content-Type: application/json");

$method = $_SERVER["REQUEST_METHOD"];


/*
HELPER FUNCTION
DELETE OLD UPLOADED FILE
*/

function deleteUploadedFile($filePath)
{
    if (!$filePath) {
        return;
    }

    $fullPath =
        dirname(__DIR__) . DIRECTORY_SEPARATOR . $filePath;

    if (file_exists($fullPath)) {
        unlink($fullPath);
    }
}


/*
GET
*/

if ($method === "GET") {

    $stmt = $pdo->query(
        "SELECT * FROM announcements
         ORDER BY created_at DESC"
    );

    $announcements =
        $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "data" => $announcements
    ]);

    exit;
}


/*
CHECK ADMIN LOGIN
*/

if (!isset($_SESSION["admin_id"])) {

    http_response_code(401);

    echo json_encode([
        "success" => false,
        "message" => "Unauthorized. Admin login required."
    ]);

    exit;
}


/*
POST
ADD OR UPDATE
*/

if ($method === "POST") {

    $id =
        intval($_POST["id"] ?? 0);

    $title =
        trim($_POST["title"] ?? "");

    $category =
        trim($_POST["category"] ?? "");

    $content =
        trim($_POST["content"] ?? "");


    if (
        $title === "" ||
        $category === "" ||
        $content === ""
    ) {

        http_response_code(400);

        echo json_encode([
            "success" => false,
            "message" =>
                "Please complete all required fields."
        ]);

        exit;
    }


    /*
    ========================================
    UPDATE ANNOUNCEMENT
    ========================================
    */

    if ($id > 0) {

        $stmt = $pdo->prepare(
            "SELECT *
             FROM announcements
             WHERE id = ?
             LIMIT 1"
        );

        $stmt->execute([$id]);

        $existing =
            $stmt->fetch(PDO::FETCH_ASSOC);


        if (!$existing) {

            http_response_code(404);

            echo json_encode([
                "success" => false,
                "message" =>
                    "Announcement not found."
            ]);

            exit;
        }


        $imagePath =
            $existing["image_path"];

        $filePath =
            $existing["file_path"];

        $fileName =
            $existing["file_name"];


        /*
        NEW IMAGE
        */

        if (
            isset($_FILES["image"]) &&
            $_FILES["image"]["error"] === UPLOAD_ERR_OK
        ) {

            $allowedImageTypes = [
                "image/jpeg",
                "image/png",
                "image/webp"
            ];


            if (
                !in_array(
                    $_FILES["image"]["type"],
                    $allowedImageTypes
                )
            ) {

                http_response_code(400);

                echo json_encode([
                    "success" => false,
                    "message" =>
                        "Invalid image type."
                ]);

                exit;
            }


            $uploadFolder =
                "../uploads/images/";


            if (!is_dir($uploadFolder)) {

                mkdir(
                    $uploadFolder,
                    0777,
                    true
                );
            }


            $extension =
                pathinfo(
                    $_FILES["image"]["name"],
                    PATHINFO_EXTENSION
                );


            $newFileName =
                uniqid(
                    "img_",
                    true
                ) . "." . $extension;


            $targetPath =
                $uploadFolder . $newFileName;


            if (
                move_uploaded_file(
                    $_FILES["image"]["tmp_name"],
                    $targetPath
                )
            ) {

                /*
                DELETE OLD IMAGE
                */

                deleteUploadedFile(
                    $existing["image_path"]
                );


                $imagePath =
                    "uploads/images/" .
                    $newFileName;
            }
        }


        /*
        NEW ATTACHMENT
        */

        if (
            isset($_FILES["file"]) &&
            $_FILES["file"]["error"] === UPLOAD_ERR_OK
        ) {

            $uploadFolder =
                "../uploads/files/";


            if (!is_dir($uploadFolder)) {

                mkdir(
                    $uploadFolder,
                    0777,
                    true
                );
            }


            $originalName =
                basename(
                    $_FILES["file"]["name"]
                );


            $extension =
                pathinfo(
                    $originalName,
                    PATHINFO_EXTENSION
                );


            $newFileName =
                uniqid(
                    "file_",
                    true
                ) . "." . $extension;


            $targetPath =
                $uploadFolder . $newFileName;


            if (
                move_uploaded_file(
                    $_FILES["file"]["tmp_name"],
                    $targetPath
                )
            ) {

                /*
                DELETE OLD ATTACHMENT
                */

                deleteUploadedFile(
                    $existing["file_path"]
                );


                $filePath =
                    "uploads/files/" .
                    $newFileName;

                $fileName =
                    $originalName;
            }
        }


        /*
        UPDATE DATABASE
        */

        $stmt = $pdo->prepare(
            "UPDATE announcements
             SET
                title = ?,
                category = ?,
                content = ?,
                image_path = ?,
                file_path = ?,
                file_name = ?
             WHERE id = ?"
        );


        $stmt->execute([
            $title,
            $category,
            $content,
            $imagePath,
            $filePath,
            $fileName,
            $id
        ]);


        echo json_encode([
            "success" => true,
            "message" =>
                "Announcement updated successfully."
        ]);

        exit;
    }


    /*
    ========================================
    ADD ANNOUNCEMENT
    ========================================
    */

    $imagePath = null;

    $filePath = null;

    $fileName = null;


    /*
    IMAGE
    */

    if (
        isset($_FILES["image"]) &&
        $_FILES["image"]["error"] === UPLOAD_ERR_OK
    ) {

        $allowedImageTypes = [
            "image/jpeg",
            "image/png",
            "image/webp"
        ];


        if (
            !in_array(
                $_FILES["image"]["type"],
                $allowedImageTypes
            )
        ) {

            http_response_code(400);

            echo json_encode([
                "success" => false,
                "message" =>
                    "Invalid image type."
            ]);

            exit;
        }


        $uploadFolder =
            "../uploads/images/";


        if (!is_dir($uploadFolder)) {

            mkdir(
                $uploadFolder,
                0777,
                true
            );
        }


        $extension =
            pathinfo(
                $_FILES["image"]["name"],
                PATHINFO_EXTENSION
            );


        $newFileName =
            uniqid(
                "img_",
                true
            ) . "." . $extension;


        $targetPath =
            $uploadFolder . $newFileName;


        if (
            move_uploaded_file(
                $_FILES["image"]["tmp_name"],
                $targetPath
            )
        ) {

            $imagePath =
                "uploads/images/" .
                $newFileName;
        }
    }


    /*
    ATTACHMENT
    */

    if (
        isset($_FILES["file"]) &&
        $_FILES["file"]["error"] === UPLOAD_ERR_OK
    ) {

        $uploadFolder =
            "../uploads/files/";


        if (!is_dir($uploadFolder)) {

            mkdir(
                $uploadFolder,
                0777,
                true
            );
        }


        $originalName =
            basename(
                $_FILES["file"]["name"]
            );


        $extension =
            pathinfo(
                $originalName,
                PATHINFO_EXTENSION
            );


        $newFileName =
            uniqid(
                "file_",
                true
            ) . "." . $extension;


        $targetPath =
            $uploadFolder . $newFileName;


        if (
            move_uploaded_file(
                $_FILES["file"]["tmp_name"],
                $targetPath
            )
        ) {

            $filePath =
                "uploads/files/" .
                $newFileName;

            $fileName =
                $originalName;
        }
    }


    /*
    INSERT
    */

    $stmt = $pdo->prepare(
        "INSERT INTO announcements
        (
            title,
            category,
            content,
            image_path,
            file_path,
            file_name
        )
        VALUES (?, ?, ?, ?, ?, ?)"
    );


    $stmt->execute([
        $title,
        $category,
        $content,
        $imagePath,
        $filePath,
        $fileName
    ]);


    echo json_encode([
        "success" => true,
        "message" =>
            "Announcement added successfully."
    ]);

    exit;
}


/*
DELETE ANNOUNCEMENT
*/

if ($method === "DELETE") {

    parse_str(
        file_get_contents("php://input"),
        $data
    );


    $id =
        intval($data["id"] ?? 0);


    if ($id <= 0) {

        http_response_code(400);

        echo json_encode([
            "success" => false,
            "message" =>
                "Invalid announcement ID."
        ]);

        exit;
    }


    /*
    GET FILE PATHS FIRST
    */

    $stmt = $pdo->prepare(
        "SELECT image_path, file_path
         FROM announcements
         WHERE id = ?
         LIMIT 1"
    );

    $stmt->execute([$id]);

    $announcement =
        $stmt->fetch(PDO::FETCH_ASSOC);


    if (!$announcement) {

        http_response_code(404);

        echo json_encode([
            "success" => false,
            "message" =>
                "Announcement not found."
        ]);

        exit;
    }


    /*
    DELETE DATABASE RECORD
    */

    $stmt = $pdo->prepare(
        "DELETE FROM announcements
         WHERE id = ?"
    );

    $stmt->execute([$id]);


    /*
    DELETE IMAGE
    */

    deleteUploadedFile(
        $announcement["image_path"]
    );


    /*
    DELETE ATTACHMENT
    */

    deleteUploadedFile(
        $announcement["file_path"]
    );


    echo json_encode([
        "success" => true,
        "message" =>
            "Announcement and uploaded files deleted successfully."
    ]);

    exit;
}


/*
METHOD NOT ALLOWED
*/

http_response_code(405);

echo json_encode([
    "success" => false,
    "message" =>
        "Method not allowed."
]);

?>