<?php
if(isset($_POST['export'])) 
{
	//Hacktastic paths.  TODO: find a better way to get where the current wordpress install is.
	define('ABSPATH', realpath('../../../')."/");	
	require_once(realpath('../../../wp-admin/admin.php'));
	require_once(dirname(__FILE__) . '/comment_export.php');
	header('Content-Description: File Transfer');
	header("Content-Disposition: attachment; filename=wordpress_commentreactor_export.json");
	header('Content-Type: text/json; charset=' . get_option('blog_charset'), true);

	$data= cr_write_export();
	echo $data;
	die();
}

function cr_get_base_url_or_default()
{
	$baseUrl = get_option('cr_base_url');
	if(!$baseUrl)
	{
		$baseUrl = 'http://www.commentreactor.net/';
	}
	return $baseUrl;
}

?>
<div class="wrap">
<h2>CommentReactor</h2>

<form method="post" action="options.php">
<?php wp_nonce_field('update-options'); ?>

<table class="form-table">

<tr valign="top">
<th scope="row">Site Name:</th>
<td><input type="text" name="cr_site" value="<?php echo get_option('cr_site'); ?>" />
<br/>
The site name you set for your site at <a target="_blank" href="http://www.commentreactor.net/site">http://www.commentreactor.net</a><br/>
</td>
</tr>
 
<tr valign="top">
<th scope="row">Base Url:</th>
<td><input type="text" name="cr_base_url" style="width:30em;" value="<?php echo cr_get_base_url_or_default(); ?>" />
<br/>
The url used to connect to CommentReactor (you should generally not have to change this).
</td>
</tr>

<tr valign="top">
<th scope="row">CommentReactor Availablity</th>
<td>
<?php
 $usageTypes = array ( 
 	array("value" => "none", "text" => "No posts"),
 	array("value" => "all", "text" => "All posts"),
 	array("value" => "before", "text" => "Posts before date"),
 	array("value" => "after", "text"=> "Post after date")
 );
 $currentValue = get_option('cr_usage');
 foreach($usageTypes as $type)
 {
 	$checked = '';
 	$value= $type['value'];
 	$text = $type['text'];
 	if($value == $currentValue)
 	{
 		$checked = 'checked="checked"';
 	}
 	echo "<label for='cr_$value'>$text</label>".
 	"<input type='radio' name='cr_usage' value='$value' $checked /><br/>";
 }
?>
<label for ="cr_usage_date">Date:</label>
<input type="text" id="cr_usage_date" style="width:10em" name='cr_usage_date' value="<?php echo get_option('cr_usage_date')?>"></input>(format mm/dd/yyyy)
<br/>
Select which posts to use with CommentReactor.  All posts made inside of CommentReactor are automatically synced (hourly) back into your 
wordpress install, so you can turn CommentReactor off at any time without worrying about losing your data.
</td>
</tr>

</table>

<input type="hidden" name="action" value="update" />
<input type="hidden" name="page_options" value="cr_site,cr_base_url,cr_usage,cr_usage_date" />

<p class="submit">
<input type="submit" name="Submit" value="<?php _e('Save Changes') ?>" />
</p>
</form>


<form action="../wp-content/plugins/commentreactor/setup.php" method = "post">
	<?php wp_nonce_field('cr_export'); ?>
	<input type="hidden" name="export" value="true" />

<table class="form-table">
<tr valign="top">
<th scope="row">Export Comments to Comment Reactor</th>
<td>
	<input class="button" type="submit" name="Submit" value="Export to File"/>
	<br/>
	In order to convert comments from your previous posts to CommentReactor, you can export a file containing 
	all of your previous posts.<br/>
	This file can be imported in the site settings page of <a target="_blank" href="http://www.commentreactor.net">http://www.commentreactor.net</a>.
</td>
</tr>
</table>
</form>


</div>
