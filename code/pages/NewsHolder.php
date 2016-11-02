<?php

/**
 * A top level page that contains news articles
 *
 * @author Marcus Nyeholt <marcus@silverstripe.com.au>
 * @license BSD License http://silverstripe.org/bsd-license/
 */
class NewsHolder extends Page {

	private static $db = array(
		'AutoFiling'			=> 'Boolean',		// whether articles created in this holder
													// automatically file into subfolders
		'FilingMode'			=> 'Varchar',		// Date, Month, Year
		'FileBy'				=> "Varchar",
        
        'OrderBy'				=> "Varchar",
        'OrderDir'				=> "Varchar",
        
		'PrimaryNewsSection'	=> 'Boolean',		// whether this holder should be regarded as a primary
													// news section (some are secondary and merely categorisation tools)
	);
	
	private static $defaults = array(
		'AutoFiling'			=> false, 
		'PrimaryNewsSection'	=> true
	);
	
	private static $icon = 'news/images/newsholder';

	private static $allowed_children = array(
		'NewsArticle',
		'NewsHolder'
	);
	/**
	 * Should this news article be automatically filed into a year/month/date
	 * folder on creation.
	 *
	 * @var boolean
	 */
	private static $automatic_filing = true;
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

		$modes = array(
			''		=> 'No filing',
			'day'	=> '/Year/Month/Day',
			'month'	=> '/Year/Month',
			'year'	=> '/Year'
		);
		$fields->addFieldToTab('Root.Main', new DropdownField('FilingMode', _t('NewsHolder.FILING_MODE', 'File into'), $modes), 'Content');
		$fields->addFieldToTab('Root.Main', new DropdownField('FileBy', _t('NewsHolder.FILE_BY', 'File by'), array('Published' => 'Published', 'Created' => 'Created')), 'Content');
		$fields->addFieldToTab('Root.Main', new CheckboxField('PrimaryNewsSection', _t('NewsHolder.PRIMARY_SECTION', 'Is this a primary news section?'), true), 'Content');

		$fields->addFieldToTab('Root.Main', new DropdownField('OrderBy', _t('NewsHolder.ORDER_BY', 'Order by'), array('OriginalPublishedDate' => 'Published', 'Created' => 'Created')), 'Content');
		$fields->addFieldToTab('Root.Main', new DropdownField('OrderDir', _t('NewsHolder.ORDER_DIR', 'Order direction'), array('DESC' => 'Descending date', 'ASC' => 'Ascending date')), 'Content');
		
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
		if ($start < 0) {
			$start = 0;
		}

		$articles = null;
		$filter = null;
		if ($this->PrimaryNewsSection) {
			// get all where the holder = me
			$filter = array('NewsSectionID' => $this->ID);
		} else {
			$subholders = $this->SubSections();
			if ($subholders) {
				$subholders->push($this);
			} else {
				$subholders = null;
			}

			if ($subholders && $subholders->Count()) {
				$ids = $subholders->column('ID');
				$filter = array('ParentID' => $ids);
			} else {
				$filter = array('ParentID' => (int) $this->ID);
			}
		}

		$orderBy = strlen($this->OrderBy) ? $this->OrderBy : 'OriginalPublishedDate';
		$dir = strlen($this->OrderDir) ? $this->OrderDir : 'DESC';
		if (!in_array($dir, array('ASC', 'DESC'))) {
			$dir = 'DESC';
		}

		$articles = NewsArticle::get()->filter($filter)->sort(array($orderBy => $dir))->limit($number, $start);
		$entries = PaginatedList::create($articles);
		$entries->setPaginationFromQuery($articles->dataQuery()->query());

		return $entries;
	}

	/**
	 * Returns a list of sub news sections, if available
	 *
	 * @return DataObjectSet
	 */
	public function SubSections($allChildren=true) {
		$subs = null;

		$childHolders = NewsHolder::get()->filter('ParentID', $this->ID);
		if ($childHolders && $childHolders->count()) {
			$subs = new ArrayList();
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
	 * Maintain API compatibility with NewsArticle
	 * 
	 * @return NewsHolder
	 */
	public function Section() {
		return $this->findSection();
	}
	
	/**
	 * Find the section this news article is currently in, based on ancestor pages
	 */
	public function findSection() {
		$page = $this;
		while ($page && $page->ID) {
			if ($page->ParentID == 0 || $page->PrimaryNewsSection) {
				return $page;
			}
			$page = $page->Parent();
		}
	}

	/**
	 * Gets an appropriate sub article holder for the given article page
	 *
	 * @param Page $article
	 */
	public function getPartitionedHolderForArticle($article) {
		if ($this->FileBy == 'Published' && $article->OriginalPublishedDate) {
			$date = $article->OriginalPublishedDate;
		} else if ($this->hasField($this->FileBy)) {
			$field = $this->FileBy;
			$date = $this->$field;
		} else {
			$date = $article->Created;
		}

		$year = date('Y', strtotime($date));
		$month = date('M', strtotime($date));
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
			$class = get_class($this);
			$child = new $class();
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

	/**
	 * We do not want to use NewsHolder->SubSections because this splits the paginations into
	 * the categories the articles are in which means the pagination will not work or will display
	 * multiple times
	 *
	 * @return Array
	 */
	public function TotalChildArticles($number = null) {
		if (!$number) {
			$number = $this->numberToDisplay;
		}

		$start = isset($_REQUEST['start']) ? (int) $_REQUEST['start'] : 0;
		if ($start < 0) {
			$start = 0;
		}

		$articles = NewsArticle::get('NewsArticle', '', '"OriginalPublishedDate" DESC, "ID" DESC', '', $start . ',' . $number)
			->filter(array('ID' => $this->getDescendantIDList()));
		$entries = PaginatedList::create($articles);
		$entries->setPaginationFromQuery($articles->dataQuery()->query());
		return $entries;
	}
}

class NewsHolder_Controller extends Page_Controller {
	public static $allowed_actions = array('Rss');

    public function init() {
        RSSFeed::linkToFeed($this->owner->Link() . "rss", _t('News.RSSLINK',"RSS feed for the News"));
        parent::init();
    }

    function Rss() {
        $parent = $this->data()->ID;
        $objects = NewsArticle::get()->filter('ParentID', $parent)->sort('LastEdited DESC')->limit(10);
        $rss = new RSSFeed($objects, $this->data()->Link(), _t('News.RSSTITLE',"10 most recent news"), "", "Title", "Content");
        $this->response->addHeader('Content-Type', 'application/rss+xml');
        return $rss->outputToBrowser();
    }
}