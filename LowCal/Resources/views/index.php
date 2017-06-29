<?php
/**
 * @var \LowCal\Base $LowCal
 */
echo $LowCal->view()->render('includes/begin', array(
        '_PAGE_TITLE' => 'LowCal Framework'
    )
);
echo $LowCal->view()->render('includes/header');
    ?>
    <p>Hello World!</p>
    <?php
echo $LowCal->view()->render('includes/footer');
echo $LowCal->view()->render('includes/end');
