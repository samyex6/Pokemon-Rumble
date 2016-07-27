<?php

$_GET['mode'] = (empty($_GET['mode']) || $_GET['mode'] !== 'date') ? 'total' : 'date';

$_GET['process'] = 'ranking-' . $_GET['mode'];

if($_GET['mode'] === 'date') {

	$dateoption = '';

	for($i = 1, $j = (min(strtotime(date('Y-m-d')), $schedule['end']) - $schedule['start']) / 86400; $i <= $j; $i++) {

		$dateoption .= '<option value="' . ($tmp = date('Y-m-d', $schedule['start'] + ($i - 1) * 86400)) . '">' . $tmp . '</li>';

	}

}

include ROOT . '/process.php';