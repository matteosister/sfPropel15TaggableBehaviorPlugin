<?php
/* 
 *  Matteo Giachino <matteog@gmail.com>
 *  Just for fun...
 */

class sfValidatorTags extends sfValidatorBase
{
    protected function configure($options = array(), $messages = array())
    {
        $this->addRequiredOption('taggable');
        $this->addOption('required', false);
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
