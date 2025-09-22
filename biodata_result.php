<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $errors = [];
    $uploads_dir = "uploads/";

    // Make sure uploads folder exists
    if (!is_dir($uploads_dir)) {
        mkdir($uploads_dir, 0777, true);
    }

    // Validate photo
    if (isset($_FILES["photo"]) && $_FILES["photo"]["error"] == 0) {
        $allowed = ["jpg" => "image/jpeg", "jpeg" => "image/jpeg", "png" => "image/png"];
        $filename = $_FILES["photo"]["name"];
        $filetype = $_FILES["photo"]["type"];
        $filesize = $_FILES["photo"]["size"];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (!array_key_exists($ext, $allowed)) {
            $errors[] = "Only JPG and PNG files are allowed.";
        }
        if ($filesize > 2 * 1024 * 1024) {
            $errors[] = "File size must be less than 2MB.";
        }

        // Move file if valid
        if (empty($errors)) {
            $newname = uniqid() . "." . $ext;
            move_uploaded_file($_FILES["photo"]["tmp_name"], $uploads_dir . $newname);
            $photo_path = $uploads_dir . $newname;
        }
    } else {
        $errors[] = "Photo upload is required.";
    }

    // Validate text fields
    foreach ($_POST as $key => $value) {
        if (empty(trim($value))) {
            $errors[] = ucfirst($key) . " is required.";
        }
    }

    if (!preg_match("/^[0-9]{11}$/", $_POST["mobile"])) {
        $errors[] = "Mobile No. must be 11 digits.";
    }

    if (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid Email is required.";
    }

    // If errors, show them
    if (!empty($errors)) {
        echo "<div style='max-width:800px;margin:auto;background:#fdd;padding:15px;border:1px solid red;border-radius:5px;'>";
        echo "<strong>Please fix the following errors:</strong><ul>";
        foreach ($errors as $err) {
            echo "<li>$err</li>";
        }
        echo "</ul></div>";
        echo "<br><a href='biodata.php'>Go Back</a>";
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Biodata Result</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 30px;
      background-color: #f9f9f9;
    }
    h2 {
      background-color: #000;
      color: white;
      padding: 10px;
      text-align: center;
      margin-top: 40px;
      border-radius: 4px;
    }
    .result {
      max-width: 800px;
      margin: auto;
      background: #fff;
      padding: 20px 40px;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0,0,0,0.2);
    }
    .photo {
      text-align: center;
      margin-bottom: 20px;
    }
    .photo img {
      max-width: 150px;
      border-radius: 8px;
      border: 2px solid #000;
    }
    p {
      margin: 8px 0;
    }
  </style>
</head>
<body>

  <div class="result">
    <div class="photo">
      <img src="<?php echo $photo_path; ?>" alt="Uploaded Photo">
    </div>

    <h2>PERSONAL INFORMATION</h2>
    <p><strong>Full Name:</strong> <?php echo htmlspecialchars($_POST["fullname"]); ?></p>
    <p><strong>Address:</strong> <?php echo nl2br(htmlspecialchars($_POST["address"])); ?></p>
    <p><strong>Date of Birth:</strong> <?php echo htmlspecialchars($_POST["dob"]); ?></p>
    <p><strong>Place of Birth:</strong> <?php echo htmlspecialchars($_POST["pob"]); ?></p>
    <p><strong>Gender:</strong> <?php echo htmlspecialchars($_POST["gender"]); ?></p>
    <p><strong>Civil Status:</strong> <?php echo htmlspecialchars($_POST["civil_status"]); ?></p>
    <p><strong>Nationality:</strong> <?php echo htmlspecialchars($_POST["nationality"]); ?></p>
    <p><strong>Religion:</strong> <?php echo htmlspecialchars($_POST["religion"]); ?></p>

    <h2>CONTACT INFORMATION</h2>
    <p><strong>Mobile No.:</strong> <?php echo htmlspecialchars($_POST["mobile"]); ?></p>
    <p><strong>Email:</strong> <?php echo htmlspecialchars($_POST["email"]); ?></p>

    <h2>EDUCATIONAL BACKGROUND</h2>
    <p><strong>Elementary:</strong> <?php echo htmlspecialchars($_POST["elem"]); ?></p>
    <p><strong>High School:</strong> <?php echo htmlspecialchars($_POST["hs"]); ?></p>
    <p><strong>College:</strong> <?php echo htmlspecialchars($_POST["college"]); ?></p>
    <p><strong>Degree:</strong> <?php echo htmlspecialchars($_POST["degree"]); ?></p>

    <h2>OTHER INFORMATION</h2>
    <p><strong>Skills:</strong> <?php echo nl2br(htmlspecialchars($_POST["skills"])); ?></p>
    <p><strong>Work Experience:</strong> <?php echo nl2br(htmlspecialchars($_POST["experience"])); ?></p>
  </div>

</body>
</html>
