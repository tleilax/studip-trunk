<?php

/**
 * @author      Till Glöggler <tgloeggl@uos.de>
 * @author      André Klaßen <klassen@elan-ev.de>
 * @license     GPL 2 or later
 */

namespace Studip\Activity;

class Activity extends \SimpleORMap
{
    public
        $object_url,
        $object_route;

    const GC_MAX_DAYS = 366; // Garbage collector removes activities after 366 days

    private static $allowed_verbs = array(
        'answered',
        'attempted',
        'attended',
        'completed',
        'created',
        'deleted',
        'edited',
        'experienced',
        'failed',
        'imported',
        'interacted',
        'passed',
        'shared',
        'sent',
        'voided'
    );

    /**
     * {@inheritdoc}
     */
    protected static function configure($config = array())
    {
        $config['db_table'] = 'activities';

        parent::configure($config);
    }

    /**
     * return a string representation for this activity
     * 
     * @return string
     */
    public function __toString()
    {
        return $this->content['content'];
    }

    /**
     * set one of the allowed verbs
     *
     * @param string $verb
     * 
     * @throws \InvalidArgumentException
     */
    public function setVerb($verb)
    {
        if (in_array($verb, self::$allowed_verbs) === false) {
            throw new \InvalidArgumentException("That verb is not allowed.");
        }

        $this->content['verb'] = $verb;
    }

    /**
     * Add a url to the list of urls
     *
     * @param type $url
     * @param type $name
     */
    public function addUrl($url, $name)
    {
        $this->object_url[$url] = $name;
    }

    /**
     * Return objet in an array representation
     *
     * @return Array
     */
    public function toArray()
    {
        $data = parent::toArray();

        $data['object_url'] = $this->object_url;
        $data['object_route'] = $this->object_route;

        return $data;
    }

    /**
     * returns a format string as placeholder for the object in question
     * (in a grammatical / lexical sense)
     *
     * @return string
     */
    public function verbToText()
    {
        $translation = array(
            'answered'    => _('beantwortete %s'),
            'attempted'   => _('versuchte %s'),
            'attended'    => _('nahm teil an %s'),
            'completed'   => _('beendete %s'),
            'created'     => _('erstellte %s'),
            'deleted'     => _('löschte %s'),
            'edited'      => _('bearbeitete %s'),
            'experienced' => _('erlebte %s'),
            'failed'      => _('verfehlte %s'),
            'imported'    => _('importierte %s'),
            'interacted'  => _('interagierte mit %s'),
            'passed'      => _('bestand %s'),
            'shared'      => _('teilte %s'),
            'sent'        => _('sendete %s'),
            'voided'      => _('löschte %s')
        );

        return ($translation[$this->verb]);
    }

     /**
     * Garbage collector for the activities.
     * Removes all activites older than GC_MAX_DAYS (default: 366).
     */
    public static function doGarbageCollect()
    {
        $stmt = DBManager::get()->prepare('DELETE FROM activities WHERE mkdate < ?');

        $stmt->execute(array(
            time() - self::GC_MAX_DAYS * 24 * 60 * 60)
        );
    }
}
