<?php
require_once '../includes/header.php';

if ($_SESSION['role'] != 'faculty') {
    header("Location: ../index.php");
}

$student_id = $_GET['student_id'];
$subject_id = $_GET['subject_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $grade = $_POST['grade'];
    $stmt = $pdo->prepare("UPDATE enrollments SET grade = ? WHERE student_id = ? AND subject_id = ?");
    if ($stmt->execute([$grade, $student_id, $subject_id])) {
        $success = "Grade submitted successfully";
    }
}

// Get student info
$stmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch();
?>

<h2>Submit Grade for <?php echo $student['name']; ?></h2>

<?php if(isset($success)): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<div class="card" style="max-width: 400px;">
    <form method="POST">
        <div class="form-group">
            <label>Grade (A-F):</label>
            <input type="text" name="grade" maxlength="1" required>
        </div>
        <button type="submit">Submit Grade</button>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>