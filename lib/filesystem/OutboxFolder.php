<?php
/**
 * OutboxFolder.class.php
 *
 * This is a FolderType implementation for file attachments of messages
 * that were sent by a user. It is a read-only folder.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author    Moritz Strohm <strohm@data-quest.de>
 * @copyright 2016 data-quest
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category  Stud.IP
 */
class OutboxFolder extends InboxOutboxFolder
{
    public static function getTypeName()
    {
        return _('Alle Anhänge gesendeter Nachrichten');
    }

    public function getIcon($role)
    {
        $icon = count($this->getFiles()) > 0
              ? 'folder-inbox-full'
              : 'folder-inbox-empty';
        return Icon::create($icon, $role);
    }

    public function getFiles()
    {
        //get all folders of the user that belongs to a received message:
        $condition = "INNER JOIN message_user
                        ON folders.range_id = message_user.message_id
                     WHERE folders.range_type = 'message'
                       AND message_user.user_id = :user_id
                       AND message_user.snd_rec = 'snd'";
        $message_folders = Folder::findBySql($condition, [
            'user_id' => $this->user->id
        ]);

        $files = [];
        foreach ($message_folders as $folder) {
            $file_refs = FileRef::findBySql('folder_id = :folder_id', [
                ':folder_id' => $folder->id
            ]);
            $files = array_merge($files, $file_refs);
        }

        return $files;
    }

    public function setDataFromEditTemplate($folderdata)
    {
        return MessageBox::error(_('Outbox-Ordner können nicht bearbeitet werden!'));
    }

    public function createFile($file)
    {
        return MessageBox::error(_('In Outbox-Ordnern können keine Dateien erzeugt werden!'));
    }
}
