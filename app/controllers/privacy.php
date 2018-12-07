<?php
/**
 * PrivacyController
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Timo Hartge <hartge@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       4.2
 */

class PrivacyController extends AuthenticatedController
{
    public function index_action($user_id)
    {
        if (!Privacy::isVisible($user_id)) {
            throw new AccessDeniedException();
        }

        Navigation::activateItem('/profile');
        $this->plugin_data = Privacy::getUserdataInformation($user_id);
        $this->user_id = $user_id;

        $actions = new ActionsWidget();
        $actions->setTitle(_('Datenschutz'));
        $actions->addLink(
            _('Anzeige Personendaten'),
            $this->url_for('privacy/index/' . $user_id),
            Icon::create('log', Icon::ROLE_CLICKABLE, tooltip2(_('Anzeige Personendaten')))
        )->asDialog('size=big');
        $actions->addLink(
            _('Personendaten drucken'),
            $this->url_for('privacy/print/' . $user_id),
            Icon::create('print', Icon::ROLE_CLICKABLE, tooltip2(_('Personendaten drucken'))),
            ['class' => 'print_action', 'target' => '_blank']
        );
        $actions->addLink(
            _('Export Personendaten als CVS'),
            $this->url_for('privacy/export/' . $user_id),
            Icon::create('file-text', Icon::ROLE_CLICKABLE, tooltip2(_('Export Personendaten als CVS')))
        );
        $actions->addLink(
            _('Export persönlicher Dateien als ZIP'),
            $this->url_for('privacy/filesexport/' . $user_id),
            Icon::create('file-archive', Icon::ROLE_CLICKABLE, tooltip2(_('Export persönlicher Dateien als ZIP')))
        );

        Sidebar::Get()->addWidget($actions);
    }

    /**
     * Create a csv file with user data from a specific table of a plugin
     *
     * @param string $plugin_id
     * @param string $table
     * @param string $user_id
     * @throws AccessDeniedException if user has no privileges
     */
    public function export2CSV_action($table, $user_id)
    {
        if (!Privacy::isVisible($user_id)) {
            throw new AccessDeniedException();
        }

        $plugin_data = Privacy::getUserdataInformation($user_id);

        if (!empty($plugin_data)) {
            foreach($plugin_data as $table_label => $table_data) {
                if ($table_data['table_name'] !== $table) {
                    continue;
                }

                $data = $table_data['table_content'];
                $headers = array_keys($data[0]);
                $csvdata = [];
                foreach ($data as $row) {
                    $csvdata[] = array_values($row);
                }
                $this->render_csv(array_merge([$headers], $csvdata), "{$table}.csv");
                return;
            }
        }

        PageLayout::postError(_("Die Daten konnten nicht exportiert werden."));
        $this->redirect('privacy/index');
    }

    /**
     * Create a print view with user data
     *
     * @param string $user_id
     * @throws AccessDeniedException if user has no privileges
     */
    public function print_action($user_id)
    {
        if (!Privacy::isVisible($user_id)) {
            throw new AccessDeniedException();
        }

        PageLayout::removeStylesheet('style.css');
        PageLayout::addStylesheet('print.css');

        $user = User::find($user_id);
        $this->plugin_data = Privacy::getUserdataInformation($user_id);
        $this->user_id = $user_id;
        $this->user_fullname = $user->getFullName();
    }

    /**
     * Create a zip file with user data
     *
     * @param string $user_id
     * @throws AccessDeniedException if user has no privileges
     */
    public function export_action($user_id)
    {
        if (!Privacy::isVisible($user_id)) {
            throw new AccessDeniedException();
        }

        $user = User::find($user_id);
        $plugin_data = Privacy::getUserdataInformation($user_id);
        $files = [];
        $csv = [];

        foreach ($plugin_data as $label => $table) {
            $data = $table['table_content'];
            if ($data) {
                $headers = array_keys($data[0]);
                $csvdata = array();
                foreach ($data as $row) {
                    $csvdata[] = array_values($row);
                }
                $tmpname = md5(uniqid($user_id.$table['table_name']));
                $filepath = $GLOBALS['TMP_PATH'] . DIRECTORY_SEPARATOR . $tmpname;
                if (array_to_csv($csvdata, $filepath, $headers)) {
                    $csv[$table['table_name']] = $filepath;
                }
            }
        }

        $tmpname = md5(uniqid('datenexport_' . $user->username));
        $zipname = $GLOBALS['TMP_PATH'] . DIRECTORY_SEPARATOR . $tmpname;
        $zip = new ZipArchive;
        $zip->open($zipname, ZipArchive::CREATE);
        foreach ($csv as $table => $file) {
            $zip->addFile($file, $table . '.csv');
        }
        if ($zip->close()) {
            foreach ($files as $plugin => $plugin_files) {
                foreach ($plugin_files as $table => $file) {
                    unlink($file);
                }
            }
        }

        $this->set_content_type('application/zip');
        $this->response->add_header(
            'Content-disposition',
            'attachment;' . encode_header_parameter('filename', "datenexport_{$user->username}.zip")
        );
        $this->response->add_header('Content-Length', filesize($zipname));
        $this->render_text(file_get_contents($zipname));
    }

    /**
     * Delivers a zip containing the files from plugins which feature the specific function
     *
     * @param string $user_id
     * @throws AccessDeniedException if user has no privileges
     */
    public function filesexport_action($user_id)
    {
        if (!Privacy::isVisible($user_id)) {
            throw new AccessDeniedException();
        }

        $user = User::find($user_id);
        $files = [];

        $tmpname = md5(uniqid('datienexport_' . $user->username));
        $zipname = $GLOBALS['TMP_PATH'] . DIRECTORY_SEPARATOR . $tmpname;

        $zip = new ZipArchive;
        $zip->open($zipname, ZipArchive::CREATE);

        $avatar = Avatar::getAvatar($user_id);
        if ($avatar->is_customized()) {
            $zip->addFile($avatar->getCustomAvatarPath('normal'), $user_id . '.png');
        }

        // FIXME this will overwrite files with the same name in different folders
        foreach (FileRef::findBySQL("user_id = ?", [$user_id]) as $core_fileref) {
            FileArchiveManager::addFileRefToArchive($zip, $core_fileref, $user_id);
        }

        foreach (PluginEngine::getPlugins('PrivacyPlugin') as $plugin) {
            $plugin_data = $plugin->getUserData($user_id);
            if ($plugin_data && $plugin_data->hasData()) {
                foreach ($plugin_data->getFileData() as $file_data) {
                    if (isset($file_data['path'])) {
                        $zip->addFile($file_data['path'], $file_data['name']);
                    } else {
                        $zip->addFromString($file_data['name'], $file_data['contents']);
                    }
                }
            }
        }

        $zip->close();

        if (!file_exists($zipname)) {
            PageLayout::postError(_('Keine Dateien vorhanden.'));
            $this->redirect("privacy/index/" . $user_id);
            return;
        }

        $archive_download_link = FileManager::getDownloadURLForTemporaryFile(
            $zipname,
            "dateiexport_{$user->username}.zip"
        );

        $this->redirect($archive_download_link);
    }

    /**
     * Show a message dialog to ask for user data
     *
     * @param string $user_id
     */
    public function askfor_action($user_id)
    {
        $mail_user = Config::get()->PRIVACY_CONTACT;

        $user = User::findByUsername($mail_user);
        if ($user) {
            $mail_subject = _('Auskunft nach Art 15 DSGVO');
            $mail_message = _("Sehr geehrte Damen und Herren,\n\nhiermit bitte ich Sie nach Art 15 DSGVO, mir Auskunft über die über mich gespeicherten personenbezogenen Daten zu geben.");
            $this->redirect(URLHelper::getURL('dispatch.php/messages/write', ['rec_uname' => $mail_user, 'default_subject' => $mail_subject, 'default_body' => $mail_message]));
        } else {
            $this->render_text(MessageBox::error(_("Es wurde keine Kontaktperson bestimmt."), array(_('Bitte wenden Sie sich an den in der Datenschutzerklärung angegebenen Ansprechpartner.'))));
        }
    }
}
