<?php
require_once '../../backend/config/config.php';
require_once '../../backend/includes/auth.php';

logout('admin');
header('Location: ../admin_login.php');
exit;
?>
