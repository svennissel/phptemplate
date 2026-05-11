<?php
require_once __DIR__ . '/includes/auth.php';
logout();
echo "<script>localStorage.removeItem('einkauf_hash'); window.location.href='login.php';</script>";
exit;
