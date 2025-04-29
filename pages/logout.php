<?php
require_once '../includes/auth.php';

session_unset();
session_destroy();
redirect('login.php');
?>