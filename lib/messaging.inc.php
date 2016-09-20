<?php
# Lifter002: DONE - no html and mails are already templates
# Lifter003: TEST
# Lifter007: TODO
# Lifter010: DONE - not applicable
/**
 * mesaging.inc.php - Funktionen fuer das Messaging
 *
 * several functions and classes used for the systeminternal messages
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Cornelis Kater <ckater@gwdg.de>
 * @author      Nils K. Windisch <studip@nkwindisch.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @package     messaging
 */

require_once 'lib/user_visible.inc.php';
require_once 'lib/datei.inc.php';




class messaging
{
    var $sig_string; //String, der Signaturen vom eigentlichen Text abgrenzt

    public static function sendSystemMessage($recipient, $message_title, $message_body)
    {
        $m = new messaging();
        $user = User::toObject($recipient);
        return $m->insert_message($message_body, $user['username'], '____%system%____', FALSE, FALSE, '1', FALSE, $message_title);
    }

    /**
     * Konstruktor
     */
    function __construct()
    {
        $this->sig_string="\n \n -- \n";
    }

    /**
     * Nachricht loeschen
     *
     * @param $message_id
     * @param $user_id
     */
    function delete_message($message_id, $user_id = FALSE)
    {
        if (!$user_id) {
            $user_id = $GLOBALS['user']->id;
        }

        $query = "UPDATE message_user
                  SET deleted = '1'
                  WHERE message_id = ? AND user_id = ? AND deleted = '0'";

        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($message_id, $user_id));

        if ($statement->rowCount() == 0) {
            return false;
        }

        $query = "SELECT 1 FROM message_user WHERE message_id = ? AND deleted = '0'";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($message_id));
        if (!$statement->fetchColumn()) {
            $this->remove_message($message_id);

            // StEP 155: Mail Attachments
            $query = "SELECT dokument_id FROM dokumente WHERE range_id = ?";
            $statement = DBManager::get()->prepare($query);
            $statement->execute(array($message_id));
            $document_ids = $statement->fetchAll(PDO::FETCH_COLUMN);

            array_map('delete_document', $document_ids);
        }
        return true;
    }

    /**
     * Removes a message or a list of messages from the database
     *
     * @param mixed $id Id(s) of the message(s) in question
     * @return bool Returns false if not a single message was removed
     */
    private function remove_message($id)
    {
        if (empty($id)) {
            return true;
        }

        $query = "DELETE message, message_user
                  FROM message
                  LEFT JOIN message_user USING(message_id)
                  WHERE message.message_id IN (?)";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($id));
        return $statement->rowCount() > 0;
    }

    /**
     * delete all messages from user
     *
     * @param $user_id
     */
    function delete_all_messages($user_id = FALSE)
    {
        if (!$user_id) {
            $user_id = $GLOBALS['user']->id;
        }

        $query = "SELECT message_id FROM message_user WHERE user_id = ? AND deleted = '0'";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($user_id));
        $message_ids = $statement->fetchAll(PDO::FETCH_COLUMN);

        foreach ($message_ids as $message_id) {
            $this->delete_message($message_id, $user_id);
        }
    }
    

    /**
     *
     * @param $userid
     */
    function user_wants_email($userid)
    {
        $query = "SELECT email_forward FROM user_info WHERE user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($userid));
        $setting = $statement->fetchColumn();

        if ($setting == 1) {
            return false;
        }
        if (in_array($setting, array(2, 3))) {
            return $setting;
        }
        return $GLOBALS['MESSAGING_FORWARD_DEFAULT'];
    }

    /**
     *
     * @param $rec_user_id
     * @param $snd_user_id
     * @param $message
     * @param $subject
     * @param $message_id
     */
    function sendingEmail($rec_user_id, $snd_user_id, $message, $subject, $message_id)
    {
        $msg      = Message::find($message_id);
        $receiver = User::find($rec_user_id);
        $to       = $receiver->Email;

        // do not try to send mails to users without a mail address
        if (!$to) {
            return;
        }

        $rec_fullname = $receiver->getFullName();

        setTempLanguage($rec_user_id);

        $title = "[Stud.IP - " . $GLOBALS['UNI_NAME_CLEAN'] . "] " . kill_format(str_replace(array("\r", "\n"), '', $subject));

        if ($snd_user_id != "____%system%____") {
            $sender = User::find($snd_user_id);

            $snd_fullname = $sender->getFullName();
            $reply_to = $sender->Email;
        }
        $attachments = array();
        if ($GLOBALS['ENABLE_EMAIL_ATTACHMENTS']) {
            $size_of_attachments = array_sum($msg->attachments->pluck('filesize')) ?: 0;
            //assume base64 takes 33% more space
            $attachments_as_links = $size_of_attachments * 1.33 > $GLOBALS['MAIL_ATTACHMENTS_MAX_SIZE'] * 1024 * 1024;
        }
        $template = $GLOBALS['template_factory']->open('mail/text');
        $template->set_attribute('message', kill_format($message));
        $template->set_attribute('rec_fullname', $rec_fullname);
        if ($attachments_as_links) {
            $template->set_attribute('attachments', $attachments);
        }
        $mailmessage = $template->render();

        $template = $GLOBALS['template_factory']->open('mail/html');
        $template->set_attribute('lang', getUserLanguagePath($rec_user_id));
        $template->set_attribute('message', $message);
        $template->set_attribute('rec_fullname', $rec_fullname);
        if ($attachments_as_links) {
            $template->set_attribute('attachments', $attachments);
        }
        $mailhtml = $template->render();

        restoreLanguage();

        // Now, let us send the message
        $mail = new StudipMail();
        $mail->setSubject($title)
            ->setReplyToEmail('')
            ->addRecipient($to, $rec_fullname)
            ->setBodyText($mailmessage);
        if ($GLOBALS['MESSAGING_FORWARD_USE_REPLYTO']) {
            $mail->setReplyToEmail($reply_to)
                ->setReplyToName($snd_fullname);
        } elseif (mb_strlen($reply_to)) {
            $mail->setSenderEmail($reply_to)
                ->setSenderName($snd_fullname)
                ->setReplyToEmail('');
        }
        $user_cfg = UserConfig::get($rec_user_id);
        if ($user_cfg->getValue('MAIL_AS_HTML')) {
            $mail->setBodyHtml($mailhtml);
        }

        if (count($attachments) && !$attachments_as_links) {
            foreach ($attachments as $attachment) {
                $mail->addStudipAttachment($attachment['dokument_id']);
            }
        }
        if (!get_config("MAILQUEUE_ENABLE")) {
            $mail->send();
        } else {
            MailQueueEntry::add($mail, $message_id, $rec_user_id);
        }
    }
    
    /**
     *
     * @param $message
     * @param $rec_uname
     * @param $user_id
     * @param $time
     * @param $tmp_message_id
     * @param $set_deleted
     * @param $signature
     * @param $subject
     * @param $force_email
     * @param $priority
     */
    function insert_message($message, $rec_uname, $user_id='', $time='', $tmp_message_id='', $set_deleted='', $signature='', $subject='', $force_email='', $priority='normal', $tags = null)
    {
        global $user;

        $my_messaging_settings = UserConfig::get($user->id)->MESSAGING_SETTINGS;

        // wenn kein subject uebergeben
        $subject = $subject ?: _('Ohne Betreff');

        $email_request = $this->send_as_email ?: $my_messaging_settings['send_as_email'];

        // wenn keine zeit uebergeben
        $time = $time ?: time();

        // wenn keine id uebergeben
        $tmp_message_id = $tmp_message_id ?: md5(uniqid('321losgehtes', true));

        // wenn keine user_id uebergeben
        $user_id = $user_id ?: $user->id;

        # send message now
        if ($user_id != '____%system%____')  { // real-user message
            $snd_user_id = $user_id;
            $set_deleted = $set_deleted ?: ($my_messaging_settings['save_snd'] != '1'); // don't save sms in outbox

        } else { // system-message
            $set_deleted = '1';
            // system-signatur
            $snd_user_id = '____%system%____';
            setTempLanguage();
            $message .= $this->sig_string;
            $message .= _('Diese Nachricht wurde automatisch vom Stud.IP-System generiert. Sie k�nnen darauf nicht antworten.');

            restoreLanguage();
        }


        // Setzen der Message-ID als Range_ID f�r angeh�ngte Dateien
        if (isset($this->provisonal_attachment_id) && $GLOBALS['ENABLE_EMAIL_ATTACHMENTS']) {
            $attachments = StudipDocument::findBySQL("range_id = 'provisional' AND description = ?", [$this->provisonal_attachment_id]);
            foreach ($attachments as $attachment) {
                $attachment->range_id = $tmp_message_id;
                $attachment->description = '';
                $attachment->store();
            }
        }

        // insert message
        $query = "INSERT INTO message (message_id, autor_id, subject, message, priority, mkdate)
                  VALUES (?, ?, ?, ?, ?, UNIX_TIMESTAMP())";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array(
            $tmp_message_id,
            $snd_user_id,
            $subject,
            $message,
            $priority,
        ));
        // insert snd
        $insert_tags = DBManager::get()->prepare("
            INSERT IGNORE INTO message_tags
            SET message_id = :message_id,
                user_id = :user_id,
                tag = :tag,
                chdate = UNIX_TIMESTAMP(),
                mkdate = UNIX_TIMESTAMP()
        ");
        $query = "INSERT INTO message_user (message_id, user_id, snd_rec, deleted, mkdate)
                  VALUES (?, ?, 'snd', ?, UNIX_TIMESTAMP())";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array(
            $tmp_message_id,
            $snd_user_id,
            $set_deleted ? 1 : 0,                  // save message?
        ));
        if ($tags) {
            is_array($tags) || $tags = explode(" ", (string) $tags);
            foreach ($tags as $tag) {
                $insert_tags->execute(array(
                    'message_id' => $tmp_message_id,
                    'user_id' => $snd_user_id,
                    'tag' => strtolower($tag)
                ));
            }
        }

        // heben wir kein array bekommen, machen wir einfach eins ...
        if (!is_array($rec_uname)) {
            $rec_uname = array($rec_uname);
        }

        // wir bastelen ein neues array, das die user_id statt des user_name enthaelt
        $rec_id = array();
        foreach ($rec_uname as $one) {
            $rec_id[] = User::findByUsername($one)->user_id;
        }
        $rec_id = array_filter($rec_id);
        // wir gehen das eben erstellt array durch und schauen, ob irgendwer was weiterleiten moechte.
        // diese user_id schreiben wir in ein tempraeres array
        foreach ($rec_id as $one) {
            $smsforward_rec = User::find($one)->smsforward_rec;
            $tmp_forward_id = User::find($smsforward_rec)->user_id;
            if ($tmp_forward_id) {
                $rec_id[] = $tmp_forward_id;
            }
        }

        // wir mergen die eben erstellten arrays und entfernen doppelte eintraege
        $rec_id = array_unique($rec_id);

        // hier gehen wir alle empfaenger durch, schreiben das in die db und schicken eine mail
        $query  = "INSERT INTO message_user (message_id, user_id, snd_rec, mkdate)
                   VALUES (?, ?, 'rec', UNIX_TIMESTAMP())";
        $insert = DBManager::get()->prepare($query);
        $snd_name = ($user_id != '____%system%____')
            ? User::find($user_id)->getFullName() . ' (' . User::find($user_id)->username . ')'
            : 'Stud.IP-System';
        foreach ($rec_id as $one) {
            $insert->execute(array($tmp_message_id, $one));
            if ($GLOBALS['MESSAGING_FORWARD_AS_EMAIL']) {
                // mail to original receiver
                $mailstatus_original = $this->user_wants_email($one);
                if ($mailstatus_original == 2 || ($mailstatus_original == 3 && $email_request == 1) || $force_email) {
                    $this->sendingEmail($one, $snd_user_id, $message, $subject, $tmp_message_id);
                }
            }
            if ($tags) {
                foreach ($tags as $tag) {
                    $insert_tags->execute(array(
                        'message_id' => $tmp_message_id,
                        'user_id' => $one,
                        'tag' => strtolower($tag)
                    ));
                }
            }
        }

        // Obtain all users that should receive a notification
        $user_ids = $rec_id;
        if (is_object($GLOBALS['user'])) {
            $user_ids = array_diff($user_ids, array($GLOBALS['user']->id));
        }

        // Create notifications
        PersonalNotifications::add(
            $user_ids,
            URLHelper::getUrl("dispatch.php/messages/read/$tmp_message_id", array('cid' => null)),
            sprintf(_('Sie haben eine Nachricht von %s erhalten!'), $snd_name),
            'message_'.$tmp_message_id,
            Icon::create('mail', 'clickable')
        );

        NotificationCenter::postNotification('MessageDidSend', $tmp_message_id, compact('user_id', 'rec_id'));

        return sizeof($rec_id);
    }

}
