<div class="white" style="padding: 1ex;">
  <? if (isset($error_msg)): ?>
    <table style="width: 100%;">
      <? my_error($error_msg, '', 1, false, true) ?>
    </table>
  <? endif ?>
    <form action="<?= $controller->url_for('siteinfo/save') ?>" method="POST">
        <label for="rubric_name"><?= _('Titel der Kategorie')?></label><br>
  <? if($edit_rubric): ?>
        <input type="text" name="rubric_name" id="rubric_name"><br>

  <? else: ?>
        <select name="rubric_id">
      <? foreach ($rubrics as $option) : ?>
            <option value="<?= $option['rubric_id'] ?>"<? if($currentrubric==$option['rubric_id']){echo " selected";} ?>><?= $option['name'] ?></option>
      <? endforeach ?>
        </select><br>
        <label for="detail_name"><?= _('Seitentitel')?></label><br>
        <input style="width: 90%;" type="text" name="detail_name" id="detail_name"><br>
        <label for="content"><?= _('Seiteninhalt')?></label><br>
        <textarea style="width: 90%;height: 15em;" name="content" id="content"></textarea><br>
  <? endif ?>
        <?= makeButton("abschicken", "input") ?>
        <a href="<?= $controller->url_for('siteinfo/show/'.$currentrubric) ?>">
            <?= makeButton("abbrechen", "img") ?>
        </a>
    </form>
</div>
