<?php
require_once "composer/vendor/autoload.php";
require_once "config.php";

use Abraham\TwitterOAuth\TwitterOAuth;

$response = [];

$raidList = [
    1 => "Lv50 ティアマト・マグナ",
    2 => "Lv60 リヴァイアサン・マグナ",
    3 => "Lv60 ユグドラシル・マグナ",
    4 => "Lv100 ゼノ・イフリート",
];


if(!isset($_GET["selected"]))
{
    echo json_encode($result);die;
}
if(!is_array($_GET["selected"]))
{
    echo json_encode($result);die;
}

$raids        = $_GET["selected"];
$queryStrings = [];
foreach ($raids as $v)
{
    if(isset($raidList[$v]))
    {
        $queryStrings[] = $raidList[$v];
    }
}
if(!$queryStrings)
{
    echo json_encode($result);die;
}

$result = findTweets($queryStrings);
$params = [
    "displayed" => (isset($_GET["displayed"]) AND is_array($_GET["displayed"])) ? $_GET["displayed"] : [],
    "raid_list" => $raidList,
];

error_log(print_r($params, true), 3, "/home/sohei/log/debug.log");
foreach ($result as $key => $tweet)
{
    if(!validate("id", $tweet["id"], $params))
    {
        unset($result[$key]); continue;
    }

    if(!validate("name", trim($tweet["name"]), $params))
    {
        unset($result[$key]);
    }
}

$pivot = [];
foreach ($result as $value) $pivot[] = $value["created_at"];
array_multisort($pivot, SORT_ASC, $result);

echo json_encode($result);

function findTweets($queryStrings)
{
    $result = [];

    $textParts = [
       "plz_resque" => "参加者募集",
       "id"         => "参戦ID：",
       "img_uri"    => "https",
    ];

    $conn = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET);

    $i = 0;

    foreach ($queryStrings as $queryString)
    {
        // API制限 15分間に180回まで
        $tweets = $conn->get("search/tweets", array("q" => $queryString, 'count' => 10));

        $tweets = (array)$tweets;

        foreach ($tweets["statuses"] as $value)
        {
            if( ( $pos = mb_strpos( $value->text, $textParts["plz_resque"] ) ) === false ) continue;

            $result[$i]["comment"]    = mb_substr($value->text, 0, $pos);
            $result[$i]["id"]         = mb_substr($value->text, 11, 8);
            $result[$i]["name"]       = mb_substr($value->text, 19, mb_strpos($value->text, $textParts["img_uri"]) - 19);
            $result[$i]["created_at"] = date("Y-m-d H:i:s", strtotime($value->created_at));

            ++$i;
        }

    }

    return $result;
}

function validate($type, $value, $params)
{
    $result = true;

    switch ($type) {

        case "id":

            $value     = encoding(trim($value));
            $displayed = encoding($params["displayed"]);

            if(in_array($value, $displayed))
            {
                $result = false;
            }

            if(!preg_match("/^[a-zA-Z0-9]+$/", $value))
            {
                $result = false;
            }

            break;

        case "name":

            $value = encoding(trim($value));
            $raids = encoding($params["raid_list"]);

            if(!in_array($value, $raids))
            {
                $result = false;
            }

            break;
    }

    return $result;
}

function encoding($data, $to_encoding = "UTF-8")
{
    if(is_array($data))
    {
        $result = array_map(function($arg){
                      return mb_convert_encoding($arg, "UTF-8");
                  }, $data);
    }
    else
    {
        $result = mb_convert_encoding($data, "UTF-8");
    }

    return $result;
}