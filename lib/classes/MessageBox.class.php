<?php
/**
 * MessageBox.class.php
 *
 * html-boxes for different kinds of messages
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * @author      Michael Riehemann <michael.riehemann@uni-oldenburg.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL Licence 2
 * @category    Stud.IP
 * @package     layout
 * @since       1.10
 *
 */

/**
 * class MessageBox
 *
 * usage:
 *
 * echo MessageBox::error('Nachricht', array('optional details'));
 *
 * use the optional parameter $close_details for displaying the messagebox with
 * closed details
 *
 * echo MessageBox::success('Nachricht', array('optional details'), true);
 *
 */
class MessageBox
{
    /**
     * This function shows an exception-messagebox. Use it only for systemerrors
     * or security related problems.
     *
     * @param string $message
     * @param array() $details
     * @param boolean $close_details
     * @return string html-output of the messagebox
     */
    public static function exception($message, $details = array(), $close_details = false)
    {
        return self::render('exception', $message, $details, $close_details);
    }

    /**
     * This function shows an error-messagebox. Use it for validation errors,
     * problems and other wrong user input.
     *
     * @param string $message
     * @param array() $details (optional)
     * @param boolean $close_details (optional)
     * @return string html-output of the messagebox
     */
    public static function error($message, $details = array(), $close_details = false)
    {
        return self::render('error', $message, $details, $close_details);
    }

    /**
     * This function shows a success messagebox. Use it for confirmation of user
     * interaction.
     *
     * @param string $message
     * @param array() $details (optional)
     * @param boolean $close_details (optional)
     * @return string html-output of the messagebox
     */
    public static function success($message, $details = array(), $close_details = false)
    {
        return self::render('success', $message, $details, $close_details);
    }

    /**
     * This function shows an info messagebox. Use it to report neutral
     * informations.
     *
     * @param string $message
     * @param array() $details (optional)
     * @param boolean $close_details (optional)
     * @return string html-output of the messagebox
     */
    public static function info($message, $details = array(), $close_details = false)
    {
        return self::render('info', $message, $details, $close_details);
    }


    /**
     * This method actually renders a message
     *
     * @param string $class the type of this message
     * @param string $message
     * @param array() $details
     * @param boolean $close_details
     * @return string html-output of the messagebox
     */
    private static function render($class, $message, $details, $close_details)
    {
        return $GLOBALS['template_factory']->render('shared/message_box', compact('class', 'message', 'details', 'close_details'));
    }

    /**
     * This function is a special debug-messagebox for developers
     *
     * @param mixed $message
     * @param string $title
     * @param string $template every developer can customize his own debugbox
     * @return string html-output of the messagebox
     * @since 2.0
     */
    public static function debug($message, $title = '', $template = 'debug_standard')
    {
        return $GLOBALS['template_factory']->render('shared/'.$template, compact('message', 'title'));
    }
}
