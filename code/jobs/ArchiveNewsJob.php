<?php

if (class_exists('AbstractQueuedJob')) {
	/**
	* 
	*
	* @author <marcus@silverstripe.com.au>
	* @license BSD License http://www.silverstripe.org/bsd-license
	*/
   class ArchiveNewsJob extends AbstractQueuedJob {
	   const RUN_EVERY	= 86400;
	   
	   public function __construct() {
		   if (!$this->totalSteps) {
			   $holders = $this->getHolders();
			   if ($holders) {
				   $this->totalSteps = $holders->count();
			   }
		   }
	   }

	   protected function getHolders() {
		   return DataObject::get('NewsHolder', '"ArchiveAfter" <> \'\'');
	   }
	   
	   public function getSignature() {
		   return 'archive-news';
	   }
	   
	   public function getTitle() {
		   return "Scan and archive news articles";
	   }

	   public function process() {
		   $holders = $this->getHolders();
		   
		   Versioned::reading_stage('Stage');
		   
		   foreach ($holders as $holder) {
			   $time = date('Y-m-d H:i:s', time() - $holder->ArchiveAfter);
			   $field = $holder->FileBy; 
			   if (!$field || $field == 'Published') {
				   $field = 'OriginalPublishedDate';
			   }
			   $toArchive = DataObject::get('NewsArticle', '"ParentID" = ' . $holder->ID .' AND "' . $field .'" < \'' . $time .'\'');
			   $archive = $holder->getArchive();
			   if ($toArchive && $archive) {
				   foreach ($toArchive as $article) {
					   $doPublish = $article->isPublished();
					   
					   $article->ParentID = $archive->ID;
					   $article->write();
					   if ($doPublish) {
						   $article->doPublish();
					   }
				   }
			   }

			   $this->currentStep++;
		   }
		   
		   $this->isComplete = true;
		   singleton('QueuedJobService')->queueJob(new ArchiveNewsJob(), date('Y-m-d 00:00:01', strtotime('tomorrow')));
	   }
   }
}
