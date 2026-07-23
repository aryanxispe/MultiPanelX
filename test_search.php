<?php
session_start();
$_GET['q'] = 'manage mods';
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'admin';
require 'ajax_search.php';
