<?php
/**
 * Created by Nivanka Fonseka (nivanka@silverstripers.com).
 * User: nivankafonseka
 * Date: 4/6/15
 * Time: 2:00 PM
 * To change this template use File | Settings | File Templates.
 */

class NewsPost extends Page {

	private static $pages_admin = true;

	function getCMSFields(){

		$fields = parent::getCMSFields();

		if(!Config::inst()->get('NewsPost', 'pages_admin')){

			$arrTypes = NewsPost::GetNewsTypes();
			if(count($arrTypes) > 1){
				$arrDropDownSource = array();
				foreach($arrTypes as $strType)
					$arrDropDownSource[$strType] = $strType;
				$fields->addFieldToTab('Root.Main',
					DropdownField::create('ClassName')->setSource($arrDropDownSource)
						->setTitle('Type'),
					'Content');
			}

		}

		return $fields;

	}

	public static function GetNewsTypes(){
		return ClassInfo::subclassesFor('NewsPost');
	}

}

class NewsPost_Controller extends Page_Controller {



}