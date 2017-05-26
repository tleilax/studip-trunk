<?php
/**
 * UserFileArea.class.php
 *
 * This class contains static methods especially designed for the user file area.
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
class UserFileArea
{
    /**
     * Returns an INBOX folder for the given user.
     *
     * @param User user The user whose inbox folder is requested.
     * @return FolderType|null Returns the inbox folder on success, null on failure.
     */
    public static function getInboxFolder(User $user)
    {
        $top_folder = Folder::findTopFolder($user->id, 'user');
        if (!$top_folder) {
            return null;
        }

        $top_folder = $top_folder->getTypedFolder();
        if (!$top_folder) {
            return null;
        }

        $inbox_folder = Folder::find(md5('INBOX_' . $user->id));

        if (!$inbox_folder) {
            //inbox folder doesn't exist: create it, if necessary.
            //We need an inbox folder if there is at least one received
            //message with at least one attachment.

            $inbox_folder = FileManager::createSubFolder(
                $top_folder,
                $user,
                'InboxFolder',
                'Inbox',
                InboxFolder::getTypeName()
            );

            if ($inbox_folder instanceof InboxFolder) {
                return $inbox_folder;
            }

            return null;
        }

        return $inbox_folder->getTypedFolder();
    }

    /**
     * Returns a FolderType object for the outbox folder of the given user.
     *
     * @param User user The user whose outbox folder is requested.
     * @return FolderType|null Returns the inbox folder on success, null on failure.
     */
    public static function getOutboxFolder(User $user)
    {
        $top_folder = Folder::findTopFolder($user->id, 'user');
        if (!$top_folder) {
            return null;
        }

        $top_folder = $top_folder->getTypedFolder();
        if (!$top_folder) {
            return null;
        }

        $outbox_folder = Folder::find(md5('OUTBOX_' . $user->id));

        if (!$outbox_folder) {
            //inbox folder doesn't exist: create it, if necessary.
            //We need an inbox folder if there is at least one received
            //message with at least one attachment.

            $outbox_folder = FileManager::createSubFolder(
                $top_folder,
                $user,
                'OutboxFolder',
                'Outbox',
                OutboxFolder::getTypeName()
            );

            if ($outbox_folder instanceof OutboxFolder) {
                return $outbox_folder;
            }

            return null;
        }

        return $outbox_folder->getTypedFolder();
    }
}
