<ul class="newsArticles">
	<% if Articles %><% control Articles %>
	<li>
	<h3><a href="$Link">$Title</a></h3>
	<% if Thumbnail %>
	<div class="newsThumbnail">
	<a href="$Link">
	$Thumbnail.SetRatioSize(50,50)
	</a>
	</div>
	<% end_if %>
	<p>$Summary</p>
	<p><a href="$Link">Read the full article... </a></p>
	</li>
	<% end_control %><% end_if %>
</ul>