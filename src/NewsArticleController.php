<?php

namespace Symbiote\News;

use SilverStripe\CMS\Controllers\ContentController;

//template inheritance doesnt work when not using PageController
//Seems PageController in root namespace is mandatory

class NewsArticleController extends \PageController {
}
