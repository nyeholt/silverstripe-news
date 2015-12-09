<?php
/**
 * Created by Nivanka Fonseka (nivanka@silverstripers.com).
 * User: nivankafonseka
 * Date: 4/6/15
 * Time: 2:01 PM
 * To change this template use File | Settings | File Templates.
 */

class NewsAdmin extends ModelAdmin {

	private static $menu_icon = 'silverstripe-news/images/news-icon.png';

	private static $url_segment = 'news';
	private static $menu_title = 'News';

	public $showImportForm = false;

	private static $managed_models = array(
		'NewsPost',
		'NewsCategory'
	);

	private static $exclude_classes = array();


	public function init(){
		Versioned::set_reading_mode('stage');
		Config::inst()->update('NewsPost', 'pages_admin', false);
		parent::init();
	}

	public function getSearchableClasses(){
		$arrRet = array();
		$arrClasses = ClassInfo::subclassesFor('NewsPost');
		$arrExclude = Config::inst()->get('NewsAdmin', 'exclude_classes');
		if(!empty($arrExclude)){
			foreach($arrClasses as $strClass){
				if(!in_array($strClass, $arrExclude)){
					$arrRet[] = $strClass;
				}
			}
		}
		else{
			$arrRet = $arrClasses;
		}
		return $arrRet;
	}


	public function getSearchContext(){

		if($this->IsEditingNews()){
			$context = new NewsSearchContext($this->modelClass, $this);
			foreach($context->getFields() as $field)
				$field->setName(sprintf('q[%s]', $field->getName()));

			foreach($context->getFilters() as $filter)
				$filter->setFullName(sprintf('q[%s]', $filter->getFullName()));

			$this->extend('updateSearchContext', $context);
			return $context;

		}

		return parent::getSearchContext();
	}



	public function getEditForm($id = null, $fields = null){
		$form = parent::getEditForm($id, $fields);

		$field = $form->Fields()->dataFieldByName($this->modelClass);
		if($field){
			$config = $field->getConfig();
			if(!ClassInfo::exists('GridFieldBetterButtonsItemRequest') && $this->IsEditingNews()){
				$config->getComponentByType('GridFieldDetailForm')->setItemRequestClass('NewsGridFieldDetailForm_ItemRequest');
			}

			$singleton = singleton($this->modelClass);
			if(is_a($singleton, 'NewsPost') && ClassInfo::exists('GridFieldSortableRows')){
				$config->addComponent(new GridFieldSortableRows('Sort'));
			}

			$config->removeComponentsByType('GridFieldDeleteAction');
			$config->removeComponentsByType('GridFieldPaginator');

			$config->addComponent($pagination = new GridFieldPaginator(100));
		}

		return $form;

	}


	public function getList() {
		$list = parent::getList();
		if($this->IsEditingNews()){
			$list = $list->sort('DateTime DESC')->filter('ClassName', $this->getSearchableClasses());
		}

		$this->extend('updateNewsList', $list);

		return $list;
	}


	public function IsEditingNews(){
		$arrClasses = ClassInfo::subclassesFor('NewsPost');
		return in_array($this->modelClass, $arrClasses);
	}



} 