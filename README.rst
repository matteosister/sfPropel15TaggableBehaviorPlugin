--------------------------------
sfPropel15TaggableBehaviorPlugin
--------------------------------

A plugin for symfony 1.x with a propel 1.5.x behavior and some other stuff...


How to install
--------------

- add this plugin as a git submodule in your project. From your project root:

    git submodule add git://github.com/matteosister/sfPropel15TaggableBehaviorPlugin.git plugins/sfPropel15TaggableBehaviorPlugin

- enable the plugin in your **ProjectConfiguration** class (config/ProjectConfiguration.class.php)

::

    <?php

    require_once dirname(__FILE__) . '/../lib/vendor/symfony/autoload/sfCoreAutoload.class.php';
    sfCoreAutoload::register();

    class ProjectConfiguration extends sfProjectConfiguration
    {
      public function setup()
      {
        // ...
        $this->enablePlugins('sfPropel15TaggableBehaviorPlugin');
        // ...
      }
    }

- add the **taggable** behavior to a class in your schema file

::

    <table name="article">
        <behavior name="taggable" />
        <column name="id" type="integer" primaryKey="true" autoIncrement="true"/>
        <column name="title" type="varchar" size="255" />
        <!-- ... -->
    </table>

- rebuild your model

::

    php symfony propel:build-all


How to use
----------

This plugin is really easy to use.
Some examples:

::

    <?php
    $article = new Article();

    // add tags
    $article->addTags('symfony'); // a string with no comma is a single tag
    $article->addTags('linux, ubuntu'); // a string with comma is multiple tag
    $article->addTags('symfony'); // if the object is already tagged nothing happens
    $article->addTags(array('linus', 'torvalds')); // list of tags as an array

    // remove tags
    $article->removeTag('symfony');
    $article->removeTag('linux, ubuntu');
    $article->removeTag(array('linus', 'torvalds'));

    // retrieve tags
    $article->getTags() // array of tags


Credits to Xavier Lacot
-----------------------

This plugin is heavily inspired to sfPropelActAsTaggablePlugin that hasn't been updated for
symfony 1.3/1.4. So this is just a new version of that plugin that also support the new
propel behavior system.

