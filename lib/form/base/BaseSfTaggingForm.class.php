<?php

/**
 * SfTagging form base class.
 *
 * @method SfTagging getObject() Returns the current form's model object
 *
 * @package    ##PROJECT_NAME##
 * @subpackage form
 * @author     ##AUTHOR_NAME##
 */
abstract class BaseSfTaggingForm extends BaseFormPropel
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'             => new sfWidgetFormInputHidden(),
      'tag_id'         => new sfWidgetFormPropelChoice(array('model' => 'SfTag', 'add_empty' => true)),
      'taggable_model' => new sfWidgetFormInputText(),
      'taggable_id'    => new sfWidgetFormInputText(),
      'created_at'     => new sfWidgetFormDateTime(),
      'updated_at'     => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'             => new sfValidatorPropelChoice(array('model' => 'SfTagging', 'column' => 'id', 'required' => false)),
      'tag_id'         => new sfValidatorPropelChoice(array('model' => 'SfTag', 'column' => 'id', 'required' => false)),
      'taggable_model' => new sfValidatorString(array('max_length' => 30, 'required' => false)),
      'taggable_id'    => new sfValidatorInteger(array('min' => -2147483648, 'max' => 2147483647, 'required' => false)),
      'created_at'     => new sfValidatorDateTime(array('required' => false)),
      'updated_at'     => new sfValidatorDateTime(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('sf_tagging[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'SfTagging';
  }


}
