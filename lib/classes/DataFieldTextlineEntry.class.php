<?php
# Lifter002: DONE
# Lifter007: TEST

/**
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @author  Marcus Lunzenauer <mlunzena@uos.de>
 * @author  Martin Gieseking  <mgieseki@uos.de>
 * @license GPL2 or any later version
 */
class DataFieldTextlineEntry extends DataFieldEntry
{
    protected $template = 'textline.php';
    
    public function getHTML($name = '', $variables = array())
    {
        if ($this->isI18n() && is_null($this->language)) {
            $attributes['input_attributes']['id'] = $name . '_' . $this->model->id;
            if ($this->isRequired()) {
                $attributes['input_attributes']['required'] = '';
            }
            $attributes['datafield_id'] = $this->model->id;
            return I18N::inputTmpl('datafields/textline_i18n.php', $name,
                    $this->getValue(), $attributes);
        }
        
        return parent::getHTML($name, $variables);
    }
    
}
