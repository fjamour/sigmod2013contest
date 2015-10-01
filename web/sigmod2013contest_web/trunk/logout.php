<?php
/*
 * Copyright (C) 2013 by
 * Fuad Jamour <fjamour@gmail.com>
 * All rights reserved.
 * MIT license.
*/
?>

<?php
	session_start();
	$_SESSION = array();
	if(isset($_COOKIE[session_name()])) {
		setcookie(session_name(), '', time()-42000, '/');
	}                                                                                                                                                                                                                       	session_destroy();
	header("Location: index.php");
?>
