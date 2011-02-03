<?php

/* 
 *  Matteo Giachino <matteog@gmail.com>
 *  Just for fun...
 */

class TaggableBehavior extends Behavior {
    
    protected $parameters = array(
        'tagging_table' => '%TABLE%_tagging',
        'tagging_table_phpname' => '%PHPNAME%Tagging',
        'tag_table' => 'taggable_tag',
        'tag_table_phpname' => 'TaggableTag',
    );

    protected $taggingTable;


    public function modifyTable()
    {
        $this->createTagTable();
    }

    protected  function createTagTable()
    {
        $table = $this->getTable();
		$database = $table->getDatabase();

		$taggingTableName = $this->parameters['tag_table'];
		if($database->hasTable($taggingTableName)) {
			$this->taggingTable = $database->getTable($taggingTableName);
		} else {
			$this->taggingTable = $database->addTable(array(
				'name'      => $taggingTableName,
				'phpName'   => $this->parameters['tag_table_phpname'],
				'package'   => $table->getPackage(),
				//'schema'    => $table->getSchema(),
				'namespace' => $table->getNamespace(),
			));
		}

        if (!$this->taggingTable->hasColumn('id')) {
            $this->taggingTable->addColumn(array(
                'name'          => 'id',
                'type'          => PropelTypes::INTEGER,
                'primaryKey'    => 'true',
                'autoIncrement' => 'true',
            ));
        }
        
        if (!$this->taggingTable->hasColumn('name')) {
            $this->taggingTable->addColumn(array(
                'name'          => 'name',
                'type'          => PropelTypes::VARCHAR,
                'size'          => '60'
            ));
        }

        $this->taggingTable->addBehavior('symfony');
    }

    public function objectMethods($builder)
    {
        $this->builder = $builder;
        
        $script = '';

        $this->addAddTagMethod($script);
        $this->addGetTagsMethod($script);
        $this->addRemoveTagMethod($script);

        //return $script;
    }

    private function addAddTagMethod(&$script)
    {
        $table = $this->getTable();
        $script .= "
        
/**
 * Add tags
 * @param	array/string \$tags A string for a single tag or an array of strings for multiple tags
 */
public function addTag(\$tags) {
    \$arrTags = is_string(\$tags) ? explode(',', \$tags) : \$tags;

    foreach (\$arrTags as \$tag) {
        \$tag = trim(\$tag);
        if (\$tag == \"\") return;
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

    private function addGetTagsMethod(&$script)
    {
        $table = new Table();
        $table = $this->getTable();

        $script .= "

/**
 * Retrieve Tags
 * @return array An array of tags
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

    private function addRemoveTagMethod(&$script)
    {
        $table = new Table();
        $table = $this->getTable();

        $script .= "
/**
 * Remove a tag
 * @param	array/string \$tags A string for a single tag or an array of strings for multiple tags
 */
public function removeTag(\$tags) {
    \$arrTags = is_string(\$tags) ? explode(',', \$tags) : \$tags;
    foreach (\$arrTags as \$tag) {
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
}

";
    }
}

