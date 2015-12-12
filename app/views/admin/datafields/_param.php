<?
# Lifter010: TODO
?>
<form class="datafield_param" action="<?= $controller->url_for('admin/datafields/edit/'.$datafield_id) ?>" method="post" <?= $hidden ? 'style="display:none;"' : '' ?>>
    <?= CSRFProtection::tokenTag() ?>
    <textarea name="typeparam" data-dev="<?= htmlReady($typeparam) ?>" cols="15" rows="5" wrap="off"><?= htmlReady($typeparam) ?></textarea>
    <input type="hidden" name="datafield_id" value="<?= $datafield_id ?>" /><br>
    <?= Icon::create('accept', 'clickable', ['title' => _('Änderungen speichern')])->asInput(["type" => "image", "class" => "middle", "name" => "save"]) ?>
    <?= Icon::create('question-circle', 'clickable', ['title' => _('preview')])->asInput(["type" => "image", "class" => "middle", "name" => "preview", "style" => ($hidden?"display:none;":"")]); ?>
    <a class="cancel" href="<?= $controller->url_for('admin/datafields') ?>">
        <?= Icon::create('decline', 'clickable', ['title' => _('Bearbeitung abbrechen')])->asImg() ?>
    </a>
</form>
