<?php
session_start();

// সকল সেশন ভেরিয়েবল মুছে ফেলা
$_SESSION = array();

// সেশন কুকি ধ্বংস করা
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// সেশন ধ্বংস করা
session_destroy();

// লগইন পেজে রিডাইরেক্ট করা
header("Location: login.php");
exit();
