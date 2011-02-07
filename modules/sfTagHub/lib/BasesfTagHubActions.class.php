<?php

/*
 *  matteosister <matteog@gmail.com>
 *  Just for fun...
 */


class BasesfTagHubActions extends sfActions
{
    public function executeAutocompleterAjax(sfWebRequest $request)
    {
        $q = $request->getParameter('term');
        $limit = $request->getParameter('limit', 20);

        $tags = TagQuery::create()
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

    public function executeDeleteTag(sfWebRequest $request)
    {
        $this->forward404Unless($request->hasParameter('tag_id'));
        $taggableName = $request->getParameter('taggable_phpname');
        $taggableQueryName = $taggableName.'Query';
        $queryObj = $taggableQueryName::create();
        $obj = $queryObj->findOneById($request->getParameter('obj_id'));
        $tag = TagQuery::create()->findOneById($request->getParameter('tag_id'));
        $obj->removeTags($tag->getName());
        return sfView::NONE;
    }
}