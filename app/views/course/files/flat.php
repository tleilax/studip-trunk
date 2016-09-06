<h1>STUB File view</h1>
<ul>
<?
//for early development stage we MUST check which class is available:
if(class_exists('StudipDocument')) : ?>
<? foreach ($files as $file) : ?>
<li><?= $file->name ?></li>
<? endforeach ?>    
<? elseif(class_exists('File')) : ?>
<? foreach ($files as $file) : ?>
<li><?= 'TO BE DESIGNED' ?></li>
<? endforeach ?>
<? endif ?>
</ul>