<?php
/**
 * Created by Nivanka Fonseka (nivanka@silverstripers.com).
 * User: nivankafonseka
 * Date: 4/6/15
 * Time: 2:29 PM
 * To change this template use File | Settings | File Templates.
 */

class NewsCategory extends DataObject
{

    private static $db = array(
        'Title'            => 'Varchar(200)',
        'SortOrder'        => 'Int'
    );

    private static $default_sort = 'SortOrder';
}
