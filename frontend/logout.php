<?php
session_start();

// සියලුම Session දත්ත ඉවත් කිරීම
session_unset();
session_destroy();

// Logout වූ පසු Login පිටුවට හෝ Home පිටුවට යොමු කිරීම
header("Location: login.php"); // ඔබට Home පිටුවට යැවීමට අවශ්‍ය නම් 'index.php' ලෙස වෙනස් කරන්න
exit();
?>
