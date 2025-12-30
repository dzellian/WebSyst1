<?php
$page_title = 'Manage Subjects';
require_once '../config/database.php';
require_once '../includes/header.php';
requireRole('admin');

$database = new Database();
$db = $database->getConnection();

$success = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $subject_code = clean($_POST['subject_code']);
                $subject_name = clean($_POST['subject_name']);
                $description = clean($_POST['description']);
                $units = (int)$_POST['units'];
                $semester = (int)$_POST['semester'];
                $year_level = (int)$_POST['year_level'];
                $faculty_id = !empty($_POST['faculty_id']) ? (int)$_POST['faculty_id'] : null;
                
                $query = "INSERT INTO subjects (subject_code, subject_name, description, units, semester, year_level, faculty_id) 
                         VALUES (:code, :name, :desc, :units, :semester, :year, :faculty)";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':code', $subject_code);
                $stmt->bindParam(':name', $subject_name);
                $stmt->bindParam(':desc', $description);
                $stmt->bindParam(':units', $units);
                $stmt->bindParam(':semester', $semester);
                $stmt->bindParam(':year', $year_level);
                $stmt->bindParam(':faculty', $faculty_id);
                
                if ($stmt->execute()) {
                    $subject_id = $db->lastInsertId();
                    
                    // Add prerequisites
                    if (!empty($_POST['prerequisites'])) {
                        $prereq_query = "INSERT INTO prerequisites (subject_id, required_subject_id) VALUES (:subject_id, :required_id)";
                        $prereq_stmt = $db->prepare($prereq_query);
                        
                        foreach ($_POST['prerequisites'] as $required_id) {
                            $prereq_stmt->bindParam(':subject_id', $subject_id);
                            $prereq_stmt->bindParam(':required_id', $required_id);
                            $prereq_stmt->execute();
                        }
                    }
                    
                    $success = 'Subject added successfully!';
                } else {
                    $error = 'Failed to add subject.';
                }
                break;
            
            case 'edit':
                $subject_id = (int)$_POST['subject_id'];
                $subject_code = clean($_POST['subject_code']);
                $subject_name = clean($_POST['subject_name']);
                $description = clean($_POST['description']);
                $units = (int)$_POST['units'];
                $semester = (int)$_POST['semester'];
                $year_level = (int)$_POST['year_level'];
                $faculty_id = !empty($_POST['faculty_id']) ? (int)$_POST['faculty_id'] : null;
                
                $query = "UPDATE subjects 
                         SET subject_code = :code, subject_name = :name, description = :desc, 
                             units = :units, semester = :semester, year_level = :year, faculty_id = :faculty
                         WHERE subject_id = :id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':code', $subject_code);
                $stmt->bindParam(':name', $subject_name);
                $stmt->bindParam(':desc', $description);
                $stmt->bindParam(':units', $units);
                $stmt->bindParam(':semester', $semester);
                $stmt->bindParam(':year', $year_level);
                $stmt->bindParam(':faculty', $faculty_id);
                $stmt->bindParam(':id', $subject_id);
                
                if ($stmt->execute()) {
                    // Delete existing prerequisites
                    $delete_prereq = "DELETE FROM prerequisites WHERE subject_id = :id";
                    $delete_stmt = $db->prepare($delete_prereq);
                    $delete_stmt->execute([':id' => $subject_id]);
                    
                    // Add new prerequisites
                    if (!empty($_POST['prerequisites'])) {
                        $prereq_query = "INSERT INTO prerequisites (subject_id, required_subject_id) VALUES (:subject_id, :required_id)";
                        $prereq_stmt = $db->prepare($prereq_query);
                        
                        foreach ($_POST['prerequisites'] as $required_id) {
                            $prereq_stmt->execute([
                                ':subject_id' => $subject_id,
                                ':required_id' => $required_id
                            ]);
                        }
                    }
                    
                    $success = 'Subject updated successfully!';
                } else {
                    $error = 'Failed to update subject.';
                }
                break;
                
            case 'delete':
                $subject_id = (int)$_POST['subject_id'];
                $query = "DELETE FROM subjects WHERE subject_id = :id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':id', $subject_id);
                
                if ($stmt->execute()) {
                    $success = 'Subject deleted successfully!';
                } else {
                    $error = 'Failed to delete subject.';
                }
                break;
        }
    }
}

// Get all subjects
$subjects_query = "SELECT s.*, u.full_name as faculty_name,
                   (SELECT COUNT(*) FROM prerequisites WHERE subject_id = s.subject_id) as prereq_count
                   FROM subjects s
                   LEFT JOIN users u ON s.faculty_id = u.user_id
                   ORDER BY s.year_level, s.semester, s.subject_code";
$subjects = $db->query($subjects_query)->fetchAll();

// Get all faculty
$faculty = $db->query("SELECT user_id, full_name FROM users WHERE role = 'faculty' ORDER BY full_name")->fetchAll();
?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">
            <i class="fas fa-book"></i>
            Subject Management
        </h2>
        <button class="btn btn-primary" onclick="openAddModal()">
            <i class="fas fa-plus"></i>
            Add Subject
        </button>
    </div>
</div>

<?php if ($success): ?>
<div class="alert alert-success">
    <i class="fas fa-check-circle"></i>
    <?php echo $success; ?>
</div>
<?php endif; ?>

<?php if ($error): ?>
<div class="alert alert-danger">
    <i class="fas fa-exclamation-circle"></i>
    <?php echo $error; ?>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Subject Name</th>
                        <th>Units</th>
                        <th>Year/Sem</th>
                        <th>Faculty</th>
                        <th>Prerequisites</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($subjects as $subject): ?>
                    <?php
                        // Get prerequisites for this subject
                        $prereq_query = "SELECT p.required_subject_id, s.subject_code, s.subject_name
                                        FROM prerequisites p
                                        JOIN subjects s ON p.required_subject_id = s.subject_id
                                        WHERE p.subject_id = :id";
                        $prereq_stmt = $db->prepare($prereq_query);
                        $prereq_stmt->execute([':id' => $subject['subject_id']]);
                        $prerequisites = $prereq_stmt->fetchAll();
                    ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($subject['subject_code']); ?></strong></td>
                        <td><?php echo htmlspecialchars($subject['subject_name']); ?></td>
                        <td><?php echo $subject['units']; ?></td>
                        <td>Year <?php echo $subject['year_level']; ?> / Sem <?php echo $subject['semester']; ?></td>
                        <td><?php echo htmlspecialchars($subject['faculty_name'] ?? 'Unassigned'); ?></td>
                        <td>
                            <?php if (count($prerequisites) > 0): ?>
                                <button class="btn btn-sm btn-info" onclick="viewPrerequisites('<?php echo htmlspecialchars($subject['subject_code']); ?>', <?php echo htmlspecialchars(json_encode($prerequisites)); ?>)">
                                    <i class="fas fa-list"></i>
                                    <?php echo count($prerequisites); ?> prerequisite(s)
                                </button>
                            <?php else: ?>
                                <span class="badge badge-success">None</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group">
                                <button class="btn btn-sm btn-warning" onclick='editSubject(<?php echo json_encode([
                                    'subject_id' => $subject['subject_id'],
                                    'subject_code' => $subject['subject_code'],
                                    'subject_name' => $subject['subject_name'],
                                    'description' => $subject['description'],
                                    'units' => $subject['units'],
                                    'semester' => $subject['semester'],
                                    'year_level' => $subject['year_level'],
                                    'faculty_id' => $subject['faculty_id'],
                                    'prerequisites' => array_column($prerequisites, 'required_subject_id')
                                ]); ?>)'>
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this subject? This will also delete all related enrollments.');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="subject_id" value="<?php echo $subject['subject_id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add/Edit Subject Modal -->
<div id="subjectModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title" id="modalTitle">Add New Subject</h3>
            <button class="modal-close" onclick="closeModal('subjectModal')">&times;</button>
        </div>
        <form method="POST" id="subjectForm">
            <div class="modal-body">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="subject_id" id="subjectId">
                
                <div class="form-group">
                    <label class="form-label required">Subject Code</label>
                    <input type="text" name="subject_code" id="subjectCode" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label required">Subject Name</label>
                    <input type="text" name="subject_name" id="subjectName" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" id="description" class="form-control" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label required">Units</label>
                    <input type="number" name="units" id="units" class="form-control" min="1" max="6" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label required">Year Level</label>
                    <select name="year_level" id="yearLevel" class="form-control" required>
                        <option value="1">1st Year</option>
                        <option value="2">2nd Year</option>
                        <option value="3">3rd Year</option>
                        <option value="4">4th Year</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label required">Semester</label>
                    <select name="semester" id="semester" class="form-control" required>
                        <option value="1">1st Semester</option>
                        <option value="2">2nd Semester</option>
                        <option value="3">Summer</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Assign Faculty</label>
                    <select name="faculty_id" id="facultyId" class="form-control">
                        <option value="">Unassigned</option>
                        <?php foreach ($faculty as $f): ?>
                        <option value="<?php echo $f['user_id']; ?>">
                            <?php echo htmlspecialchars($f['full_name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Prerequisites</label>
                    <select name="prerequisites[]" id="prerequisites" class="form-control" multiple size="5">
                        <?php foreach ($subjects as $s): ?>
                        <option value="<?php echo $s['subject_id']; ?>">
                            <?php echo htmlspecialchars($s['subject_code'] . ' - ' . $s['subject_name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="form-text">Hold Ctrl/Cmd to select multiple</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('subjectModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    <span id="submitBtnText">Save Subject</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Prerequisites View Modal -->
<div id="prereqModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title" id="prereqModalTitle">Prerequisites</h3>
            <button class="modal-close" onclick="closeModal('prereqModal')">&times;</button>
        </div>
        <div class="modal-body">
            <div id="prereqList"></div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeModal('prereqModal')">Close</button>
        </div>
    </div>
</div>

<script>
function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Add New Subject';
    document.getElementById('formAction').value = 'add';
    document.getElementById('submitBtnText').textContent = 'Save Subject';
    document.getElementById('subjectForm').reset();
    document.getElementById('subjectId').value = '';
    openModal('subjectModal');
}

function editSubject(data) {
    document.getElementById('modalTitle').textContent = 'Edit Subject';
    document.getElementById('formAction').value = 'edit';
    document.getElementById('submitBtnText').textContent = 'Update Subject';
    
    document.getElementById('subjectId').value = data.subject_id;
    document.getElementById('subjectCode').value = data.subject_code;
    document.getElementById('subjectName').value = data.subject_name;
    document.getElementById('description').value = data.description || '';
    document.getElementById('units').value = data.units;
    document.getElementById('yearLevel').value = data.year_level;
    document.getElementById('semester').value = data.semester;
    document.getElementById('facultyId').value = data.faculty_id || '';
    
    // Select prerequisites
    const prereqSelect = document.getElementById('prerequisites');
    Array.from(prereqSelect.options).forEach(option => {
        option.selected = data.prerequisites.includes(parseInt(option.value));
    });
    
    openModal('subjectModal');
}

function viewPrerequisites(subjectCode, prerequisites) {
    document.getElementById('prereqModalTitle').textContent = 'Prerequisites for ' + subjectCode;
    
    let html = '<ul style="list-style: none; padding: 0;">';
    prerequisites.forEach(prereq => {
        html += `<li style="padding: 10px; border-bottom: 1px solid var(--border);">
                    <i class="fas fa-book" style="color: var(--primary); margin-right: 10px;"></i>
                    <strong>${prereq.subject_code}</strong> - ${prereq.subject_name}
                 </li>`;
    });
    html += '</ul>';
    
    document.getElementById('prereqList').innerHTML = html;
    openModal('prereqModal');
}
</script>

<?php require_once '../includes/footer.php'; ?>