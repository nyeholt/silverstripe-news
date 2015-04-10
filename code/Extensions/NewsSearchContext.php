<?php
/**
 * Created by Nivanka Fonseka (nivanka@silverstripers.com).
 * User: nivankafonseka
 * Date: 4/10/15
 * Time: 4:41 PM
 * To change this template use File | Settings | File Templates.
 */

class NewsSearchContext extends SearchContext {


	public function __construct($modelClass, $fields = null, $filters = null) {

		$fields = new FieldList(
			TextField::create('Title'),
			TextField::create('URLSegment'),
			HeaderField::create('DatesHeader', 'Dates')->setHeadingLevel(3),
			DateField::create('StartDate')->setTitle(null)->setAttribute('placeholder', 'Start Date'),
			DateField::create('EndDate')->setTitle(null)->setAttribute('placeholder', 'End Date'),
			CheckboxSetField::create('Types')->setSource(NewsSearchContext::GetNewsTypes())
				->setValue(isset($_REQUEST['q']) && isset($_REQUEST['q']['Types']) ? $_REQUEST['q']['Types'] : null),
			TextField::create('Tags'),
			TextField::create('Summary'),
			TextField::create('Content'),
			TextField::create('Author'),
			CheckboxSetField::create('Categories')->setSource(NewsCategory::get()->map('ID', 'Title')->toArray())
				->setValue(isset($_REQUEST['q']) && isset($_REQUEST['q']['Categories']) ? $_REQUEST['q']['Categories'] : null)

		);


		$filters = array(
			'Title'				=> new PartialMatchFilter('Title'),
			'URLSegment'		=> new PartialMatchFilter('URLSegment'),
			'Tags'				=> new PartialMatchFilter('Tags'),
			'Summary'			=> new PartialMatchFilter('Summary'),
			'Content'			=> new PartialMatchFilter('Content'),
			'Author'			=> new PartialMatchFilter('Author'),
			'StartDate'			=> new GreaterThanOrEqualFilter('DateTime'),
			'EndDate'			=> new LessThanOrEqualFilter('DateTime')
		);


		parent::__construct($modelClass, $fields, $filters);
	}

	public function getQuery($searchParams, $sort = false, $limit = false, $existingQuery = null) {

		$dataList = parent::getQuery($searchParams, $sort, $limit, $existingQuery);

		$params = is_object($searchParams) ? $searchParams->getVars() : $searchParams;
		if(!is_object($searchParams)){

			if(isset($params['Types'])){
				$dataList = $dataList->filter('ClassName', $params['Types']);
			}

			if(isset($params['Categories']) && is_array($params['Categories']) && !empty($params['Categories'])){
				$dataList = $dataList->where('EXISTS ( SELECT 1 FROM `NewsPost_Categories` np_c
					WHERE `np_c`.`NewsCategoryID` IN (' . implode(',', $params['Categories']) . ')
						AND `np_c`.`NewsPostID` = `NewsPost`.`ID`
					LIMIT 1
				)');
			}

		}

		return $dataList;
	}



	public static function GetNewsTypes(){
		$arrRet = array();
		foreach(ClassInfo::subclassesFor('NewsPost') as $strClassName)
			$arrRet[$strClassName] = $strClassName;

		return $arrRet;
	}




} 