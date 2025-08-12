<?php
/**
 * Database Import Script for Hostinger Deployment
 * DELETE THIS FILE AFTER SUCCESSFUL IMPORT FOR SECURITY.
 */

require_once 'config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = getDbConnection();
        $sql_file = 'jowaki_db.sql';
        
        if (!file_exists($sql_file)) {
            throw new Exception("Database file not found");
        }
        
        $sql_content = file_get_contents($sql_file);
        $statements = array_filter(array_map('trim', explode(';', $sql_content)));
        
        $pdo->beginTransaction();
        foreach ($statements as $statement) {
            if (!empty($statement) && !preg_match('/^(--|\/\*|SET|START|COMMIT)/', $statement)) {
                $pdo->exec($statement);
            }
        }
        $pdo->commit();
        $success = true;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Import - Jowaki</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; }
        .warning { background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; }
        .btn { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
    </style>
</head>
<body>
    <h1>Database Import Tool</h1>
    
    <div class="warning">
        <strong>⚠️ Security Warning:</strong> Delete this file after successful import!
    </div>
    
    <?php if (isset($success)): ?>
        <div class="success">
            <h3>✅ Database Import Successful!</h3>
            <p>Delete this file now for security.</p>
        </div>
    <?php elseif (isset($error)): ?>
        <div class="error">
            <h3>❌ Import Failed</h3>
            <p><?php echo htmlspecialchars($error); ?></p>
        </div>
    <?php endif; ?>
    
    <form method="POST" onsubmit="return confirm('Import database? This will overwrite existing data.');">
        <button type="submit" class="btn">Import Database</button>
    </form>
    
    <h3>Default Admin:</h3>
    <p>Email: admin@jowaki.com</p>
    <p>Password: (Check documentation or reset via admin panel)</p>
</body>
</html>
