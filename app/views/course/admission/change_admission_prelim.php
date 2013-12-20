<? foreach(PageLayout::getMessages() as $pm) : ?>
    <?= $pm ?>
<? endforeach; ?>
<form class="studip_form" action="<?= $controller->link_for() ?>" method="post">
<?= CSRFProtection::tokenTag()?>
<? foreach($request as $k => $v) : ?>
    <?= addHiddenFields($k, $v) ?>
<? endforeach ?>
<?= Studip\Button::create(_("Ja"), 'change_admission_prelim_ok')?>
<?= Studip\Button::create(_("Nein"), 'change_admission_prelim')?>
</form>