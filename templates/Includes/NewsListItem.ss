<ul class="newsArticles">
	<% if Articles %><% control Articles %>
	<li>
	<h3><a href="$Link">$Title</a></h3>
	<% if Thumbnail %>
	$Thumbnail.SetRatioSize(50,50)
	<% end_if %>
	<p>$Content.Summary(50)</p>
	<p><a href="$Link">Read the full article... </a></p>
	</li>
	<% end_control %><% end_if %>
</ul>