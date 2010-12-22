<? if (isset($message)): ?>
  <?= $message ?>
<? endif ?>

<? if (count($domains) == 0) : ?>
    <?= MessageBox::info(_('Es sind keine Nutzerdom�nen vorhanden.')) ?>
<? else : ?>
<h3><?= _('Liste der Nutzerdom�nen') ?></h3>

<table class="default" style="margin-bottom: 1em;">
    <?= $this->render_partial('admin/domain/domains') ?>
</table>

<? endif ?>