<?= (isset($flash['success']))?MessageBox::info($flash['success']):'' ?>
<?= (isset($flash['error']))?MessageBox::error($flash['error'], $flash['error_detail']):'' ?>
<? if (empty($via_ajax)): ?>
<h2><?=_("Bearbeiten von Konfigurationsparameter")?></h2>
<? endif; ?>
<form action="<?= $controller->url_for('admin/configuration/edit_configuration/'.$edit['config_id']) ?>" method=post>
    <table class="default">
        <tr class="<?= TextHelper::cycle('cycle_odd', 'cycle_even') ?>">
            <td><?=_("Name")?> (<em>field</em>): </td>
            <td><?= htmlReady($edit['field'])?></td>
        </tr>
        <tr class="<?= TextHelper::cycle('cycle_odd', 'cycle_even') ?>">
            <td><?=_("Inhalt")?> (<em>value</em>): </td>
            <td><textarea cols="55" rows="4" name="value"><?= htmlReady($edit['value'])?></textarea></td>
        </tr>
        <tr class="<?= TextHelper::cycle('cycle_odd', 'cycle_even') ?>">
            <td><?=_("Beschreibung")?> (<em>description</em>): </td>
            <td><?= htmlReady($edit['description'])?></td>
        </tr>
        <tr class="<?= TextHelper::cycle('cycle_odd', 'cycle_even') ?>">
            <td><?=_("Kommentar")?> (<em>comment</em>): </td>
            <td><textarea cols="55" rows="4" name="comment"><?= htmlReady($edit['comment'])?></textarea></td>
        </tr>
        <tr class="<?= TextHelper::cycle('cycle_odd', 'cycle_even') ?>">
            <td><?=_("Standard")?> (<em>is_default</em>): </td>
            <td>
                <? if ($edit['is_default'] == 1): ''?> TRUE
                <? elseif ($edit['is_default'] == 0): ''?> FALSE
                <? elseif ($edit['is_default'] == NULL): ''?> <?= _('<em>-kein Eintrag vorhanden</em>')?>
                <? endif; ?>
            </td>
        </tr>
        <tr class="<?= TextHelper::cycle('cycle_odd', 'cycle_even') ?>">
            <td>
                <?=_("Typ")?> (<em>type</em>):
            </td>
            <td>
                <?= $edit['type']?>
            </td>
        </tr>
        <tr class="<?= TextHelper::cycle('cycle_odd', 'cycle_even') ?>">
            <td>
                <?=_("Bereich")?> (<em>range</em>):
            </td>
            <td>
                <?= $edit['range']?>
            </td>
        </tr>
        <tr class="<?= TextHelper::cycle('cycle_odd', 'cycle_even') ?>">
            <td><label for="section"><?=_("Kategorie")?> (<em>section</em>):</label></td>
            <td>
                <select name= "section" onchange="$(this).next('input').val( $(this).val() );">
                <? foreach (array_keys($allconfigs) as $section): ?>
                  <option value = "<?= $section?>"
                    <?= ($edit['section'] == $section) ? 'selected="selected"' : '' ?>>
                    <?=$section?>
                  </option>
                <? endforeach; ?>
                </select>
                <input type="text" name="section_new" id="section" />
                <?= _('(<em>Bitte die neue Kategorie eingeben</em>)')?>
           </td>
        </tr>
        <tr class="steel2">
            <td></td>
            <td>
                <?= makeButton('uebernehmen2','input',_('�nderungen �bernehmen'),'uebernehmen') ?>
                <a class="cancel" href="<?=$controller->url_for('admin/configuration/configuration')?>"><?= makebutton('abbrechen', 'img', _('Zur�ck zur �bersicht'))?></a>
            </td>
        </tr>
    </table>
</form>