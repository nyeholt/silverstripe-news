<?php
/**
 * Created by Nivanka Fonseka (nivanka@silverstripers.com).
 * User: nivankafonseka
 * Date: 4/6/15
 * Time: 4:23 PM
 * To change this template use File | Settings | File Templates.
 */

class NewsGridFieldDetailForm_ItemRequest extends GridFieldDetailForm_ItemRequest {

	private static $allowed_actions = array (
		'publish',
		'unpublish',
		'edit',
		'view',
		'ItemEditForm'
	);

	public function ItemEditForm(){
		$form = parent::ItemEditForm();

		if($this->record->ID !== 0) {

			$form->Actions()->insertAfter(FormAction::create('doPublish', 'Save & Publish')->setUseButtonTag(true)
				->addExtraClass('ss-ui-action-constructive')
				->setAttribute('data-icon', 'accept'), 'action_doSave');

			if($this->record->isPublished()){
				$form->Actions()->push(FormAction::create('doUnpublish', 'Un-publish')->setUseButtonTag(true)
					->addExtraClass('ss-ui-action-destructive action-unpublished'));
			}

		}

		return $form;

	}

	public function doUnpublish($data, $form) {
		$controller = $this->getToplevelController();

		if(!$this->record->canEdit() || !$this->record->canPublish()) {
			return $controller->httpError(403);
		}

		try {
			$this->record->doUnpublish();
		} catch(ValidationException $e){

		}

		$link = '<a href="' . $this->Link('edit') . '">"'
			. htmlspecialchars($this->record->Title, ENT_QUOTES)
			. '"</a>';
		$message = _t(
			'NewsGridFieldDetailForm_ItemRequest.Unpublished',
			'Unpublished {name} {link}',
			array(
				'name' => $this->record->i18n_singular_name(),
				'link' => $link
			)
		);

		$form->sessionMessage($message, 'good', false);

		return $this->edit($controller->getRequest());

	}


	/**
	 * @param $data
	 * @param $form
	 * @return HTMLText|SS_HTTPResponse|ViewableData_Customised
	 */
	public function doPublish($data, $form, $request = null, $redirectURL = null)
	{

		$new_record = $this->record->ID == 0;
		$controller = $this->getToplevelController();
		$list = $this->gridField->getList();

		if($list instanceof ManyManyList) {
			// Data is escaped in ManyManyList->add()
			$extraData = (isset($data['ManyMany'])) ? $data['ManyMany'] : null;
		} else {
			$extraData = null;
		}

		if(!$this->record->canEdit() || !$this->record->canPublish()) {
			return $controller->httpError(403);
		}

		if (isset($data['ClassName']) && $data['ClassName'] != $this->record->ClassName) {
			$newClassName = $data['ClassName'];
			// The records originally saved attribute was overwritten by $form->saveInto($record) before.
			// This is necessary for newClassInstance() to work as expected, and trigger change detection
			// on the ClassName attribute
			$this->record->setClassName($this->record->ClassName);
			// Replace $record with a new instance
			$this->record = $this->record->newClassInstance($newClassName);
		}

		try {
			$form->saveInto($this->record);
			$this->record->write();
			$this->record->publish('Stage', 'Live');
			$list->add($this->record, $extraData);
		} catch(ValidationException $e) {
			$form->sessionMessage($e->getResult()->message(), 'bad', false);
			$responseNegotiator = new PjaxResponseNegotiator(array(
				'CurrentForm' => function() use(&$form) {
						return $form->forTemplate();
					},
				'default' => function() use(&$controller) {
						return $controller->redirectBack();
					}
			));
			if($controller->getRequest()->isAjax()){
				$controller->getRequest()->addHeader('X-Pjax', 'CurrentForm');
			}
			return $responseNegotiator->respond($controller->getRequest());
		}

		// TODO Save this item into the given relationship

		$link = '<a href="' . $this->Link('edit') . '">"'
			. htmlspecialchars($this->record->Title, ENT_QUOTES)
			. '"</a>';
		$message = _t(
			'GridFieldDetailForm.SaveAndPublished',
			'Saved and Published {name} {link}',
			array(
				'name' => $this->record->i18n_singular_name(),
				'link' => $link
			)
		);

		$form->sessionMessage($message, 'good', false);

		if($new_record) {
			return $controller->redirect($this->Link());
		} elseif($this->gridField->getList()->byId($this->record->ID)) {
			// Return new view, as we can't do a "virtual redirect" via the CMS Ajax
			// to the same URL (it assumes that its content is already current, and doesn't reload)
			return $this->edit($controller->getRequest());
		} else {
			// Changes to the record properties might've excluded the record from
			// a filtered list, so return back to the main view if it can't be found
			$noActionURL = $controller->removeAction($data['url']);
			$controller->getRequest()->addHeader('X-Pjax', 'Content');
			return $controller->redirect($noActionURL, 302);
		}


	}


} 