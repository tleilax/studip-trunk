<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<? use Studip\Button, Studip\LinkButton; ?>
<form action="<?= $controller->url_for('document/administration/filter') ?>"
      method="post" class="studip_form">
    <table class="default">
    <thead>
    <tr>
        <th><?=_('Vorhandene Konfigurationen')?></th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td>
            <label for="showFilter"><?=_('Art der Konfiguration:')?>
                <select name="showFilter" id="showFilter">
                    <option value="all" <?if(isset($_SESSION['document_config_filter']) &&
                            $_SESSION['document_config_filter'] == 'all') : ?>selected<?endif;?>>
                            <?=_('Alle')?></option>
                    <option value="group" <?if(isset($_SESSION['document_config_filter']) &&
                            $_SESSION['document_config_filter'] == 'group') : ?>selected<?endif;?>>
                            <?=_('Gruppe')?></option>
                    <option value="individual" <?if(isset($_SESSION['document_config_filter']) &&
                            $_SESSION['document_config_filter'] == 'individual') : ?>selected<?endif;?>>
                            <?=_('Individuell')?></option>
                </select>
            </label>
        </td>
    </tr>
    <tbody>
    <tfoot>
        <tr>
            <td>
                <?= Button::create(_('Filtern'),'filter') ?>
            </td>
        </tr>
    </tfoot>    
    </table>
</form>
<!--show configurations-->
<table class="default">
    <colgroup>
        <col width="5%">
        <col width="7%">
        <col width="7%">
        <col width="10%">
        <col width="10%">
        <col width="5%">
        <col width="5%">
    </colgroup>
    <thead>
        <tr>
            <th><?= _('Konfiguration für:') ?></th>
            <th><?= _('Max. Uploadgröße') ?></th>
            <th><?= _('Nutzerquota') ?></th>
            <th><?= _('Untersagte Dateitypen') ?></th>
            <th><?= _('gesperrt')?></th>
            <th><?= _('Upload deaktiviert') ?></th>
            <th><?= _('Aktion') ?></th>
        </tr>
    </thead>
    <tbody>
        <?foreach($viewData['configs'] as $config) :?>
            <tr>
                <td><?= $config['name']?></td>
                <td><?= relsize($config['upload_quota'], false)?></td>
                <td><?= relsize($config['quota'], false)?></td>
                <td>
                    <? if(!empty($config['types'])) : ?>
                        <? foreach($config['types'] as $typ) : ?>
                            <?= htmlReady($typ['type']) ?>
                        <? endforeach;?>
                    <? endif;?>
                </td>
                <td>
                    <? if($config['closed'] == 1) : ?>
                        <a href="<?= $controller->url_for('document/administration/activateDocumentArea', $config['id']) ?>" <!--data-behaviour="ajax-toggle"--> 
                        <?= Assets::img('icons/16/blue/checkbox-checked', tooltip2(_('Dateibereich öffnen'))) ?></a>
                    <? else : ?>
                    <a data-lightbox href="<?= $controller->url_for('document/administration/deactivateDocumentArea', $config['id']) ?>" <!--data-behaviour="ajax-toggle"-->
                        <?= Assets::img('icons/16/blue/checkbox-unchecked', tooltip2(_('Dateibereich sperren'))) ?></a>
                    <? endif; ?>
                </td>
                <td>
                    <?if($config['forbidden'] == 1) : ?>
                        <a href="<?= $controller->url_for('document/administration/activateUpload', $config['id']) ?>" <!--data-behaviour="ajax-toggle"-->
                        <?= Assets::img('icons/16/blue/checkbox-checked', tooltip2(_('Upload aktivieren'))) ?></a>
                    <?else : ?>
                            <a href="<?= $controller->url_for('document/administration/deactivateUpload', $config['id']) ?>"<!--data-behaviour="ajax-toggle"-->
                        <?= Assets::img('icons/16/blue/checkbox-unchecked', tooltip2(_('Upload deaktivieren'))) ?></a>
                    <?endif;?>
                
                </td>
                <td>
                    <a data-lightbox href="<?= $controller->url_for('document/administration/edit/'.$config['id'])?>">
                        <?= Assets::img('icons/16/blue/edit')?></a>
                    <?if($config['name'] != 'default') :?>
                        <a href="<?= $controller->url_for('document/administration/delete/'.$config['id'])?>">
                        <?= Assets::img('icons/16/blue/trash')?></a>
                    <?endif;?>
                </td>
            </tr>
        <?endforeach;?>
    </tbody>
</table>
    