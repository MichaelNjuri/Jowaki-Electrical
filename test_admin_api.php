<?php
// Test file to verify admin login API accessibility
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin API Test</title>
</head>
<body>
    <h1>Admin Login API Test</h1>
    
    <div id="result"></div>
    
    <script>
        // Test if the API endpoint is accessible
        fetch('admin/admin_login_api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ 
                username: 'test', 
                password: 'test' 
            })
        })
        .then(response => {
            console.log('Response status:', response.status);
            console.log('Response headers:', response.headers);
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            document.getElementById('result').innerHTML = 
                '<p>API Response: ' + JSON.stringify(data, null, 2) + '</p>';
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('result').innerHTML = 
                '<p style="color: red;">Error: ' + error.message + '</p>';
        });
    </script>
</body>
</html>
