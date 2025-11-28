<?php
// tools/run_seed_with_session.php
// Usage (CLI): php run_seed_with_session.php <professor_id>
if ($argc < 2) {
    echo "Usage: php run_seed_with_session.php <professor_id>\n";
    exit(1);
}
$prof = intval($argv[1]);
if ($prof <= 0) { echo "Invalid professor id\n"; exit(1); }
// Initialize session variables then include the API
session_start();
$_SESSION['user_id'] = $prof;
$_SESSION['role'] = 'professor';
// include and run seed
require_once __DIR__ . '/../api/seed_sample.php';
