<?php
if (!defined('e107_INIT')) { exit; }
include_lan(e_PLUGIN.'twits_menu/languages/'.e_LANGUAGE.'.php');
include_once(e_PLUGIN.'twits_menu/class.php');
include(e_PLUGIN.'twits_menu/twits_shortcodes.php');
include_once(e_HANDLER.'date_handler.php');
if(file_exists(THEME.'twits_template.php'))
{
	include_once(THEME.'twits_template.php');
}
else
{
	include_once(e_PLUGIN.'twits_menu/twits_template.php');
}

global $tp, $sc_style;
$gen = new convert();
$date_format = (($pref['twits_dateformat']) ? $pref['twits_dateformat'] : 'long');
$tweets = (($pref['twits_tweets']) ? $pref['twits_tweets'] : '1');
$retweets = (($pref['twits_retweets']) ? $pref['twits_retweets'] : '0');

$text = $tweet_text = '';
if($pref['twits_username'] !== '')
{
	$username = $pref['twits_username'];
	$xml = simplexml_load_file("http://api.twitter.com/1/statuses/user_timeline/".$username.".xml?count=25&include_rts=".$retweets."&callback=?");
	$sid = array();

	if($pref['twits_replies'] == '0')
	{
		$a = 0;
		foreach($xml as $status)
		{
			$a++;
			if(empty($status->in_reply_to_user_id))
			{
				array_push($sid, ($a-1));
			}
		}
	}
	else
	{
		for($i = 0; $i <= ($tweets - 1); $i++)
		{
			array_push($sid, $i);
		}
	}

	$b = 1;
	foreach($sid as $id)
	{
		if($b <= $tweets)
		{
			$tweet_id = $xml->status[$id]->id;
			cachevars('username', $username);
			cachevars('status', parseContent($xml->status[$id]->text));
			cachevars('datestamp', $gen->convert_date(strtotime($xml->status[$id]->created_at), $date_format));
			cachevars('retweet', $tweet_id);
			cachevars('reply', $tweet_id);
			cachevars('favorite', $tweet_id);
			
			$all_tweets .= $tp->parseTemplate($EACH_TWEET, FALSE, $twits_shortcodes);				
		}
		$b++;
	}
	$no_tweet_account = '';
}
else
{
	$all_tweets = '';
	$no_tweet_account = TWITS_MENU_06;
}
cachevars('all_tweets', $all_tweets);
cachevars('no_tweet_account', $no_tweet_account);
$text = $tp->parseTemplate($TWITS_MENU, FALSE, $twits_shortcodes);
$ns->tablerender($tp->toHTML($pref['twits_header']), $text, 'twits');
?>