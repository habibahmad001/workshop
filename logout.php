<?php
require_once __DIR__ . '/db.php';
auth_logout_user();
redirect('login.php');
