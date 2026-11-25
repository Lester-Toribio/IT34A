
<?php 
require_once '../../config/config.php';


requireRole('admin');

$section = $_GET['section'] ?? '';

$action = $_GET['action'] ?? '';

if ($section === 'activity-logs'){
        // Activity Logs Query
        $stmt = $pdo->query("
        SELECT * 
        FROM activity_logs
        ORDER BY activity_log_created_at DESC
    ");

    $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

} elseif ($section === 'user-sessions') {

    // User Sessions Query
    $stmt = $pdo->query("
        SELECT
            us.session_id,
            us.user_id,
            u.user_username,
            u.user_email,
            u.user_role,
            us.session_start,
            us.session_end,
            us.session_duration
        FROM user_sessions us
        INNER JOIN users u
            ON us.user_id = u.user_id
        ORDER BY us.session_start DESC
    ");

    $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if($action==='export'){
    if($section=== 'activity-logs'){

        $stmt = $pdo->query("
            SELECT *
            FROM activity_logs
            ORDER BY activity_log_created_at DESC
        ");

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        logActivity(
            $pdo,
            $_SESSION['user_id'],
            $_SESSION['user_email'],
            'export_activity_logs',
            'success'
        );
        
        exportCsv(
            'activity_logs',
            [
                'Record ID','User ID','User Email', 'Action',
                'Status','IP Address','User Agent','Date & Time',
            ],
            $rows
        );
    }
    
    if($section=== 'user-sessions'){
        $stmt = $pdo->query("
            SELECT 
                us.session_id,
                us.user_id,
                u.user_username,
                u.user_email,
                u.user_role,
                us.session_start,
                us.session_end,
                us.session_duration
            FROM user_sessions us
            INNER JOIN users u on us.user_id = u.user_id
            ORDER BY us.session_start DESC
        ");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        logActivity(
            $pdo,
            $_SESSION['user_id'],
            $_SESSION['user_email'],
            'export_activity_logs',
            'success'
        );

        foreach($rows as &$r){
            $r['sessions_end'] = $r['session_end'] ?: 'Active';

            if ($r['session_duration'] !== null){
                $s = (int)$r['session_duration'];
                $hours = floor($s/3600);
                $minutes = floor(($s % 3600)/60);
                $seconds = $s % 60;
                $r['session_duration'] = sprintf(
                    '%02d hr %02d min %02d sec',
                    $hours,$minutes,$seconds
                );
            } else {
                $r['session_duration'] = 'Active';
            }
        }

        unset($r);

        exportCsv(
            'user-sessions',
            [
                'Session ID','User ID','Username', 'Email',
                'Role','Session Start','Session End','Duration',
            ],
            $rows
        );
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h1>Hello Admin</h1>
   <a href="<?= BASE_URL ?>/auth/signout.php">Sign Out</a>

     <hr>

    <nav>
        <a href="index.php?section=activity-logs" class="btn btn-primary">Activity Logs </a>
        <a href="index.php?section=user-sessions" class="btn btn-primary">User Sessions </a>
    </nav>

<?php if($section==='activity-logs'):?>
    <h4>Activity logs
        <a href="index.php?section=activity-logs&amp;action=export">Export CSV</a>
    </h4>

    <table border="1">   
    <thead>
        <tr>
            <th>ID</th>
            <th>User ID </th>
            <th>Email</th>
            <th>Action</th>
            <th>Status</th>
            <th>IP Address</th>
            <th>User Agent</th>
            <th>Timestamp</th>
        </tr>
        </thead>
        <tbody>
            <?php foreach($activities as $activity): ?>
                <tr> 
                    <td> 
                        <?= htmlspecialchars(
                            $activity['activity_log_id']) ?>
                    </td>

                    <td> 
                        <?= htmlspecialchars(
                            $activity['user_id']) ?>
                    </td>

                    <td> 
                        <?= htmlspecialchars(
                            $activity['user_email']) ?>
                    </td>
                    <td> 
                        <?= htmlspecialchars(
                            $activity['activity_log_action']) ?>
                    </td>

                    <td> 
                        <?= htmlspecialchars(
                            $activity['activity_log_status']) ?>
                    </td>
                    <td> 
                        <?= htmlspecialchars(
                            $activity['activity_log_ip_address']) ?>
                    </td>

                    <td> 
                        <?= htmlspecialchars(
                            $activity['activity_log_user_agent']) ?>
                    </td>
                    <td> 
                        <?= htmlspecialchars(
                            $activity['activity_log_created_at']) ?>
                    </td>


                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>


                    <?php if ($section === 'user-sessions'): ?>

        <h4>User Sessions
            <a href="index.php?section=user-sessions&amp;action=export">Export CSV</a>
        </h4>

        <table>

            <thead>
                <tr>
                    <th>Session ID</th>
                    <th>User ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Session Start</th>
                    <th>Session End</th>
                    <th>Duration</th>
                </tr>
            </thead>

            <tbody>

                <?php foreach ($sessions as $session): ?>

                    <tr>

                        <td>
                            <?= htmlspecialchars($session['session_id']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($session['user_id']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($session['user_username']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($session['user_email']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($session['user_role']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($session['session_start']) ?>
                        </td>

                        <td>
                            <?= $session['session_end']
                                ? htmlspecialchars($session['session_end'])
                                : 'Active'
                            ?>
                        </td>

                        <td>

                            <?php

                            if ($session['session_duration'] !== null) {

                                $seconds = (int) $session['session_duration'];

                                $hours = floor($seconds / 3600);
                                $minutes = floor(($seconds % 3600) / 60);
                                $seconds = $seconds % 60;

                                echo sprintf(
                                    '%02d hr %02d min %02d sec',
                                    $hours,
                                    $minutes,
                                    $seconds
                                );

                            } else {

                                echo 'Active';

                            }

                            ?>

                        </td>

                    </tr>

                <?php endforeach; ?>

            </tbody>

        </table>

    <?php endif; ?>

</body>
</html>