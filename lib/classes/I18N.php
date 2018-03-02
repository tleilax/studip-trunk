<?php

/**
 * Translation class
 *
 * Automatic generation of inputs and textareas for i18n
 */
class I18N
{

    /**
     * Creates a set of HTML input elements for this form element in text form.
     * One element will be generated for each configured content language.
     *
     * @param string $name HTML name of the Inputfild
     * @param I18NString $value (Needs to be an i18n input string)
     * @param array $attributes Additional attributes of the input
     * @return string Crafted input
     */
    public static function input($name, $value, $attributes = [])
    {
        return self::inputTmpl('i18n/input.php', $name, $value,
                ['input_attributes' => $attributes]);
    }
    
    /**
     * Returns a widget used in HTML forms to handle translated values in a
     * textline (normally a input form element).
     * The design of this widget has to be defined by the given template.
     *
     * @param Flexi_Template|string An template or a path to the template file.
     * @param string $name HTML name of the Inputfild
     * @param I18NString $value (Needs to be an i18n input string)
     * @param array $attributes Additional attributes of the input
     * @return string Crafted input
     */
    public static function inputTmpl($tmpl, $name, $value, $tmpl_attributes = [])
    {
        if (!($value instanceof I18NString)) {
            $value = new I18NString($value);
        }
        
        if (!$tmpl instanceof Flexi_Template) {
            $tmpl = $GLOBALS['template_factory']->open($tmpl);
        }
        
        $tmpl->set_attributes([
            'languages'  => $GLOBALS['CONTENT_LANGUAGES'],
            'base_lang'  => key($GLOBALS['CONTENT_LANGUAGES']),
            'enabled'    => self::isEnabled(),
            'name'       => $name,
            'value'      => $value,
            'attributes' => (array) $tmpl_attributes
        ]);
        
        return $tmpl->render();
    }
    
    /**
     * Create a set of HTML textarea elements for this form element in text form.
     * One element will be generated for each configured content language.
     *
     * @param string $name HTML name of the Textarea
     * @param I18NString $value (Needs to be an i18n input string)
     * @param array $attributes Additional attributes of the input
     * @return string Crafted textarea
     */
    public static function textarea($name, $value, $attributes = [])
    {
        
        return self::inputTmpl('i18n/textarea.php', $name, $value,
                ['input_attributes' => $attributes,
                 'wysiwig' => in_array('wysiwyg', words($attributes['class']))]);
    }

    /**
     * Returns a widget used in HTML forms to handle translated values in a
     * multiline textarea (normally a textarea form element).
     * The design of this widget has to be defined by the given template.
     *
     * @param Flexi_Template|string $tmpl An template or a path to the template file.
     * @param string $name HTML name of the Textarea
     * @param I18NString $value (Needs to be an i18n input string)
     * @param array $attributes Additional attributes of the input
     * @return string Crafted textarea
     */
    public static function textareaTmpl($tmpl, $name, $value, $tmpl_attributes = [])
    {
        if (!($value instanceof I18NString)) {
            $value = new I18NString($value);
        }
        
        if (!$tmpl instanceof Flexi_Template) {
            $tmpl = $GLOBALS['template_factory']->open($tmpl);
        }
        
        $tmpl->set_attributes([
            'languages'  => $GLOBALS['CONTENT_LANGUAGES'],
            'base_lang'  => key($GLOBALS['CONTENT_LANGUAGES']),
            'wysiwyg'    => in_array('wysiwyg', words($attributes['class'])),
            'enabled'    => self::isEnabled(),
            'name'       => $name,
            'value'      => $value,
            'attributes' => (array) $tmpl_attributes
        ]);
        
        return $tmpl->render();
    }
    
    /**
     * Returns whether the i18n functionality is enabeld i.e. more than the
     * default language is configured in config_defaults.inc.php.
     *
     * @return bool True if i18n is enabled.
     */
    public static function isEnabled()
    {
        return count($GLOBALS['CONTENT_LANGUAGES']) > 1;
    }
}
