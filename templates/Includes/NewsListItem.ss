<ul class="newsArticles">
	<% if Articles %><% control Articles %>
	<li>
	<a href="$Link"><h3>$Title</h3></a>
	<% if Thumbnail %>
	$Thumbnail.SetRatioSize(50,50)
	<% end_if %>
	<p>$Content.Summary(50)</p>
	<p><a href="$Link">Read the full article... </a></p>
	</li>
	<% end_control %><% end_if %>
</ul>