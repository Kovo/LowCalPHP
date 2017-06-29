<?php
/**
 * @var \LowCal\Base $LowCal
 * @var $exception_code
 * @var $exception_msg
 */
echo $LowCal->view()->render('includes/begin', array(
		'_PAGE_TITLE' => 'LowCal Framework'
	)
);
echo $LowCal->view()->render('includes/header');
?>
    <div id="contentContainer">
        <div id="contentInnerContainer">
            <h1>500 Error</h1>
            <p>Woops! Seems like this website's server is not happy with some piece of code. It's probably my fault. QA is never a programmer's strong suit when looking over his/her own code. I should have that extra Red Bull! If you'd like, you can let me know this error occurred. That would probably help!</p>
            <br/><br/>
            <p>Specific error was: <em><?php echo $exception_code.' - '.$exception_msg; ?></em></p>
        </div>
    </div>
<?php
echo $LowCal->view()->render('includes/footer');
echo $LowCal->view()->render('includes/end');