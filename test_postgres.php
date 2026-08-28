<?php

require_once __DIR__ . '/config/database.php';

echo "Testing PostgreSQL Connection...\n\n";

try {
    // 1. Check if password is set
    if (empty(getenv('DB_PASSWORD'))) {
        throw new Exception("DB_PASSWORD is empty in the .env file. Please add your Supabase database password to the .env file.");
    }

    // 2. Initialize Database Connection
    $db = Database::getInstance()->getConnection();
    echo "[SUCCESS] Connected to PostgreSQL on Supabase.\n";

    // 3. Simple Query Test
    $stmt = $db->query("SELECT 1 AS test_val");
    $result = $stmt->fetch();
    if ($result && $result['test_val'] == 1) {
        echo "[SUCCESS] Executed simple SELECT 1 query successfully.\n";
    }

    // 4. Test querying an application table (e.g., users)
    // We'll check if the users table exists and count the rows
    $stmt = $db->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch();
    echo "[SUCCESS] Queried 'users' table. Row count: " . $result['count'] . "\n";
    
    echo "\nAll connection tests passed! Your application is ready to use Supabase PostgreSQL.\n";

} catch (PDOException $e) {
    echo "\n[ERROR] Database connection or query failed:\n";
    
    $message = $e->getMessage();
    
    // Scrub password from any PDO messages (though PDO usually hides it, best to be safe)
    $password = getenv('DB_PASSWORD');
    if (!empty($password)) {
        $message = str_replace($password, '********', $message);
    }
    
    echo $message . "\n\n";
    echo "Configuration Troubleshooting:\n";
    echo "1. Verify your password in the .env file is exactly correct.\n";
    echo "2. Ensure you have run supabase_schema.sql in the Supabase SQL Editor.\n";
    echo "3. Ensure your DB_HOST (db.rqetluoejqqrtovtlsoj.supabase.co) is active.\n";
} catch (Exception $e) {
    echo "\n[ERROR] " . $e->getMessage() . "\n";
}
