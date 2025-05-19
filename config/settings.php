<?php
require_once 'config.php';
$checkTable = $conn->query("SHOW TABLES LIKE 'blogs'");
if ($checkTable->num_rows == 0) {
    header("Location: /setup");
    exit();
}
else{
    $settings = $conn->query("SELECT * FROM settings")->fetch_assoc();
    $site_title = $settings['site_title'];
    $site_description = $settings['site_description'];
    $site_keywords = $settings['site_keywords'];
    $site_url = $settings['site_url'];
    $site_logo = $settings['site_logo'];
}
?>