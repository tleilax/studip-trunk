<h1>STUB File view</h1>
<ul>

<? foreach ($topfolder->file_refs as $fileref) : ?>
    <li>
    <div>
        <a href="<?=$fileref->getDownloadURL()?>"><?=htmlReady($fileref->file->name)?></a>
        <a href="<?= URLHelper::getLink('dispatch.php/file/edit', ['fileId' => $fileref->id]) ?>"
            data-dialog="reload-on-close">
            <?= Icon::create('edit', 'clickable')->asImg('12px') ?>
        </a>
        <a href="<?= URLHelper::getLink('dispatch.php/file/link', ['fileId' => $fileref->id]) ?>"
            data-dialog="reload-on-close">
            <?= Icon::create('group', 'clickable')->asImg('12px') ?>
        </a>
        <a href="<?= URLHelper::getLink('dispatch.php/file/delete', ['fileId' => $fileref->id]) ?>"
            data-dialog="reload-on-close">
            <?= Icon::create('trash', 'clickable')->asImg('12px') ?>
        </a>
    </div>
    </li>
<? endforeach ?>

<? foreach ($topfolder->subfolders as $folder) : ?>
    <? foreach ($folder->file_refs as $fileref) : ?>
    <li>
    <div>
        <a href="<?=$fileref->getDownloadURL()?>"><?=htmlReady($fileref->file->name)?></a>
        <a href="<?= URLHelper::getLink('dispatch.php/file/edit', ['fileId' => $fileref->id]) ?>"
            data-dialog="reload-on-close">
            <?= Icon::create('edit', 'clickable')->asImg('12px') ?>
        </a>
        <a href="<?= URLHelper::getLink('dispatch.php/file/link', ['fileId' => $fileref->id]) ?>"
            data-dialog="reload-on-close">
            <?= Icon::create('group', 'clickable')->asImg('12px') ?>
        </a>
        <a href="<?= URLHelper::getLink('dispatch.php/file/delete', ['fileId' => $fileref->id]) ?>"
            data-dialog="reload-on-close">
            <?= Icon::create('trash', 'clickable')->asImg('12px') ?>
        </a>
    </div>
    </li>
    <? endforeach ?>
<? endforeach ?>
