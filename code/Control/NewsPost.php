<?php
/**
 * Created by Nivanka Fonseka (nivanka@silverstripers.com).
 * User: nivankafonseka
 * Date: 4/6/15
 * Time: 2:00 PM
 * To change this template use File | Settings | File Templates.
 */

class NewsPost extends Page
{

    private static $pages_admin = true;

    private static $db = array(
        'DateTime'        => 'SS_Datetime',
        'Tags'            => 'Varchar(500)',
        'Author'        => 'Varchar(100)',
        'Summary'        => 'HTMLText'
    );

    private static $many_many = array(
        'Categories'    => 'NewsCategory'
    );

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        if (!Config::inst()->get('NewsPost', 'pages_admin')) {
            $arrTypes = NewsPost::GetNewsTypes();
            if (count($arrTypes) > 1) {
                $arrDropDownSource = array();
                foreach ($arrTypes as $strType) {
                    $arrDropDownSource[$strType] = $strType;
                }
                $fields->addFieldToTab('Root.Main',
                    DropdownField::create('ClassName')->setSource($arrDropDownSource)
                        ->setTitle('Type'),
                    'Content');
            }
        }

        $fields->addFieldsToTab('Root.Main',
            array(
                DropdownField::create('ParentID')->setSource(NewsIndex::get()->map()->toArray())->setTitle('Parent Page'),
                DatetimeField::create('DateTime'),
                TextField::create('Tags'),
                TextField::create('Author'),
                HtmlEditorField::create('Summary')->setRows(5)
            ),
            'Content');


        if ($this->ID) {
            $fields->addFieldToTab('Root.Main',
                CheckboxSetField::create('Categories')->setSource(NewsCategory::get()->map('ID', 'Title')->toArray()),
            'Content');
        }

        $this->extend('updateNewsPostCMSFields', $fields);

        return $fields;
    }

    public static function GetNewsTypes()
    {
        return ClassInfo::subclassesFor('NewsPost');
    }


    /**
     * @return ArrayList
     * return a list of tags as a list of ArrayData's
     */
    public function TagList()
    {
        $list = new ArrayList();
        $newsIndex = $this->Parent();

        foreach (explode(',', $this->Tags) as $tag) {
            $tag = trim($tag);
            $list->push(new ArrayData(array(
                'Tag'        => $tag,
                'Link'        => $newsIndex->Link('tag/' . urlencode($tag))
            )));
        }

        return $list;
    }
}

class NewsPost_Controller extends Page_Controller
{
}
