<?php
header('Content-Type:application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');

// Be 100% sure the timezone is set
if (ini_get("date.timezone") === "" && function_exists("date_default_timezone_set")) {
    date_default_timezone_set("UTC");
}

include_once('RssParser.php');

$rss = new RSSParser(array('url' => 'http://idsclub.diandian.com/rss'));

// 取最新3条项目
$news = $rss->getFeed(3);

echo json_encode(array('status' => 'ok', 'news' => $news));
exit();