<?php
/**
 * Created by Nivanka Fonseka (nivanka@silverstripers.com).
 * User: nivankafonseka
 * Date: 4/6/15
 * Time: 2:01 PM
 * To change this template use File | Settings | File Templates.
 */

class NewsAdmin extends ModelAdmin {

	private static $menu_icon = 'framework/admin/images/menu-icons/16x16/db.png';

	private static $url_segment = 'news';
	private static $menu_title = 'News';

	private static $managed_models = array(
		'NewsPost',
		'NewsCategory'
	);


	public function init(){
		Config::inst()->update('NewsPost', 'pages_admin', false);
		parent::init();
	}

	public function getEditForm($id = null, $fields = null){
		$form = parent::getEditForm($id, $fields);

		if(!ClassInfo::exists('GridFieldBetterButtonsItemRequest')){
			$arrNewsPosts = ClassInfo::subclassesFor('NewsPost');
			if(in_array($this->modelClass, $arrNewsPosts)){
				$field = $form->Fields()->dataFieldByName($this->modelClass);
				if($field){
					$field->getConfig()->getComponentByType('GridFieldDetailForm')->setItemRequestClass('NewsGridFieldDetailForm_ItemRequest');
				}
			}
		}

		return $form;

	}


	public function getList() {
		$list = parent::getList();
		$list = $list->sort('DateTime DESC');
		return $list;
	}



} 