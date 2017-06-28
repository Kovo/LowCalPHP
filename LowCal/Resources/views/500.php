<?php
	echo $PZPHP->view()->render('includes/begin', array(
			'_PAGE_TITLE' => 'Internal Site Error - 500',
			'_PAGE_DESCRIPTION' => '',
			'_PAGE_KEYWORDS' => '',
		)
	);
	echo $PZPHP->view()->render('includes/header');
?>
	<div id="contentContainer">
		<div id="contentInnerContainer">
			<h1>500 Error</h1>
			<p>Woops! Seems like this website's server is not happy with some piece of code. It's probably my fault. QA is never a programmer's strong suit when looking over his/her own code. I should have that extra Red Bull! If you'd like, you can let me know this error occurred. That would probably help!</p>
			<br/><br/>
			<p>Specific error was: <em><?php echo $exceptionCode.' - '.$exceptionMsg; ?></em></p>
		</div>
	</div>
<?php
	echo $PZPHP->view()->render('includes/footer');
	echo $PZPHP->view()->render('includes/end');
