<div class="typography">
	<% if Level(2) %>
	<% include BreadCrumbs %>
	<% end_if %>
	<h2>$Title</h2>
	<div class="newsArticlePublishedDate">$PublishedDate.Nice</div>
	<div class="newsArticleSummary">
	$Summary
	</div>
	<div class="newsArticleContent">
	$Content
	</div>

	$Form
	$PageComments
</div>
