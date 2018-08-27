<form class="default" method="post"
      data-dialog="<?= $show_wiki_page_form ? 'reload-on-close' : '1' ?>"
      action="<?= URLHelper::getLink('dispatch.php/wiki/import/' . $course->id) ?>">
    <?= CSRFProtection::tokenTag() ?>

    <? if (!$show_wiki_page_form and !$success): ?>
        <fieldset>
            <legend><?= _('Suche nach Veranstaltungen') ?></legend>
            <label>
                <?= _('Bitte wählen Sie eine Veranstaltung aus.') ?>
                <?= $course_search->render() ?>
            </label>
        </fieldset>
    <? endif ?>

    <? if ($show_wiki_page_form): ?>
        <input type="hidden" name="selected_course_id"
               value="<?= htmlReady($selected_course->id) ?>">
        <? if ($wiki_pages): ?>
            <table class="default">
                <caption>
                    <?= sprintf(
                        _('%s: Importierbare Wikiseiten'),
                        $selected_course->getFullName()
                    ) ?>
                </caption>
                <tr>
                    <th>
                        <input type="checkbox"
                               data-proxyfor=":checkbox[name='selected_wiki_page_ids[]']">
                    </th>
                    <th><?= _('Seitenname') ?></th>
                </tr>
                <? foreach ($wiki_pages as $wiki_page): ?>
                    <tr>
                        <td>
                            <input type="checkbox"
                                   name="selected_wiki_page_ids[]"
                                   value="<?= htmlReady($wiki_page->id) ?>">
                        </td>
                        <td><?= htmlReady($wiki_page->keyword) ?></td>
                    </tr>
                <? endforeach ?>
            </table>
            <div data-dialog-button>
                <?= \Studip\Button::create(
                    _('Importieren'),
                    'import'
                ) ?>
            </div>
        <? else: ?>
            <?= MessageBox::info(
                _('Die gewählte Veranstaltung besitst keine Wikiseiten!')
            ) ?>
        <? endif ?>
    <? endif ?>
    <? if ($success): ?>
        <div data-dialog-button>
            <?= \Studip\LinkButton::create(
                _('Import neu starten'),
                URLHelper::getURL(
                    'dispatch.php/wiki/import/' . $course->id
                ),
                [
                    'data-dialog' => '1'
                ]
            ) ?>
        </div>
    <? endif ?>
</form>
