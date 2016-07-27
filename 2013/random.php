<?php

include dirname(__FILE__) . '/../bbs/source/class/class_core.php';

C::app()->init();

$query = DB::query('SELECT id, name FROM pm_data WHERE id < 650 ORDER BY rand()');
$team = array();
$each = floor(649 / 54);
$remain = 649 - 54 * $each;
$i = 0;
while($pkm = DB::fetch($query)) {
	if(count($team[$i]) >= $each) ++$i;
	$team[$i][] = '<img src="../bbs/source/plugin/pokemon/pokemon_system/images/spm/' . $pkm['id'] . '.gif"> ' . $pkm['id'] . ': ' . $pkm['name'];
}
echo '<style>*{font-size:12px;}td{border-collapse:collapse;border:1px solid #e3e3ef;padding:0 10px 0 10px;}</style><table>';
foreach($team as $key => $pkm) {
	if($key % 9 === 0) echo '<tr>';
	echo '<td>' . implode('<br>', $pkm) . '</td>';
	if(($key + 1) % 9 === 0) echo '</tr>';
}
echo '</table>';

/*$data = array(
	'pre_common_block' => array('id' => 'uid', 'name' => 'username'),
	'pre_common_invite' => array('id' => 'fuid', 'name' => 'fusername'),
	'pre_common_member' => array('id' => 'uid', 'name' => 'username'),
	'pre_common_member_security' => array('id' => 'uid', 'name' => 'username'),
	'pre_common_mytask' => array('id' => 'uid', 'name' => 'username'),
	'pre_common_report' => array('id' => 'uid', 'name' => 'username'),
	'pre_forum_thread' => array('id' => 'authorid', 'name' => 'author'),
	'pre_forum_post' => array('id' => 'authorid', 'name' => 'author'),
	'pre_forum_activityapply' => array('id' => 'uid', 'name' => 'username'),
	'pre_forum_groupuser' => array('id' => 'uid', 'name' => 'username'),
	'pre_forum_pollvoter' => array('id' => 'uid', 'name' => 'username'),
	'pre_forum_postcomment' => array('id' => 'authorid', 'name' => 'author'),
	'pre_forum_ratelog' => array('id' => 'uid', 'name' => 'username'),
	'pre_home_album' => array('id' => 'uid', 'name' => 'username'),
	'pre_home_blog' => array('id' => 'uid', 'name' => 'username'),
	'pre_home_clickuser' => array('id' => 'uid', 'name' => 'username'),
	'pre_home_docomment' => array('id' => 'uid', 'name' => 'username'),
	'pre_home_doing' => array('id' => 'uid', 'name' => 'username'),
	'pre_home_feed' => array('id' => 'uid', 'name' => 'username'),
	'pre_home_feed_app' => array('id' => 'uid', 'name' => 'username'),
	'pre_home_friend' => array('id' => 'fuid', 'name' => 'fusername'),
	'pre_home_friend_request' => array('id' => 'fuid', 'name' => 'fusername'),
	'pre_home_notification' => array('id' => 'authorid', 'name' => 'author'),
	'pre_home_pic' => array('id' => 'uid', 'name' => 'username'),
	'pre_home_poke' => array('id' => 'fromuid', 'name' => 'fromusername'),
	'pre_home_share' => array('id' => 'uid', 'name' => 'username'),
	'pre_home_show' => array('id' => 'uid', 'name' => 'username'),
	'pre_home_specialuser' => array('id' => 'uid', 'name' => 'username'),
	'pre_home_visitor' => array('id' => 'vuid', 'name' => 'vusername'),
	'pre_portal_article_title' => array('id' => 'uid', 'name' => 'username'),
	'pre_portal_comment' => array('id' => 'uid', 'name' => 'username'),
	'pre_portal_topic' => array('id' => 'uid', 'name' => 'username'),
	'pre_portal_topic_pic' => array('id' => 'uid', 'name' => 'username')
);
foreach($data as $key => $val) {
	DB::query('UPDATE ' . $key . ' SET ' . $val['name'] . ' = \'Sho\' WHERE ' . $val['name'] . ' = \'Hio\'');
}*/