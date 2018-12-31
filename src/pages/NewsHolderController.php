<?php

namespace Symbiote\News\Pages;

use SilverStripe\CMS\Controllers\ContentController;
use SilverStripe\Control\RSS\RSSFeed;

class NewsHolder_Controller extends ContentController {

	private static $allowed_actions = array('Rss');

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
