<?php 
require_once('JSON.php');

//from wp-admin/includes/file.php
function cr_temp_dir() {
	if ( defined('WP_TEMP_DIR') )
		return trailingslashit(WP_TEMP_DIR);

	$temp = WP_CONTENT_DIR . '/';
	if ( is_dir($temp) && is_writable($temp) )
		return $temp;

	if ( function_exists('sys_get_temp_dir') )
		return trailingslashit(sys_get_temp_dir());

	return '/tmp/';
}



function cr_write_export()
{
	set_time_limit(0);
// The next few lines of code gratutiously swiped from wordpress own export.php
$postList = array();
global $wp_query;
global $wpdb;

$where = "WHERE comment_count > 0";
// grab a snapshot of post IDs, just in case it changes during the export
$post_ids = $wpdb->get_col("SELECT ID FROM $wpdb->posts $where ORDER BY post_date_gmt ASC");
if ($post_ids) 
{
	$wp_query->in_the_loop = true;  // Fake being in the loop.
	// fetch 20 posts at a time rather than loading the entire table into memory
	while ( $next_posts = array_splice($post_ids, 0, 20) ) 
	{
		$where = "WHERE ID IN (".join(',', $next_posts).")";
		$posts = $wpdb->get_results("SELECT * FROM $wpdb->posts $where ORDER BY post_date_gmt ASC");
		foreach ($posts as $post) 
		{
			setup_postdata($post); 
			$postObj = array();
			$postObj['name'] = $post->ID;
			$postObj['title'] = $post->post_title;
			$postObj['url'] = get_permalink($post->ID);
			$postObj['createDate'] = (1000 * strtotime($post->post_date)); 
			$commentList = array();
			$comments = $wpdb->get_results( $wpdb->prepare("SELECT * FROM $wpdb->comments WHERE comment_approved = %d AND comment_post_ID = %d AND comment_agent NOT LIKE %s", 1, $post->ID, 'CommentReactor%') );
			if($comments)
			{
				foreach ( $comments as $c ) 
				{
					$commentObj = array();
					$commentObj['extId'] = $c->comment_ID;
					$commentObj['username'] = $c->comment_author;
					//PHP's strtotime sucks.  youd think that it would like comment_date_gmt, but it adds the current timezone
					//to the result, so if you feed in a GMT date, it spits back out a non-gmt one.
					$commentObj['createDate'] = (1000 * strtotime($c->comment_date));  
					$commentObj['deleted'] = false;
					$commentObj['text'] = $c->comment_content;					
					array_push($commentList, $commentObj);
				}
			}
			$postObj['comments'] = $commentList;	
			array_push($postList, $postObj);		
		}
	}
}
$json = new Services_JSON();
return 	$json->encode($postList);

}
?>
