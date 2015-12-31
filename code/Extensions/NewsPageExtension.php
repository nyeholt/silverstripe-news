<?php
/**
 * Created by Nivanka Fonseka (nivanka@silverstripers.com).
 * User: nivankafonseka
 * Date: 4/6/15
 * Time: 3:26 PM
 * To change this template use File | Settings | File Templates.
 */

class NewsPageExtension extends DataExtension
{

    public function NewsIndex($strClass ='')
    {
        if ($strClass) {
            return NewsIndex::get()->filter('ClassName', $strClass)->first();
        }
        
        return NewsIndex::get()->first();
    }

    public function LatestNewsPosts($iCount = 5, $strClass = "")
    {
        $list = NewsPost::get()->limit($iCount)->sort('DateTime DESC');
        if ($strClass) {
            $list = $list->filter('ClassName', $strClass);
        }
        return $list;
    }

    public function BlogArchives()
    {
        if ($newsIndex = $this->NewsIndex()) {
            $alRet = new ArrayList();

            $strPattern = SiteConfig::current_site_config()->ArchivePattern ? : '%Y, %M';
            $iLimit = SiteConfig::current_site_config()->NumberOfArchives ? : PHP_INT_MAX;

            $strTable = Versioned::current_stage() == 'Stage' ? 'NewsPost' : 'NewsPost_Live';

            $results = DB::query('SELECT DATE_FORMAT(`DateTime`, \'' . $strPattern . '\') AS Date
				FROM ' . $strTable .  '
				WHERE `DateTime` IS NOT NULL
				GROUP BY Date
				LIMIT ' . $iLimit);

            while ($row = $results->nextRecord()) {
                $alRet->push(new ArrayData(array(
                    'Link'        => $newsIndex->Link('archive/' . urlencode($row['Date'])),
                    'Archive'    => $row['Date'],
                    'List'        => NewsPost::get()->where('DATE_FORMAT(`DateTime`, \'' . $strPattern . '\') = \'' . $row['Date'] . '\'')
                )));
            }

            return $alRet;
        }
    }

    public function BlogTags()
    {
        if ($newsIndex = $this->NewsIndex()) {
            $alRet = new ArrayList();
            $arrTags = array();

            $strTable = Versioned::current_stage() == 'Stage' ? 'NewsPost' : 'NewsPost_Live';
            $results = DB::query('SELECT `Tags` AS Tags, COUNT(1) AS Items
				FROM ' . $strTable .  '
				WHERE `Tags` IS NOT NULL
				GROUP BY Tags');

            while ($row = $results->nextRecord()) {
                $arrCurrentItems = explode(',', $row['Tags']);
                foreach ($arrCurrentItems as $strItem) {
                    $strItem = trim($strItem);
                    $strLower = strtolower($strItem);
                    if (!array_key_exists($strLower, $arrTags)) {
                        $arrTags[$strLower] = new ArrayData(array(
                            'Tag'        => $strItem,
                            'Count'        => $row['Items'],
                            'Link'        => $newsIndex->Link('tag/' . urlencode($strItem))
                        ));
                    } else {
                        $arrayData = $arrTags[$strLower];
                        $arrayData->Count += $row['Items'];
                    }
                }
            }

            foreach ($arrTags as $arrTag) {
                $alRet->push($arrTag);
            }


            return $alRet->sort('Count')->limit(SiteConfig::current_site_config()->NumberOfTags ? : PHP_INT_MAX);
        }
    }
}
