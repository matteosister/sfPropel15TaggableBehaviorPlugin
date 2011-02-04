<?php
/* 
 *  Matteo Giachino <matteog@gmail.com>
 *  Just for fun...
 */

class sfValidatorTagsDelete extends sfValidatorBase {
    protected function configure($options = array(), $messages = array())
    {
        parent::configure($options, $messages);
    }

    protected function doClean($value)
    {
        $clean = (string) $value;

        $taggable = $this->getOption('taggable');
        $taggable->addTags($clean);

        return $clean;
    }
}
