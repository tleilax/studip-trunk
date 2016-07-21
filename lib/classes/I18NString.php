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

    /**
     * Trim all language strings
     *
     * @param string $symbols All symbols to trim. Default only spaces
     */
    public function trim($symbols = " ") {
        foreach ($this->lang as &$lang) {
            $lang = trim($lang, $symbols);
            $this->base = trim($this->base);
        }
        return ($this);
    }

    /**
     * Stores the i18n String manually in the database
     *
     * @param $object_id The objects primary key
     * @param $table The table the object is stores in
     * @param $field The fieldname
     */
    public function store($object_id, $table, $field) {
        $db = DBManager::get();

        // Find pk
        $pk = $db->fetchColumn("SHOW INDEX FROM $table WHERE Key_name = 'PRIMARY'", null, 4);

        /* Replace base value */
        $baseSQL = $db->prepare("UPDATE `$table` SET `$field` = ? WHERE `$pk` = ?");
        $baseSQL->execute(array($this->base, $object_id));

        /* Replace translations */
        $i18nSQL = $baseSQL = $db->prepare("REPLACE INTO `i18n` (`object_id`, `table`, `field`, `lang`, `value`) VALUES (?,?,?,?,?)");
        foreach ($this->lang as $lang => $value) {
            $i18nSQL->execute(array($object_id, $table, $field, $lang, $value));
        }
    }


    public static function load($object_id, $table, $field) {
        $db = DBManager::get();

        // Find pk
        $pk = $db->fetchColumn("SHOW INDEX FROM $table WHERE Key_name = 'PRIMARY'", null, 4);
        $base = $db->fetchColumn("SELECT `$field` FROM `$table` WHERE $pk = ?", array($object_id));
        $lang = $db->fetchPairs("SELECT `lang`, `value` FROM `i18n` WHERE `object_id` = ? AND `table` = ? AND `field` = ?", array($object_id, $table, $field));
        return new self($base, $lang);
    }
}
