<?
if ($this->edit_perm) {
    $widget = new ActionsWidget();
    $widget->addLink(_('Abschnitt hinzufügen'), $controller->url_for('course/lti/edit/'), Icon::create('add'), ['data-dialog' => '']);
    if ($this->tools) {
        $widget->addLink(_('Link aus LTI-Tool einfügen'), $controller->url_for('course/lti/add_link'), Icon::create('add'), ['data-dialog' => 'size=auto']);
    }
    Sidebar::get()->addWidget($widget);
}

Helpbar::get()->addPlainText('', _('Auf dieser Seite können Sie externe Anwendungen einbinden, sofern diese den LTI-Standard (Version 1.x) unterstützen.'));
?>

<? if (empty($lti_data_array)): ?>
    <?= MessageBox::info(_('Es wurden noch keine Inhalte angelegt.')) ?>
<? endif ?>

<? foreach ($lti_data_array as $lti_data): ?>
    <? $launch_url = $lti_data->getLaunchURL() ?>
    <? if ($launch_url): ?>
        <? $lti_link = $controller->getLtiLink($lti_data) ?>
        <? $launch_data = $lti_link->getBasicLaunchData() ?>
        <? $signature = $lti_link->getLaunchSignature($launch_data) ?>
    <? endif ?>

    <section class="contentbox">
        <header>
            <h1>
                <?= htmlReady($lti_data->title) ?>
            </h1>

            <? if ($edit_perm): ?>
                <nav>
                    <form action="" method="POST">
                        <?= CSRFProtection::tokenTag() ?>
                        <? if ($lti_data->position > 0): ?>
                            <?= Icon::create('arr_2up')->asInput([
                                'formaction' => $controller->url_for('course/lti/move/' . $lti_data->position . '/up')
                            ]) ?>
                        <? endif ?>
                        <? if ($lti_data->position < count($lti_data_array) - 1): ?>
                            <?= Icon::create('arr_2down')->asInput([
                                'formaction' => $controller->url_for('course/lti/move/' . $lti_data->position . '/down')
                            ]) ?>
                        <? endif ?>

                        <?= Icon::create('edit')->asInput([
                            'formaction' => $controller->url_for('course/lti/edit/' . $lti_data->position),
                            'title' => _('Abschnitt bearbeiten'),
                            'data-dialog' => ''
                        ]) ?>
                        <?= Icon::create('trash')->asInput([
                            'formaction' => $controller->url_for('course/lti/delete/' . $lti_data->position),
                            'title' => _('Abschnitt löschen'),
                            'data-confirm' => sprintf(_('Wollen Sie wirklich den Abschnitt "%s" löschen?'), $lti_data->title)
                        ]) ?>
                    </form>
                </nav>
            <? endif ?>
        </header>

        <section>
            <?= formatReady($lti_data->description) ?>

            <? if ($launch_url && $lti_data->options['document_target'] == 'iframe'): ?>
                <iframe style="border: none; height: 640px; width: 100%;"
                        src="<?= $controller->url_for('course/lti/iframe', compact('launch_url', 'launch_data', 'signature')) ?>"></iframe>
            <? endif ?>
        </section>

        <? if ($launch_url && $lti_data->options['document_target'] != 'iframe'): ?>
            <footer>
                <form class="default" action="<?= htmlReady($launch_url) ?>" method="POST" target="_blank">
                    <? foreach ($launch_data as $key => $value): ?>
                        <input type="hidden" name="<?= htmlReady($key) ?>" value="<?= htmlReady($value) ?>">
                    <? endforeach ?>
                    <?= Studip\Button::create(_('Anwendung starten'), 'oauth_signature', ['value' => $signature]) ?>
                </form>
            </footer>
        <? endif ?>
    </section>
<? endforeach ?>
