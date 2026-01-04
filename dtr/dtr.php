<?php
// dtr.php
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Handle Time In
if (isset($_POST['time_in'])) {
    $today = date('Y-m-d');
    $current_time = date('H:i:s');
    
    // Check if already timed in today
    $stmt = $pdo->prepare("SELECT * FROM dtr_records WHERE user_id = ? AND date = ?");
    $stmt->execute([$user_id, $today]);
    
    if ($stmt->rowCount() > 0) {
        $error = "You have already timed in today!";
    } else {
        // Determine status based on time (8:00 AM is the standard time)
        $time_in_hour = date('H');
        $status = ($time_in_hour >= 8) ? 'late' : 'present';
        
        $stmt = $pdo->prepare("INSERT INTO dtr_records (user_id, date, time_in, status) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$user_id, $today, $current_time, $status])) {
            $message = "Time In recorded successfully at " . date('h:i:s A');
        } else {
            $error = "Failed to record Time In. Please try again.";
        }
    }
}

// Handle Time Out
if (isset($_POST['time_out'])) {
    $today = date('Y-m-d');
    $current_time = date('H:i:s');
    
    $stmt = $pdo->prepare("SELECT * FROM dtr_records WHERE user_id = ? AND date = ?");
    $stmt->execute([$user_id, $today]);
    $record = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$record) {
        $error = "You need to Time In first!";
    } elseif ($record['time_out']) {
        $error = "You have already timed out today!";
    } else {
        $stmt = $pdo->prepare("UPDATE dtr_records SET time_out = ? WHERE user_id = ? AND date = ?");
        if ($stmt->execute([$current_time, $user_id, $today])) {
            $message = "Time Out recorded successfully at " . date('h:i:s A');
        } else {
            $error = "Failed to record Time Out. Please try again.";
        }
    }
}

// Get current month records
$current_month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');
$stmt = $pdo->prepare("SELECT * FROM dtr_records WHERE user_id = ? AND DATE_FORMAT(date, '%Y-%m') = ? ORDER BY date DESC");
$stmt->execute([$user_id, $current_month]);
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Check today's status
$today = date('Y-m-d');
$stmt = $pdo->prepare("SELECT * FROM dtr_records WHERE user_id = ? AND date = ?");
$stmt->execute([$user_id, $today]);
$today_record = $stmt->fetch(PDO::FETCH_ASSOC);

// Calculate total hours for the month
$total_hours = 0;
foreach ($records as $rec) {
    if ($rec['time_in'] && $rec['time_out']) {
        $time_in = strtotime($rec['time_in']);
        $time_out = strtotime($rec['time_out']);
        $total_hours += ($time_out - $time_in) / 3600;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DTR System - Daily Time Record</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; }
        .navbar { background: #667eea; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .navbar h1 { font-size: 24px; }
        .navbar a { color: white; text-decoration: none; margin-left: 20px; padding: 8px 15px; border-radius: 5px; }
        .navbar a:hover { background: rgba(255,255,255,0.2); }
        .container { max-width: 1400px; margin: 40px auto; padding: 0 20px; }
        .welcome-card { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 30px; }
        .welcome-card h2 { color: #333; margin-bottom: 10px; }
        .date-time { font-size: 24px; color: #667eea; font-weight: bold; margin: 20px 0; }
        .action-buttons { display: flex; gap: 20px; margin-top: 20px; }
        .btn { padding: 15px 40px; border: none; border-radius: 5px; font-size: 16px; cursor: pointer; font-weight: bold; transition: all 0.3s; }
        .btn-in { background: #27ae60; color: white; }
        .btn-in:hover { background: #229954; transform: translateY(-2px); }
        .btn-out { background: #e74c3c; color: white; }
        .btn-out:hover { background: #c0392b; transform: translateY(-2px); }
        .btn:disabled { background: #95a5a6; cursor: not-allowed; transform: none; }
        .status-card { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 30px; }
        .status-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-top: 20px; }
        .status-item { background: #f8f9fa; padding: 20px; border-radius: 8px; text-align: center; }
        .status-item h3 { color: #667eea; font-size: 32px; margin-bottom: 5px; }
        .status-item p { color: #666; font-size: 14px; }
        .records-card { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .filter-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .filter-bar input { padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f8f9fa; font-weight: bold; color: #333; }
        .badge { padding: 5px 10px; border-radius: 15px; font-size: 12px; font-weight: bold; }
        .badge-present { background: #d4edda; color: #155724; }
        .badge-late { background: #fff3cd; color: #856404; }
        .badge-absent { background: #f8d7da; color: #721c24; }
        .badge-half-day { background: #d1ecf1; color: #0c5460; }
        .message { padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .current-status { background: #e8f4f8; padding: 20px; border-radius: 8px; margin-top: 20px; border-left: 4px solid #667eea; }
        .current-status h4 { color: #333; margin-bottom: 10px; }
        .time-display { font-size: 20px; color: #667eea; font-weight: bold; }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>Daily Time Record</h1>
        <div>
            <a href="dashboard.php">Dashboard</a>
            <?php if (isAdmin()): ?>
                <a href="admin.php">Manage Users</a>
            <?php endif; ?>
            <a href="logout.php">Logout</a>
        </div>
    </div>
    
    <div class="container">
        <?php if ($message): ?>
            <div class="message success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="message error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="welcome-card">
            <h2>Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</h2>
            <div class="date-time">
                <?php echo date('l, F d, Y'); ?>
                <span id="current-time" style="margin-left: 20px;"><?php echo date('h:i:s A'); ?></span>
            </div>
            
            <form method="POST" style="display: inline;">
                <div class="action-buttons">
                    <button type="submit" name="time_in" class="btn btn-in" 
                            <?php echo ($today_record && $today_record['time_in']) ? 'disabled' : ''; ?>>
                        ⏱️ Time In
                    </button>
                    <button type="submit" name="time_out" class="btn btn-out"
                            <?php echo (!$today_record || !$today_record['time_in'] || $today_record['time_out']) ? 'disabled' : ''; ?>>
                        ⏱️ Time Out
                    </button>
                </div>
            </form>
            
            <?php if ($today_record): ?>
            <div class="current-status">
                <h4>Today's Record:</h4>
                <p>
                    <strong>Time In:</strong> 
                    <span class="time-display">
                        <?php echo $today_record['time_in'] ? date('h:i:s A', strtotime($today_record['time_in'])) : 'Not recorded'; ?>
                    </span>
                </p>
                <?php if ($today_record['time_out']): ?>
                <p style="margin-top: 10px;">
                    <strong>Time Out:</strong> 
                    <span class="time-display">
                        <?php echo date('h:i:s A', strtotime($today_record['time_out'])); ?>
                    </span>
                </p>
                <p style="margin-top: 10px;">
                    <strong>Total Hours:</strong> 
                    <span class="time-display">
                        <?php 
                        $hours = (strtotime($today_record['time_out']) - strtotime($today_record['time_in'])) / 3600;
                        echo number_format($hours, 2) . ' hours';
                        ?>
                    </span>
                </p>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="status-card">
            <h3>Monthly Summary</h3>
            <div class="status-grid">
                <div class="status-item">
                    <h3><?php echo count($records); ?></h3>
                    <p>Total Records</p>
                </div>
                <div class="status-item">
                    <h3><?php echo count(array_filter($records, fn($r) => $r['status'] === 'present')); ?></h3>
                    <p>On Time</p>
                </div>
                <div class="status-item">
                    <h3><?php echo count(array_filter($records, fn($r) => $r['status'] === 'late')); ?></h3>
                    <p>Late</p>
                </div>
                <div class="status-item">
                    <h3><?php echo number_format($total_hours, 2); ?></h3>
                    <p>Total Hours</p>
                </div>
            </div>
        </div>
        
        <div class="records-card">
            <div class="filter-bar">
                <h3>Attendance Records</h3>
                <input type="month" value="<?php echo $current_month; ?>" 
                       onchange="window.location.href='dtr.php?month=' + this.value">
            </div>
            
            <?php if (count($records) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Day</th>
                        <th>Time In</th>
                        <th>Time Out</th>
                        <th>Hours Worked</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($records as $record): ?>
                    <tr>
                        <td><?php echo date('M d, Y', strtotime($record['date'])); ?></td>
                        <td><?php echo date('l', strtotime($record['date'])); ?></td>
                        <td><?php echo $record['time_in'] ? date('h:i A', strtotime($record['time_in'])) : '-'; ?></td>
                        <td><?php echo $record['time_out'] ? date('h:i A', strtotime($record['time_out'])) : '-'; ?></td>
                        <td>
                            <?php 
                            if ($record['time_in'] && $record['time_out']) {
                                $hours = (strtotime($record['time_out']) - strtotime($record['time_in'])) / 3600;
                                echo number_format($hours, 2) . ' hrs';
                            } else {
                                echo '-';
                            }
                            ?>
                        </td>
                        <td>
                            <span class="badge badge-<?php echo $record['status']; ?>">
                                <?php echo strtoupper($record['status']); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
                <p style="text-align: center; padding: 40px; color: #999;">No records found for this month</p>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        // Update time every second
        function updateTime() {
            const now = new Date();
            const hours = now.getHours();
            const minutes = now.getMinutes();
            const seconds = now.getSeconds();
            const ampm = hours >= 12 ? 'PM' : 'AM';
            const displayHours = hours % 12 || 12;
            
            const timeString = String(displayHours).padStart(2, '0') + ':' + 
                              String(minutes).padStart(2, '0') + ':' + 
                              String(seconds).padStart(2, '0') + ' ' + ampm;
            
            document.getElementById('current-time').textContent = timeString;
        }
        
        setInterval(updateTime, 1000);
    </script>
</body>
</html>