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
 * A news article in the system
 *
 * @author Marcus Nyeholt <marcus@silverstripe.com.au>
 */
class NewsArticle extends Page
{
    public static $icon = 'news/images/newspaper';

	public static $db = array(
		'Summary' => 'HTMLText',
		'Author' => 'Varchar(128)',
		'OriginalPublishedDate' => 'Date',
		'ExternalURL' => 'Varchar(255)',
		'Source' => 'Varchar(128)',
	);

	/**
	 * The InternalFile is used when the news article is mostly contained in a file based item -
	 * if this is set, then the URL to the item is returned in the call to "Link" for this asset. 
	 *
	 * @var array
	 */
	public static $has_one = array(
		'InternalFile' => 'File',
		'NewsSection' => 'NewsHolder',
	);

	public function getCMSFields()
	{
		$fields = parent::getCMSFields();

		$fields->addFieldToTab('Root.Content.Main', new TextField('Author', _t('NewsArticle.AUTHOR', 'Author')), 'Content');
		$fields->addFieldToTab('Root.Content.Main', $dp = new DateField('OriginalPublishedDate', _t('NewsArticle.PUBLISHED_DATE', 'When was this article first published?')), 'Content');

		$dp->setConfig('showcalendar', true);

		$fields->addFieldToTab('Root.Content.Main', new TextField('ExternalURL', _t('NewsArticle.EXTERNAL_URL', 'External URL to article (will automatically redirect to this URL if no article content set)')), 'Content');
		$fields->addFieldToTab('Root.Content.Main', new TextField('Source', _t('NewsArticle.SOURCE', 'News Source')), 'Content');
		if (!$this->OriginalPublishedDate) {
			// @TODO Fix this to be correctly localized!!
			$this->OriginalPublishedDate = date('Y-m-d');
		}

		// $fields->addFieldToTab('Root.Content.Main', new TreeDropdownField('InternalPageLinkID', _t('NewsArticle.INTERNAL_PAGE', 'A page on this site for the news')), 'Content');
		$fields->addFieldToTab('Root.Content.Main', new TreeDropdownField('InternalFileID', _t('NewsArticle.INTERNAL_FILE', 'Select a file containing this news article, if any'), 'File'), 'Content');
		$fields->addFieldToTab('Root.Content.Main', new HtmlEditorField('Summary', _t('NewsArticle.SUMMARY', 'Article Summary (displayed in listings)')), 'Content');
		return $fields;
	}

	/**
	 * When the article is saved, and this article's section dictates that it
	 * needs to be filed, then do so
	 */
	public function onBeforeWrite()
	{
		parent::onBeforeWrite();
		if (!$this->OriginalPublishedDate) {
			// @TODO Fix this to be correctly localized!!
			$this->OriginalPublishedDate = date('Y-m-d 12:00:00');
		}

		$parent = $this->Parent();

		// just in case we've been moved, update our section
		$section = $this->findSection();
		$this->NewsSectionID = $section->ID;
		
		if ($section->ID == $parent->ID && $section->AutoFiling) {
			if (!$this->Created) {
				$this->Created = date('Y-m-d H:i:s');
			}
			$pp = $this->PartitionParent();
			$this->ParentID = $pp->ID;
		}
	}

	/**
	 * Make sure all parents are published when publishing a news article
	 */
	public function onAfterPublish() {
		// go through all parents that are news holders and publish them if they haven't been
		$parent = $this->Parent();
		while ($parent && $parent instanceof NewsHolder) {
			if ($parent->Status != 'Published') {
				$parent->doPublish();
			}
			$parent = $parent->Parent();
		}
	}

	/**
	 * Get the top level parent of this article that is marked as a section
	 *
	 *  @return NewsHolder
	 */
	public function Section()
	{
		if ($this->NewsSectionID) {
			return $this->NewsSection();
		}

		$section = $this->findSection();
		return $section;
	}

	/**
	 * Find the section this news article is currently in, based on ancestor pages
	 */
	public function findSection() {
		$page = $this;
		while($page) {
			if ($page->ParentID == 0 || $page->PrimaryNewsSection) {
				return $page;
			}
			$page = $page->Parent();
		}
	}

	/**
	 * Gets the parent for this article page based on its section, and its
	 * creation date
	 */
	public function PartitionParent()
	{
		$section = $this->findSection();
		$holder = $section->getPartitionedHolderForArticle($this);
		return $holder;
	}

	/**
	 * Link to the news article. If it has an external URL set, or a file, link to that instead. 
	 *
	 * @param String $action
	 * @return String
	 */
	public function Link($action='')
	{
		if (strlen($this->ExternalURL) && !strlen($this->Content)) {
			// redirect away
			return $this->ExternalURL;
		}
		if ($this->InternalFile()->ID) {
			$file = $this->InternalFile();
			return $file->Link($action);
		}
		return parent::Link($action);
	}
}

class NewsArticle_Controller extends Page_Controller
{
}