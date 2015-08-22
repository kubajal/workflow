<?php

namespace OmniFlow;

include_once("_startup.php");
$objDateTime = new \DateTime('NOW');

Context::debug('cron.php running at '.$objDateTime->format( 'd-M-Y' ));
EventEngine::Check();
echo '<br />done';