<? use Studip\Button, Studip\LinkButton; ?>
<div id="edit_tour_step" class="edit_tour_step">
    <? if (Request::isXhr()) : ?>
        <? foreach (PageLayout::getMessages() as $msg) : ?>
            <?= $msg ?>
        <? endforeach ?>
    <? endif ?>
    <form id="edit_tour_form" class="default"
          action="<?= $controller->url_for('tour/edit_step/' . $tour_id . '/' . $step->step . '/save') ?>"
          method="post">
        <?= CSRFProtection::tokenTag(); ?>
        <fieldset>
            <input type="hidden" name="tour_step_nr" value="<?= $step->step ?>">
            <input type="hidden" name="tour_step_editmode" value="<?= $mode ?>">
            <legend><?= sprintf(_('Schritt %s'), $step->step) ?></legend>
            <label>
                <?= _('Titel:') ?>
                <input type="text" size="60" maxlength="255" name="step_title"
                       value="<?= $step ? htmlReady($step->title) : '' ?>"
                       placeholder="<?= _('Bitte geben Sie einen Titel f�r den Schritt an') ?>">
            </label>

            <label>
                <?= _('Inhalt:') ?>
                <textarea cols="60" rows="5" name="step_tip"
                          placeholder="<?= _('Bitte geben Sie den Text f�r diesen Schritt ein') ?>"><?= $step ? htmlReady($step->tip) : '' ?></textarea>
            </label>

            <label>
                <span class="required"><?= _('Art:') ?></span>
                <select name="step_interactive">
                    <option value="0" <?= $step->interactive == 0 ? ' selected="selected"' : '' ?>>
                        <?= _('Gef�hrt') ?>
                    </option>
                    <option value="1" <?= $step->interactive == 1 ? ' selected="selected"' : '' ?>>
                        <?= _('Interaktiv') ?>
                    </option>
                </select>
            </label>

            <? if ($force_route) : ?>
                <input type="hidden" name="step_route" value="<?= $force_route ?>">
                <input type="hidden" name="step_css" value="<?= $step->css_selector ?>">
            <? else : ?>
                <label for="step_route" class="caption">
                    <span class="required"><?= _('Seite:') ?></span>
                    <input type="text" size="60" maxlength="255" name="step_route"
                           value="<?= $step ? htmlReady($step->route) : '' ?>"
                           placeholder="<?= _('Route f�r den Schritt (z.B. "dispatch.php/profile")') ?>">
                </label>
                <label>
                    <?= _('CSS-Selektor:') ?>
                    <input type="text" size="60" maxlength="255" name="step_css"
                           value="<?= $step ? htmlReady($step->css_selector) : '' ?>"
                           placeholder="<?= _('Selektor, an dem der Schritt angezeigt wird') ?>"/>
                </label>

            <? endif ?>
        </fieldset>
        <? if ($step->css_selector) : ?>
            <fieldset>
                <legend><?= _('Orientierung:') ?></legend>
                <div class="tour_step_orientation"
                     style="<?= $step->css_selector ? 'display: block' : 'display: none' ?>">
                    <table>
                        <tr>
                            <td></td>
                            <td><input type="radio" name="step_orientation"
                                       value="TL" <?= ($step->orientation == 'TL') ? 'checked' : '' ?>><?= _('oben (links)') ?>
                            </td>
                            <td><input type="radio" name="step_orientation"
                                       value="T" <?= ($step->orientation == 'T') ? 'checked' : '' ?>><?= _('oben') ?>
                            </td>
                            <td><input type="radio" name="step_orientation"
                                       value="TR" <?= ($step->orientation == 'TR') ? 'checked' : '' ?>><?= _('oben (rechts)') ?>
                            </td>
                            <td></td>
                        </tr>
                        <tr>
                            <td><input type="radio" name="step_orientation"
                                       value="LT" <?= ($step->orientation == 'LT') ? 'checked' : '' ?>><?= _('links (oben)') ?>
                            </td>
                            <td colspan="3"></td>
                            <td><input type="radio" name="step_orientation"
                                       value="RT" <?= ($step->orientation == 'RT') ? 'checked' : '' ?>><?= _('rechts (oben)') ?>
                            </td>
                        </tr>
                        <tr>
                            <td><input type="radio" name="step_orientation"
                                       value="L" <?= ($step->orientation == 'L') ? 'checked' : '' ?>><?= _('links') ?>
                            </td>
                            <td colspan="3" style="text-align: center"><?= _('Selektiertes Element') ?></td>
                            <td><input type="radio" name="step_orientation"
                                       value="R" <?= ($step->orientation == 'R') ? 'checked' : '' ?>><?= _('rechts') ?>
                            </td>
                        </tr>
                        <tr>
                            <td><input type="radio" name="step_orientation"
                                       value="RB" <?= ($step->orientation == 'RB') ? 'checked' : '' ?>><?= _('links (unten)') ?>
                            </td>
                            <td colspan="3"></td>
                            <td><input type="radio" name="step_orientation"
                                       value="LB" <?= ($step->orientation == 'LB') ? 'checked' : '' ?>><?= _('rechts (unten)') ?>
                            </td>
                        </tr>
                        <tr>
                            <td></td>
                            <td><input type="radio" name="step_orientation"
                                       value="BL" <?= ($step->orientation == 'BL') ? 'checked' : '' ?>><?= _('unten (links)') ?>
                            </td>
                            <td><input type="radio" name="step_orientation"
                                       value="B" <?= ($step->orientation == 'B') ? 'checked' : '' ?>><?= _('unten') ?>
                            </td>
                            <td><input type="radio" name="step_orientation"
                                       value="BR" <?= ($step->orientation == 'BR') ? 'checked' : '' ?>><?= _('unten (rechts)') ?>
                            </td>
                            <td></td>
                        </tr>
                    </table>
                </div>
            </fieldset>
        <? endif ?>
        <footer data-dialog-button>
            <?= CSRFProtection::tokenTag() ?>
            <? if ($via_ajax): ?>
                <?= Button::create(_('Speichern'), 'confirm', ['data-dialog' => '1', 'data-dialog-button' => '1']) ?>
            <? else: ?>
                <?= Button::createAccept(_('Speichern'), 'submit') ?>
                <?= LinkButton::createCancel(_('Abbrechen'), $controller->url_for('tour/admin_overview'), []) ?>
            <? endif; ?>
        </footer>
    </form>
</div>
<script>
    jQuery(document).on('change', 'input[name=step_css]', function (event) {
        if (jQuery('input[name=step_css]').val())
            jQuery('.tour_step_orientation').show();
        else
            jQuery('.tour_step_orientation').hide();
    });
    if (STUDIP.Tour.started) {
        jQuery('#tour_controls').hide();
        jQuery('#tour_tip').hide();
        jQuery('#selector_overlay').hide();
    }
</script>