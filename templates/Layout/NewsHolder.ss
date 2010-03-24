<div class="typography">
	
	<h2>$Title</h2>
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
				<a class="next" href="$Articles.NextLink" title="View the next page">See older articles</a>
			  </div>
			  <% end_if %>
			  <% if Articles.NotFirstPage %>
			  <div id="PrevLink">
				<a class="prev" href="$Articles.PrevLink" title="View the previous page">See newer articles</a>
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