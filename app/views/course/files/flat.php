<h1>STUB File view</h1>
<ul>
<?
//for early development stage we MUST check which class is available:
if(class_exists('StudipDocument')) : ?>
<? foreach ($files as $file) : ?>
<li>
    <?= htmlReady($file->name) ?>
    <a href="<?= URLHelper::getLink('dispatch.php/file/delete', 'fileId' => $file->id) ?>">
        <?= Icon::create('trash', 'clickable')->asImg('12px') ?>
    </a>
</li>
<? endforeach ?>    
<? elseif(class_exists('File')) : ?>
<? foreach ($files as $file) : ?>
<li><?= 'TO BE DESIGNED' ?></li>
<? endforeach ?>
<? endif ?>
</ul>