<?php
	require_once 'functions.php';

	openid_logout();
	header("Location: " . URL . "login.php?action=logout");
	exit();
?>
