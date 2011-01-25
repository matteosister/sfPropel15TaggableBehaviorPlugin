<?php

/**
 * SfTagging filter form base class.
 *
 * @package    ##PROJECT_NAME##
 * @subpackage filter
 * @author     ##AUTHOR_NAME##
 */
abstract class BaseSfTaggingFormFilter extends BaseFormFilterPropel
{
  public function setup()
  {
    $this->setWidgets(array(
      'tag_id'         => new sfWidgetFormPropelChoice(array('model' => 'SfTag', 'add_empty' => true)),
      'taggable_model' => new sfWidgetFormFilterInput(),
      'taggable_id'    => new sfWidgetFormFilterInput(),
      'created_at'     => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate())),
      'updated_at'     => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate())),
    ));

    $this->setValidators(array(
      'tag_id'         => new sfValidatorPropelChoice(array('required' => false, 'model' => 'SfTag', 'column' => 'id')),
      'taggable_model' => new sfValidatorPass(array('required' => false)),
      'taggable_id'    => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'created_at'     => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDate(array('required' => false)), 'to_date' => new sfValidatorDate(array('required' => false)))),
      'updated_at'     => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDate(array('required' => false)), 'to_date' => new sfValidatorDate(array('required' => false)))),
    ));

    $this->widgetSchema->setNameFormat('sf_tagging_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'SfTagging';
  }

  public function getFields()
  {
    return array(
      'id'             => 'Number',
      'tag_id'         => 'ForeignKey',
      'taggable_model' => 'Text',
      'taggable_id'    => 'Number',
      'created_at'     => 'Date',
      'updated_at'     => 'Date',
    );
  }
}
