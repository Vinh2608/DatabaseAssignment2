<?php

if (isset($_POST)) {
    require_once 'database.php';
    $target_dir = "./images/";
    $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    // Check if image file is a actual image or fake image
    if (isset($_POST["submit"])) {
        $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
        if ($check !== false) {
            echo "File is an image - " . $check["mime"] . ".";
            $uploadOk = 1;
        } else {
            echo "File is not an image.";
            $uploadOk = 0;
        }
    }


    // Allow certain file formats
    if (
        $imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
        && $imageFileType != "gif"
    ) {
        echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        $uploadOk = 0;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        echo "Sorry, your file was not uploaded.";
        // if everything is ok, try to upload file
    } else {
        if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
            echo "The file " . htmlspecialchars(basename($_FILES["fileToUpload"]["name"])) . " has been uploaded.";
            $imgurl = "./images/" . htmlspecialchars(basename($_FILES["fileToUpload"]["name"]));
            $name = $_POST['product_name'];
            $category = $_POST['category'];
            $price = $_POST['price'];
            $errors = array();

            $name = htmlspecialchars($name);
            $name = trim($name);
            $price = trim($price);


            $filteredPrice = filter_var(
                $price,
                FILTER_VALIDATE_INT,
                array('options' => array('min_range' => 0))
            );

            if ($name == "") {
                array_push($errors, "Name is empty !!!");
            }
            if (!$filteredPrice) {
                array_push($errors, "Price is invalid !!!");
            }

            if (!empty($errors)) {
                session_start();
                $_SESSION['insert_errors'] = $errors;
                header('Location:index.php?page=insert');
            } else {
                $price = (int) $price;
                $query = "SELECT * from products
                    where name='$name'";

                $result = mysqli_query($conn, $query);
                if (mysqli_num_rows($result) != 0) {
                    array_push($errors, "Duplicate items !!!");
                    session_start();
                    $_SESSION['insert_errors'] = $errors;;
                    header('Location:index.php?page=insert');
                    die();
                } else {
                    $query = "";
                    if ($imgurl != "") {
                        $query = "INSERT INTO products (name, category, price, imgurl)
                VALUES ('$name', '$category', '$price', '$imgurl')";
                    } else {
                        $query = "INSERT INTO products (name, category, price)
                VALUES ('$name', '$category', '$price')";
                    }

                    if (mysqli_query($conn, $query)) {
                        session_start();
                        $_SESSION['insert_success'] = true;
                    } else {
                        session_start();
                        $_SESSION['insert_success'] = false;
                    }
                    header('Location:index.php?page=product&category=all');
                }
            }
            mysqli_close($conn);
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }
}
