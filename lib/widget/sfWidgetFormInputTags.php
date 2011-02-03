<?php
/* 
 *  Matteo Giachino <matteog@gmail.com>
 *  Just for fun...
 */

class sfWidgetFormInputTags extends sfWidgetFormInput
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
        $inputId = $this->generateId($name);
        $html = '';

        if (!$taggable->isNew()) {
            $tags = $taggable->getTags();
            $html .= '<ul class="checkbox_list" id="sfPropel15TaggablePlugin">';
            foreach ($tags as $i => $tag) {
                $tag = trim($tag);
                $html .= '<li>';
                $html .= '<input type="checkbox" id="'.$inputId.'_'.$i.'_delete" name="'.str_replace("]", "_delete][]",$name).'" value="'.$tag.'" /> <label for="'.$inputId.'_'.$i.'_delete">'.$tag.'</label>';
                $html .= '</li>';
            }
            $html .= "</ul>";
        }
        

        //return $html . $this->renderTag('input', array_merge(array('type' => $this->getOption('type'), 'name' => $name, 'value' => $value), $attributes));
        $inputField = parent::render($name, $value, $attributes, $errors);

        $routing = sfContext::getInstance()->getRouting();

        $js = sprintf("
        <script type=\"text/javascript\">
        function split( val ) {
			return val.split( /,\s*/ );
		}
		function extractLast( term ) {
			return split( term ).pop();
		}
        jQuery('#%s').autocomplete({
            source: function( request, response ) {
                $.getJSON( \"%s\", {
                    term: extractLast( request.term )
                }, response );
            },
            search: function() {
                // custom minLength
                var term = extractLast( this.value );
                if ( term.length < 2 ) {
                    return false;
                }
            },
            focus: function() {
                // prevent value inserted on focus
                return false;
            },
            select: function( event, ui ) {
                var terms = split( this.value );
                // remove the current input
                terms.pop();
                // add the selected item
                terms.push( ui.item.value );
                // add placeholder to get the comma-and-space at the end
                terms.push( \"\" );
                this.value = terms.join( \", \" );
                return false;
            }
        });
        </script>
        ",
            $this->generateId($name),
            $routing->generate('tag_hub_autocompleter_ajax')
        );

        return $html.$inputField.$js;
    }

    public function getJavaScripts()
    {
        return array(
            '/sfPropel15TaggableBehaviorPlugin/js/jquery-1.4.4.min.js',
            '/sfPropel15TaggableBehaviorPlugin/js/jquery-ui-1.8.9.custom.min.js',
            '/sfPropel15TaggableBehaviorPlugin/js/sfPropel15TaggableBehaviorPlugin'
        );
    }

    public function  getStylesheets()
    {
        return array(
            '/sfPropel15TaggableBehaviorPlugin/css/smoothness/jquery-ui-1.8.9.custom.css' => 'screen',
            '/sfPropel15TaggableBehaviorPlugin/css/sfPropel15TaggableBehaviorPlugin.css' => 'screen'
        );
    }
}

