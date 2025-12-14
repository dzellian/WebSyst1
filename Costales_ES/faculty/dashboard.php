<?php
require_once '../includes/header.php';

if ($_SESSION['role'] != 'faculty') {
    header("Location: ../index.php");
}

$faculty_id = $_SESSION['user_id'];

// Get faculty info
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$faculty_id]);
$faculty = $stmt->fetch();

// Get total students taught
$stmt = $pdo->query("
    SELECT COUNT(DISTINCT e.student_id) as total_students
    FROM enrollments e
    WHERE e.status = 'approved'
");
$total_students = $stmt->fetch()['total_students'];

// Get total subjects
$stmt = $pdo->query("
    SELECT COUNT(DISTINCT e.subject_id) as total_subjects
    FROM enrollments e
    WHERE e.status = 'approved'
");
$total_subjects = $stmt->fetch()['total_subjects'];

// Get pending grades (students without grades)
$stmt = $pdo->query("
    SELECT COUNT(*) as pending_grades
    FROM enrollments e
    WHERE e.status = 'approved' AND e.grade IS NULL
");
$pending_grades = $stmt->fetch()['pending_grades'];

// Get list of subjects with enrolled students
$stmt = $pdo->query("
    SELECT DISTINCT s.id, s.name, s.code, COUNT(DISTINCT e.student_id) as student_count
    FROM subjects s
    LEFT JOIN enrollments e ON s.id = e.subject_id AND e.status = 'approved'
    GROUP BY s.id, s.name, s.code
    HAVING student_count > 0
    ORDER BY s.name
");
$subjects = $stmt->fetchAll();

// Get recent enrollment requests (pending)
$stmt = $pdo->query("
    SELECT e.*, u.name as student_name, s.name as subject_name
    FROM enrollments e
    JOIN users u ON e.student_id = u.id
    JOIN subjects s ON e.subject_id = s.id
    WHERE e.status = 'pending'
    ORDER BY e.enrolled_at DESC
    LIMIT 5
");
$pending_requests = $stmt->fetchAll();

// Get grades submitted today
$stmt = $pdo->query("
    SELECT e.*, u.name as student_name, s.name as subject_name
    FROM enrollments e
    JOIN users u ON e.student_id = u.id
    JOIN subjects s ON e.subject_id = s.id
    WHERE DATE(e.enrolled_at) = CURDATE() AND e.grade IS NOT NULL
    ORDER BY e.enrolled_at DESC
    LIMIT 5
");
$recent_grades = $stmt->fetchAll();
?>

<div style="margin-bottom: 30px;">
    <h2>Welcome, <?php echo $faculty['name']; ?>! 👋</h2>
    <p style="color: #666; margin-top: 5px;">Faculty Dashboard</p>
</div>

<!-- Statistics Cards -->
<div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); margin-bottom: 30px;">
    
    <!-- Total Students Card -->
    <div class="card" style="border-left: 5px solid #667eea;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <p style="color: #999; margin-bottom: 5px;">Total Students</p>
                <h3 style="font-size: 32px; margin: 0; color: #667eea;"><?php echo $total_students; ?></h3>
                <p style="color: #999; font-size: 12px; margin-top: 5px;">Enrolled in your classes</p>
            </div>
            <div style="font-size: 40px;">👥</div>
        </div>
    </div>

    <!-- Total Subjects Card -->
    <div class="card" style="border-left: 5px solid #27ae60;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <p style="color: #999; margin-bottom: 5px;">Teaching Subjects</p>
                <h3 style="font-size: 32px; margin: 0; color: #27ae60;"><?php echo $total_subjects; ?></h3>
                <p style="color: #999; font-size: 12px; margin-top: 5px;">Active subjects</p>
            </div>
            <div style="font-size: 40px;">📚</div>
        </div>
    </div>

    <!-- Pending Grades Card -->
    <div class="card" style="border-left: 5px solid #f39c12;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <p style="color: #999; margin-bottom: 5px;">Pending Grades</p>
                <h3 style="font-size: 32px; margin: 0; color: #f39c12;"><?php echo $pending_grades; ?></h3>
                <p style="color: #999; font-size: 12px; margin-top: 5px;">Need grade submission</p>
            </div>
            <div style="font-size: 40px;">📝</div>
        </div>
    </div>

    <!-- Pending Requests Card -->
    <div class="card" style="border-left: 5px solid #e74c3c;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <p style="color: #999; margin-bottom: 5px;">Enrollment Requests</p>
                <h3 style="font-size: 32px; margin: 0; color: #e74c3c;"><?php echo count($pending_requests); ?></h3>
                <p style="color: #999; font-size: 12px; margin-top: 5px;">Waiting approval</p>
            </div>
            <div style="font-size: 40px;">⏳</div>
        </div>
    </div>
</div>

<!-- Main Content Grid -->
<div class="grid" style="grid-template-columns: 2fr 1fr; gap: 20px;">

    <!-- Left Column -->
    <div>
        <!-- Subjects & Students Table -->
        <div class="card">
            <h3>Your Classes</h3>
            <table>
                <tr>
                    <th>Subject Name</th>
                    <th>Code</th>
                    <th>Students</th>
                    <th>Action</th>
                </tr>
                <?php if (count($subjects) > 0): ?>
                    <?php foreach($subjects as $subj): ?>
                        <tr>
                            <td><?php echo $subj['name']; ?></td>
                            <td><strong><?php echo $subj['code']; ?></strong></td>
                            <td>
                                <span style="background: #667eea; color: white; padding: 4px 10px; border-radius: 3px;">
                                    <?php echo $subj['student_count']; ?>
                                </span>
                            </td>
                            <td>
                                <a href="class_list.php?subject_id=<?php echo $subj['id']; ?>" 
                                   style="background: #27ae60; color: white; padding: 5px 10px; text-decoration: none; border-radius: 3px; font-size: 12px;">
                                    View Class
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align: center; color: #999;">No classes assigned</td>
                    </tr>
                <?php endif; ?>
            </table>
        </div>

        <!-- Recent Grades Submitted -->
        <div class="card">
            <h3>Recent Grades Submitted</h3>
            <table>
                <tr>
                    <th>Student</th>
                    <th>Subject</th>
                    <th>Grade</th>
                    <th>Date</th>
                </tr>
                <?php if (count($recent_grades) > 0): ?>
                    <?php foreach($recent_grades as $grade): ?>
                        <tr>
                            <td><?php echo $grade['student_name']; ?></td>
                            <td><?php echo $grade['subject_name']; ?></td>
                            <td>
                                <span style="background: #27ae60; color: white; padding: 3px 8px; border-radius: 3px; font-weight: bold;">
                                    <?php echo $grade['grade']; ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($grade['enrolled_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align: center; color: #999;">No grades submitted today</td>
                    </tr>
                <?php endif; ?>
            </table>
        </div>
    </div>

    <!-- Right Column -->
    <div>
        <!-- Profile Card -->
        <div class="card">
            <h3>Your Profile</h3>
            <?php if($faculty['profile_pic']): ?>
                <img src="../uploads/profiles/<?php echo $faculty['profile_pic']; ?>" 
                     class="profile-pic" alt="Profile" 
                     style="width: 100%; border-radius: 5px; margin-bottom: 15px;">
            <?php else: ?>
                <div style="background: #f0f0f0; width: 100%; height: 150px; border-radius: 5px; display: flex; align-items: center; justify-content: center; margin-bottom: 15px;">
                    <span style="color: #999;">No profile picture</span>
                </div>
            <?php endif; ?>
            
            <p><strong><?php echo $faculty['name']; ?></strong></p>
            <p style="color: #666; font-size: 12px; margin-bottom: 15px;"><?php echo $faculty['email']; ?></p>
            
            <?php if($faculty['signature']): ?>
                <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #ddd;">
                    <p style="font-size: 12px; color: #999; margin-bottom: 5px;">Signature</p>
                    <img src="../uploads/signatures/<?php echo $faculty['signature']; ?>" 
                         style="max-width: 100%; height: auto;">
                </div>
            <?php endif; ?>
            
            <a href="profile.php" style="display: block; margin-top: 15px; background: #667eea; color: white; padding: 10px; text-align: center; text-decoration: none; border-radius: 5px;">
                Edit Profile
            </a>
        </div>

        <!-- Quick Actions -->
        <div class="card">
            <h3>Quick Actions</h3>
            <div style="display: flex; flex-direction: column; gap: 10px;">
                <a href="class_list.php" style="background: #667eea; color: white; padding: 10px; text-align: center; text-decoration: none; border-radius: 5px;">
                    👥 View All Classes
                </a>
                <a href="submit_grades.php" style="background: #27ae60; color: white; padding: 10px; text-align: center; text-decoration: none; border-radius: 5px;">
                    📊 Submit Grades
                </a>
                <a href="../logout.php" style="background: #e74c3c; color: white; padding: 10px; text-align: center; text-decoration: none; border-radius: 5px;">
                    🚪 Logout
                </a>
            </div>
        </div>

        <!-- Pending Requests Alert -->
        <?php if (count($pending_requests) > 0): ?>
            <div class="card" style="background: #fff3cd; border-left: 5px solid #f39c12;">
                <h3 style="color: #f39c12; margin-top: 0;">⏳ Pending Requests</h3>
                <div style="max-height: 250px; overflow-y: auto;">
                    <?php foreach($pending_requests as $req): ?>
                        <div style="padding: 10px; border-bottom: 1px solid #f0e6d2; font-size: 12px;">
                            <p style="margin: 0; margin-bottom: 5px;"><strong><?php echo $req['student_name']; ?></strong></p>
                            <p style="margin: 0; color: #666; margin-bottom: 5px;">📚 <?php echo $req['subject_name']; ?></p>
                            <p style="margin: 0; color: #999; font-size: 11px;">
                                <?php echo date('M d, Y H:i', strtotime($req['enrolled_at'])); ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Additional Info -->
<div class="card" style="margin-top: 30px; background: #f8f9fa;">
    <h3>System Information</h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
        <div>
            <p style="color: #999; font-size: 12px;">Member Since</p>
            <p style="font-size: 16px; font-weight: bold;">
                <?php echo date('M d, Y', strtotime($faculty['created_at'])); ?>
            </p>
        </div>
        <div>
            <p style="color: #999; font-size: 12px;">Last Login</p>
            <p style="font-size: 16px; font-weight: bold;">Today</p>
        </div>
        <div>
            <p style="color: #999; font-size: 12px;">Account Status</p>
            <p style="font-size: 16px; font-weight: bold; color: #27ae60;">🟢 Active</p>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>