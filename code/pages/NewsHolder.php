<?php

/**
 * A top level page that contains news articles
 *
 * @author Marcus Nyeholt <marcus@silverstripe.com.au>
 * @license BSD License http://silverstripe.org/bsd-license/
 */
class NewsHolder extends Page {

	public static $db = array(
		'AutoFiling'			=> 'Boolean',		// whether articles created in this holder
													// automatically file into subfolders
		'ArchiveAfter'			=> 'Varchar',		// if set, child items will be archived after this number of days
		
		'FilingMode'			=> 'Varchar',		// Date, Month, Year
		'FileBy'				=> "Varchar",
		'PrimaryNewsSection'	=> 'Boolean',		// whether this holder should be regarded as a primary
													// news section (some are secondary and merely categorisation tools)
		'OrderBy'				=> 'Varchar',		// what field to order by
		'OrderDir'				=> 'Varchar',		// what direction to order by
	);

	public static $defaults = array(
		'AutoFiling'			=> false,
		'PrimaryNewsSection'	=> true
	);

	public static $icon = 'news/images/newsholder';

	public static $month_format = 'M';

	public static $allowed_children = array(
		'NewsArticle'
	);
	/**
	 * Should this news article be automatically filed into a year/month/date
	 * folder on creation.
	 *
	 * @var boolean
	 */
	public static $automatic_filing = true;
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
	 * Gets the fields to display for this news holder in the CMS
	 *
	 * @return FieldSet
	 */
	public function getCMSFields() {
		$fields = parent::getCMSFields();
//		$fields->addFieldToTab('Root.Content.Main', new CheckboxField('AutoFiling', _t('NewsHolder.AUTO_FOLDER', 'Automatically file contained Articles'), true), 'Content');

		$modes = array(
			''		=> 'No filing',
			'day'	=> '/Year/Month/Day',
			'month'	=> '/Year/Month',
			'year'	=> '/Year'
		);

		$fields->addFieldToTab('Root.Content.Main', new DropdownField('FilingMode', _t('NewsHolder.FILING_MODE', 'File into'), $modes), 'Content');
		$fields->addFieldToTab('Root.Content.Main', new DropdownField('FileBy', _t('NewsHolder.FILE_BY', 'File by'), array('Published' => 'Published', 'Created' => 'Created')), 'Content');
		$fields->addFieldToTab('Root.Content.Main', new DropdownField('OrderBy', _t('NewsHolder.ORDER_BY', 'Order by'), array('OriginalPublishedDate' => 'Published', 'Created' => 'Created')), 'Content');
		$fields->addFieldToTab('Root.Content.Main', new DropdownField('OrderDir', _t('NewsHolder.ORDER_DIR', 'Order direction'), array('DESC' => 'Descending date', 'ASC' => 'Ascending date')), 'Content');
		
		$fields->addFieldToTab('Root.Content.Main', new CheckboxField('PrimaryNewsSection', _t('NewsHolder.PRIMARY_SECTION', 'Is this a primary news section?'), true), 'Content');
		
		$times = array(
			''			=> 'Never',
			86400*7		=> '1 week',
			86400*30	=> '1 month',
			86400*60	=> '2 months',
			86400*90	=> '3 months',
		);

		$fields->addFieldToTab('Root.Content.Main', new DropdownField('ArchiveAfter', _t('NewsHolder.ARCHIVE_AFTER', 'Archive after'), $times), 'Content');
		
		$this->extend('updateNewsHolderCMSFields', $fields);
		
		return $fields;
	}

	public function onBeforeWrite() {
		parent::onBeforeWrite();

		// set the filing mode, now that it's being obsolete
		if ($this->AutoFiling && !$this->FilingMode) {
			$this->FilingMode = 'day';
			$this->AutoFiling = false;
		}
	}
	
	public function onAfterWrite() {
		if (strlen($this->ArchiveAfter)) {
			$this->getArchive();
		}
	}

	/**
	 * Returns a list of articles within this news holder.
	 *
	 * If there are sub-newsholders, it will return all the articles from there also
	 *
	 * @return DataObjectSet
	 */
	public function Articles($number=null) {
		if (!$number) {
			$number = $this->numberToDisplay;
		}

		$start = isset($_REQUEST['start']) ? (int) $_REQUEST['start'] : 0;

		$articles = null;
		$filter = null;
		if ($this->PrimaryNewsSection) {
			// get all where the holder = me
			$filter = singleton('NewsUtils')->dbQuote(array('NewsSectionID = ' => (int) $this->ID));
		} else {
			$subholders = $this->SubSections();
			if ($subholders) {
				$subholders->push($this);
			} else {
				$subholders = new DataObjectSet(array($this));
			}

			if ($subholders && $subholders->Count()) {
				$ids = $subholders->column('ID');
				$filter = singleton('NewsUtils')->dbQuote(array('ParentID IN' => $ids));
			} else {
				$filter = singleton('NewsUtils')->dbQuote(array('ParentID = ' => (int) $this->ID));
			}
		}

		$orderBy = strlen($this->OrderBy) ? $this->OrderBy : 'OriginalPublishedDate';
		$dir = strlen($this->OrderDir) ? $this->OrderDir : 'DESC';
		if (!in_array($dir, array('ASC', 'DESC'))) {
			$dir = 'DESC';
		}
		$articles = DataObject::get('NewsArticle', $filter, "\"$orderBy\" $dir, \"ID\" DESC", '', $start . ',' . $number);
		return $articles;
	}

	/**
	 * Returns a list of sub news sections, if available
	 *
	 * @return DataObjectSet
	 */
	public function SubSections($allChildren = true) {
		$subs = null;

		$childHolders = DataObject::get('NewsHolder', singleton('NewsUtils')->dbQuote(array('ParentID =' => $this->ID)));
		//var_dump($childHolders);

		if ($childHolders && $childHolders->Count()) {
			$subs = new DataObjectSet();
			foreach ($childHolders as $holder) {
				$subs->push($holder);
				if ($allChildren === true) {
					// see if there's any children to include
					$subSub = $holder->SubSections();
					if ($subSub) {
						$subs->merge($subSub);
					}
				}

			}
		}
		return $subs;
	}

	/**
	 * Gets an appropriate sub article holder for the given article page
	 *
	 * @param Page $article
	 */
	public function getPartitionedHolderForArticle($article) {
		$date = null;
		if ($this->FileBy == 'Published' && $article->OriginalPublishedDate) {
			$date = $article->OriginalPublishedDate;
		} else {
			$fileBy = $this->FileBy;
			if (strlen($fileBy) && $article->hasField($fileBy)) {
				$date = $article->$fileBy;
			} 
		}
		
		if (!$date) {
			$date = $article->Created;
		}

		$year = date('Y', strtotime($date));
		$month = date(self::$month_format, strtotime($date));
		$day = date('d', strtotime($date));

		$yearFolder = $this->dateFolder($year);
		if (!$yearFolder) {
			throw new Exception("Failed retrieving folder");
		}

		if ($this->FilingMode == 'year') {
			return $yearFolder;
		}

		$monthFolder = $yearFolder->dateFolder($month);
		if (!$monthFolder) {
			throw new Exception("Failed retrieving folder");
		}

		if ($this->FilingMode == 'month') {
			return $monthFolder;
		}

		$dayFolder = $monthFolder->dateFolder($day);
		if (!$dayFolder) {
			throw new Exception("Failed retrieving folder");
		}

		return $dayFolder;
	}

	/**
	 *
	 * Finds or creates a new child object based on a given name
	 *
	 * @param String $name
	 * @param String $type
	 */
	public function dateFolder($name, $publish=false) {
		// see if we have a named child, otherwise create one
		$child = DataObject::get_one('NewsHolder', 'ParentID = ' . $this->ID . ' AND Title = \'' . Convert::raw2sql($name) . '\'');

		if (!$child || !$child->ID) {
			$child = new NewsHolder();
			$child->Title = $name;
			$child->ParentID = $this->ID;
			$child->AutoFiling = false;
			$child->PrimaryNewsSection = false;
			$child->write();
			if ($publish) {
				$child->doPublish();
			}
		}
		return $child;
	}

	/**
	 * Pages to update cache file for static publisher
	 *
	 * @return Array
	 */
	public function pagesAffectedByChanges() {
		$urls = array($this->Link());
		return $urls;
	}
	
	public function getArchive() {
		$archive = DataObject::get_one('Page', '"ParentID" = ' . $this->ID . ' AND "URLSegment" = \'archive\'');

		if ($archive && !($archive instanceof NewsHolder)) {
			$archive->URLSegment = 'old-archive';
			$doPublish = $archive->isPublished();
			$archive->write();
			if ($doPublish) {
				$archive->doPublish();
			}
			$archive = null;
		}

		if (!$archive) {
			$stage = Versioned::current_stage();
			if (!$stage || $stage == 'Stage') {
				$archive = new NewsHolder(array(
					'Title'			=> 'Archive',
					'ParentID'		=> $this->ID,
					'URLSegment'	=> 'archive',
					'ShowInMenus'	=> false,
					'AutoFiling'		=> true,
					'FilingMode'		=> 'month',
					'FileBy' 			=> 'Published',
					'PrimaryNewsSection'	=> true,
				));

				$archive->write();
			}
		}
		
		return $archive;
	}
}

class NewsHolder_Controller extends Page_Controller {

    public static $allowed_actions = array('Rss');

    public function init() {
        RSSFeed::linkToFeed($this->owner->Link() . "rss", _t('News.RSSLINK',"RSS feed for the News"));
        parent::init();
    }

    function Rss() {
        $rss = new RSSFeed(DataObject::get("NewsArticle", "", "LastEdited DESC", "", 10), $this->owner->Link(), _t('News.RSSTITLE',"10 most recent news"), "", "Title", "Content");
        $rss->outputToBrowser();
    }
	
}
