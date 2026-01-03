<?php
    session_start();
    header('Content-Type: application/json');

    // Database connection
    include "connection.php";

    if (!$conn) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }

    // Get the posted data
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!isset($data['credential'])) {
        echo json_encode(['success' => false, 'message' => 'No credential provided']);
        exit;
    }

    $credential = $data['credential'];

    // Verify the Google token
    function verifyGoogleToken($token) {
        // Replace with your actual Google Client ID
        $clientId = '1022110118702-okoo828eanqdt8rt7bg46c8r3gfm03m3.apps.googleusercontent.com';
        
        // Call Google's token verification API
        $url = 'https://oauth2.googleapis.com/tokeninfo?id_token=' . $token;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            return false;
        }
        
        $tokenInfo = json_decode($response, true);
        
        // Verify the token is for your client ID
        if (!isset($tokenInfo['aud']) || $tokenInfo['aud'] !== $clientId) {
            return false;
        }
        
        return $tokenInfo;
    }

    // Verify the token
    $userInfo = verifyGoogleToken($credential);

    if (!$userInfo) {
        echo json_encode(['success' => false, 'message' => 'Invalid token']);
        exit;
    }

    // Extract user information
    $email = mysqli_real_escape_string($conn, $userInfo['email']);
    $name = mysqli_real_escape_string($conn, $userInfo['name']);
    $google_id = mysqli_real_escape_string($conn, $userInfo['sub']);
    $picture = isset($userInfo['picture']) ? mysqli_real_escape_string($conn, $userInfo['picture']) : '';

    // Check if user exists in database
    $checkQuery = "SELECT * FROM users WHERE email = '$email' OR google_id = '$google_id'";
    $result = mysqli_query($conn, $checkQuery);
    $user = mysqli_fetch_assoc($result);

    if ($user) {
        // User exists - update Google ID and last login if not set
        if (empty($user['google_id'])) {
            $updateQuery = "UPDATE users SET 
                            google_id = '$google_id', 
                            profile_picture = '$picture', 
                            last_login = NOW() 
                            WHERE id = " . $user['id'];
            mysqli_query($conn, $updateQuery);
        } else {
            // Just update last login
            $updateQuery = "UPDATE users SET last_login = NOW() WHERE id = " . $user['id'];
            mysqli_query($conn, $updateQuery);
        }
        
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['uname'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['login_type'] = 'google';
            
        echo json_encode(['success' => true, 'message' => 'Login successful']);
    } else {
        // New user - create account with NULL password (Google Sign-In doesn't use password)
        // Set password and cpassword to NULL for Google users
        $insertQuery = "INSERT INTO users 
                        (uname, email, password, cpassword, google_id, profile_picture, role, status, created_at, last_login) 
                        VALUES 
                        ('$name', '$email', NULL, NULL, '$google_id', '$picture', 'User', 1, NOW(), NOW())";
        
        if (mysqli_query($conn, $insertQuery)) {
            $userId = mysqli_insert_id($conn);
            
            // Set session variables
            $_SESSION['user_id'] = $userId;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_name'] = $name;
            $_SESSION['user_role'] = 'User';
            $_SESSION['login_type'] = 'google';
                    
            echo json_encode(['success' => true, 'message' => 'Account created and logged in']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create account: ' . mysqli_error($conn)]);
        }
    }

    mysqli_close($conn);
?>