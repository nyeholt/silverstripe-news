	<div class="typography">
		<h2>$Title</h2>
		$Content

		<!-- if there's some subsections, lets use those and list their children -->
		<% if SubSections %>
			
			<% control SubSections %>
			<h2>$Title.XML</h2>
			$SetArticleNumber(3)
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
					<% if Articles.SummaryPagination %><% control Articles.SummaryPagination %>
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
