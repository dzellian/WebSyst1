<?php
require_once '../includes/header.php';

if ($_SESSION['role'] != 'faculty') {
    header("Location: ../index.php");
}

// Get enrolled students
$stmt = $pdo->query("
    SELECT DISTINCT u.*, s.name as subject_name, e.subject_id
    FROM users u
    JOIN enrollments e ON u.id = e.student_id
    JOIN subjects s ON e.subject_id = s.id
    WHERE e.status = 'approved'
");
$students = $stmt->fetchAll();
?>

<h2>Class List</h2>

<div class="card">
    <table>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Profile</th>
            <th>Signature</th>
            <th>Subject</th>
            <th>Action</th>
        </tr>
        <?php foreach($students as $s): ?>
            <tr>
                <td><?php echo $s['name']; ?></td>
                <td><?php echo $s['email']; ?></td>
                <td>
                    <?php if($s['profile_pic']): ?>
                        <img src="../uploads/profiles/<?php echo $s['profile_pic']; ?>" class="profile-pic" alt="Profile">
                    <?php endif; ?>
                </td>
                <td>
                    <?php if($s['signature']): ?>
                        <img src="../uploads/signatures/<?php echo $s['signature']; ?>" class="signature-img" alt="Signature">
                    <?php endif; ?>
                </td>
                <td><?php echo $s['subject_name']; ?></td>
                <td>
                    <a href="submit_grades.php?student_id=<?php echo $s['id']; ?>&subject_id=<?php echo $s['subject_id']; ?>" style="background: #27ae60; padding: 5px 10px; color: white; text-decoration: none; border-radius: 3px;">
                        Grade
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>