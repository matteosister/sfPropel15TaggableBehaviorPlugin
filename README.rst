--------------------------------
sfPropel15TaggableBehaviorPlugin
--------------------------------

A behavior and a widget(work in progress) for symfony 1.x and propel 1.5/1.6



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

- publish assets

::

    php symfony plugin:publish-assets


Classes And Tables generated
----------------------------

The behavior creates a **taggable_tag** table that is populated with tags
Then it creates a **%table%_tagging table** for every object in your model with the taggable behavior.
This middle table is marked as **isCrossRef**, with two foreign keys, one on the object and one on the tag table.
This integrates the tagging mechanism completely inside propel

How to use
----------

Some examples:

::

    <?php
    $article = new Article();

    // there are two ways to add tags. The easy way (addTags method):
    $article->addTags('symfony'); // a string with no comma is a single tag
    $article->addTags('linux, ubuntu'); // a string with comma is multiple tag
    $article->addTags('symfony'); // if the object is already tagged nothing happens
    $article->addTags(array('linus', 'torvalds')); // list of tags as an array

    // or the more elegant propel way (propel addTag method)
    $tag = new Tag('symfony');
    $article->addTag($tag);

    // remove tags
    $article->removeTags('symfony');
    $article->removeTags('linux, ubuntu');
    $article->removeTags(array('linus', 'torvalds'));

    // retrieve tags
    $article->getTags() // PropelCollection of Tag object


Tag widget!
-----------

This plugin has also a nice jquery 1.5 powered widget for your forms

- Enable the sfTagHub module in your settings.yml file:

*app/backend/config/settings.yml*

::

    all:
      .settings:
      # ...
      enabled_modules:        [..., sfTagHub]



- Create a tag field with **sfWidgetFormInputTags** widget in your form class, and don't forget the validator
both of them accept the taggable object as a parameter

::

    class ArticleForm extends BaseArticleForm
    {
      public function configure()
      {
          // ....
          $this->setWidget('tags', new sfWidgetFormInputTags(array('taggable' => $this->getObject())));
          $this->setValidator('tags', new sfValidatorTags(array('taggable' => $this->getObject())));
      }
    }

- clear your cache

::

    php symfony cc

Now your form has a widget with jquery autocomplete that read from the tag table. And a list of tags associated with a delete button and a nice fadeout effect.
The tags are saved server side (when you hit "save" on your form), your comma separated string is parsed,

