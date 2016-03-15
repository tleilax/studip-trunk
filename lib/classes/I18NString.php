<?php

/**
 * I18NString class
 */
class I18NString
{
    // Fields we will need
    private $base, $lang;

    /**
     * Initialize a new I18NString instance.
     *
     * @param string    $base   text in default content language
     * @param array     $lang   text in additional languages
     */
    public function __construct($base, $lang = array())
    {
        $this->base = $base;
        $this->lang = $lang;
    }

    /**
     * Return the text representation of this object.
     */
    public function __toString()
    {
        if (isset($_SESSION['_language']) && isset($this->lang[$_SESSION['_language']])) {
            return $this->lang[$_SESSION['_language']];
        }

        return (string) $this->base;
    }

    /**
     * Return the string in the default content language.
     */
    public function original()
    {
        return $this->base;
    }

    /**
     * Return the string in the specified additional language.
     */
    public function translation($lang)
    {
        return $this->lang[$lang];
    }

    /**
     * Return an array containg the text in all additional languages.
     */
    public function toArray()
    {
        return $this->lang;
    }
}
