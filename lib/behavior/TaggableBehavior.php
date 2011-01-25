<?php

/* 
 *  Matteo Giachino <matteog@gmail.com>
 *  Just for fun...
 */

class TaggableBehavior extends Behavior {
    protected $parameters = array();

    public function objectMethods()
    {
        $script = '';
        $script .= $this->generateAddTagMethod();
        $script .= $this->generateGetTagsMethod();
        $script .= $this->generateRemoveTagMethod();
        return $script;
    }

    private function generateAddTagMethod()
    {
        $table = new Table();
        $table = $this->getTable();

        return "
        
/**
 * Tag a propel object with taggable behavior
 * @tag array or string
 */
public function addTags(\$tags) {
    \$arrTags = is_string(\$tags) ? explode(',', \$tags) : \$tags;

    foreach (\$arrTags as \$tag) {
        \$tag = trim(\$tag);
        \$theTag = SfTagQuery::create()->filterByName(\$tag)->findOne();

        // if the tag do not already exists
        if (null === \$theTag) {
            // create the tag
            \$theTag = new SfTag();
            \$theTag->setName(\$tag);
            \$theTag->save();
        }

        // if the object is not already tagged
        if (!in_array(\$tag, \$this->getTags())) {
            // apply the tag
            \$tagging = new SfTagging();
            \$tagging->setTagId(\$theTag->getId());
            \$tagging->setTaggableModel('{$table->getPhpName()}');
            \$tagging->setTaggableId(\$this->getId());
            \$tagging->save();
        }
    }
}
        
";
    }

    private function generateGetTagsMethod()
    {
        $table = new Table();
        $table = $this->getTable();

        return "

/**
 * Retrieve Tags for Object
 */
public function getTags() {
    \$taggings = SfTaggingQuery::create()
        ->filterByTaggableModel('{$table->getPhpName()}')
        ->filterByTaggableId(\$this->getId())
        ->joinWith('SfTagging.SfTag')
        ->find();

    \$arrTags = array();
    foreach (\$taggings as \$tagging) {
        \$arrTags[]  = \$tagging->getSfTag()->getName();
    }
    return \$arrTags;
}

";
    }

    private function generateRemoveTagMethod()
    {
        $table = new Table();
        $table = $this->getTable();

        return "
/**
 * Remove a tag
 */
public function removeTag(\$tag) {
    \$tag = trim(\$tag);
    \$taggings = SfTaggingQuery::create()
        ->filterByTaggableModel('{$table->getPhpName()}')
        ->filterByTaggableId(\$this->getId())
        ->useSfTagQuery()
            ->filterByName(\$tag)
        ->endUse()
        ->find();
    foreach (\$taggings as \$tagging) {
        \$tagging->delete();
    }
}

";
    }
}

