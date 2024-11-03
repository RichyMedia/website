<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the client's IP address
    function getClientIP() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'];
        }
    }

    $clientIP = getClientIP();
    $file_path = 'ip_log.json';

    // Load the IP log file or initialize an empty array if it doesn't exist
    $ip_log = file_exists($file_path) ? json_decode(file_get_contents($file_path), true) : [];

    // Check if the IP address is already stored and if 86400 minutes have passed
    if (isset($ip_log[$clientIP])) {
        $last_time = $ip_log[$clientIP];
        $current_time = time();

        if (($current_time - $last_time) < 86400) { // 86400 seconds = 24 hours
            echo "<script type='text/javascript'>
                alert('You can only send one message every 24 hours. Please try again later.');
                window.location.href = '/'; // Redirect to the home page
            </script>";
            exit; // Stop further processing
        }
    }

    // Update the IP log with the current timestamp
    $ip_log[$clientIP] = time();
    file_put_contents($file_path, json_encode($ip_log));

    // Validate honeypot field
    $honeypot = filter_input(INPUT_POST, 'honeypot', FILTER_SANITIZE_STRING);
    if (!empty($honeypot)) {
        echo "<script type='text/javascript'>
            alert('Spam detected. Your submission will not be processed.');
            window.location.href = '/'; // Redirect to the home page
        </script>";
        exit;
    }


    // Sanitize input
    $name = htmlspecialchars(trim(filter_input(INPUT_POST, 'demo-name', FILTER_SANITIZE_STRING)));
    $email = filter_input(INPUT_POST, 'demo-email', FILTER_SANITIZE_EMAIL);
    $category = htmlspecialchars(trim(filter_input(INPUT_POST, 'demo-category', FILTER_SANITIZE_STRING)));
    $priority = htmlspecialchars(trim(filter_input(INPUT_POST, 'demo-priority', FILTER_SANITIZE_STRING)));
    $message = htmlspecialchars(trim(filter_input(INPUT_POST, 'demo-message', FILTER_SANITIZE_STRING)));
    $copy = isset($_POST['demo-copy']);

    // Validate email address
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script type='text/javascript'>
            alert('Invalid email address. Please provide a valid email.');
            window.location.href = '/'; // Redirect to the home page
        </script>";
        exit;
    }

    // Email details
    $to = "email@email.com, email2@email.com"; // E-mails are sent to these addresses
    $subject = "Contact Form Submission";
    $body = "Name: $name\nEmail: $email\nCategory: $category\nPriority: $priority\nMessage: $message";
    $headers = "From: email@email.com\r\n" . 
               "Reply-To: $email\r\n"; // Added Reply-To header

    // Send the main email
    $success = mail($to, $subject, $body, $headers);

    // Send a copy to the user if requested and the main email was successful
    if ($copy && $success) {
        mail($email, $subject, $body, $headers);
    }

    // Output the result and include JavaScript for redirection
    echo "<script type='text/javascript'>
        alert('" . ($success ? "Message successfully sent!" : "Message sending failed!") . "');
        window.location.href = '/'; // Redirect to the home page
    </script>";
}
?>
