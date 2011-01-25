<?php
/* 
 *  Matteo Giachino <matteog@gmail.com>
 *  Just for fun...
 */

class sfWidgetFormInputTags extends sfWidgetFormJQueryAutocompleter
{
    protected function configure($options = array(), $attributes = array())
    {
        parent::configure($options, $attributes);
        
        $this->addRequiredOption('taggable');

        $this->addOption('type', 'text');

        $this->setOption('is_hidden', false);
    }

    public function render($name, $value = null, $attributes = array(), $errors = array())
    {
        $taggable = $this->getOption('taggable');
        $tags = $taggable->getTags();
        $inputId = $this->generateId($name);

        $html = '<ul class="checkbox_list" id="sfPropel15TaggablePlugin">';
        foreach ($tags as $tag) {
            $tag = trim($tag);
            $html .= '<li>';
            $html .= '<input type="checkbox" id="'.$inputId.'_delete" name="'.str_replace("]", "_delete][]",$name).'" value="'.$tag.'" /> <label for="'.$inputId.'_delete">'.$tag.'</label>';
            $html .= '</li>';
        }
        $html .= "</ul>";
        //return $html . $this->renderTag('input', array_merge(array('type' => $this->getOption('type'), 'name' => $name, 'value' => $value), $attributes));
        $autocompleterInput = parent::render($name, $value, $attributes, $errors);
        return $html.$autocompleterInput;
    }

    public function getJavaScripts()
    {
        return array_merge(parent::getJavascripts(), array(
            '/sfPropel15TaggableBehaviorPlugin/js/sfPropel15TaggableBehaviorPlugin'
        ));
    }
}

