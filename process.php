<?php

$return = ['etime' => [], 'html' => '', 'msg' => '', 'page' => ''];

switch($_GET['process']) {

	case 'vote':

		if(!$_GET['vote'] || !is_array($_GET['vote'])) {

			$return['msg'] = '您哪只精灵都不喜欢么？!';

			break;

		}

		function setRumbleCookie($todate) {

			setcookie('Hpa8_2132_viewmode', 1, $todate);
			setcookie('Hpa8_2132_diyactive', 1, $todate);

		}

		// Filter disqualified votes

		$ip		= empty($_SERVER['REMOTE_ADDR']) ? '' : $_SERVER['REMOTE_ADDR'];
		$ip2	= empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? '' : $_SERVER['HTTP_X_FORWARDED_FOR'];
		$ip3	= empty($_SERVER['HTTP_CLIENT_IP']) ? '' : $_SERVER['HTTP_CLIENT_IP'];
		$uid	= empty($_G['uid']) ? 0 : $_G['uid'];

		$count = DB::result_first('SELECT COUNT(*) FROM ext_rumble14_log WHERE 
			date > ' . ($fromdate = strtotime(date('Y-m-d'))) . ' AND date < ' . ($todate = $fromdate + 86400) . ' AND 
			(ip NOT IN (\'\', \'unknown\', \'127.0.0.1\') AND ip NOT LIKE \'198.143%\' AND ip IN (\'' . $ip . '\', \'' . $ip2 . '\', \'' . $ip3 . '\') OR 
			ip2 NOT IN (\'\', \'unknown\', \'127.0.0.1\') AND ip2 NOT LIKE \'198.143%\' AND ip2 IN (\'' . $ip . '\', \'' . $ip2 . '\', \'' . $ip3 . '\') OR 
			ip3 NOT IN (\'\', \'unknown\', \'127.0.0.1\') AND ip3 NOT LIKE \'198.143%\' AND ip3 IN (\'' . $ip . '\', \'' . $ip2 . '\', \'' . $ip3 . '\') OR 
			uid != 0 AND uid = ' . $uid . ')'
		);

		if(!empty($count) || !empty($_COOKIE['Hpa8_2132_viewmode1']) || !empty($_COOKIE['Hpa8_2132_diyactive1'])) {

			$return['msg'] = '您今日好像已经投过票了哟~';

			break;

		}

		preg_match('/(\d{1,3}\.\d{1,3}\.)\d{1,3}\.\d{1,3}/', $ip, $match);
		preg_match('/(\d{1,3}\.\d{1,3}\.)\d{1,3}\.\d{1,3}/', $ip2, $match2);
		preg_match('/(\d{1,3}\.\d{1,3}\.)\d{1,3}\.\d{1,3}/', $ip3, $match3);

		if(empty($match[1])) $match[1] = ' ';
		if(empty($match2[1])) $match2[1] = ' ';
		if(empty($match3[1])) $match3[1] = ' ';


		$count = DB::result_first('SELECT COUNT(*) FROM ext_rumble14_log WHERE 
			date > ' . $fromdate . ' AND date < ' . $todate . ' AND 
			(ip != \'\' AND 
				(ip LIKE \'' . $match[1] . '%\' OR ip LIKE \'' . $match2[1] . '%\' OR ip LIKE \'' . $match3[1] . '%\') OR 
			ip2 != \'\' AND 
				(ip2 LIKE \'' . $match[1] . '%\' OR ip2 LIKE \'' . $match2[1] . '%\' OR ip2 LIKE \'' . $match3[1] . '%\') OR 
			ip3 != \'\' AND 
				(ip3 LIKE \'' . $match[1] . '%\' OR ip3 LIKE \'' . $match2[1] . '%\' OR ip3 LIKE \'' . $match3[1] . '%\'))'
		);

		if($count > 9) {

			$return['msg'] = '您所在的地区日投配额已用完！';

			break;

		}


		$votes = [];

		array_walk($_GET['vote'], function($val, $key) {
			array_walk($val, function($val, $key) {
				global $votes;
				$votes[] = $val < 0 ? 0 : $val;
			});
		});

		$votes = array_unique($votes);

		$info		= explode(PHP_EOL, DB::result_first('SELECT info FROM ext_rumble14_group WHERE date = \'' . date('Y-m-d') . '\''));
		$pokemon	= [];

		foreach($info as $key => $val) {

			$tmp = explode('|', trim($val));

			// Check the limitation of the group being voted

			if(($count = count(array_intersect(explode(',', $tmp[3]), $votes))) > $tmp[1]) {

				$return['msg'] = $tmp[0] . '组只能给' . $tmp[1] . '位参赛者投票哟=.=!';

				break 2;

			} elseif($count < 1) {

				$return['msg'] = '每组至少要选择一只精灵哟！（或者说这精灵不在此组）';

				break 2;

			}

		}

		$votes = implode(',', $votes);

		/*if(DB::result_first('SELECT vote FROM ext_rumble14_log ORDER BY date DESC') === ($votes = implode(',', $votes))) {

			$return['msg'] = '您今日好像已经投过票了哟~~';

			setRumbleCookie($todate);

			break;

		}*/


		DB::query('UPDATE ext_rumble14_score SET score' . ($round = ObtainRound(date('Y-m-d', $_SERVER['REQUEST_TIME']))) . ' = score' . $round . ' + 1 WHERE id IN (' . $votes . ')');
		DB::query('INSERT INTO ext_rumble14_log (date, ip, ip2, ip3, uid, vote) VALUES 
			(' . $_SERVER['REQUEST_TIME'] . ', ' . '\'' . $ip . '\', \'' . $ip2 . '\', \'' . $ip3 . '\', ' . $uid . ', ' . '\'' . $votes . '\')');

		setRumbleCookie($todate);

		$return['msg'] = '投票成功！';

	break;

	case 'ranking-date':

		$_GET['date'] = (empty($_GET['date']) || !($tmp = strtotime($_GET['date'])) || $tmp < $schedule['start'] || $tmp > $schedule['end'] || $tmp > $_SERVER['REQUEST_TIME']) ? 
			date('Y-m-d', min(strtotime(date('Y-m-d', $_SERVER['REQUEST_TIME'])) - ($isgm ? 0 : 86400), $schedule['end'])) : $_GET['date'];

		$info = trim(DB::result_first('SELECT info FROM ext_rumble14_group WHERE date = \'' . $_GET['date'] . '\''));

		if(!$info) {

			$return['msg'] = '时间错误!';

			break;

		}

		$info = explode(PHP_EOL, $info);
		$group = $pokemon = [];
		$column = 'score' . ObtainRound($_GET['date']);

		foreach($info as $key => $val) {

			$info[$key] = explode('|', trim($val));

			$group[$info[$key][0]] = [
				'glimit' => $info[$key][2], 
				'pokemon' => array_flip(explode(',', $info[$key][3]))
			];

			// Generating a pokemon list of key with pokemon's id, and value will be defined in later codes

			$pokemon = $pokemon + $group[$info[$key][0]]['pokemon'];

		}


		// Obtaining pokemon's score by one query and save into $pokemon for further intersections

		$query = DB::query('SELECT id, name, ' . $column . ' FROM ext_rumble14_score WHERE id IN (' . implode(',', array_keys($pokemon)) . ')');
		$score = $name = [];
		$totalscore = 0;
		$totalpeople = DB::result_first('SELECT COUNT(*) FROM ext_rumble14_log WHERE date > ' . ($tmp = strtotime($_GET['date'])) . ' AND date < ' . ($tmp + 86400));

		while($info = DB::fetch($query)) {

			/*
				Combining WITHOUT assigning a new key (as array_merge() will do so), the value of $pokemon will be defined here.
				because multisort will re-asiign numeric key name so add an id key
			*/

			$pokemon = [$info['id'] => ['id' => $info['id'], 'name' => $info['name'], 'score' => $info[$column]]] + $pokemon;

			// Preparing values for array_multisort

			$score[$info['id']]	= $info[$column];

		}

		// Sorting $pokemon according to the score

		foreach($group as $key => &$val) {

			/*
				Intersects the keys that matches both $pokemon and $val['pokemon']
				put $pokemon in the left hand side to overlap the value which becomes [id, name]
				because $pokemon is already in descending order and after intersecting the order in left hand side will not be changed
				so that the result will be expected in sorted order
			*/

			$val['pokemon'] = array_intersect_key($pokemon, $val['pokemon']);

			array_multisort($score = array_column($val['pokemon'], 'score'), SORT_DESC, SORT_NUMERIC, $val['pokemon']);

			$val['total'] = array_sum($score);

			$totalscore += $val['total'];

			// Generating outputs

			$return['html'] .= '<table class="rk-date"><tr><th colspan="5">Group ' . $key . ' (' . $val['total'] . ')</th></tr><tr><th>排名</th><th>编号</th><th>精灵</th><th>票数</th><th>票率</th></tr>';

			$rank = 1;
			$stackrank = 0;
			$lastscore = -1;

			// Calculating the ranking

			foreach($val['pokemon'] as $keyb => &$valb) {

				if($lastscore == $valb['score']) {

					++$stackrank;

				} else {

					$rank += $stackrank;
					$stackrank = 1;

				}
				
				$valb['rank'] = $rank;
				$lastscore = $valb['score'];

				$return['html'] .= '<tr' . ($valb['rank'] <= $val['glimit'] ? ' class="graduated"' : '') . '><td>' . $valb['rank'] . '</td><td>' . $valb['id'] . '</td><td>' . $valb['name'] . '</td><td>' . $valb['score'] . '</td><td>' . round($valb['score'] / $totalpeople * 100, 2) . '%</td></tr>';

			}

			$return['html'] .= '</table>';

			unset($valb);

		}

		$caption = [null, '海选', '分组', '突围', '64强', '32强', '16强', '8强', '半决赛', '决赛'];

		$return['html'] = '<table class="rk-date"><tr><th>' . $_GET['date'] . ' ' . $caption[substr($column, 5)] . '(' . (reset($group) ? key($group) : '') . '-' . (end($group) ? key($group) : '') . ') 票数总计：' . $totalscore . ' 人数总计：' . $totalpeople . ' 人均票数：' . round($totalscore / $totalpeople, 1) . '票/人</th></tr></table><span style="line-height: 32px; margin-left: 10px; ">*注：今日票数将会在第二天自动更新</span><br>' . $return['html'];

		$return['js'] = 'window.history.pushState("", "", "?page=rank&mode=date&date=' . $_GET['date'] . '");';

	break;

	case 'ranking-total':

		$path = ROOT . '/cache/ranking.php';

		if(file_exists($path) && filemtime($path) + 300 > $_SERVER['REQUEST_TIME']) {

			include $path;

		} else {

			/*
				In this case, the ranking will be displayed regardless of pokemon's grouping
				in other words this mode is a collective ranking of all pokemon (in different types of order)

				Types of order:
					- Id
					- Score percent (Total of 9) (In the specific team) (votes / team votes total * 100)
					- Average percent (Score percent sum up / 9)
			*/

			$query = DB::query('SELECT date, info FROM ext_rumble14_group');
			$group = $score = [];

			while($info = DB::fetch($query)) {

				$group = [
					'round' => ObtainRound($info['date']), 
					'more' => explode(PHP_EOL, $info['info'])
				];

				// Initilize variable for scoring

				if(empty($score[$group['round']])) $score[$group['round']] = [];

				foreach($group['more'] as $key => &$val) {

					$val	= explode('|', trim($val));
					$val[3]	= explode(',', $val[3]);

					// Generates an array whose keys are pokemon's id and values with groups within

					$score[$group['round']] = $score[$group['round']] + array_combine($val[3], array_fill(0, count($val[3]), $info['date']));

				}

			}

			unset($val);

			$query = DB::query('SELECT id, name, score1, score2, score3, score4, score5, score6, score7, score8, score9 FROM ext_rumble14_score');
			$pokemon = $total = [];

			while($info = DB::fetch($query)) {

				$info['count'] = 0;

				// Counting total votes for each group

				for($i = 1; $i < 10; $i++) {

					$info['count'] += min($info['score' . $i], 1);

				}

				$pokemon[] = $info;

			}


			for($i = 0, $j = ($schedule['end'] - $schedule['start']) / 86400; $i < $j; $i++) {

				$total[ObtainRound($tmp2 = date('Y-m-d', $tmp = $schedule['start'] + $i * 86400)) . ',' . $tmp2] = DB::result_first('SELECT COUNT(*) FROM ext_rumble14_log WHERE date > ' . $tmp . ' AND date < ' . ($tmp + 86400));

			}

			// Performing final calculations by obtaining the percentages

			array_walk($pokemon, function(&$val, $key) {

				$val['avgscore'] = 0;

				for($i = 1; $i < 10; $i++) {

					$val['score' . $i]	= empty($GLOBALS['score'][$i][$val['id']]) || 
						empty($GLOBALS['total'][$tmp = $i . ',' . $GLOBALS['score'][$i][$val['id']]]) ? 
						0 : round($val['score' . $i] / $GLOBALS['total'][$tmp] * 100, 2);
					$val['avgscore']	+= $val['score' . $i];

				}

				$val['avgscore'] = $val['count'] > 0 ? round($val['avgscore'] / $val['count'], 2) : 0;

			});

			$fp = fopen($path, 'w+');

			flock($fp, LOCK_EX);
			fwrite($fp, '<?php ' . PHP_EOL . '$pokemon = ' . preg_replace('/\s{1,}/', '', preg_replace('/(\d\.\d\d)(\d+)/', '${1}', var_export($pokemon, TRUE))) . ';');
			flock($fp, LOCK_UN);
			fclose($fp);

		}


		// Starts to sort the results

		$limit		= 50;
		$page		= empty($_GET['pagenum']) ? 1 : intval($_GET['pagenum']);
		$_GET['sequence']	= !empty($_GET['sequence']) && $_GET['sequence'] === 'DESC' ? 'DESC' : 'ASC';

		if($page > ($totalpage = ceil(count($pokemon) / $limit)) || $page < 1) {

			$return['msg'] = '页数不对……';

			break;

		}

		if(empty($_GET['orderby']) || !in_array($_GET['orderby'], ['id', 'score1', 'score2', 'score3', 'score4', 'score5', 'score6', 'score7', 'score8', 'score9', 'avgscore'])) $_GET['orderby'] = 'id';

		${$_GET['orderby']} = array_column($pokemon, $_GET['orderby']);

		array_multisort(${$_GET['orderby']}, SORT_NUMERIC, constant('SORT_' . $_GET['sequence']), $pokemon);

		
		// Generating pages

		$return['page'] .= '<ul id="rk-page" class="r">' . ($page > 1 ? '<li data-page="' . ($page - 1) . '">&lt;&lt;</li>' : '');

		for($i = max($page - 5, 1), $j = min($page + 5, $totalpage); $i <= $j; $i++) {

			$return['page'] .= '<li data-page="' . $i . '"' . ($i === $page ? ' class="current"' : '') . '>' . $i . '</li>';

		}

		$return['page'] .= ($page < $totalpage ? '<li data-page="' . ($page + 1) . '">&gt;&gt;</li>' : '') . '</ul>';

		// Generating contents

		$return['html'] .= '<table class="rk-total"><tr><th width="7%">排名</th><th>精灵</th>
			<th width="8%">海选</th><th width="8%">分组</th><th width="8%">突围</th><th width="8%">64强</th><th width="8%">32强</th>
			<th width="8%">16强</th><th width="8%">8强</th><th width="8%">半决赛</th><th width="8%">决赛</th><th width="8%">平均</th></tr>';


		for($i = ($page - 1) * $limit, $j = $i + $limit; $i < $j; $i++) {

			if(empty($pokemon[$i])) break;

			$return['html'] .= '<tr><td>' . ($i + 1) . '</td><td>No. ' . $pokemon[$i]['id'] . ' ' . $pokemon[$i]['name'] . '</td><td>' . 
				($pokemon[$i]['score1'] ? $pokemon[$i]['score1'] . '%' : '-') . '</td><td>' . 
				($pokemon[$i]['score2'] ? $pokemon[$i]['score2'] . '%' : '-') . '</td><td>' . 
				($pokemon[$i]['score3'] ? $pokemon[$i]['score3'] . '%' : '-') . '</td><td>' . 
				($pokemon[$i]['score4'] ? $pokemon[$i]['score4'] . '%' : '-') . '</td><td>' . 
				($pokemon[$i]['score5'] ? $pokemon[$i]['score5'] . '%' : '-') . '</td><td>' . 
				($pokemon[$i]['score6'] ? $pokemon[$i]['score6'] . '%' : '-') . '</td><td>' . 
				($pokemon[$i]['score7'] ? $pokemon[$i]['score7'] . '%' : '-') . '</td><td>' . 
				($pokemon[$i]['score8'] ? $pokemon[$i]['score8'] . '%' : '-') . '</td><td>' . 
				($pokemon[$i]['score9'] ? $pokemon[$i]['score9'] . '%' : '-') . '</td><td>' . 
				($pokemon[$i]['avgscore'] ? $pokemon[$i]['avgscore'] . '%' : '-') . '</td></tr>';

		}

		$return['html'] .= '</table>';

		$return['js'] = 'window.history.pushState("", "", "?page=rank&mode=total&orderby=' . $_GET['orderby'] . '&sequence=' . $_GET['sequence'] . '&pagenum=' . $page . '");';

	break;

}