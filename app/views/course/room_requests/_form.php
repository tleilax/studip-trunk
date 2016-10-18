<input type="hidden" name="room_request_form" value="1">
<? if (isset($new_room_request_type)) : ?>
    <input type="hidden" name="new_room_request_type" value="<?= $new_room_request_type ?>">
<? endif ?>
<?= MessageBox::info(_('Geben Sie den gew�nschten Raum und/oder Raumeigenschaften an. Ihre Raumanfrage wird von der
                    zust�ndigen Raumverwaltung bearbeitet.'),
    array(_('<b>Achtung:</b> Um sp�ter einen passenden Raum f�r Ihre Veranstaltung zu bekommen,
        geben Sie bitte <span style="text-decoration: underline">immer</span> die gew�nschten Eigenschaften mit an!')
    )) ?>

<section class="times-rooms-grid ">
    <section>
        <h2><?= _('Art des Wunsches') ?></h2>
        <article>
            <?= htmlready($request->getTypeExplained(), 1, 1); ?>
        </article>
    </section>
    <section>
        <h2><?= _('Bearbeitungsstatus') ?></h2>
        <article>
            <? if ($request->isNew()) : ?>
                <?= _("Diese Anfrage ist noch nicht gespeichert") ?>
            <? else : ?>
                <?= htmlReady($request->getStatusExplained()); ?>
            <? endif ?>
        </article>
    </section>
</section>


<div style="clear: both"></div>

<?
if ($request_resource_id = $request->getResourceId()) :
    $resObject = ResourceObject::Factory($request_resource_id);
?>
    <section style="margin: 20px 0;">
        <h2><?= _('Gew�nschter Raum') ?></h2>

        <p>
            <strong><?= htmlReady($resObject->getName()) ?></strong>
            <?= Icon::create('trash', 'clickable', ['title' => _('den ausgew�hlten Raum l�schen')])->asInput(['type' => 'image', 'style' => 'vertical-align:middle', 'name' => 'reset_resource_id']) ?>
            <? if($resObject->getPlainProperties(false, true)): ?>
            <?= tooltipIcon(_('Der ausgew�hlte Raum bietet folgende der w�nschbaren Eigenschaften:') . " \n" . $resObject->getPlainProperties(false, true)) ?>
            <? endif; ?>
        </p>
        <input type="hidden" name="selected_room" value="<?= htmlready($request_resource_id) ?>">
    </section>
<? endif ?>


<section class="times-rooms-grid ">
    <? if (!Config::get()->RESOURCES_DIRECT_ROOM_REQUESTS_ONLY) : ?>
    <section>
        <h2>
            <?= _("Raumeigenschaften angeben:") ?>
        </h2>
        <? if ($request->getCategoryId()) : ?>
            <? if (count($room_categories)) : ?>
                <label for="select_room_type">
                    <?= _('Gew�hlter Raumtyp') ?>
                </label>


                <select name="select_room_type" id="select_room_type" style="width: auto;" >
                    <? foreach ($room_categories as $rc) : ?>
                        <?= sprintf('<option value="%s" %s>%s </option>',
                            $rc["category_id"],
                            ($request->category_id == $rc["category_id"]) ? "selected" : "",
                            htmlReady($rc["name"])
                        ) ?>
                    <? endforeach ?>
                </select>
                <?= Icon::create("accept", "clickable", ['title' => _('Raumtyp ausw�hlen')])
                        ->asInput(['type'  => "image",
                                   'style' => "vertical-align:middle",
                                   'name'  => "send_room_type",
                                   'value' => _("Raumtyp ausw�hlen")]) ?>
                <?= Icon::create('refresh', 'clickable', ['title' => _('alle Angaben zur�cksetzen')])->asInput(["type" => "image", "style" => "vertical-align:middle", "name" => "reset_room_type"]) ?>


            <? endif ?>
            <? $props = $request->getAvailableProperties() ?>
            <? if (!empty($props)) : ?>
                <h4><?= _('Folgende Eigenschaften sind w�nschbar:') ?></h4>
                <? foreach ($props as $index => $prop) : ?>
                    <section>

                        <? if ($prop['type'] != 'bool') : ?>
                            <label for="<?= $prop['type'] ?>_<?= $index ?>">
                                <?= htmlReady($prop["name"]) ?>
                            </label>
                        <? endif ?>

                        <? if ($prop['type'] == 'bool') : ?>

                            <label for="bool_<?= $index ?>" class="horizontal">
                                <input type="checkbox" id="bool_<?= $index ?>"
                                       name="request_property_val[<?= $prop["property_id"] ?>]"
                                    <?= $request->getPropertyState($prop["property_id"]) ? "checked" : "" ?>>
                                <?= htmlReady($prop["name"]) ?>
                            </label>


                        <? elseif ($prop['type'] == 'num'): ?>
                            <? if ($prop['system'] == 2) : ?>
                                <input type="number" id="num_<?= $index ?>"
                                       name="request_property_val[<?= $prop["property_id"] ?>]"
                                       value="<?= htmlReady($request->getPropertyState($prop["property_id"]))  ?>">
                                <? if ($admission_turnout) : ?>
                                    <br>
                                    <label for="seats_are_admission_turnout">
                                    <input id="seats_are_admission_turnout" type="checkbox"
                                               name="seats_are_admission_turnout"
                                        <?= ($request->getPropertyState($prop["property_id"]) == $admission_turnout && $admission_turnout > 0) ? "checked" : "" ?>>

                                        <?= _('max. Teilnehmeranzahl �bernehmen') ?>
                                    </label>
                                <? endif ?>
                            <? else : ?>
                                <input id="num_<?= $index ?>" type="text" size="4" maxlength="4"
                                       name="request_property_val[<?= $prop["property_id"] ?>]"
                                       value="<?= htmlReady($request->getPropertyState($prop["property_id"]))  ?>">
                            <? endif ?>
                        <? elseif ($prop['type'] == 'text') : ?>
                            <textarea id="text_<?= $index ?>" name="request_property_val[<?= $prop["property_id"] ?>]"
                                      cols="30"
                                      rows="2"><?= htmlReady($request->getPropertyState($prop["property_id"])) ?></textarea>
                        <? else : ?>
                            <? $options = explode(";", $prop["options"]); ?>
                            <select id="select_<?= $index ?>" name="request_property_val[<?= $prop["property_id"] ?>]">
                                <option value="">--</option>
                                <? foreach ($options as $a) : ?>
                                    <option <?= ($request->getPropertyState($prop["property_id"]) == $a) ? "selected" : "" ?>
                                        value="<?= $a ?>"><?= htmlReady($a) ?></option>
                                <? endforeach ?>
                            </select>
                        <? endif ?>



                    </section>
                <? endforeach ?>
            <? endif ?>
        <? else : ?>
            <label for="select_room_type">
                <?= _('Bitte geben Sie zun�chst einen Raumtyp an, der f�r Sie am besten geeignet ist') ?>
            </label>
            <select name="select_room_type" id="select_room_type" style="width:auto;">
                <option value=""><?= _('bitte ausw�hlen') ?></option>
                <? foreach ($room_categories as $rc) : ?>
                    <option value="<?= $rc["category_id"] ?>"><?= htmlReady($rc["name"]) ?></option>
                <? endforeach ?>
            </select>
            <?= Icon::create("accept", "clickable", ['title' => _('Raumtyp ausw�hlen')])
                    ->asInput(['type'  => "image",
                               'style' => "vertical-align:middle",
                               'name'  => "send_room_type",
                               'value' => _("Raumtyp ausw�hlen")]) ?>
        <? endif ?>

        <? if ($request->category_id) : ?>
            <section>
                <?= Studip\Button::create('Passende R�ume suchen', 'search_properties') ?>
            </section>
        <? endif ?>
    </section>
    <? endif; ?>
    <section>
        <h2>
            <?= _('Raum suchen') ?>
        </h2>
        <? if (!empty($search_result)) : ?>
            <? if (count($search_result)) : ?>
                <p>
                    <strong><?= sizeof($search_result) ?></strong> <?= (!$search_by_properties ? _("R�ume gefunden:") : _("passende R�ume gefunden.")) ?>
                </p>
            <? endif ?>
            <div class="selectbox">
                <fieldset>
                    <? foreach ($search_result as $key => $val)  : ?>
                        <? $resObject = ResourceObject::Factory($key); ?>
                        <div class="flex-row">
                            <label class="horizontal" for="select_room_<?= $key ?>">
                                <? if ($val['overlap_status'] === "status-green") : ?>
                                    <?= Icon::create('accept', $val['overlap_status'])->asImg("20px", array('class' => "text-bottom")) ?>
                                <? elseif($val['overlap_status'] === "status-red") : ?>
                                    <?= Icon::create('decline', $val['overlap_status'])->asImg("20px", array('class' => "text-bottom")) ?>
                                <? else : ?>
                                    <?= Icon::create('question', $val['overlap_status'])->asImg("20px", array('class' => "text-bottom")) ?>
                                <? endif ?>
                                <?= htmlReady(my_substr($val['name'], 0, 50)); ?>
                                <? if($resObject->getPlainProperties(false, true)): ?>
                                <?= tooltipIcon(_('Der gefundene Raum bietet folgende Eigenschaften:') . " \n" . $resObject->getPlainProperties(false, true)) ?>
                                <? endif; ?>
                            </label>
                            <input type="radio" name="select_room" id="select_room_<?= $key ?>" value="<?= $key ?>">
                        </div>
                    <? endforeach ?>
                </fieldset>
            </div>
            <?= Studip\Button::create(_("Raum als Wunschraum ausw�hlen"), 'send_room') ?>
            <?= Studip\Button::create(_("neue Suche starten"), 'reset_room_search') ?>
            <? if ($search_by_properties) : ?>
                <p><strong><?= _('Diese R�ume erf�llen die Wunschkriterien, die Sie links angegeben haben.') ?></strong>
                </p>
            <? endif ?>
        <? else : ?>
            <? if($_REQUEST["search_exp_room"]) : ?>
                <p><strong><?= _('Keinen') ?></strong> <?= _('Raum gefunden') ?></p>
            <? endif ?>

        <? endif ?>
        <? if (!count($search_result)) : ?>
            <section>

                <input id="search_exp_room" type="text" size="30" maxlength="255" name="search_exp_room"
                       placeholder="Geben Sie zur Suche den Raumnamen ganz oder teilweise ein">
                <?= Icon::create('search', 'clickable', ['title' => _('Suche starten')])->asInput( ["type" => "image", "class" => "middle", "name" => "search_room"]) ?>
            </section>
        <? endif ?>
    </section>
</section>

<? if ($is_resources_admin) : ?>
    <section>
        <h2><?= _('Benachrichtigungen') ?></h2>


        <label for="reply_recipients_lecturer" class="horizontal">
            <input type="checkbox" name="reply_recipients" id="reply_recipients_lecturer"
                   value="lecturer" <?= ($request->reply_recipients == 'lecturer' ? 'checked' : '') ?>>
            <?= _('Benachrichtigung bei Ablehnung der Raumanfrage auch an alle Lehrenden der Veranstaltung senden') ?>
        </label>
    </section>
<? endif ?>

<section>
    <h2><?= _('Nachricht an den Raumadministrator') ?></h2>


        <textarea name="comment" cols="58" rows="4" placeholder="Weitere W�nsche oder Bemerkungen zur gew�nschten Raumbelegung"
                  style="width:90%"><?= htmlReady($request->getComment()); ?></textarea>
</section>
