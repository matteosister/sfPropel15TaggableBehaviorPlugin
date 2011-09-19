<?php

/* 
 *  matteosister <matteog@gmail.com>
 *  Just for fun...
 */

class TaggableBehavior extends Behavior {
    
    protected $parameters = array(
        'tagging_table' => '%TABLE%_tagging',
        'tagging_table_phpname' => '%PHPNAME%Tagging',
        'tag_table' => 'taggable_tag',
        'tag_table_phpname' => 'Tag',
    );

    protected $taggingTable, 
        $tagTable,
        $objectBuilderModifier,
        $queryBuilderModifier,
        $peerBuilderModifier;


    public function modifyDatabase()
    {
        die('mod db');
    }

    public function modifyTable()
    {
        $this->createTagTable();
        $this->createTaggingTable();
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
            // every behavior adding a table should re-execute database behaviors
            // see bug 2188 http://www.propelorm.org/changeset/2188
            foreach ($database->getBehaviors() as $behavior) {
                $behavior->modifyDatabase();
            }
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
            // every behavior adding a table should re-execute database behaviors
            // see bug 2188 http://www.propelorm.org/changeset/2188
            foreach ($database->getBehaviors() as $behavior) {
                $behavior->modifyDatabase();
            }
        }

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

    /**
     * Adds methods to the object
     */
    public function objectMethods($builder)
    {
        $this->builder = $builder;

        $script = '';

        $this->addAddTagsMethod($script);
        $this->addRemoveTagMethod($script);

        return $script;
    }

    private function addAddTagsMethod(&$script)
    {
        $table = $this->getTable();
        $script .= "
        
/**
 * Add tags
 * @param   array|string    \$tags A string for a single tag or an array of strings for multiple tags
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

        if (!\$this->getTags()->contains(\$theTag))
            \$this->addTag(\$theTag);
    }
}      

/**
 * Remove all tags
 * @param      PropelPDO \$con optional connection object
 */
public function removeAllTags(PropelPDO \$con = null) {
      // Get all tags for this object
    \$taggings = \$this->get{$this->taggingTable->getPhpName()}s(\$con);
    foreach (\$taggings as \$tag) {
      \$tag->delete(\$con);
    }
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
 * @param   array|string    \$tags A string for a single tag or an array of strings for multiple tags
 */
public function removeTags(\$tags) {
    \$arrTags = is_string(\$tags) ? explode(',', \$tags) : \$tags;
    foreach (\$arrTags as \$tag) {
        \$tag = trim(\$tag);
        \$tagObj = {$this->tagTable->getPhpName()}Query::create()->findOneByName(\$tag);
        if (null === \$tagObj) {
            return;
        }
        \$taggings = \$this->get{$this->taggingTable->getPhpName()}s();
        foreach (\$taggings as \$tagging) {
            if (\$tagging->get{$this->tagTable->getPhpName()}Id() == \$tagObj->getId()) {
                \$tagging->delete();
            }
        }
    }
}

";
    }

    /**
     * Adds method to the query object
     */
    public function queryMethods($builder)
    {
        $this->builder = $builder;
        $script = '';
        
        $this->addFilterByTagName($script);

        return $script;
    }

    protected function addFilterByTagName(&$script)
    {
        $script .= "
/**
 * Filter the query on the tag name
 *
 * @param     string \$tagName A single tag name
 *
 * @return    " . $this->builder->getStubQueryBuilder()->getClassname() . " The current query, for fluid interface
 */
public function filterByTagName(\$tagName)
{
    return \$this->use".$this->taggingTable->getPhpName()."Query()->useTagQuery()->filterByName(\$tagName)->endUse()->endUse();
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

