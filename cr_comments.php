<div id="CR_<?php echo $post->ID; ?>">
Loading Comments...
<div style="display:none;">
	<!-- 
		Cache of comments. Up to the second comments loaded via JSONP.  Powered by http://www.commentreactor.com.
	-->
	<?php if ($comments) : ?>
		<ol>
		<?php foreach ($comments as $comment) : ?>
			<li id="comment-<?php comment_ID() ?>">
				<cite><?php comment_author_link() ?></cite> Says:
				<?php if ($comment->comment_approved == '0') : ?>
				<em>Your comment is awaiting moderation.</em>
				<?php endif; ?>
				<?php comment_text() ?>
			</li>
		<?php endforeach; /* end for each comment */ ?>
		</ol>
	<?php endif; ?>
</div>
<?php 
 global $cr_site;
 global $cr_base_url;
?>
</div>
<script src='<?php echo $cr_base_url ?>js/all.js'></script>
<script>
	(function() 
		{
			var siteName = "<?php echo $cr_site ?>";
			var title = "<?php echo $post->post_title; ?>";
			var baseUrl = "<?php echo $cr_base_url ?>";
			var postName = "<?php echo $post->ID; ?>"
			var url = location.href.split("?")[0] + "?p=" + postName;
			CR.styleLoad(baseUrl, siteName);
			CR.postCommentLoad(baseUrl, siteName, postName, url, title);
		})()
</script>


