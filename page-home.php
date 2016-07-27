<?php

// Format: MARK|VOTE LIMIT|GRADUATE LIMIT|pokemon 1,pokemon 2...

$tmp = DB::result_first('SELECT info FROM ext_rumble14_group WHERE date = \'' . ($date = date('Y-m-d')) . '\'');

if($tmp) {

	$info		= explode(PHP_EOL, $tmp);
	$pokemon	= [];
	$round		= ObtainRound($date);

	foreach($info as $key => $val) {

		$tmp = explode('|', $val);

		// Set up group arrays

		$group[$tmp[0]] = [
			'vlimit'	=> $tmp[1], 
			'glimit'	=> $tmp[2], 
			'pokemon'	=> explode(',', trim($tmp[3]))
		];

		// Merge up pokemon id for name extraction

		$pokemon = array_merge($pokemon, $group[$tmp[0]]['pokemon']);

	}

	// Querying for pokemon's name

	$query		= DB::query('SELECT id, name FROM ext_rumble14_score WHERE id IN (' . implode(',', array_unique($pokemon)) . ')');
	$pokemon	= [];

	while($info = DB::fetch($query)) {

		$pokemon[$info['id']] = $info['name'];

	}

}
