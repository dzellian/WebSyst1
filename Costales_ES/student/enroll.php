<?php
require_once '../includes/header.php';

if ($_SESSION['role'] != 'student') {
    header("Location: ../index.php");
}

$student_id = $_SESSION['user_id'];

// Get subject if passed in URL
$subject_id = isset($_GET['subject_id']) ? $_GET['subject_id'] : null;

// Get all subjects
$stmt = $pdo->query("SELECT * FROM subjects ORDER BY name");
$subjects = $stmt->fetchAll();

// Get completed subjects
$stmt = $pdo->prepare("SELECT subject_id FROM completed_subjects WHERE student_id = ?");
$stmt->execute([$student_id]);
$completed = $stmt->fetchAll(PDO::FETCH_COLUMN);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $subject_id = $_POST['subject_id'];
    
    // Check if already enrolled
    $stmt = $pdo->prepare("SELECT id FROM enrollments WHERE student_id = ? AND subject_id = ?");
    $stmt->execute([$student_id, $subject_id]);
    if ($stmt->fetch()) {
        $error = "Already enrolled in this subject";
    } else {
        // Get subject details
        $stmt = $pdo->prepare("SELECT prerequisite_id FROM subjects WHERE id = ?");
        $stmt->execute([$subject_id]);
        $subject = $stmt->fetch();
        
        // Check prerequisite
        if ($subject['prerequisite_id'] && !in_array($subject['prerequisite_id'], $completed)) {
            $error = "You must complete the prerequisite first";
        } else {
            $stmt = $pdo->prepare("INSERT INTO enrollments (student_id, subject_id) VALUES (?, ?)");
            if ($stmt->execute([$student_id, $subject_id])) {
                $success = "Enrollment request submitted successfully!";
                $subject_id = null;
            }
        }
    }
}

// Get enrolled subjects
$stmt = $pdo->prepare("SELECT s.*, e.status FROM enrollments e JOIN subjects s ON e.subject_id = s.id WHERE e.student_id = ? ORDER BY e.enrolled_at DESC");
$stmt->execute([$student_id]);
$enrolled = $stmt->fetchAll();
?>

<h2>📚 Enroll in Subjects</h2>

<?php if(isset($success)): ?>
    <div class="alert alert-success">✅ <?php echo $success; ?></div>
<?php endif; ?>

<?php if(isset($error)): ?>
    <div class="alert alert-danger">❌ <?php echo $error; ?></div>
<?php endif; ?>

<div class="grid" style="grid-template-columns: 1fr 1fr;">
    <div class="card">
        <h3>Select a Subject to Enroll</h3>
        <form method="POST">
            <div class="form-group">
                <label>Choose Subject:</label>
                <select name="subject_id" required>
                    <option value="">-- Select a subject --</option>
                    <?php foreach($subjects as $s): ?>
                        <option value="<?php echo $s['id']; ?>" <?php echo ($subject_id == $s['id']) ? 'selected' : ''; ?>>
                            <?php echo $s['name']; ?> (<?php echo $s['code']; ?>)
                            <?php if($s['prerequisite_id']): ?>
                                - Requires: <?php 
                                $p = $pdo->prepare("SELECT name FROM subjects WHERE id = ?");
                                $p->execute([$s['prerequisite_id']]);
                                $prereq = $p->fetch();
                                echo $prereq['name'];
                                ?>
                            <?php endif; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" style="width: 100%;">Enroll Now</button>
        </form>
    </div>

    <div class="card">
        <h3>Your Enrollments (<?php echo count($enrolled); ?>)</h3>
        <table>
            <tr>
                <th>Subject</th>
                <th>Status</th>
            </tr>
            <?php if (count($enrolled) > 0): ?>
                <?php foreach($enrolled as $e): ?>
                    <tr>
                        <td><?php echo $e['name']; ?></td>
                        <td>
                            <?php 
                            $status_color = [
                                'pending' => '#f39c12',
                                'approved' => '#27ae60',
                                'rejected' => '#e74c3c'
                            ];
                            $color = $status_color[$e['status']] ?? '#95a5a6';
                            ?>
                            <span style="background: <?php echo $color; ?>; color: white; padding: 3px 8px; border-radius: 3px; font-size: 11px;">
                                <?php echo ucfirst($e['status']); ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="2" style="text-align: center; color: #999;">Not enrolled in any subject</td>
                </tr>
            <?php endif; ?>
        </table>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>