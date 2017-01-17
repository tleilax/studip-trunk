<?php
/**
 * message.php - Message controller
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 * 
 * @author Stud.IP developers
 * @author Moritz Strohm <strohm@data-quest.de> (only code related to the new file area)
 */

require_once 'lib/statusgruppe.inc.php';

class MessagesController extends AuthenticatedController {

    protected $number_of_displayed_messages = 50;
    protected $utf8decode_xhr = true;

    function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        PageLayout::setTitle(_("Nachrichten"));
        PageLayout::setHelpKeyword("Basis.InteraktionNachrichten");

        // The default body and/or subject passed via GET url parameters
        // should not be utf8decoded and thus need to be restored in their
        // pristine values
        if (Request::isXhr() && Request::isGet()) {
            $request = Request::getInstance();
            foreach (words('default_body default_subject') as $key) {
                $request[$key] = $_GET[$key];
            }
        }
    }

    public function overview_action($message_id = null)
    {
        Navigation::activateItem('/messaging/messages/inbox');


        if (Request::get("read_all")) {
            Message::markAllAs($GLOBALS['user']->id, 1);
            PageLayout::postMessage(MessageBox::success(_("Alle Nachrichten wurden als gelesen markiert.")));
        }

        if (Request::isPost()) {
            foreach (Request::getArray("bulk") as $message_id) {
                $this->delete_message($message_id);
            }
            PageLayout::postMessage(MessageBox::success(sprintf(_("%u Nachrichten wurden gel�scht"), count(Request::getArray("bulk")))));
        }

        $this->messages = $this->get_messages(
            true,
            Request::int("limit", $this->number_of_displayed_messages),
            Request::int("offset", 0),
            Request::get("tag"),
            Request::get("search")
        );
        $this->received   = true;
        $this->tags       = Message::getUserTags();
        $this->message_id = $message_id;
        $this->settings   = UserConfig::get($GLOBALS['user']->id)->MESSAGING_SETTINGS;
    }

    public function sent_action($message_id = null)
    {
        Navigation::activateItem('/messaging/messages/sent');

        if (Request::isPost()) {
            foreach (Request::getArray("bulk") as $message_id) {
                $this->delete_message($message_id);
            }
            PageLayout::postMessage(MessageBox::success(sprintf(_("%u Nachrichten wurden gel�scht"), count(Request::getArray("bulk")))));
        }

        $this->messages = $this->get_messages(
            false,
            Request::int("limit", $this->number_of_displayed_messages),
            Request::int("offset", 0),
            Request::get("tag"),
            Request::get("search")
        );
        $this->received   = false;
        $this->tags       = Message::getUserTags();
        $this->message_id = $message_id;
        $this->settings   = UserConfig::get($GLOBALS['user']->id)->MESSAGING_SETTINGS;

        $this->render_action("overview");
    }

    public function more_action()
    {
        $messages = $this->get_messages(
            Request::int("received") ? true : false,
            Request::int("limit", $this->number_of_displayed_messages) + 1,
            Request::int("offset", 0),
            Request::get("tag"),
            Request::get("search")
        );
        $this->output = array('messages' => array(), "more" => 0);
        if (count($messages) > Request::int("limit")) {
            $this->output["more"] = 1;
            array_pop($messages);
        }
        $this->settings   = UserConfig::get($GLOBALS['user']->id)->MESSAGING_SETTINGS;
        $template_factory = $this->get_template_factory();
        foreach ($messages as $message) {
            $this->output['messages'][] = $template_factory
                                            ->open("messages/_message_row.php")
                                            ->render(array('message'    => $message,
                                                           'controller' => $this,
                                                           'received'   => (bool) Request::int("received"),
                                                           'settings'   => $this->settings
                                                    ));
        }

        $this->render_json($this->output);
    }

    public function read_action($message_id)
    {
        $this->message = new Message($message_id);
        if (!$this->message->permissionToRead()) {
            throw new AccessDeniedException();
        }

        //load the message's top folder (if any):
        $attachment_folder = Folder::findTopFolder($this->message->id);
        if($attachment_folder) {
            $this->attachment_folder = $attachment_folder->getTypedFolder();
        }
        
        
        PageLayout::setTitle(_('Betreff') . ': ' . $this->message['subject']);

        if ($this->message['autor_id'] === $GLOBALS['user']->id) {
            Navigation::activateItem('/messaging/messages/sent');
        } else {
            Navigation::activateItem('/messaging/messages/inbox');
        }
        if (Request::isXhr()) {
            $this->response->add_header('X-Tags', json_encode(studip_utf8encode($this->message->getTags())));
            $this->response->add_header('X-All-Tags', json_encode(studip_utf8encode(Message::getUserTags())));
        } else {
            // Try to redirect to overview of recevied/sent messages if
            // controller is not called via ajax to ensure message is loaded
            // in dialog.
            $target = ($this->message->autor_id === $GLOBALS['user']->id)
                    ? $this->url_for('messages/sent/' . $message_id)
                    : $this->url_for('messages/overview/' . $message_id);

            $script = sprintf('if (STUDIP.Dialog.shouldOpen()) { location.href = "%s"; }', $target);
            PageLayout::addHeadElement('script', array(), $script);
        }
        $this->message->markAsRead($GLOBALS["user"]->id);
    }

    /**
     * Lets the user compose a message and send it.
     */
    public function write_action()
    {
        PageLayout::setTitle(_("Neue Nachricht schreiben"));
        
        //the message-ID for the new message:
        $this->message_id = Request::option("message_id") ?: md5(uniqid("neWMesSagE"));
        
        
        $this->to = array();
        $this->default_message = new Message();
        
        //flag to determine if the message is forwarded or not:
        $forward_message = false;
        
        
        //check if a receiver is given:
        if (Request::username("rec_uname")) {
            $user = new MessageUser();
            $user->setData(array('user_id' => get_userid(Request::username("rec_uname")), 'snd_rec' => "rec"));
            $this->default_message->receivers[] = $user;
        }
        
        //check if a list of receivers is given:
        if (Request::getArray("rec_uname")) {
            foreach (Request::usernameArray("rec_uname") as $username) {
                $user = new MessageUser();
                $user->setData(array('user_id' => get_userid($username), 'snd_rec' => "rec"));
                $this->default_message->receivers[] = $user;
            }
        }
        
        //check if the message shall be sent to all members of a status group:
        if (Request::option("group_id")) {
            $this->default_message->receivers = array();
            $group = Statusgruppen::find(Request::option("group_id"));
            if (($group['range_id'] === $GLOBALS['user']->id)
                    || ($GLOBALS['perm']->have_studip_perm("autor", $group['range_id']))) {
                foreach ($group->members as $member) {
                    $user = new MessageUser();
                    $user->setData(array('user_id' => $member['user_id'], 'snd_rec' => "rec"));
                    $this->default_message->receivers[] = $user;
                }
            }
        }

        //check if the message shall be sent to all members of an institute:
        if(Request::get('inst_id') && $GLOBALS['perm']->have_perm('admin')) {
            $query = "SELECT user_id FROM user_inst WHERE Institut_id = ? AND inst_perms != 'user'";
            $this->default_message->receivers = DBManager::get()->fetchAll($query, array(Request::option('inst_id')), 'MessageUser::build');
        }

        //check if the message shall be sent to all (or some) members of a course:
        if (Request::get("filter") && Request::option("course_id")) {
            $course = new Course(Request::option('course_id'));
            if ($GLOBALS['perm']->have_studip_perm("tutor", Request::option('course_id')) || $course->getSemClass()['studygroup_mode']) {
                $this->default_message->receivers = array();
                if (Request::get("filter") === 'claiming') {
                    $cs = CourseSet::getSetForCourse(Request::option("course_id"));
                    if (is_object($cs) && !$cs->hasAlgorithmRun()) {
                        foreach (AdmissionPriority::getPrioritiesByCourse($cs->getId(), Request::option("course_id")) as $user_id => $p) {
                            $this->default_message->receivers[] = MessageUser::build(array('user_id' => $user_id, 'snd_rec' => 'rec'));
                        }
                    }
                } else {
                    $params = array(Request::option('course_id'), Request::option('who'));
                    switch (Request::get("filter")) {
                        case 'send_sms_to_all':
                            $query = "SELECT b.user_id,'rec' as snd_rec FROM seminar_user a, auth_user_md5 b WHERE a.Seminar_id = ? AND a.user_id = b.user_id AND a.status = ? ORDER BY Nachname, Vorname";
                            break;
                        case 'all':
                            $query = "SELECT user_id,'rec' as snd_rec FROM seminar_user LEFT JOIN auth_user_md5 USING(user_id) WHERE Seminar_id = ? ORDER BY Nachname, Vorname";
                            break;
                        case 'prelim':
                            $query = "SELECT user_id,'rec' as snd_rec FROM admission_seminar_user LEFT JOIN auth_user_md5 USING(user_id) WHERE seminar_id = ? AND status='accepted' ORDER BY Nachname, Vorname";
                            break;
                        case 'awaiting':
                            $query = "SELECT user_id,'rec' as snd_rec FROM admission_seminar_user LEFT JOIN auth_user_md5 USING(user_id) WHERE seminar_id = ? AND status='awaiting' ORDER BY Nachname, Vorname";
                            break;
                        case 'inst_status':
                            $query = "SELECT b.user_id,'rec' as snd_rec FROM user_inst a, auth_user_md5 b WHERE a.Institut_id = ? AND a.user_id = b.user_id AND a.inst_perms = ? ORDER BY Nachname, Vorname";
                            break;
                    }
                    $this->default_message->receivers = DBManager::get()->fetchAll($query, $params, 'MessageUser::build');
                }
            }

        }

        if (Request::option('prof_id') && Request::option('deg_id') && $GLOBALS['perm']->have_perm('root')) {
            $query = "SELECT DISTINCT user_id,'rec' as snd_rec
            FROM user_studiengang
            WHERE fach_id = ? AND abschluss_id = ?";
            $this->default_message->receivers = DBManager::get()->fetchAll($query, array(
                Request::option('prof_id'),
                Request::option('deg_id')
            ), 'MessageUser::build');
        }

        if (Request::option('sd_id') && $GLOBALS['perm']->have_perm('root')) {
            $query = "SELECT DISTINCT user_id,'rec' as snd_rec
            FROM user_studiengang
            WHERE abschluss_id = ?";
            $this->default_message->receivers = DBManager::get()->fetchAll($query, array(
                Request::option('sd_id')
            ), 'MessageUser::build');
        }

        if (Request::option('sp_id') && $GLOBALS['perm']->have_perm('root')) {
            $query = "SELECT DISTINCT user_id,'rec' as snd_rec
            FROM user_studiengang
            WHERE fach_id = ?";
            $this->default_message->receivers = DBManager::get()->fetchAll($query, array(
                Request::option('sp_id')
            ), 'MessageUser::build');
        }

        if (!$this->default_message->receivers->count() && is_array($_SESSION['sms_data']['p_rec'])) {
            $this->default_message->receivers = DBManager::get()->fetchAll("SELECT user_id,'rec' as snd_rec FROM auth_user_md5 WHERE username IN(?) ORDER BY Nachname,Vorname", array($_SESSION['sms_data']['p_rec']), 'MessageUser::build');
            unset($_SESSION['sms_data']);
        }
        
        //check if the message is a reply or if it shall be forwarded:
        if (Request::option("answer_to")) {
            $this->default_message->receivers = array();
            $old_message = new Message(Request::option("answer_to"));
            if (!$old_message->permissionToRead()) {
                throw new AccessDeniedException("Message is not for you.");
            }
            if (!Request::get('forward')) {
                //message is a reply message
                if (Request::option("quote") === $old_message->getId()) {
                    if (Studip\Markup::isHtml($old_message['message'])) {
                        $this->default_message['message'] = "<div>[quote]\n".$old_message['message']."\n[/quote]</div>";
                    } else {
                        $this->default_message['message'] = "[quote]\n".$old_message['message']."\n[/quote]";
                    }
                }
                $this->default_message['subject'] = mb_substr($old_message['subject'], 0, 4) === "RE: " ? $old_message['subject'] : "RE: ".$old_message['subject'];
                $user = new MessageUser();
                $user->setData(array('user_id' => $old_message['autor_id'], 'snd_rec' => "rec"));
                $this->default_message->receivers[] = $user;
                $this->answer_to = $old_message->id;
            } else {
                //message shall be forwarded
                $forward_message = true;
                
                $messagesubject = 'FWD: ' . $old_message['subject'];
                $message = _("-_-_ Weitergeleitete Nachricht _-_-");
                $message .= "\n" . _("Betreff") . ": " . $old_message['subject'];
                $message .= "\n" . _("Datum") . ": " . strftime('%x %X', $old_message['mkdate']);
                $message .= "\n" . _("Von") . ": " . get_fullname($old_message['autor_id']);
                $num_recipients = $old_message->getNumRecipients();
                if ($GLOBALS['user']->id == $old_message->autor_id) {
                    $message .= "\n" . _("An") . ": " . ($num_recipients == 1 ? _('Eine Person') : sprintf(_('%s Personen'), $num_recipients));
                } else {
                    $message .= "\n" . _("An") . ": " . $GLOBALS['user']->getFullname() . ($num_recipients > 1 ? ' ' . sprintf(_('(und %d weitere)'), $num_recipients) : '');
                }
                $message .= "\n\n";
                if (Studip\Markup::isHtml($old_message['message'])) {
                    $message = '<div>' . htmlReady($message,false,true) . '</div>' . $old_message['message'];
                } else {
                    $message .= $old_message['message'];
                }
                if ($old_message->getNumAttachments()) {
                    //there is at least one attachment: we must copy it
                    $this->message_id = $old_message->getNewId();
                    //Request::set('message_id', $forwarded_message_id);
                    $old_attachment_folder = MessageFolder::findMessageTopFolder($old_message->id);
                    
                    if($old_attachment_folder) {
                        $new_attachment_folder = MessageFolder::findMessageTopFolder($this->message_id, $GLOBALS['user']->id);
                        if($new_attachment_folder) {
                            foreach($old_attachment_folder->getFiles() as $old_attachment) {
                                $new_attachment = new FileRef();
                                $new_attachment->file_id = $old_attachment->file_id;
                                $new_attachment->folder_id = $new_attachment_folder->getId();
                                $new_attachment->name = $old_attachment->file->name;
                                $new_attachment->description = $old_attachment->description;
                                $new_attachment->license = $old_attachment->license;
                                $new_attachment->user_id = $GLOBALS['user']->id;

                                if($new_attachment->store()) {
                                    $this->default_attachments[] = [
                                        'icon' => GetFileIcon(
                                            $new_attachment->file->getExtension()
                                            )->asImg(['class' => "text-bottom"]),
                                        'name' => $new_attachment->name,
                                        'document_id' => $new_attachment->id,
                                        'size' => relsize($new_attachment->file->size, false)
                                    ];
                                }
                            }
                        }
                    }
                }
                $this->default_message['subject'] = $messagesubject;
                $this->default_message['message'] = $message;
            }
        }
        if (Request::get("default_body")) {
            $this->default_message['message'] = Request::get("default_body");
        }
        if (Request::get("default_subject")) {
            $this->default_message['subject'] = Request::get("default_subject");
        }
        $settings = UserConfig::get($GLOBALS['user']->id)->MESSAGING_SETTINGS;
        $this->mailforwarding = Request::get('emailrequest') ? true : $settings['request_mail_forward'];
        if (trim($settings['sms_sig'])) {
            if (Studip\Markup::isHtml($this->default_message['message']) || Studip\Markup::isHtml($settings['sms_sig'])) {
                if (!Studip\Markup::isHtml($this->default_message['message'])) {
                    $this->default_message['message'] = '<div>' . nl2br($this->default_message['message']) . '</div>';
                }
                $this->default_message['message'] .= '<br><br>--<br>';
                if (Studip\Markup::isHtml($settings['sms_sig'])) {
                    $this->default_message['message'] .= $settings['sms_sig'];
                } else {
                    $this->default_message['message'] .= formatReady($settings['sms_sig']);
                }

            } else {
                $this->default_message['message'] .= "\n\n--\n" . $settings['sms_sig'];
            }
        }
        
        
        //Files that were uploaded earlier and were left unattached
        //are only attached to new messages which are not forwarded.
        //This is because forwarded messages will only have those attachments
        //that were present in the original message.
        if(!$forward_message) {
            
            //Check if there are files that were uploaded earlier and not attached
            //to a message. These files can be attached to the new message.
            
            //unattached folders are all folders that are from type 'MessageFolder',
            //belong to the range type 'message', are owned by the current user
            //and whose range-ID does not belong to a message.
            //Background: Attachment folders of messages that haven't been sent
            //have a "provisional" range-ID. When the message is sent this
            //"provisional" range-ID is replaced by the message-ID.
            $unattached_folders = Folder::findBySql(
                "folder_type = 'MessageFolder'
                AND
                range_type = 'message'
                AND
                user_id = :user_id
                AND
                range_id NOT IN (
                    SELECT message_id FROM message
                    WHERE autor_id = :user_id
                )",
                [
                    'user_id' => $GLOBALS['user']->id
                ]
            );
            
            $unattached_files = [];
            
            //loop through all unattached folders, retrieve all file_refs,
            //add them to the default_attachments array and store them in a
            //new folder that gets the "provisional" range-ID of this message.
            //After that, delete the old folders.
            foreach($unattached_folders as $unattached_folder) {
                foreach($unattached_folder->file_refs as $file_ref) {
                    $unattached_files[] = $file_ref;
                    $this->default_attachments[] = [
                        'icon' => GetFileIcon(
                            $file_ref->file->getExtension()
                            )->asImg(['class' => "text-bottom"]),
                        'name' => $file_ref->name,
                        'document_id' => $file_ref->id,
                        'size' => relsize($file_ref->file->size, false)
                    ];
                }
                
            }
            
            //we must display a note for the user to avoid sending a message
            //with the wrong attachements attached to it.
            if($unattached_files) {
                PageLayout::postInfo(_('Es wurden Dateianh�nge gefunden, welche zwar hochgeladen, aber noch nicht versandt wurden. Diese wurden an diese Nachricht angeh�ngt!'));
            }
            
            //create an attachment folder for the new message:
            $new_attachment_folder = MessageFolder::findMessageTopFolder($this->message_id, $GLOBALS['user']->id);
            
            //"bend" the folder-ID of each unattached file to the new attachment folder's ID:
            foreach($unattached_files as $file) {
                $file->folder_id = $new_attachment_folder->getId();
                $file->store();
            }
            
            //now we can delete the old unattached folders since we transferred
            //the attachments to a new folder:
            foreach($unattached_folders as $unattached_folder) {
                $unattached_folder->delete();
            }
        }
        
        
        NotificationCenter::postNotification("DefaultMessageForComposerCreated", $this->default_message);


    }

    /**
     * Sends a message and redirects the user.
     */
    public function send_action() {
        PageLayout::setTitle(_("Nachricht verschicken"));
        if (Request::isPost() && count(array_filter(Request::getArray("message_to"))) && Request::submitted("message_body")) {
            $messaging = new messaging();
            $rec_uname = array();
            foreach (Request::getArray("message_to") as $user_id) {
                if ($user_id) {
                    $rec_uname[] = get_username($user_id);
                }
            }
            $messaging->provisonal_attachment_id = Request::option("message_id");
            $messaging->send_as_email =  Request::int("message_mail");
            $messaging->insert_message(
                Studip\Markup::purifyHtml(Request::get("message_body")),
                $rec_uname,
                $GLOBALS['user']->id,
                '',
                '',
                '',
                null,
                Request::get("message_subject"),
                "",
                'normal',
                trim(Request::get("message_tags")) ?: null
            );
            if (Request::option('answer_to')) {
                $old_message = Message::find(Request::option('answer_to'));
                if ($old_message) {
                    $old_message->originator->answered = 1;
                    $old_message->store();
                }
            }
            PageLayout::postMessage(MessageBox::success(_("Nachricht wurde verschickt.")));
        } else if (!count(array_filter(Request::getArray('message_to')))) {
            PageLayout::postMessage(MessageBox::error(_('Sie haben nicht angegeben, wer die Nachricht empfangen soll!')));
        }
    }

    public function tag_action($message_id) {
        if (Request::isPost()) {
            $message = Message::find($message_id);
            if (!$message->permissionToRead()) {
                throw new AccessDeniedException();
            }
            if (Request::get('add_tag')) {
                $message->addTag(Request::get('add_tag'));
            } elseif (Request::get('remove_tag')) {
                $message->removeTag(Request::get('remove_tag'));
            }
        }
        $this->redirect('messages/read/' . $message_id);
    }

    function print_action($message_id)
    {
        $message = Message::find($message_id);
        if (!$message->permissionToRead()) {
            throw new AccessDeniedException();
        }
        if ($message && $message->permissionToRead($GLOBALS['user']->id)) {
            $this->msg = $message->toArray();
            $this->msg['from'] = $message['autor_id'] === '____%system%____'
                                ? _('Stud.IP')
                                : ($message->getSender()
                                    ? $message->getSender()->getFullname()
                                    : _('unbekannt'));
            $this->msg['to'] = $GLOBALS['user']->id == $message->autor_id ?
                join(', ', $message->getRecipients()->pluck('fullname')) :
                $GLOBALS['user']->getFullname() . ' ' . sprintf(_('(und %d weitere)'), $message->getNumRecipients()-1);
            $this->msg['attachments'] = $message->attachments->toArray('filename filesize');
            PageLayout::setTitle($this->msg['subject']);
            $this->set_layout($GLOBALS['template_factory']->open('layouts/base'));
        } else {
            $this->set_status(400);
            return $this->render_nothing();
        }
    }

    protected function delete_message($message_id)
    {
        $messageuser = new MessageUser(array($GLOBALS['user']->id, $message_id, "snd"));
        $success = 0;
        if (!$messageuser->isNew()) {
            $messageuser['deleted'] = 1;
            $success = $messageuser->store();
        }
        $messageuser = new MessageUser(array($GLOBALS['user']->id, $message_id, "rec"));
        if (!$messageuser->isNew()) {
            $messageuser['deleted'] = 1;
            $success += $messageuser->store();
        }
        return $success;
    }

    public function delete_action($message_id)
    {
        $message = Message::find($message_id);

        $ticket = Request::get('studip-ticket');
        if (Request::isPost() && $ticket && check_ticket($ticket)) {
            $success = $this->delete_message($message_id);
            if ($success) {
                PageLayout::postMessage(MessageBox::success(_('Nachricht gel�scht!')));
            } else {
                PageLayout::postMessage(MessageBox::error(_('Nachricht konnte nicht gel�scht werden.')));
            }
        }

        $redirect = $message->autor_id === $GLOBALS['user']->id
            ? $this->url_for('messages/sent')
            : $this->url_for('messages/overview');

        $this->redirect($redirect);
    }

    protected function get_messages($received = true, $limit = 50, $offset = 0, $tag = null, $search = null)
    {
        if ($tag) {
            $messages_data = DBManager::get()->prepare("
                SELECT message.*
                FROM message_user
                    INNER JOIN message ON (message_user.message_id = message.message_id)
                    INNER JOIN message_tags ON (message_tags.message_id = message_user.message_id AND message_tags.user_id = message_user.user_id)
                WHERE message_user.user_id = :me
                    AND message_user.deleted = 0
                    AND snd_rec = :sender_receiver
                    AND message_tags.tag = :tag
                ORDER BY message_user.mkdate DESC
                LIMIT ".(int) $offset .", ".(int) $limit ."
            ");
            $messages_data->execute(array(
                'me' => $GLOBALS['user']->id,
                'tag' => $tag,
                'sender_receiver' => $received ? "rec" : "snd"
            ));
        } elseif($search) {

            $suchmuster = '/".*"/U';
            preg_match_all($suchmuster, $search, $treffer);
            array_walk($treffer[0], function(&$value) { $value = trim($value, '"'); });

            // remove the quoted parts from $_searchfor
            $_searchfor = trim(preg_replace($suchmuster, '', $search));

            // split the searchstring $_searchfor at every space
            $parts = explode(' ', $_searchfor);
            foreach ($parts as $key => $val) {
                if ($val == '') {
                    unset($parts[$key]);
                }
            }
            if (!empty($parts)) {
                $_searchfor = array_merge($parts, $treffer[0]);
            } else  {
                $_searchfor = $treffer[0];
            }

            if (!Request::int('search_autor') && !Request::int('search_subject') && !Request::int('search_content')) {
                $message = _('Es wurden keine Bereiche angegeben, in denen gesucht werden soll.');
                PageLayout::postMessage(MessageBox::error($message));

                $search_sql = "AND 0";
            } else {
                $search_sql = "";
                foreach ($_searchfor as $val) {
                    $tmp_sql = array();
                    if (Request::get("search_autor")) {
                        $tmp_sql[] = "CONCAT(auth_user_md5.Vorname, ' ', auth_user_md5.Nachname) LIKE ".DBManager::get()->quote("%".$val."%")." ";
                    }
                    if (Request::get("search_subject")) {
                        $tmp_sql[] = "message.subject LIKE ".DBManager::get()->quote("%".$val."%")." ";
                    }
                    if (Request::get("search_content")) {
                        $tmp_sql[] = "message.message LIKE ".DBManager::get()->quote("%".$val."%")." ";
                    }
                    $search_sql .= "AND (";
                    $search_sql .= implode(" OR ", $tmp_sql);
                    $search_sql .= ") ";
                }
            }

            $messages_data = DBManager::get()->prepare("
                SELECT message.*
                FROM message_user
                    INNER JOIN message ON (message_user.message_id = message.message_id)
                    LEFT JOIN auth_user_md5 ON (auth_user_md5.user_id = message.autor_id)
                WHERE message_user.user_id = :me
                    AND message_user.deleted = 0
                    AND snd_rec = :sender_receiver
                    $search_sql
                ORDER BY message_user.mkdate DESC
                LIMIT ".(int) $offset .", ".(int) $limit ."
            ");
            $messages_data->execute(array(
                'me' => $GLOBALS['user']->id,
                'sender_receiver' => $received ? "rec" : "snd"
            ));
        } else {
            $messages_data = DBManager::get()->prepare("
                SELECT message.*
                FROM message_user
                    INNER JOIN message ON (message_user.message_id = message.message_id)
                WHERE message_user.user_id = :me
                    AND message_user.deleted = 0
                    AND snd_rec = :sender_receiver
                ORDER BY message_user.mkdate DESC
                LIMIT ".(int) $offset .", ".(int) $limit ."
            ");
            $messages_data->execute(array(
                'me' => $GLOBALS['user']->id,
                'sender_receiver' => $received ? "rec" : "snd"
            ));
        }
        $messages_data->setFetchMode(PDO::FETCH_ASSOC);
        $messages = array();
        foreach ($messages_data as $data) {
            $messages[] = Message::buildExisting($data);
        }
        return $messages;
    }

    public function upload_attachment_action() {
        if ($GLOBALS['user']->id === "nobody") {
            throw new AccessDeniedException();
        }
        if (!$GLOBALS['ENABLE_EMAIL_ATTACHMENTS']) {
            throw new AccessDeniedException(_('Mailanh�nge sind nicht erlaubt.'));
        }
        $file = studip_utf8decode($_FILES['file']);
        $output = array(
            'name' => $file['name'],
            'size' => $file['size']
        );
        
        $message_id = Request::option('message_id');
        $output['message_id'] = $message_id;
        
        $message_top_folder = MessageFolder::findMessageTopFolder($message_id, $GLOBALS['user']->id);
        
        $error = $message_top_folder->validateUpload($file, $GLOBALS['user']->id);
        if ($error != null) {
            list($type, $error) = explode("�", $GLOBALS['msg']);
            throw new Exception($error);
        }
        
        $user = User::find($GLOBALS['user']->id);
        
        $file_object = new File();
        $file_object->user_id = $user->id;
        $file_object->mime_type = ''; //TODO: detect mime type
        $file_object->name = $output['name'];
        $file_object->size = (int)$output['size'];
        $file_object->storage = 'disk';
        $file_object->author_name = $user->getFullName();
        
        $file_ref = $message_top_folder->createFile($file_object);
        
        if (!$file_ref) {
            throw new Exception('Unable to handle uploaded file!');
        }
        
        $result = move_uploaded_file($file['tmp_name'], $file_object->getPath());
        if(!$data_stored) {
            throw new Exception('Data of file with ID ' . $file_ref->file_id . ' cannot be stored in path for uploaded files!');
        }
        
        $output['document_id'] = $file_ref->file_id;
        
        $output['icon'] = GetFileIcon($file_ref->file->getExtension())->asImg(['class' => "text-bottom"]);

        $this->render_json($output);
    }

    public function delete_attachment_action()
    {
        CSRFProtection::verifyUnsafeRequest();
        $attachment = FileRef::find(Request::option('document_id'));
        if($attachment){
            $attachment->delete();
        }
        $this->render_nothing();
    }

    public function preview_action()
    {
        if (Request::isXhr()) {
            $this->render_text(formatReady(Request::get("text")));
        }
    }

    public function delete_tag_action()
    {
        CSRFProtection::verifyUnsafeRequest();
        DbManager::get()->execute("DELETE FROM message_tags WHERE user_id=? AND tag LIKE ?", array($GLOBALS['user']->id, Request::get('tag')));
        PageLayout::postMessage(MessageBox::success(_('Schlagwort gel�scht!')));
        $this->redirect($this->url_for('messages/overview'));
    }
}
