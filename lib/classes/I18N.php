<?php

/**
 * Translation class
 */
class I18N
{
    /**
     * Create a set of HTML input elements for this form element in text form.
     * One element will be generated for each configured content language.
     */
    public static function input($name, $value, $attributes = array())
    {
        $languages = $GLOBALS['CONTENT_LANGUAGES'];
        $base_lang = key($languages);

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
                                   Assets::image_path('languages/' . $lang['picture']))
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

        return $result;
    }
}
