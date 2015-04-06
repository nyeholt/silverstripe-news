<?php
/**
 * Created by Nivanka Fonseka (nivanka@silverstripers.com).
 * User: nivankafonseka
 * Date: 4/6/15
 * Time: 2:00 PM
 * To change this template use File | Settings | File Templates.
 */

class NewsIndex extends Page {

	private static $db = array(
		'ItemsPerPage' 	=> 'Int',
	);

	function getCMSFields(){
		$fields = parent::getCMSFields();

		$fields->removeByName('Content');

		$fields->addFieldToTab('Root.Main',
			TextField::create('ItemsPerPage')
				->setTitle('Number of items per page'),
			'Metadata'
		);

		return $fields;
	}

}

class NewsIndex_Controller extends Page_Controller {



}