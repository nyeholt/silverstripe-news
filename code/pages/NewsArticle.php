<?php

/**
 * A news article in the system
 *
 * @author Marcus Nyeholt <marcus@silverstripe.com.au>
 * @license BSD License http://silverstripe.org/bsd-license/
 */
class NewsArticle extends Page {

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
		'Thumbnail' => 'Image',
	);

	public function getCMSFields() {
		$fields = parent::getCMSFields();

		$fields->addFieldToTab('Root.Main', new TextField('Author', _t('NewsArticle.AUTHOR', 'Author')), 'Content');
		$fields->addFieldToTab('Root.Main', $dp = new DateField('OriginalPublishedDate', _t('NewsArticle.PUBLISHED_DATE', 'When was this article first published?')), 'Content');

		$dp->setConfig('showcalendar', true);

		$fields->addFieldToTab('Root.Main', new TextField('ExternalURL', _t('NewsArticle.EXTERNAL_URL', 'External URL to article (will automatically redirect to this URL if no article content set)')), 'Content');
		$fields->addFieldToTab('Root.Main', new TextField('Source', _t('NewsArticle.SOURCE', 'News Source')), 'Content');

		$fields->addFieldToTab('Root.Main', $if = new UploadField('Thumbnail', _t('NewsArticle.THUMB', 'Thumbnail')), 'Content');
		$if->setConfig('allowedMaxFileNumber', 1)->setFolderName('news-articles/thumbnails');
		$if->getValidator()->setAllowedExtensions(array('jpg', 'jpeg', 'png', 'gif'));

		if (!$this->OriginalPublishedDate) {
			// @TODO Fix this to be correctly localized!!
			$this->OriginalPublishedDate = date('Y-m-d');
		}

		// $fields->addFieldToTab('Root.Content.Main', new TreeDropdownField('InternalPageLinkID', _t('NewsArticle.INTERNAL_PAGE', 'A page on this site for the news')), 'Content');
		$fields->addFieldToTab('Root.Main', new TreeDropdownField('InternalFileID', _t('NewsArticle.INTERNAL_FILE', 'Select a file containing this news article, if any'), 'File'), 'Content');
		$fields->addFieldToTab('Root.Main', $summary = new HtmlEditorField('Summary', _t('NewsArticle.SUMMARY', 'Article Summary (displayed in listings)')), 'Content');
		$summary->addExtraClass('stacked');
		return $fields;
	}

	/**
	 * When the article is saved, and this article's section dictates that it
	 * needs to be filed, then do so
	 */
	public function onBeforeWrite() {
		parent::onBeforeWrite();
		
		// dummy initial date
		if (!$this->OriginalPublishedDate) {
			// @TODO Fix this to be correctly localized!!
			$this->OriginalPublishedDate = date('Y-m-d 12:00:00');
		}
		
		$parent = $this->Parent();

		// just in case we've been moved, update our section
		$section = $this->findSection();
		$this->NewsSectionID = $section->ID;

		$newlyCreated = $section->ID == $parent->ID;
		$changedPublishDate = $this->isChanged('OriginalPublishedDate', 2);

		if (($changedPublishDate || $newlyCreated) && ($section->AutoFiling || $section->FilingMode)) {
			if (!$this->Created) {
				$this->Created = date('Y-m-d H:i:s');
			}
			$pp = $this->PartitionParent();
			if ($pp->ID != $this->ParentID) {
				$this->ParentID = $pp->ID;
			}
		}

	}

	/**
	 * Make sure all parents are published when publishing a news article
	 */
	public function onBeforePublish() {
		// go through all parents that are news holders and publish them if they haven't been
		$this->publishSection();
	}
	
	public function onAfterPublish() {
		// $this->publishSection();
	}

	/**
	 * Ensure's the section is published.
	 * 
	 * We need to do it both before and after publish because of some quirks with
	 * folders not existing on one but existing on the other depending on the order of
	 * writing the objects
	 */
	protected function publishSection() {
		$parent = DataObject::get_by_id('NewsHolder', $this->ParentID);
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
	public function Section() {
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
		if ($this->ParentID && $this->Parent() instanceof NewsHolder) {
			return $this->Parent()->findSection();
		}
		return $this;
	}

	/**
	 * Gets the parent for this article page based on its section, and its
	 * creation date
	 */
	public function PartitionParent() {
		$section = $this->findSection();
		$holder = $section->getPartitionedHolderForArticle($this);
		return $holder;
	}

	/**
	 * Indicates if this has an external URL link
	 *
	 * @return boolean
	 */
	public function HasExternalLink() {
		return strlen($this->ExternalURL) || $this->InternalFileID;
	}

	/**
	 * Link to the news article. If it has an external URL set, or a file, link to that instead. 
	 *
	 * @param String $action
	 * @return String
	 */
	public function Link($action='') {
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
	
	 	
	/**
	 * Pages to update cache file for static publisher
	 *
	 * @return Array
	 */
	public function pagesAffectedByChanges() {
    	$parent = $this->Parent();
    	$urls 	= array($this->Link());
		
		// add all parent (holders)
		while($parent && $parent->ParentID > -1){
    		$urls[] = $parent->Link();
    		$parent = $parent->Parent();
   		}
   		
   		$this->extend('updatePagesAffectedByChanges', $urls);
    	
    	return $urls;
  	}

}

class NewsArticle_Controller extends Page_Controller {
}