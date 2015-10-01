<?php
/*
 * Copyright (C) 2013 by
 * Fuad Jamour <fjamour@gmail.com>
 * All rights reserved.
 * MIT license.
*/
?>

<?php session_start(); ?>

<?php
	if($_SESSION['auth'] != 1) {
		header('Location: login.php');
		exit(0);
	}
?>

<?php include('globals.php'); ?>

<?php include('util.php');  ?>
 
<?php
	$valid_vars = false;
	if(isset($_GET['f'])) {
		$timestamp;
		$type;
		$path;
		if(decrypt_timestamp($_GET['f'], $timestamp, $type)) {
			$ppath = $SUBMISSIONS_PATH . '/' . $_SESSION['team_name'] . '/' . $timestamp . '/';
			$file = 'NOT_THERE';
			if($type == 'lib') {  //lib means either libcore.so or impl.tar.gz, see which is availbale
				if(file_exists($ppath . 'libcore.so'))
					$file = 'libcore.so';
				else if(file_exists($ppath . 'impl.tar.gz'))
					$file = 'impl.tar.gz';
			} elseif($type == 'log') {
				if(file_exists($ppath . 'log.txt'))
					$file = 'log.txt';
			}
			if($file == 'NOT_THERE') {
				header('HTTP/1.0 404 Not Found');
				include('err.php');
				exit;			
			}
			$path = $ppath . $file;
			if(!file_exists($path)) {
				header('HTTP/1.0 404 Not Found');
				include('err.php');
				exit;
			}
			if($type == 'lib') {
				header('Content-Description: File Transfer');
				header('Content-type: application/octet-stream');
				header('Content-Disposition: attachment; filename=' . '"'.$file.'"');
				header('Content-Transfer-Encoding: binary');
				header('Expires: 0');
				header('Cache-Control: must-revalidate');
				header('Pragma: public');
				header('Content-Length: ' . filesize($path));
				ob_clean();
				flush();
				readfile($path);
				exit;
			} elseif($type == 'log') {
				header('Content-Description: File Transfer');
				header('Content-Type: text/plain');
				header('Content-Disposition: filename="log.txt"');
				header('Content-Transfer-Encoding: binary');
				header('Expires: 0');
				header('Cache-Control: must-revalidate');
				header('Pragma: public');
				header('Content-Length: ' . filesize($path));
				ob_clean();
				flush();
				readfile($path);
				exit;
			}
		} else {
			header('HTTP/1.0 404 Not Found');
			include('err.php');
			exit;
		}
	} else {
		header('HTTP/1.0 404 Not Found');
		include('err.php');
		exit;
	}
?>


