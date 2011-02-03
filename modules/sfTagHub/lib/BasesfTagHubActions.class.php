<?php

/* 
 *  Matteo Giachino <matteog@gmail.com>
 *  Just for fun...
 */


class BasesfTagHubActions extends sfActions
{
    public function executeAutocompleterAjax(sfWebRequest $request)
    {
        $tb = new TaggableBehavior();
        $tb->getParameter('tag_table_phpname');

        $q = $request->getParameter('term');
        $limit = $request->getParameter('limit', 20);

        $tags = TaggableTagQuery::create()
            ->filterByName('%'.$q . '%', ModelCriteria::LIKE)
            ->limit($limit)
            ->find();

        $arrTags = array();
        foreach ($tags as $tag)
        {
            $thisTag['id'] = $tag->getId();
            $thisTag['label'] = $tag->getName();
            $thisTag['value'] = $tag->getName();
            $arrTags[] = $thisTag;
        }

        return $this->renderText(json_encode($arrTags));
    }
}