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


		if(get_class($this->owner) == 'NewsIndex'){
			$strURL = $this->owner->getNewsItemsEditLink();


			$original =  parent::getChildrenAsUL($attributes, $titleEval, $extraArg, $limitToMarked, $childrenMethod, $numChildrenMethod,
				$rootCall, $nodeCountThreshold, $nodeCountCallback);

			$output = $original ? $original : "";
			$output.= "<ul$attributes>\n";
			$output.= '<li class="readonly">
				<a class="cms-panel-link" data-pjax-target="Content" href="' . $strURL . '">' . $this->owner->getTreeEditLinkText() .  '</a>
			</li>';
			$output.= "</ul>";
			return $output;
		}
		else{
			return parent::getChildrenAsUL($attributes, $titleEval, $extraArg, $limitToMarked, $childrenMethod, $numChildrenMethod,
				$rootCall, $nodeCountThreshold, $nodeCountCallback);
		}
	}
	
	public function loadDescendantIDListInto(&$idList) {
		if($children = $this->AllChildren()) {
			foreach($children as $child) {
				if(in_array($child->ID, $idList)) {
					continue;
				}
				$idList[] = $child->ID;
				$ext = $child->getExtensionInstance('NewsHierarchy');
				$ext->setOwner($child);
				$ext->loadDescendantIDListInto($idList);
				$ext->clearOwner();
			}
		}
	}

	public function doAllChildrenIncludingDeleted($context = null) {
		if(!$this->owner) user_error('Hierarchy::doAllChildrenIncludingDeleted() called without $this->owner');

		$baseClass = ClassInfo::baseDataClass($this->owner->class);
		if($baseClass) {
			$stageChildren = $this->owner->stageChildren(true);
			$stageChildren = $this->RemoveNewsPostsFromSiteTree($stageChildren);

			// Add live site content that doesn't exist on the stage site, if required.
			if($this->owner->hasExtension('Versioned')) {
				// Next, go through the live children.  Only some of these will be listed
				$liveChildren = $this->owner->liveChildren(true, true);
				if($liveChildren) {
					$liveChildren = $this->RemoveNewsPostsFromSiteTree($liveChildren);
					$merged = new ArrayList();
					$merged->merge($stageChildren);
					$merged->merge($liveChildren);
					$stageChildren = $merged;
				}
			}

			$this->owner->extend("augmentAllChildrenIncludingDeleted", $stageChildren, $context);

		} else {
			user_error("Hierarchy::AllChildren() Couldn't determine base class for '{$this->owner->class}'",
				E_USER_ERROR);
		}

		return $stageChildren;
	}


	public function RemoveNewsPostsFromSiteTree(DataList $list){
		$pageTypes = NewsPost::GetNewsTypes();
		$updatedList = $list;
		foreach($pageTypes as $strClassName){
			$updatedList = $updatedList->exclude('ClassName', $strClassName);
		}
		return $updatedList;
	}

} 