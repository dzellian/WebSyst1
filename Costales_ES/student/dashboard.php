<?php
require_once '../includes/header.php';

if ($_SESSION['role'] != 'student') {
    header("Location: ../index.php");
}

$student_id = $_SESSION['user_id'];

// Get student info
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch();

// Get total enrolled subjects
$stmt = $pdo->prepare("
    SELECT COUNT(*) as total_enrolled
    FROM enrollments
    WHERE student_id = ? AND status = 'approved'
");
$stmt->execute([$student_id]);
$total_enrolled = $stmt->fetch()['total_enrolled'];

// Get pending enrollments
$stmt = $pdo->prepare("
    SELECT COUNT(*) as pending_enrollments
    FROM enrollments
    WHERE student_id = ? AND status = 'pending'
");
$stmt->execute([$student_id]);
$pending_enrollments = $stmt->fetch()['pending_enrollments'];

// Get rejected enrollments
$stmt = $pdo->prepare("
    SELECT COUNT(*) as rejected_enrollments
    FROM enrollments
    WHERE student_id = ? AND status = 'rejected'
");
$stmt->execute([$student_id]);
$rejected_enrollments = $stmt->fetch()['rejected_enrollments'];

// Get current GPA (average grade)
$stmt = $pdo->prepare("
    SELECT AVG(
        CASE 
            WHEN grade = 'A' THEN 4.0
            WHEN grade = 'B' THEN 3.0
            WHEN grade = 'C' THEN 2.0
            WHEN grade = 'D' THEN 1.0
            WHEN grade = 'F' THEN 0.0
            ELSE NULL
        END
    ) as gpa
    FROM enrollments
    WHERE student_id = ? AND grade IS NOT NULL
");
$stmt->execute([$student_id]);
$gpa_data = $stmt->fetch();
$gpa = $gpa_data['gpa'] ? round($gpa_data['gpa'], 2) : 'N/A';

// Get completed subjects
$stmt = $pdo->prepare("
    SELECT COUNT(*) as completed
    FROM completed_subjects
    WHERE student_id = ?
");
$stmt->execute([$student_id]);
$completed_count = $stmt->fetch()['completed'];

// Get enrolled subjects with status
$stmt = $pdo->prepare("
    SELECT s.*, e.status, e.grade, e.enrolled_at
    FROM enrollments e
    JOIN subjects s ON e.subject_id = s.id
    WHERE e.student_id = ?
    ORDER BY e.enrolled_at DESC
");
$stmt->execute([$student_id]);
$enrollments = $stmt->fetchAll();

// Get completed subjects
$stmt = $pdo->prepare("
    SELECT s.*
    FROM completed_subjects cs
    JOIN subjects s ON cs.subject_id = s.id
    WHERE cs.student_id = ?
");
$stmt->execute([$student_id]);
$completed_subjects = $stmt->fetchAll();

// Get available subjects (not enrolled)
$stmt = $pdo->prepare("
    SELECT s.*
    FROM subjects s
    WHERE s.id NOT IN (
        SELECT subject_id FROM enrollments WHERE student_id = ?
    )
    LIMIT 6
");
$stmt->execute([$student_id]);
$available_subjects = $stmt->fetchAll();

// Get subjects by status
$approved_count = 0;
$pending_count = 0;
$rejected_count = 0;
$grades_received = 0;

foreach ($enrollments as $e) {
    if ($e['status'] == 'approved') $approved_count++;
    if ($e['status'] == 'pending') $pending_count++;
    if ($e['status'] == 'rejected') $rejected_count++;
    if ($e['grade']) $grades_received++;
}
?>

<div style="margin-bottom: 30px;">
    <h2>Welcome, <?php echo $student['name']; ?>! 👋</h2>
    <p style="color: #666; margin-top: 5px;">Student Dashboard - <?php echo date('l, F j, Y'); ?></p>
</div>

<!-- Statistics Cards -->
<div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); margin-bottom: 30px;">
    
    <!-- Total Enrolled Card -->
    <div class="card" style="border-left: 5px solid #667eea;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <p style="color: #999; margin-bottom: 5px; font-size: 12px;">Total Enrolled</p>
                <h3 style="font-size: 32px; margin: 0; color: #667eea;"><?php echo $total_enrolled; ?></h3>
                <p style="color: #999; font-size: 11px; margin-top: 5px;">Active subjects</p>
            </div>
            <div style="font-size: 40px;">📚</div>
        </div>
    </div>

    <!-- Current GPA Card -->
    <div class="card" style="border-left: 5px solid #27ae60;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <p style="color: #999; margin-bottom: 5px; font-size: 12px;">Current GPA</p>
                <h3 style="font-size: 32px; margin: 0; color: #27ae60;">
                    <?php echo is_numeric($gpa) ? $gpa : $gpa; ?>
                </h3>
                <p style="color: #999; font-size: 11px; margin-top: 5px;">Out of 4.0</p>
            </div>
            <div style="font-size: 40px;">⭐</div>
        </div>
    </div>

    <!-- Completed Subjects Card -->
    <div class="card" style="border-left: 5px solid #f39c12;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <p style="color: #999; margin-bottom: 5px; font-size: 12px;">Completed</p>
                <h3 style="font-size: 32px; margin: 0; color: #f39c12;"><?php echo $completed_count; ?></h3>
                <p style="color: #999; font-size: 11px; margin-top: 5px;">Subjects finished</p>
            </div>
            <div style="font-size: 40px;">✅</div>
        </div>
    </div>

    <!-- Pending Requests Card -->
    <div class="card" style="border-left: 5px solid #e74c3c;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <p style="color: #999; margin-bottom: 5px; font-size: 12px;">Pending</p>
                <h3 style="font-size: 32px; margin: 0; color: #e74c3c;"><?php echo $pending_enrollments; ?></h3>
                <p style="color: #999; font-size: 11px; margin-top: 5px;">Waiting approval</p>
            </div>
            <div style="font-size: 40px;">⏳</div>
        </div>
    </div>
</div>

<!-- Main Content Grid -->
<div class="grid" style="grid-template-columns: 2fr 1fr; gap: 20px;">

    <!-- Left Column -->
    <div>
        <!-- Current Enrollments -->
        <div class="card">
            <h3>📖 My Enrollments</h3>
            <table>
                <tr>
                    <th>Subject</th>
                    <th>Code</th>
                    <th>Status</th>
                    <th>Grade</th>
                    <th>Date</th>
                </tr>
                <?php if (count($enrollments) > 0): ?>
                    <?php foreach($enrollments as $e): ?>
                        <tr>
                            <td><?php echo $e['name']; ?></td>
                            <td><strong><?php echo $e['code']; ?></strong></td>
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
                            <td>
                                <?php if($e['grade']): ?>
                                    <span style="background: #2c3e50; color: white; padding: 3px 8px; border-radius: 3px; font-weight: bold;">
                                        <?php echo $e['grade']; ?>
                                    </span>
                                <?php else: ?>
                                    <span style="color: #999;">-</span>
                                <?php endif; ?>
                            </td>
                            <td style="font-size: 12px;"><?php echo date('M d, Y', strtotime($e['enrolled_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center; color: #999;">No enrollments yet. Start enrolling in subjects!</td>
                    </tr>
                <?php endif; ?>
            </table>
        </div>

        <!-- Completed Subjects -->
        <?php if (count($completed_subjects) > 0): ?>
            <div class="card">
                <h3>✅ Completed Subjects (Prerequisites Met)</h3>
                <table>
                    <tr>
                        <th>Subject</th>
                        <th>Code</th>
                        <th>Description</th>
                    </tr>
                    <?php foreach($completed_subjects as $c): ?>
                        <tr>
                            <td><?php echo $c['name']; ?></td>
                            <td><strong><?php echo $c['code']; ?></strong></td>
                            <td style="font-size: 12px; color: #666;">
                                <?php echo strlen($c['description']) > 40 ? substr($c['description'], 0, 40) . '...' : $c['description']; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        <?php endif; ?>

        <!-- Available Subjects to Enroll -->
        <div class="card">
            <h3>🎯 Available Subjects to Enroll</h3>
            <?php if (count($available_subjects) > 0): ?>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                    <?php foreach($available_subjects as $subj): ?>
                        <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; border-left: 3px solid #667eea;">
                            <h4 style="margin: 0 0 5px 0;"><?php echo $subj['name']; ?></h4>
                            <p style="color: #667eea; font-weight: bold; margin: 0 0 10px 0; font-size: 12px;">
                                <?php echo $subj['code']; ?>
                            </p>
                            <p style="color: #666; font-size: 12px; margin: 0 0 10px 0;">
                                <?php echo strlen($subj['description']) > 50 ? substr($subj['description'], 0, 50) . '...' : $subj['description']; ?>
                            </p>
                            <a href="enroll.php?subject_id=<?php echo $subj['id']; ?>" 
                               style="background: #667eea; color: white; padding: 8px 15px; text-decoration: none; border-radius: 3px; display: inline-block; font-size: 12px;">
                                Enroll Now
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p style="text-align: center; color: #999;">All available subjects have been viewed. Try enrolling in more!</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Right Column -->
    <div>
        <!-- Profile Card -->
        <div class="card">
            <h3>👤 Your Profile</h3>
            <?php if($student['profile_pic']): ?>
                <img src="../uploads/profiles/<?php echo $student['profile_pic']; ?>" 
                     alt="Profile" 
                     style="width: 100%; border-radius: 5px; margin-bottom: 15px; object-fit: cover; height: 150px;">
            <?php else: ?>
                <div style="background: #f0f0f0; width: 100%; height: 150px; border-radius: 5px; display: flex; align-items: center; justify-content: center; margin-bottom: 15px;">
                    <span style="color: #999;">No profile picture</span>
                </div>
            <?php endif; ?>
            
            <p><strong><?php echo $student['name']; ?></strong></p>
            <p style="color: #666; font-size: 12px; margin-bottom: 10px;"><?php echo $student['email']; ?></p>
            
            <div style="background: #f8f9fa; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
                <p style="margin: 0; color: #999; font-size: 11px;">Student ID</p>
                <p style="margin: 0; font-weight: bold; color: #667eea;"><?php echo $student['id']; ?></p>
            </div>

            <?php if($student['signature']): ?>
                <div style="margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #ddd;">
                    <p style="font-size: 11px; color: #999; margin-bottom: 5px;">Signature</p>
                    <img src="../uploads/signatures/<?php echo $student['signature']; ?>" 
                         style="max-width: 100%; height: auto; border-radius: 3px;">
                </div>
            <?php endif; ?>
            
            <a href="profile.php" style="display: block; background: #667eea; color: white; padding: 10px; text-align: center; text-decoration: none; border-radius: 5px; font-size: 12px;">
                Edit Profile
            </a>
        </div>

        <!-- Status Summary -->
        <div class="card">
            <h3>📊 Status Summary</h3>
            <div style="space-y: 10px;">
                <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eee;">
                    <span style="color: #666;">Approved</span>
                    <span style="background: #27ae60; color: white; padding: 2px 8px; border-radius: 3px; font-size: 12px; font-weight: bold;">
                        <?php echo $approved_count; ?>
                    </span>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eee;">
                    <span style="color: #666;">Pending</span>
                    <span style="background: #f39c12; color: white; padding: 2px 8px; border-radius: 3px; font-size: 12px; font-weight: bold;">
                        <?php echo $pending_count; ?>
                    </span>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eee;">
                    <span style="color: #666;">Rejected</span>
                    <span style="background: #e74c3c; color: white; padding: 2px 8px; border-radius: 3px; font-size: 12px; font-weight: bold;">
                        <?php echo $rejected_count; ?>
                    </span>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 8px 0;">
                    <span style="color: #666;">Grades Received</span>
                    <span style="background: #2c3e50; color: white; padding: 2px 8px; border-radius: 3px; font-size: 12px; font-weight: bold;">
                        <?php echo $grades_received; ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card">
            <h3>⚡ Quick Actions</h3>
            <div style="display: flex; flex-direction: column; gap: 8px;">
                <a href="enroll.php" style="background: #667eea; color: white; padding: 10px; text-align: center; text-decoration: none; border-radius: 5px; font-size: 12px;">
                    📝 Enroll in Subject
                </a>
                <a href="profile.php" style="background: #27ae60; color: white; padding: 10px; text-align: center; text-decoration: none; border-radius: 5px; font-size: 12px;">
                    👤 View Profile
                </a>
                <a href="../logout.php" style="background: #e74c3c; color: white; padding: 10px; text-align: center; text-decoration: none; border-radius: 5px; font-size: 12px;">
                    🚪 Logout
                </a>
            </div>
        </div>

        <!-- Academic Info -->
        <div class="card" style="background: #f0f7ff; border-left: 5px solid #667eea;">
            <h3 style="color: #667eea; margin-top: 0;">ℹ️ Academic Info</h3>
            <div style="font-size: 12px;">
                <p style="margin: 5px 0; color: #555;">
                    <strong>Member Since:</strong><br>
                    <?php echo date('M d, Y', strtotime($student['created_at'])); ?>
                </p>
                <p style="margin: 10px 0 0 0; color: #555;">
                    <strong>Account Status:</strong><br>
                    <span style="color: #27ae60; font-weight: bold;">🟢 Active</span>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Progress Chart (Optional) -->
<div class="card" style="margin-top: 30px;">
    <h3>📈 Enrollment Progress</h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 20px; margin-top: 20px;">
        
        <!-- Progress Bar for Approved -->
        <div>
            <p style="margin: 0 0 10px 0; font-size: 12px; color: #666;">Approved (<?php echo $approved_count; ?>)</p>
            <div style="background: #ecf0f1; height: 20px; border-radius: 10px; overflow: hidden;">
                <div style="background: #27ae60; height: 100%; width: <?php echo $total_enrolled > 0 ? ($approved_count / $total_enrolled * 100) : 0; ?>%; transition: 0.3s;"></div>
            </div>
        </div>

        <!-- Progress Bar for Pending -->
        <div>
            <p style="margin: 0 0 10px 0; font-size: 12px; color: #666;">Pending (<?php echo $pending_count; ?>)</p>
            <div style="background: #ecf0f1; height: 20px; border-radius: 10px; overflow: hidden;">
                <div style="background: #f39c12; height: 100%; width: <?php echo $total_enrolled + $pending_count > 0 ? ($pending_count / ($total_enrolled + $pending_count) * 100) : 0; ?>%; transition: 0.3s;"></div>
            </div>
        </div>

        <!-- Progress Bar for Rejected -->
        <div>
            <p style="margin: 0 0 10px 0; font-size: 12px; color: #666;">Rejected (<?php echo $rejected_count; ?>)</p>
            <div style="background: #ecf0f1; height: 20px; border-radius: 10px; overflow: hidden;">
                <div style="background: #e74c3c; height: 100%; width: <?php echo $total_enrolled + $pending_count + $rejected_count > 0 ? ($rejected_count / ($total_enrolled + $pending_count + $rejected_count) * 100) : 0; ?>%; transition: 0.3s;"></div>
            </div>
        </div>

        <!-- Progress Bar for Grades -->
        <div>
            <p style="margin: 0 0 10px 0; font-size: 12px; color: #666;">Graded (<?php echo $grades_received; ?>)</p>
            <div style="background: #ecf0f1; height: 20px; border-radius: 10px; overflow: hidden;">
                <div style="background: #2c3e50; height: 100%; width: <?php echo $total_enrolled > 0 ? ($grades_received / $total_enrolled * 100) : 0; ?>%; transition: 0.3s;"></div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>