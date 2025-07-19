<?php
require_once '../../backend/config/config.php';
require_once '../../backend/includes/auth.php';

logout('admin');
header('Location: ../../index.php');
exit;
?>
