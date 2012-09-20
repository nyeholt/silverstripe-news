<div class="content-container typography">	
	<article>
		<h1>$Title</h1>
		<div class="content">
			$Content
			<!-- if there's some subsections, lets use those and list their children -->
			<% if SubSections(false) %>
				<% control SubSections(false) %>
				<h2><a href="$Link">$Title.XML</a></h2>
				<% include NewsListItem %>
				<% end_control %>
			<% else %>
				<!-- otherwise, lets just use the children of the current news holder -->
				<% include NewsListItem %>
				<% if Articles.MoreThanOnePage %>
					<div id="NextPrevLinks">
					  <% if Articles.NotLastPage %>
					  <div id="NextLink">
						<p><a class="next" href="$Articles.NextLink" title="View the next page">See older articles</a></p>
					  </div>
					  <% end_if %>
					  <% if Articles.NotFirstPage %>
					  <div id="PrevLink">
						<p><a class="prev" href="$Articles.PrevLink" title="View the previous page">See newer articles</a></p>
					  </div>
					  <% end_if %>
					  <span>
						<% if Articles.PaginationSummary %><% control Articles.PaginationSummary %>
						  <% if CurrentBool %>
							$PageNum
						  <% else %>
							<a href="$Link" title="View page number $PageNum">$PageNum</a>
						  <% end_if %>
						<% end_control %><% end_if %>
					  </span>
					</div>
				 <% end_if %>
			<% end_if %>
		</div>
	</article>
	$Form
	$PageComments
</div>
