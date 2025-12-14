<?php
require_once '../includes/header.php';

if ($_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
}

// Add subject
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    $stmt = $pdo->prepare("INSERT INTO subjects (name, code, description, prerequisite_id) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$_POST['name'], $_POST['code'], $_POST['description'], $_POST['prerequisite_id'] ?: null])) {
        $success = "Subject added";
    }
}

// Delete subject
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM subjects WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    $success = "Subject deleted";
}

$stmt = $pdo->query("SELECT * FROM subjects");
$subjects = $stmt->fetchAll();
?>

<h2>Manage Subjects</h2>

<?php if(isset($success)): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<div class="grid">
    <div class="card">
        <h3>Add Subject</h3>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label>Subject Name:</label>
                <input type="text" name="name" required>
            </div>
            <div class="form-group">
                <label>Code:</label>
                <input type="text" name="code" required>
            </div>
            <div class="form-group">
                <label>Description:</label>
                <textarea name="description"></textarea>
            </div>
            <div class="form-group">
                <label>Prerequisite:</label>
                <select name="prerequisite_id">
                    <option value="">None</option>
                    <?php foreach($subjects as $s): ?>
                        <option value="<?php echo $s['id']; ?>"><?php echo $s['name']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit">Add Subject</button>
        </form>
    </div>
    
    <div class="card">
        <h3>Subjects List</h3>
        <table>
            <tr>
                <th>Name</th>
                <th>Code</th>
                <th>Prerequisite</th>
                <th>Action</th>
            </tr>
            <?php foreach($subjects as $s): ?>
                <tr>
                    <td><?php echo $s['name']; ?></td>
                    <td><?php echo $s['code']; ?></td>
                    <td>
                        <?php 
                        if($s['prerequisite_id']) {
                            $p = $pdo->prepare("SELECT name FROM subjects WHERE id = ?");
                            $p->execute([$s['prerequisite_id']]);
                            $prereq = $p->fetch();
                            echo $prereq['name'];
                        } else {
                            echo "None";
                        }
                        ?>
                    </td>
                    <td>
                        <a href="?delete=<?php echo $s['id']; ?>" class="btn btn-danger" onclick="return confirm('Delete?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>