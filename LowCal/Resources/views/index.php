<?php
	echo $PZPHP->view()->render('includes/begin', array(
			'_PAGE_TITLE' => 'PzPHP Framework',
			'_PAGE_DESCRIPTION' => '',
			'_PAGE_KEYWORDS' => '')
	);
	echo $PZPHP->view()->render('includes/header');
?>
Hello World!
<?php
	echo $PZPHP->view()->render('includes/footer');
	echo $PZPHP->view()->render('includes/end');
