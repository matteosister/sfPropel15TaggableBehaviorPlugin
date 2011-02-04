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
        $taggableName = $taggable->getPeer()->getTableMap()->getClassname();
        $inputId = $this->generateId($name);
        $html = '';

        if (!$taggable->isNew()) {
            $tags = $taggable->getTags();
            $html .= '<ul class="checkbox_list" id="sfPropel15TaggablePlugin">';
            foreach ($tags as $i => $tag) {
                $html .= '<li>';
                $html .= '<a href="javascript:void(0)" class="tag-delete" id="'.$tag->getId().'">'.$tag.'</a>';
                $html .= '</li>';
            }
            $html .= "</ul>";
        }
        

        //return $html . $this->renderTag('input', array_merge(array('type' => $this->getOption('type'), 'name' => $name, 'value' => $value), $attributes));
        $inputField = parent::render($name, $value, $attributes, $errors);

        $routing = sfContext::getInstance()->getRouting();
        //$routing->generate('tag_hub_delete_tag', array('id' => $taggable->getId(), 'tag_id' => ))

        $js = sprintf("
        <script type=\"text/javascript\">

        jQuery('ul.checkbox_list li a.tag-delete').each( function() {
            jQuery(this).click( function() {
                $.ajax({
                  url: '".$routing->generate('tag_hub_delete_tag', array('obj_id' => $taggable->getId(), 'taggable_phpname' => $taggableName))."?tag_id=' + jQuery(this).attr('id'),
                  context: jQuery(this),
                  success: function(){
                    jQuery(this).fadeOut('fast');
                  }
                });
            });
        });
        
        // autocompleter stuff
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

        // split a csv string
        function split( val ) {
			return val.split( /,\s*/ );
		}

        // last term after comma
		function extractLast( term ) {
			return split( term ).pop();
		}
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
            '/sfPropel15TaggableBehaviorPlugin/js/jquery-1.5.min.js',
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

