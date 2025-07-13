<?php
require_once '../../backend/config/config.php';
require_once '../../backend/includes/auth.php';

logout('user');
header('Location: ../login.php');
exit;
?>
