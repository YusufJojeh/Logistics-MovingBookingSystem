<?php
session_start();
require_once '../autoload.php';
Auth::logout();
header('Location: login.php');
exit; 