<?

//Kein Javascript aktiviert, also �ber Select-Box arbeiten. Wir sind automatisch schon in Schritt 2 der
                //non-javascript-Suche.
if ($withButton) : ?>
<div style="width: <?= $box_width ?>px; background-color: #ffffff; border: 1px #999999 solid; display:inline-block">
<? $input_style = " style=\"width: ".($box_width-23)."px; background-color:#ffffff; border: 0px;\""; ?>
    <? if ($box_align === "left") : ?>
    <input class="text-bottom" type="image" src="<?= Assets::image_path("icons/16/blue/refresh.png") ?>">
    <? endif ?>
<? endif ?>
<select<?= $input_style .($inputClass ? " class=\"".$inputClass."\"" : "") ?> name="<?= $name ?>">
<? if (count($searchresults)) : ?>
  <? foreach ($searchresults as $result) : ?>
  <option value="<?= $result[0] ?>"><?= $result[1] ?></option>
  <? endforeach ?>
<? else : ?>
  <option value=""><?= _("Keine Treffer gefunden") ?></option> 
<? endif ?>
</select>
<? if ($withButton) : ?>
    <? if ($box_align !== "left") : ?>
    <input class="text-bottom" type="image" src="<?= Assets::image_path("icons/16/blue/refresh.png") ?>">
    <? endif ?>
    </div>
<? endif ?>