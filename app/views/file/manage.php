<h1 style="font-weight: bold; font-size:2em; text-align:center;">WORK IN PROGRESS!</h1>

<aside style="float:left; width: 20%;">
    <?= Icon::create(
        'file',
        'info',
        [
            'style' => 'width: 100%; max-height: 18em; height: 100%;'
        ]) ?>
    <h3 style="text-align: center; font-size: 140%;"><?= htmlReady($file_ref->name) ?></h3>
    <dl>
        <dt><?= _('Größe') ?></dt>
        <dd><?= relSize($file_ref->size, false) ?></dd>
        
        <dt><?= _('Erstellt') ?></dt>
        <dd><?= date('d.m.Y H:i', $file_ref->mkdate) ?></dd>
        
        <dt><?= _('Geändert') ?></dt>
        <dd><?= date('d.m.Y H:i', $file_ref->chdate) ?></dd>
        
        <dt><?= _('Besitzer/-in') ?></dt>
        <dd>
        <? if($file_ref->owner): ?>
        <?= htmlReady($file_ref->owner->getFullName()) ?>
        <? endif ?>
        </dd>
    </dl>
</aside>
<table class="default withdetails" style="width:79%;">
    <tr id="edit_form_attributes" class="open">
        <td onclick="jQuery(this).closest('tr').siblings().removeClass('open'); jQuery(this).closest('tr').toggleClass('open');"><?= _("Datei bearbeiten") ?><td>
    </tr>
    <tr class="details nohover">
        <td>
            <form enctype="multipart/form-data"
                method="post"
                class="default"
                action="<?= $controller->url_for('/edit/' . $file_ref_id) ?>">

                <?= CSRFProtection::tokenTag() ?>
                <input type="hidden" name="fileref_id" value="<?=htmlReady($file_ref_id)?>">
                <input type="hidden" name="folder_id" value="<?=htmlReady($file_ref_id)?>">
                <fieldset>
                    <label>
                        <?= _('Name') ?>
                        <input type="text" name="name" value="<?= htmlReady($name) ?>">
                    </label>
                    <label>
                        <?= _('Lizenz') ?>
                        <select name="licence">
                            <option value="1">Placeholder1</option>
                            <option value="2">Placeholder2</option>
                        </select>
                    </label>

                    <label>
                        <?= _('Beschreibung') ?>
                        <textarea name="description" placeholder="<?= _('Optionale Beschreibung') ?>"><?= htmlReady($description); ?></textarea>
                    </label>

                    <div>
                        <?= Studip\Button::createAccept(_('Speichern'), 'save') ?>
                    </div>
                </fieldset>
            </form>
        </td>
    </tr>
    <tr id="edit_form_copy">
        <td onclick="jQuery(this).closest('tr').siblings().removeClass('open'); jQuery(this).closest('tr').toggleClass('open');"><?= _("Datei kopieren") ?><td>
    </tr>
    <tr class="details nohover">
        <td>
            <form class="default" action="<?= $controller->url_for('/move/'. $file_ref->id); ?>">
                <fieldset>
                    <input type="hidden" name="copymode" value="copy">
                    <div id="folder_select_copy-container">
                        <label for="folder_select_copy-destination"><?= _('Ziel'); ?></label>
                        <select id="folder_select_copy-destination"
                            onchange="STUDIP.Files.changeFolderSource('copy');">
                            <option value="null"></option>
                            <optgroup label="lokal">
                                    <option value="myfiles"><?= _('Meine Dateien'); ?></option>
                                    <option value="courses"><?= _('Veranstaltungen'); ?></option>
                                    <option value="institutes"><?= _('Einrichtungen'); ?></option>
                            </optgroup>
                            <optgroup label="extern">
                                    <option disabled="disabled" value="plugin1"><?= _('Plugin1'); ?></option>
                                    <option disabled="disabled" value="plugin2"><?= _('Plugin2'); ?></option>
                                    <option disabled="disabled" value="plugin3"><?= _('Plugin3'); ?></option>
                            </optgroup>
                        </select>
                    </div>
                    <input id="folder_select_copy-range-user_id" type="hidden" name="user_id" value="<?= htmlReady($user_id); ?>">
                    <div id="folder_select_copy-range-course" style="display: none;">
                        <label for="range"><?= htmlReady(_('Veranstaltung')); ?></label>
                        <?= $search; ?>         
                    </div>
                    <div id="folder_select_copy-range-inst" style="display: none;">
                        <label for="range"><?= htmlReady(_('Einrichtung')); ?></label>
                        <?= $inst_search; ?>
                    </div>
                        
                    <div id="folder_select_copy-subfolder" style="display: none;">
                        <label for="subfolder"><?= _('Ordner'); ?></label>
                        <select id="subfolder" name="dest_folder" ></select>
                    </div>
                    
                    <div>
                        <?= Studip\Button::createAccept(_('Kopieren'), 'do_move') ?>
                    </div>
                </fieldset>
            </form>
        </td>
    </tr>
    <tr id="edit_form_move">
        <td onclick="jQuery(this).closest('tr').siblings().removeClass('open'); jQuery(this).closest('tr').toggleClass('open');"><?= _("Datei verschieben") ?><td>
    </tr>
    <tr class="details nohover">
        <td>
            <form class="default" action="<?= $controller->url_for('/move/'. $file_ref->id); ?>">
                <fieldset>
                    <input type="hidden" name="copymode" value="move">
                    <div id="folder_select_move-container">
                        <label for="folder_select_move-destination"><?= _('Ziel'); ?></label>
                        <select id="folder_select_move-destination"
                            onchange="STUDIP.Files.changeFolderSource('move');">
                            <option value="null"></option>
                            <optgroup label="lokal">
                                    <option value="myfiles"><?= _('Meine Dateien'); ?></option>
                                    <option value="courses"><?= _('Veranstaltungen'); ?></option>
                                    <option value="institutes"><?= _('Einrichtungen'); ?></option>
                            </optgroup>
                            <optgroup label="extern">
                                    <option disabled="disabled" value="plugin1"><?= _('Plugin1'); ?></option>
                                    <option disabled="disabled" value="plugin2"><?= _('Plugin2'); ?></option>
                                    <option disabled="disabled" value="plugin3"><?= _('Plugin3'); ?></option>
                            </optgroup>
                        </select>
                    </div>
                    <input id="folder_select_move-range-user_id" type="hidden" name="user_id" value="<?= htmlReady($user_id); ?>">
                    <div id="folder_select_move-range-course" style="display: none;">
                        <label for="range"><?= htmlReady(_('Veranstaltung')); ?></label>
                        <?= $search; ?>         
                    </div>
                    <div id="folder_select_move-range-inst" style="display: none;">
                        <label for="range"><?= htmlReady(_('Einrichtung')); ?></label>
                        <?= $inst_search; ?>
                    </div>
                        
                    <div id="folder_select_move-subfolder" style="display: none;">
                        <label for="subfolder"><?= _('Ordner'); ?></label>
                        <select id="subfolder" name="dest_folder" ></select>
                    </div>


                    <div>
                        <?= Studip\Button::createAccept(_('Verschieben'), 'do_move') ?>
                    </div>
                </fieldset>
            </form>
        </td>
    </tr>
    <tr id="edit_form_delete">
        <td onclick="jQuery(this).closest('tr').siblings().removeClass('open'); jQuery(this).closest('tr').toggleClass('open');"><?= _("Datei löschen") ?><td>
    </tr>
    <tr class="details nohover">
        <td>
            <form class="default" action="<?= $controller->url_for('/delete/'. $file_ref->id); ?>">
                <fieldset>
                    <p><?= _('Soll die ausgewählte Datei wirklich gelöscht werden?') ?></p>
                </fieldset>
            </form>
        </td>
    </tr>
</table>
