<?php

$_GET['date'] = empty($_GET['date']) || 
	!($date = strtotime($_GET['date'])) || 
	$date < $schedule['start'] || 
	$date > $schedule['end'] ? 0 : $_GET['date'];

if(empty($_GET['date'])) {

	$report		= [];
	$contest	= [null, '海选赛', '分组赛', '突围赛', '64强赛', '32强赛', '16强赛', '8强赛', '半决赛', '总决赛'];
	$number		= ['一', '二', '三', '四', '五', '六', '七', '八'];

	for($i = 0, $j = floor(min($_SERVER['REQUEST_TIME'], $schedule['end']) - $schedule['start']) / 86400 - 1; $i <= $j; $i++) {

		if(empty($report[$tmp = ObtainRound($date = date('Y-m-d', $schedule['start'] + $i * 86400))])) {

			$report[$tmp] = [];

		}

		$report[$tmp][] = $date;

	}

} else {

	$report = [];

	for($i = 0; $i < 11; $i++) {

		if(!file_exists($path = IMGPATH . '/report14/' . $_GET['date'] . '-' . $i . '.jpg')) break;

		$report[] = $path;

	}

	$title['report'] = $_GET['date'] . ' 战报';

}