=== Comment Reactor ===
Contributors: Ian Taylor - Comment Reactor Team
Tags: comments, commentreactor, reactor, ajax, images, video
Requires at least: 2.1
Tested up to: 2.6.5
Stable tag: trunk

The Comment Reactor plugin replaces your comments with the feature-packed commenting system from http://commentreactor.com.

== Description ==

Comment Reactor gives you a more featureful commenting experience for your blog.  

Comment Reactor is a hosted service that uses AJAX to power a rich commenting engine.  
Commenters can attach images, files, and videos to their comments.  You can control the depth of nesting allowed by Comment Reactor.  
The comments made inside of Comment Reactor are automatically synced back into your wordpress comments, allowing for SEO to still function.

You can also import your existing comments from wordpress into Comment Reactor.

== Installation ==

1. Create a Comment Reactor account at http://www.commentreactor.net/register 
1. Add a Comment Reactor site for your blog, creating a unique sitename.
1. Upload the contents of the commentreactor folder to the `/wp-content/plugins/commentreactor` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Visit the Comment Reactor page in the 'Settings' menu in WordPress.
1. Configure your Comment Reactor plugin by setting your sitename, and configuring which posts Comment Reactor should appear on.  Don't worry, you can always turn off Comment Reactor and all your comments will still exist in wordpress.
1. Clear any wordpress caches that you may have configured.
1. Get commenting!


== Frequently Asked Questions ==

= Do comments added in Comment Reactor make it back into my wordpress database? =

Yes, once an hour the Comment Reactor plugin will reach out and download any new comments from commentreactor.com and add them 
to your wordpress database.

= Comment Reactor uses AJAX, and Google doesn't index things loaded with AJAX.  How will my blog get indexed in Google? =

Since Comment Reactor syncs your posts into the wordpress database, we render the page with all the text of the comments built in so that Google will find 
the information.  Then when the page loads, we use AJAX to load the up-to-date version of the comments from commentreactor.com.  This means that you get 
the benefit of the AJAX interface, and Google can still index your page.

