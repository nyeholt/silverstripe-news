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

} 