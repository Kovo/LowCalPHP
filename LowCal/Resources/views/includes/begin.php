<?php
/**
 * @var \LowCal\Base $LowCal
 * @var $_PAGE_TITLE
 */
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title><?php echo $_PAGE_TITLE; ?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
		<meta http-equiv="X-Frame-Options" content="sameorigin">
		<meta http-equiv="imagetoolbar" content="no"/>
		<meta name="robots" content="index, follow" />
		<meta name="googlebot" content="index, follow" />
		<meta name="description" content="" />
		<meta name="keywords" content="" />

		<?php echo $LowCal->view()->render('includes/resources/css'); ?>
		<?php echo $LowCal->view()->render('includes/resources/js'); ?>
	</head>
	<body>
		<div id="globalContainer">
