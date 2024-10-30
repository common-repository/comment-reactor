<?php
/*
Plugin Name: Comment Reactor
Plugin URI: http://commentreactor.com/
Description: The Comment Reactor wordpress plugin enables you to add rich media to your comments.
Author: ian taylor, commentreactor.com team
Version: 1.0.1
Author URI: http://commentreactor.com/
*/

$cr_base_url = get_option('cr_base_url');
$cr_site = get_option('cr_site');
$cr_usage = get_option('cr_usage');
$cr_usage_date = strtotime(get_option('cr_usage_date'));

require_once('JSON.php');

function cr_comments_template($value) 
{
	global $post;
	if (cr_is_enabled_post($post))
	{
		return dirname(__FILE__) . '/cr_comments.php';
	}
	return $value;
}
function cr_get_comments_number($num_comments) 
{
	global $post;
	if (cr_is_enabled_post($post))
	{
		//always return one so that comments are always active... The result won't be used anyway.
		return 1;	
	}
	return $num_comments;
}

function cr_is_enabled()
{
	global $cr_site;
	global $cr_usage;
	global $cr_base_url;
	if (!$cr_site || !$cr_base_url || !$cr_usage)
	{
		return false;
	}
	if($cr_usage == 'none')
	{
		return false;
	}
	return true;
}
function cr_is_enabled_post($post)
{
	global $cr_usage;
	global $cr_usage_date;
	if (! cr_is_enabled())
	{
		return false;
	}
	if ($cr_usage == 'all')
	{
		return true;
	}
	if($cr_usage == 'before' && strtotime($post->post_date_gmt) <  $cr_usage_date)
	{
		return true;
	}
	if($cr_usage == 'after' && strtotime($post->post_date_gmt) > $cr_usage_date)
	{
		return true;
	}
	return false;
}
function cr_comments_number($text)
{
	global $post;
	global $cr_base_url;
	global $cr_site;
	global $cr_included_comment_script;
	
	if (!cr_is_enabled_post($post))
	{
		return $text;
	}
	$cr_id = "CR_CommentCount". $post->ID;
	
	$newText = "View comments <span class='CR_CommentCount' id='"
	.$cr_id
	."'></span>";
	if (!$cr_included_comment_script)
	{
		$newText = $newText."<script src='"
			.$cr_base_url
			."js/all.js'></script>" 
			."<script>CR.commentCountLoad('"
			.$cr_base_url
			."', '"
			.$cr_site
			."')</script>";
		$cr_included_comment_script = true;
	}
	return $newText;
}

function cr_comment_sync()
{
	global $cr_base_url;
	global $cr_site;
	if (cr_is_enabled())
	{
		$lastRunOptName = "CR_LastSyncTime";
		$lastRunTime = get_option($lastRunOptName);
		if (!$lastRunTime)
		{
			$lastRunTime = 0;
		}	
		$json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE); 
		//CR expects msec accuracy on timestamps.
		$lastRunTime = ($lastRunTime * 1000);
		$request = $cr_base_url . "api/" . $cr_site ."/FluidityAsync/getNewComments.json?jsonp=&since=".$lastRunTime;
		$response = file_get_contents($request);
		$response = substr($response, 1, strlen($response) -2 );
	
		$phpResponse = $json->decode($response);
		foreach($phpResponse as $key => $val)
		{
			cr_add_comments($key, $val);
		}
		//second request for updated comments
		$request = $cr_base_url . "api/" . $cr_site ."/FluidityAsync/getUpdatedComments.json?jsonp=&since=".$lastRunTime;
		$response = file_get_contents($request);
		$response = substr($response, 1, strlen($response) -2 );
	
		$phpResponse = $json->decode($response);
		foreach($phpResponse as $comment)
		{	
			echo $comment['id'];
			cr_update_comment($comment);
		}		
		$finishTime = time();
		update_option($lastRunOptName, $finishTime);
	}
}

function cr_update_comment($comment)
{
	global $wpdb;
	
	$commentAgent = "CommentReactor_" . intval($comment['id']);
	$statement = $wpdb->prepare("SELECT comment_id FROM $wpdb->comments where comment_agent = %s", $commentAgent);
	$comment_id = $wpdb->get_var($statement);
	if ($comment_id)
	{
		$wp_comment = get_comment($comment_id, ARRAY_A);
		if ($comment['status'] == 'approved')
		{
			$wp_comment['comment_approved'] = 1;
		}
		else
		{
			$wp_comment['comment_approved'] = 0;
		}
		$wp_comment['comment_content'] = $comment['text'];
		wp_update_comment($wp_comment); 
	}
}

function cr_add_comments($post_id, $comments)
{
	$post = get_post($post_id);
	if ($post && cr_is_enabled($post))
	{
		foreach ($comments as $comment)
		{
			cr_add_comment($comment, $post->ID);
		}
	}
}

function cr_add_comment($comment, $post_id)
{
	//CR timestamps have msec.  
	$createDate = intval($comment['createDate'])/1000;
	
	$data = array(
	'comment_post_ID' => $post_id,
	'comment_author' => $comment['creatorUserName'],
	'comment_date' => date('Y-m-d H:i:s', $createDate ),
	'comment_date_gmt' => date('Y-m-d H:i:s', $createDate),
	'comment_content' => $comment['text'],
	'comment_approved' => 1,
	'comment_agent' => 'CommentReactor_' . intval($comment['id']),
	'comment_type' => ''
	);
	if ($comment['status'] != 'approved')
	{
		$data['comment_approved'] = 0;
	}
	wp_insert_comment($data);
}

//Wire up actions to events.
add_action('cr_comment_sync_action', 'cr_comment_sync');
//cr_comment_sync();

function cr_fast_intervals() 
{
	return array(
	'test' => array('interval' => 30, 'display' => '30 secs')
	);
}

function cr_start()
{
	//set the interval to 'test' for 30 second interval testing.
	wp_schedule_event(0, 'hourly', 'cr_comment_sync_action');
}

function cr_stop()
{
	wp_clear_scheduled_hook('cr_comment_sync_action');
}

function cr_add_page()
{
	add_options_page('CommentReactor', 'CommentReactor', 8, 'commentreactor', 'cr_setup');
}

function cr_setup()
{
	require_once('admin-header.php');
	include_once('setup.php');
}
register_activation_hook(__FILE__, 'cr_start');
register_deactivation_hook(__FILE__, 'cr_stop'); 

add_action('admin_menu', 'cr_add_page');
add_filter('cron_schedules', 'cr_fast_intervals');
	
if (cr_is_enabled())
{
	add_filter('comments_template', 'cr_comments_template');
	add_filter('get_comments_number', 'cr_get_comments_number');
	add_filter('comments_number', 'cr_comments_number');
}

?>
