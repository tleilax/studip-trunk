<?php
/*
 *  Copyright (c) 2012  Rasmus Fuhse <fuhse@data-quest.de>
 *
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License as
 *  published by the Free Software Foundation; either version 2 of
 *  the License, or (at your option) any later version.
 */

/**
 * Interface of BlubberContact - all users that are displayed in Blubber
 * must be instances of BlubberContact, regardless if they are Stud.IP-users
 * or anonymous writers.
 */
interface BlubberContact {
    /**
     * Returns the name that should be displayed.
     */
    public function getName();

    /**
     * Returns the URL of the user. If this returns an empty string or null,
     * there won't be any link to the user, so that's okay, too. Needs an absolute URL.
     */
    public function getURL();

    /**
     * Returns an instance of Avatar or any superclass of it.
     */
    public function getAvatar();

    /**
     * This method is executed if someone mentions the user in a blubber. It
     * should notify the user, so he/she get's aware of the posting.
     * @param $posting: BlubberPosting in which the user is mentioned.
     */
    public function mention($posting);
}

/**
 * A simple extension of User, so it handles all studip-users. It only fulfills
 * the BlubberContact interface so that the studip-user can be displayed as a
 * blubber-author.
 */
class BlubberUser extends User implements BlubberContact {

    /**
     * Displays the name of the user.
     * @return string : name of the user
     */
    public function getName() {
        return trim($this['Vorname']." ".$this['Nachname']);
    }

    /**
     * Returns an absolute URL to the profile-page.
     * @return string : absolute URL
     */
    public function getURL() {
        return $GLOBALS['ABSOLUTE_URI_STUDIP']."dispatch.php/profile?username=".$this['username'];
    }

    /**
     * Returns an instance of Avatar for the user.
     * @return Avatar
     */
    public function getAvatar() {
        return Avatar::getAvatar($this->getId());
    }

    /**
     * Notifies the user with Stud.IP-message that/he/she was mentioned in a
     * blubber-posting.
     * @param type $posting
     */
    public function mention($posting) {
        $messaging = new messaging();
        $url = $GLOBALS['ABSOLUTE_URI_STUDIP']."plugins.php/blubber/streams/thread/"
            . $posting['root_id']
            . ($posting['context_type'] === "course" ? '?cid='.$posting['Seminar_id'] : "");
        $messaging->insert_message(
            sprintf(
                _("%s hat Sie in einem Blubber erw�hnt. Zum Beantworten klicken auf Sie auf folgenen Link:\n\n%s\n"),
                get_fullname(),
                $url
            ),
            $this['username'],
            $GLOBALS['user']->id,
            null, null, null, null,
            _("Sie wurden erw�hnt.")
        );
    }
}