<?
# Lifter010: TODO
?>
<? if (isset($flash['error'])): ?>
    <?= MessageBox::error($flash['error'], $flash['error_detail']) ?>
<? elseif (isset($flash['message'])): ?>
    <?= MessageBox::info($flash['message'], $flash['detail']) ?>
<? elseif (isset($flash['success'])): ?>
    <?= MessageBox::success($flash['success'], $flash['detail']) ?>
<? endif ?>

<style type="text/css">
.filter_selection select {
    width: 100%;
}
.filter_selection input[name=remove_filter] {
    float: right;
}
</style>

<h2>
    <?= _('Manuelles Eintragen von Nutzergruppen in Veranstaltungen') ?>
</h2>
<h3>
    <?= _('Suche nach Seminaren')?>
</h3>
<form action="<?= $controller->url_for('admin/autoinsert/manual') ?>" method="post">
<?= CSRFProtection::tokenTag() ?>
    <?= $this->render_partial("admin/autoinsert/_search.php", compact('semester_data', 'sem_search', 'sem_select')) ?>
</form>


<? if (count($seminar_search) > 0 and $sem_search and $sem_select): ?>
<form action="<?= $controller->url_for('admin/autoinsert/manual') ?>" method="post">
<?= CSRFProtection::tokenTag() ?>
    <input type="hidden" name="sem_search" value="<?= $sem_search ?>">
    <input type="hidden" name="sem_select" value="<?= $sem_select ?>">
  <? foreach ($filtertype as $type): ?>
    <input type="hidden" name="filtertype[]" value="<?= $type ?>">
  <? endforeach; ?>

    <fieldset>
        <legend><?= _('Suchresultate') ?></legend>
        <div class="type-select">
            <label for="sem_id"><?= _('Seminar:') ?></label>
           <select name="sem_id" id="sem_id">
           <? foreach ($seminar_search as $seminar): ?>
                <option value="<?= $seminar[0] ?>" <?= ($sem_id==$seminar[0]) ? 'selected="selected"' : '' ?>>
                    <?= $seminar[1] ?>
                </option>
           <? endforeach; ?>
            </select>
        </div>
    </fieldset>

  <? if (count($filtertype) != count($available_filtertypes)): ?>
    <fieldset>
        <legend><?= _('Filterkriterien')?>:</legend>
        <div class="type-select">
            <select name="add_filtertype">
            <? foreach ($available_filtertypes as $key => $value): ?>
              <? if (!in_array($key, $filtertype)): ?>
                <option value="<?= $key ?>"><?= $value ?></option>
              <? endif ?>
            <? endforeach; ?>

            </select>
            <input class="middle" type="image"
                src="<?= Assets::image_path("icons/16/blue/plus.png") ?>"
                name="add_filter">
        </div>
    </fieldset>
  <? endif ?>

    <!-- #2 Auswahllisten anzeigen -->
    <? if (!empty($filtertype)): ?>
    <fieldset>
        <legend><?= _('Ausgew�hlte Filtertypen')?>:</legend>
        <table class="default filter_selection">
            <colgroup>
                <col width="50%">
                <col width="50%">
            </colgroup>
        <? $index = 0; foreach ($filtertype as $type): ?>
          <? if ($index%2 == 0): ?>
            <? if ($index != 0): ?></tr><? endif ?>
            <tr class="<?= TextHelper::cycle('cycle_odd', 'cycle_even') ?>">
          <? endif ?>
                <td>
                    <label for="<?= $type ?>"><b><?= $available_filtertypes[$type] ?></b></label>
                    <input type="image" name="remove_filter" class="middle"
                        src="<?= Assets::image_path('icons/16/blue/minus.png') ?>"
                        title="<?= _('Filter entfernen') ?>" value="<?= $type ?>">
                    <br>

                    <select name="filter[<?= $type ?>][]" multiple="multiple" size="5">
                        <? foreach ($values[$type] as $key => $value): ?>
                            <? if (is_array($value)): ?>
                                <option value="<?= $key ?>" style="font-weight: bold;" <?= in_array($key, (array)@$filter[$type]) ? 'selected="selected"' : '' ?>><?= $value['name'] ?></option>
                                <? foreach ($value['values'] as $k => $v): ?>
                                    <option value="<?= $k ?>" style="padding-left: 10px;" <?= in_array($k, (array)@$filter[$type]) ? 'selected="selected"' : '' ?>><?= $v ?></option>
                                <? endforeach; ?>
                            <? else: ?>
                                <option value="<?= $key ?>" <?= in_array($key, (array)@$filter[$type]) ? 'selected="selected"' : '' ?>><?= $value ?></option>
                            <? endif ?>
                        <? endforeach; ?>
                    </select>

                </td>
        <? $index++; endforeach; ?>
            <? if ($index%2 != 0): ?>
                <td>&nbsp;</td>
            <? endif ?>
            </tr>
        </table>
        <div class="type-button">
            <?= makebutton('eintragen', 'input', false, 'submit') ?>
            <input type="image" name="preview" class="middle" title="Vorschau"
                src="<?= Assets::image_path('icons/16/blue/question-circle.png') ?>">
        </div>
    </fieldset>
    <? endif ?>
</form>

<script type="text/javascript">
jQuery(function ($) {
    $('input[name=preview]').show().click(function (event) {
        if (!$(this).next().length || !$(this).next().is('span')) {
            $(this).after($('<span id="autoinsert_count" style="vertical-align: middle;"/>'));
        }
        $.getJSON('<?= $controller->url_for('admin/autoinsert/manual_count') ?>',
            $(this).closest('form').serializeArray(),
            function (json) {
                var result = "";
                if (!json || json.error) {
                    result  = "Fehler".toLocaleString() + ": ";
                    result += json.error
                       ? json.error.toLocaleString()
                       : "Fehler bei der �bertragung".toLocaleString();
                } else {
                    result  = "Gefundene Nutzer".toLocaleString() + ": ";
                    result += "<strong>" + json.users + "</strong>";
                }
                $('#autoinsert_count').html(result);
            }
        );
        event.preventDefault();
    });
    $('input[name=remove_filter]').click(function(event) {
        return confirm("Wollen Sie diesen Filter wirklich entfernen?".toLocaleString());
    });
});
</script>
<? endif ?>

<?
$aktionen[] = array(
    "text" => '<a href="'.$controller->url_for('admin/autoinsert').'">'._('Zur�ck zur Startseite').'</a>',
    "icon" => "icons/16/black/edit.png"
);
$aktionen[] = array(
    "text" => '<a href="'.$controller->url_for('admin/autoinsert/manual').'">'._('Benutzergruppen manuell eintragen').'</a>',
    "icon" => "icons/16/black/visibility-visible.png"
);

$infobox = array(
    'picture' => 'infobox/modules.jpg',
    'content' => array(
        array(
            'kategorie' => _("Aktionen"),
            'eintrag'   => $aktionen
        ),
        array(
            'kategorie' => _("Hinweise"),
            'eintrag'   => array(
                array(
                    "text" => _("Teilnehmer die bereits in ein und demselben Seminar eingetragen wurden, k�nnen nicht noch einmal eingetragen werden. Selbst wenn sie sich selbstst�ndig ausgetragen haben."),
                    "icon" => "icons/16/black/info.png"
                ),
                array(
                    "text" => _("Es k�nnen nur Veranstaltungen ausgew�hlt werden, in denen keine Zugangsbeschr�nkungen aktiviert wurden."),
                    "icon" => "icons/16/black/info.png"
                )
            )
        )
    )
);
