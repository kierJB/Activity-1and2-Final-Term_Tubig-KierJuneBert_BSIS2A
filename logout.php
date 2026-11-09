<?php

require_once 'config.php';

$_SESSION = [];
session_destroy();
header('location: index.php');
exit;
?>