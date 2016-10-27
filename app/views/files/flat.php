<h1>STUB File view</h1>
<ul>

<? if($files): ?>
<? foreach ($files as $file_ref) : ?>
    <li>
    <div>
        <a href="<?=$file_ref->getDownloadURL()?>"><?=htmlReady($file_ref->file->name)?></a>
        <a href="<?= $controller->url_for('file/edit/' . $file_ref->id) ?>"
            data-dialog="reload-on-close">
            <?= Icon::create('edit', 'clickable')->asImg('12px') ?>
        </a>
        <a href="<?= $controller->url_for('file/link/' . $file_ref->id) ?>"
            data-dialog="reload-on-close">
            <?= Icon::create('group', 'clickable')->asImg('12px') ?>
        </a>
        <a href="<?= $controller->url_for('file/delete/' . $file_ref->id) ?>"
            data-dialog="reload-on-close">
            <?= Icon::create('trash', 'clickable')->asImg('12px') ?>
        </a>
    </div>
    </li>
<? endforeach ?>
<? endif ?>

</ul>