<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// YAHAN APNA SUPABASE URL AUR KEY DAALO
$supabase_url = 'https://your-project.supabase.co';
$supabase_key = 'sb_secret_XHED4*********'; // secret key

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_key = isset($_POST['user_key']) ? $_POST['user_key'] : '';
    $device_id = isset($_POST['device_id']) ? $_POST['device_id'] : '';
    
    // Call Supabase API
    $url = "$supabase_url/rest/v1/users?license_key=eq.$user_key&select=*";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'apikey: ' . $supabase_key,
        'Authorization: Bearer ' . $supabase_key
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200) {
        $users = json_decode($response, true);
        
        if (empty($users)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid key']);
            exit;
        }
        
        $user = $users[0];
        
        // Check expiry
        if (strtotime($user['expiry_date']) < time()) {
            echo json_encode(['status' => 'error', 'message' => 'Key expired']);
            exit;
        }
        
        // Device check
        if ($user['device_id'] === null) {
            // First login
            $update_url = "$supabase_url/rest/v1/users?license_key=eq.$user_key";
            $update_data = json_encode([
                'device_id' => $device_id,
                'last_login' => date('Y-m-d H:i:s'),
                'first_login' => date('Y-m-d H:i:s')
            ]);
            
            $ch = curl_init($update_url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
            curl_setopt($ch, CURLOPT_POSTFIELDS, $update_data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'apikey: ' . $supabase_key,
                'Authorization: Bearer ' . $supabase_key,
                'Content-Type: application/json'
            ]);
            curl_exec($ch);
            curl_close($ch);
            
            echo json_encode([
                'status' => 'success',
                'message' => 'Device registered!'
            ]);
            
        } else if ($user['device_id'] === $device_id) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Login successful!'
            ]);
        } else {
            echo json_encode([
                'status' => 'device_mismatch',
                'message' => 'Key already used on another device'
            ]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error']);
    }
}
?>