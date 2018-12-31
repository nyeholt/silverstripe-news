<ul class="newsArticles">
	<% if Articles %><% loop Articles %>
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
	<% end_loop %><% end_if %>
</ul>