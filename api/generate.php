<?php
$admin_secret = 'wisdom123'; // CHANGE KARO!

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_secret'])) {
    if ($_POST['admin_secret'] !== $admin_secret) {
        die('Unauthorized');
    }
    
    $plan = $_POST['plan'] ?? 'free';
    $days = intval($_POST['days'] ?? 30);
    
    // Generate key
    $key = strtoupper(
        implode('-', [
            substr(md5(uniqid()), 0, 4),
            substr(md5(uniqid()), 0, 4),
            substr(md5(uniqid()), 0, 4)
        ])
    );
    
    $expiry = date('Y-m-d H:i:s', strtotime("+$days days"));
    
    // Supabase config
    $supabase_url = 'https://your-project.supabase.co';
    $supabase_key = 'sb_secret_XHED4*********';
    
    $data = json_encode([
        'license_key' => $key,
        'plan' => $plan,
        'expiry_date' => $expiry,
        'status' => 'active'
    ]);
    
    $ch = curl_init("$supabase_url/rest/v1/users");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'apikey: ' . $supabase_key,
        'Authorization: Bearer ' . $supabase_key,
        'Content-Type: application/json'
    ]);
    
    curl_exec($ch);
    curl_close($ch);
    
    echo "Key: $key (Expires: $expiry)";
    exit;
}
?>

<!DOCTYPE html>
<html>
<body>
    <h2>Generate Key</h2>
    <form method="POST">
        <input type="password" name="admin_secret" placeholder="Admin Password"><br>
        <select name="plan">
            <option value="free">Free</option>
            <option value="premium">Premium</option>
        </select><br>
        <input type="number" name="days" value="30"><br>
        <button type="submit">Generate</button>
    </form>
</body>
</html>