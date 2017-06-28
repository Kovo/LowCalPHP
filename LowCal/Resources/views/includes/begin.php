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
		<meta name="description" content="<?php echo $_PAGE_DESCRIPTION; ?>" />
		<meta name="keywords" content="<?php echo $_PAGE_KEYWORDS; ?>" />

		<link rel="shortcut icon" href="<?php echo PzPHP_Config::get('IMG_URL'); ?>/favicon.ico" type="image/x-icon">
		<link rel="icon" href="<?php echo PzPHP_Config::get('IMG_URL'); ?>/favicon.ico" type="image/x-icon">

		<?php echo $PZPHP->view()->render('includes/resources/css'); ?>
		<?php echo $PZPHP->view()->render('includes/resources/js'); ?>
	</head>
	<body>
		<div id="globalContainer">
