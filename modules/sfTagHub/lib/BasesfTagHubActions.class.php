<?php

/* 
 *  Matteo Giachino <matteog@gmail.com>
 *  Just for fun...
 */


class BasesfTagHubActions extends sfActions
{
    public function executeAutocompleterAjax(sfWebRequest $request)
    {
        $q = $request->getParameter('q');
        $limit = $request->getParameter('limit');

        $tags = SfTagQuery::create()
            ->filterByName('%'.$q . '%', ModelCriteria::LIKE)
            ->limit($limit)
            ->find();

        $arrTags = array();
        foreach ($tags as $tag)
        {
            $arrTags[$tag->getId()] = (string) $tag->getName();
        }

        return $this->renderText(json_encode($arrTags));
    }
}