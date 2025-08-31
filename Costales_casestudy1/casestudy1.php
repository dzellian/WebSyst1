<?php

$name = "Gellie Anne Costales";
$title = "Web and App Developer";
$phone = "09661981707";
$email = "gileecstls@gmail.com";
$linkedin = "linkedin.comm/in/Gellie Anne Costales";
$github = "github.comm/dzellian";
$address = "Guiset Sur, San Manuel, Pangasinan";
$birthday = "21 July 2002";
$nationality = "Filipino";
$summary = "BSIT student majoring in Web and Mobile Development, eager to apply classroom knowledge to real-world projects. 
Passionate about learning, building user-friendly applications, and growing as a developer while contributing to team success.";

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?php echo $name; ?> - Resume</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 0;
      line-height: 1.6;
      background: #f5f5f5;
    }
    .container {
      width: 80%;
      margin: 20px auto;
      background: #fff;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0px 0px 8px rgba(0,0,0,0.1);
    }
    header {
      display: flex;
      align-items: center;
      background: #f145bdff;
      color: white;
      padding: 20px;
      border-radius: 10px 10px 0 0;
    }
    header img {
      border-radius: 50%;
      margin-right: 20px;
      width: 120px;
      height: 120px;
    }
    header h1 {
      margin: 0;
    }
    .contact {
      margin-top: 5px;
      font-size: 14px;
    }
    section {
      margin: 20px 0;
    }
    h2 {
      color: #f145bdff;
      border-bottom: 2px solid #f145bdff;
      padding-bottom: 5px;
    }
    ul {
      margin: 0;
      padding-left: 20px;
    }
    .skills ul {
      columns: 2;
    }
  </style>
</head>
<body>
  <div class="container">

  
    <header>
      <img src="profile.png" alt="Profile Photo">
      <div>
        <h1><?php echo $name; ?></h1>
        <p><?php echo $title; ?></p>
        <div class="contact">
          <p><strong>Phone:</strong> <?php echo $phone; ?> | 
             <strong>Email:</strong> <?php echo $email; ?></p>
          <p><strong>LinkedIn:</strong> <?php echo $linkedin; ?> | 
             <strong>Github:</strong> <?php echo $github; ?></p>
          <p><strong>Address:</strong> <?php echo $address; ?></p>
          <p><strong>Birthday:</strong> <?php echo $birthday; ?> | 
             <strong>Nationality:</strong> <?php echo $nationality; ?></p>
        </div>
      </div>
    </header>

  
    <section>
      <p><?php echo $summary; ?></p>
    </section>


    <?php
$education = <<<HTML
<section>
  <h2>Education</h2>
  <article>
    <h3>High School (2013–2019)</h3>
    <p><em>San Quintin National High School</em><br></p>
    <p><strong>Activities:</strong></p>
    <ul>
      <li>N/A</li>
      <li>N/A</li>
    </ul>
  </article>
  <article>
    <h3>Bachelor of Science in Information Technology (2021–Present)</h3>
    <p><em>Pangasinan State University - Urdaneta City Campus</em><br></p>
    <p><strong>Specialization:</strong> Web and Mobile Developing</p>
  </article>
</section>
HTML;

$experience = <<<HTML
<section>
  <h2>Experience</h2>
  <article>
    <h3>N/A</h3>
    <p><em>N/A</em></p>
    <ul>
      <li>N/A</li>
      <li>N/A</li>
      <li>N/A</li>
    </ul>
  </article>
</section>
HTML;

echo $education;
echo $experience;
?>


    <?php
$skillsSection = <<<HTML
<section class="skills">       
    <h2>Skills</h2>       
    <ul>         
        <li>Php</li>         
        <li>C#, C++</li>         
        <li>Java, HTML</li>                
    </ul>     
</section>
HTML;

echo $skillsSection;
?>


  </div>
</body>
</html>
