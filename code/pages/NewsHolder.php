<?php
/*

Copyright (c) 2009, SilverStripe Australia PTY LTD - www.silverstripe.com.au
All rights reserved.

Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:

    * Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
    * Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the
      documentation and/or other materials provided with the distribution.
    * Neither the name of SilverStripe nor the names of its contributors may be used to endorse or promote products derived from this software
      without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE
GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT,
STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY
OF SUCH DAMAGE.
*/

/**
 * 
 *
 * @author Marcus Nyeholt <marcus@silverstripe.com.au>
 */
class NewsHolder extends Page
{
    public static $icon = 'news/images/newsholder';

	/**
	 * A bit of a cheat way of letting the template determine how many articles to display.
	 *
	 * We need to do this because using something like <% if Articles(2).HasMore %> doesn't work, as
	 * the .HasMore isn't parsed correctly...
	 * 
	 * @var int
	 */
	protected $numberToDisplay = 10;


	/**
	 * Returns a list of articles within this news holder.
	 *
	 * If there are sub-newsholders, it will return all the articles from there also
	 *
	 * @return DataObjectSet
	 */
	public function Articles($number=null)
	{
		if (!$number) {
			$number = $this->numberToDisplay;
		}

		$start = isset($_REQUEST['start']) ? (int) $_REQUEST['start'] : 0;

		$subholders = DataObject::get('NewsHolder', db_quote(array('ParentID =' => $this->ID)));
		$articles = null;
		if ($subholders && $subholders->Count()) {
			$ids = $subholders->column('ID');
			$articles = DataObject::get('NewsArticle', db_quote(array('ParentID IN' => $ids)), 'PublishedDate DESC', '', $start.','.$number);
		} else {
			$articles = DataObject::get('NewsArticle', db_quote(array('ParentID = ' => $this->ID)), 'PublishedDate DESC', '', $start.','.$number);
		}

		return $articles;
	}

	/**
	 *
	 * Set the number of articles to be displayed in a listing
	 * 
	 * @param int $num
	 */
	public function SetArticleNumber($num)
	{
		$this->numberToDisplay = $num;
	}

	/**
	 * Returns a list of sub news sections, if available
	 *
	 * @return DataObjectSet
	 */
	public function SubSections()
	{
		$subholders = DataObject::get('NewsHolder', db_quote(array('ParentID =' => $this->ID)));
		return $subholders;
	}
}

class NewsHolder_Controller extends Page_Controller
{

}
?>
