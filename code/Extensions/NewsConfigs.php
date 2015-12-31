<?php
/**
 * Created by Nivanka Fonseka (nivanka@silverstripers.com).
 * User: nivankafonseka
 * Date: 4/6/15
 * Time: 3:29 PM
 * To change this template use File | Settings | File Templates.
 */

class NewsConfigs extends DataExtension
{

    private static $db = array(
        'ArchivePattern'        => 'Varchar(100)',
        'NumberOfArchives'        => 'Int',
        'NumberOfTags'            => 'Int',
        'ItemsPerPage'            => 'Int'
    );

    public function updateCMSFields(FieldList $fields)
    {
        $fields->addFieldsToTab('Root.Settings.News', array(
            DropdownField::create('ArchivePattern')->setSource(array(
                '%Y'        => 'Year',
                '%Y, %M'    => 'Year, Full month name',
                '%Y, %m'    => 'Year, month number',
                '%b'        => 'Abbreviated month name (Jan..Dec)'
            )),
            NumericField::create('NumberOfArchives'),
            NumericField::create('NumberOfTags'),
            NumericField::create('ItemsPerPage')
        ));
    }
}
