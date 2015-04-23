<?php
/**
 * Created by Nivanka Fonseka (nivanka@silverstripers.com).
 * User: nivankafonseka
 * Date: 4/6/15
 * Time: 3:07 PM
 * To change this template use File | Settings | File Templates.
 */

class NewsHierarchy extends Hierarchy {

	public function getChildrenAsUL($attributes = "", $titleEval = '"<li>" . $child->Title', $extraArg = null,
									$limitToMarked = false, $childrenMethod = "AllChildrenIncludingDeleted",
									$numChildrenMethod = "numChildren", $rootCall = true,
									$nodeCountThreshold = null, $nodeCountCallback = null) {


		if($this->owner->ClassName == 'NewsIndex'){
			$strURL = Director::baseURL() . '/admin/news?ParentID=' . $this->owner->ID;
			$output = "<ul$attributes>\n";
			$output.= '<li class="readonly">
				<a class="cms-panel-link" data-pjax-target="Content" href="' . $strURL . '">Use News admin to manage pages of this tree</a>
			</li>';
			$output.= "</ul>";
			return $output;
		}
		else{
			return parent::getChildrenAsUL($attributes, $titleEval, $extraArg, $limitToMarked, $childrenMethod, $numChildrenMethod,
				$rootCall, $nodeCountThreshold, $nodeCountCallback);
		}
	}

} 