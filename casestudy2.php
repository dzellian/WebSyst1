<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Grade Result</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f7fa;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .card {
            background: white;
            padding: 30px 30px;
            border-radius: 12px;
            box-shadow: 0px 4px 10px rgba(0,0,0,0.15);
            text-align: center;
            max-width: 500px;
            width: 150%;
        }
        h2 {
            margin-bottom: 10px;
            color: #333;
        }
        p {
            font-size: 18px;
            margin: 8px 0;
        }
        .grade {
            font-weight: bold;
            font-size: 20px;
            margin-top: 15px;
            padding: 10px;
            border-radius: 8px;
        }
        .A { background: #4caf50; color: white; }
        .B { background: #2196f3; color: white; }
        .C { background: #ff9800; color: white; }
        .D { background: #9c27b0; color: white; }
        .F { background: #f44336; color: white; }
        .error { background: #e91e63; color: white; }
    </style>
</head>
<body>
    
<?php
if ($_SERVER['REQUEST_METHOD'] === "GET") {
    $name = $_GET["name"] ?? '';
    $score = $_GET["score"] ?? '';
    

    if ($name !== '' && $score !== '') {
        echo "<div class='card'>";
        echo "<h2>Student: $name</h2>";
        echo "<p>Score: $score</p>";

        if ($score >= 95 && $score <= 100) {
            echo "Grade: Excellent";
            echo "<p class='grade A'> Outstanding Performance!</p>";
        } elseif ($score >= 90 && $score <= 94) {
             echo "Grade: Very Good";
            echo "<p class='grade B'>Great Job!</p>";
        } elseif ($score >= 85 && $score <= 89) {
             echo "Grade: Good";
            echo "<p class='grade C'>Good effort, keep it up!</p>";
        } elseif ($score >= 75 && $score <= 84) {
             echo "Grade: Needs Improvement";
            echo "<p class='grade D'>Work harder next time.</p>";
        } elseif ($score <=74) {
             echo "Grade: Failed";
            echo "<p class='grade F'>You need to improve.</p>";
        } 

        echo "</div>";
    } else {
        echo "<div class='card'><p>Please provide <b>name</b> and <b>score</b> in the URL.</p></div>";
    }
}
?>

</body>
</html>

