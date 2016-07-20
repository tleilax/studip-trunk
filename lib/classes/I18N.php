<?php

/**
 * Translation class
 *
 * Automatic generation of inputs and textareas for i18n
 */
class I18N
{

    /**
     * Create a set of HTML input elements for this form element in text form.
     * One element will be generated for each configured content language.
     *
     * @param $name HTML name of the Inputfild
     * @param $value Value (Needs to be an i18n input string)
     * @param array $attributes Additional attributes of the input
     * @return string Crafted input
     * @throws i18nException If given value was not an i18n Object
     */
    public static function input($name, $value, $attributes = array())
    {
        if (!($value instanceof I18NString)) {
            throw new i18nException("Given input seems to be no i18n String. Check declaration in SimpleORMap Subclass!");
        }

        $languages = $GLOBALS['CONTENT_LANGUAGES'];
        $base_lang = key($languages);

        $result .= "<div class='i18n_group ".(count($GLOBALS['CONTENT_LANGUAGES']) <= 1 ? 'single_lang' : '')."'>";
        foreach ($languages as $locale => $lang) {
            if ($locale === $base_lang) {
                $attr = array(
                    'name' => $name,
                    'value' => $value->original(),
                    'id' => $attributes['id']
                );
            } else {
                $attr = array(
                    'name' => $name . '_i18n[' . $locale . ']',
                    'value' => $value->translation($locale),
                    'id' => NULL
                );
            }
            $attr += array(
                'class' => $attributes['class'] . ' i18n',
                'style' => sprintf('%s background-image: url(%s);', $attributes['style'],
                                   Assets::image_path('languages/' . $lang['picture'])),
                'data-lang_desc' => $lang['name']
            );

            $result .= '<input type="text"';
            foreach ($attr + $attributes as $key => $val) {
                if ($val === true) {
                    $result .= sprintf(' %s', $key);
                } else if (isset($val)) {
                    $result .= sprintf(' %s="%s"', $key, htmlReady($val));
                }
            }
            $result .= ">\n";
        }
        $result .= "</div>";

        return $result;
    }

    /**
     * Create a set of HTML textarea elements for this form element in text form.
     * One element will be generated for each configured content language.
     *
     * @param $name HTML name of the Textarea
     * @param $value Value (Needs to be an i18n input string)
     * @param array $attributes Additional attributes of the input
     * @return string Crafted textarea
     * @throws i18nException If given value was not an i18n Object
     */
    public static function textarea($name, $value, $attributes = array())
    {
        $languages = $GLOBALS['CONTENT_LANGUAGES'];
        $base_lang = key($languages);

        $result .= "<div class='i18n_group ".(count($GLOBALS['CONTENT_LANGUAGES']) <= 1 ? 'single_lang' : '')."'>";
        foreach ($languages as $locale => $lang) {
            if ($locale === $base_lang) {
                $attr = array(
                    'name' => $name,
                    'id' => $attributes['id']
                );
                $text = $value->original();
            } else {
                $attr = array(
                    'name' => $name . '_i18n[' . $locale . ']',
                    'id' => NULL
                );
                $text = $value->translation($locale);
            }
            $attr += array(
                'class' => $attributes['class'] . ' i18n',
                'style' => sprintf('%s background-image: url(%s);', $attributes['style'],
                    Assets::image_path('languages/' . $lang['picture'])),
                'data-lang_desc' => $lang['name']
            );

            $result .= '<textarea';
            foreach ($attr + $attributes as $key => $val) {
                if ($val === true) {
                    $result .= sprintf(' %s', $key);
                } else if (isset($val)) {
                    $result .= sprintf(' %s="%s"', $key, htmlReady($val));
                }
            }
            $result .= ">$text</textarea>\n";
        }
        $result .= "</div>";
        return $result;
    }
}
