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
        'tag_table_phpname' => 'Tag',
    );

    protected $taggingTable, $tagTable;


    public function modifyTable()
    {
        $this->createTagTable();
        $this->createTaggingTable();
    }

    public function objectMethods($builder)
    {
        $this->builder = $builder;

        $script = '';

        $this->addAddTagMethod($script);
        //$this->addGetTagsMethod($script);
        $this->addRemoveTagMethod($script);

        return $script;
    }

    protected  function createTagTable()
    {
        $table = $this->getTable();
		$database = $table->getDatabase();
		$tagTableName = $this->getTagTableName();
        $tagTablePhpName = $this->replaceTokens($this->parameters['tag_table_phpname']);

		if($database->hasTable($tagTableName)) {
			$this->tagTable = $database->getTable($tagTableName);
		} else {
			$this->tagTable = $database->addTable(array(
				'name'      => $tagTableName,
				'phpName'   => $tagTablePhpName,
				'package'   => $table->getPackage(),
				'schema'    => $table->getSchema(),
				'namespace' => $table->getNamespace(),
			));
		}

        if (!$this->tagTable->hasColumn('id')) {
            $this->tagTable->addColumn(array(
                'name'          => 'id',
                'type'          => PropelTypes::INTEGER,
                'primaryKey'    => 'true',
                'autoIncrement' => 'true',
            ));
        }
        
        if (!$this->tagTable->hasColumn('name')) {
            $this->tagTable->addColumn(array(
                'name'          => 'name',
                'type'          => PropelTypes::VARCHAR,
                'size'          => '60',
                'primaryString' => 'true'
            ));
        }
        
        $symfonyBehavior = new SfPropelBehaviorSymfony();
        $this->tagTable->addBehavior($symfonyBehavior);
    }

    protected function createTaggingTable()
    {
        $table = $this->getTable();
		$database = $table->getDatabase();
        $pks = $this->getTable()->getPrimaryKey();
        if (count($pks) > 1) {
			throw new EngineException('The Taggable behavior does not support tables with composite primary keys');
		}
        $taggingTableName = $this->getTaggingTableName();

        if($database->hasTable($taggingTableName)) {
			$this->taggingTable = $database->getTable($taggingTableName);
		} else {
			$this->taggingTable = $database->addTable(array(
				'name'      => $taggingTableName,
				'phpName'   => $this->replaceTokens($this->parameters['tagging_table_phpname']),
				'package'   => $table->getPackage(),
				'schema'    => $table->getSchema(),
				'namespace' => $table->getNamespace(),
			));
		}

        $symfonyBehavior = new SfPropelBehaviorSymfony();
        $this->taggingTable->addBehavior($symfonyBehavior);

        $objFkColumn;
        if ($this->taggingTable->hasColumn($table->getName().'_id')) {
            $objFkColumn = $this->taggingTable->getColumn($table->getName().'_id');
        } else {
            $objFkColumn = $this->taggingTable->addColumn(array(
                'name'          => $table->getName().'_id',
                'type'          => PropelTypes::INTEGER,
                'primaryKey'    => 'true'
            ));
        }

        $tagFkColumn;
        if ($this->taggingTable->hasColumn('tag_id')) {
            $tagFkColumn = $this->taggingTable->getColumn('tag_id');
        } else {
            $tagFkColumn = $this->taggingTable->addColumn(array(
                'name'          => 'tag_id',
                'type'          => PropelTypes::INTEGER,
                'primaryKey'    => 'true'
            ));
        }


        $this->taggingTable->setIsCrossRef(true);
        
        $fkTag = new ForeignKey();
        $fkTag->setForeignTableCommonName($this->tagTable->getName());
        $fkTag->setForeignSchemaName($this->tagTable->getSchema());
        $fkTag->setOnDelete(ForeignKey::CASCADE);
		$fkTag->setOnUpdate(ForeignKey::NONE);
        foreach ($pks as $column) {
			$fkTag->addReference($tagFkColumn, $column->getName());
		}
        $this->taggingTable->addForeignKey($fkTag);

        $fkObj = new ForeignKey();
        $fkObj->setForeignTableCommonName($this->getTable()->getName());
        $fkObj->setForeignSchemaName($this->getTable()->getSchema());
        $fkObj->setOnDelete(ForeignKey::CASCADE);
		$fkObj->setOnUpdate(ForeignKey::NONE);
        foreach ($pks as $column) {
			$fkObj->addReference($objFkColumn, $column->getName());
		}
        $this->taggingTable->addForeignKey($fkObj);
    }

    private function addAddTagMethod(&$script)
    {
        $table = $this->getTable();
        $script .= "
        
/**
 * Add tags
 * @param	array/string \$tags A string for a single tag or an array of strings for multiple tags
 */
public function addTags(\$tags) {
    \$arrTags = is_string(\$tags) ? explode(',', \$tags) : \$tags;

    foreach (\$arrTags as \$tag) {
        \$tag = trim(\$tag);
        if (\$tag == \"\") return;
        \$theTag = {$this->tagTable->getPhpName()}Query::create()->filterByName(\$tag)->findOne();

        // if the tag do not already exists
        if (null === \$theTag) {
            // create the tag
            \$theTag = new {$this->tagTable->getPhpName()}();
            \$theTag->setName(\$tag);
            \$theTag->save();
        }

        \$this->addTag(\$theTag);
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
public function removeTags(\$tags) {
    \$arrTags = is_string(\$tags) ? explode(',', \$tags) : \$tags;
    foreach (\$arrTags as \$tag) {
        \$tag = trim(\$tag);
        \$tagObj = {$this->tagTable->getPhpName()}Query::create()->findOneByName(\$tag);
        if (null === \$tagObj) {
            return;
        }
        \$taggings = \$this->get{$this->taggingTable->getPhpName()}();
        foreach (\$taggings as \$tagging) {
            if (\$tagging->get{$this->tagTable->getPhpName()}Id() == \$tagObj->getId()) {
                \$tagging->delete();
            }
        }
    }
}

";
    }

    protected function getTagTableName()
	{
		return $this->replaceTokens($this->getParameter('tag_table'));
	}

    protected function getTaggingTableName()
	{
		return $this->replaceTokens($this->getParameter('tagging_table'));
	}

    public function replaceTokens($string)
	{
		$table = $this->getTable();
		return strtr($string, array(
			'%TABLE%'   => $table->getName(),
			'%PHPNAME%' => $table->getPhpName(),
		));
	}
}

