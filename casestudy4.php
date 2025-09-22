<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Biodata</title>
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
    form {
      max-width: 800px;
      margin: auto;
      background: #fff;
      padding: 20px 40px;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0,0,0,0.2);
    }
    label {
      display: block;
      margin-bottom: 5px;
      font-weight: bold;
    }
    input, select, textarea {
      width: 100%;
      padding: 8px;
      margin-bottom: 15px;
      border: 1px solid #ccc;
      border-radius: 4px;
    }
    input[type="submit"] {
      background-color: #000;
      color: white;
      font-size: 16px;
      cursor: pointer;
      border: none;
      padding: 12px;
      border-radius: 4px;
    }
    input[type="submit"]:hover {
      background-color: #444;
    }
    .section {
      margin-bottom: 30px;
    }
    .photo {
      text-align: center;
      margin-bottom: 20px;
    }
  </style>
</head>
<body>

  <form method="post" action="biodata_result.php" enctype="multipart/form-data">
    <div class="photo">
      <label>Upload Photo:</label>
      <input type="file" name="photo" accept=".jpg, .jpeg, .png" required>
    </div>

    <h2>PERSONAL INFORMATION</h2>
    <div class="section">
      <label>Full Name:</label>
      <input type="text" name="fullname" required>

      <label>Address:</label>
      <textarea name="address" required></textarea>

      <label>Date of Birth:</label>
      <input type="date" name="dob" required>

      <label>Place of Birth:</label>
      <input type="text" name="pob" required pattern="[A-Za-z\s]+">

      <label>Gender:</label>
      <select name="gender" required>
        <option value="">-- Select Gender --</option>
        <option>Male</option>
        <option>Female</option>
      </select>

      <label>Civil Status:</label>
      <select name="civil_status" required>
        <option value="">-- Select Status --</option>
        <option>Single</option>
        <option>Married</option>
        <option>Widowed</option>
        <option>Separated</option>
      </select>

      <label>Nationality:</label>
      <input type="text" name="nationality" required pattern="[A-Za-z\s]+">

      <label>Religion:</label>
      <input type="text" name="religion" >
    </div>

    <h2>CONTACT INFORMATION</h2>
    <div class="section">
      <label>Mobile No.:</label>
      <input type="text" name="mobile" pattern="[0-9]{11}" placeholder="e.g. 09123456789" required>

      <label>Email:</label>
      <input type="email" name="email" required>
    </div>

    <h2>EDUCATIONAL BACKGROUND</h2>
    <div class="section">
      <label>Elementary:</label>
      <input type="text" name="elem" required>

      <label>High School:</label>
      <input type="text" name="hs" required>

      <label>College:</label>
      <input type="text" name="college" required>

      <label>Degree:</label>
      <input type="text" name="degree" required>
    </div>

    <h2>OTHER INFORMATION</h2>
    <div class="section">
      <label>Skills:</label>
      <textarea name="skills" required></textarea>

      <label>Work Experience:</label>
      <textarea name="experience" required></textarea>
    </div>

    <input type="submit" value="Submit">
  </form>

</body>
</html>
