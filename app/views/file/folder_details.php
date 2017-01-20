<?$folder_template = $folder->getDescriptionTemplate()?>
<div id="file_edit_window">
    <?= $this->render_partial('file/_folder_aside.php') ?>
    <div id="file_management_forms">
        <h3><?= _('Eigenschaften') ?></h3>
        <article><?= $folder_template instanceof Flexi_Template ? $folder_template->render() : $folder_template ?></article>
        <? if ($folder->description) : ?>
            <h3><?= _('Beschreibung') ?></h3>
            <article><?= htmlReady($folder->description); ?></article>
        <? endif ?>
    </div>
</div>
<div data-dialog-button>
<? if ($folder->isEditable($GLOBALS['user']->id)) : ?>
    <?= \Studip\LinkButton::create(_('Bearbeiten'),
        $controller->url_for('file/edit_folder/' . $folder->getId()),
        ['data-dialog' => 1]
    ) ?>
<? endif ?>
    <?= \Studip\LinkButton::create(_('Ordner öffnen'),
    $controller->url_for('file/open_folder/' . $folder->getId())
    ) ?>
</div>
