<?php
	echo $PZPHP->view()->render('includes/begin', array(
			'_PAGE_TITLE' => 'Page Not Found - 404',
			'_PAGE_DESCRIPTION' => '',
			'_PAGE_KEYWORDS' => '',
		)
	);
	echo $PZPHP->view()->render('includes/header');
?>
	<div id="contentContainer">
		<div id="contentInnerContainer">
			<h1>404 Error</h1>
			<p>Woops! Seems like that resource is no longer available. If you came to this page through another site, it means that site may have an outdated link (in fact, I'd bet on it). If you came to this page from another page on my site, please let me know!</p>
			<br/><br/>
			<p>Specific error was: <em><?php echo $exceptionCode.' - '.$exceptionMsg; ?></em></p>
		</div>
	</div>
<?php
	echo $PZPHP->view()->render('includes/footer');
	echo $PZPHP->view()->render('includes/end');
