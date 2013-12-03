<?use Studip\Button, Studip\LinkButton; ?>
<?if(isset($this->flash['error'])) : ?>
    <?= MessageBox::error($this->flash['error']) ?>
<?elseif (isset($this->flash['success'])) : ?>
    <?= MessageBox::success($this->flash['success']) ?> 
<?  endif;?>
<form action="<?= $controller->url_for('document/administration/individual') ?>" method="post">
<table class="default">
    

    <colgroup>
        <col width="33.3%">
        <col width="33.3%">
        <col width="33.3%">
    </colgroup>
    <thead>
        <tr>
            <th><?= _('Suche') ?></th>
            <th colspan="2"></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>
               <label><?=_('Benutzername')?></label><br>
               <input id="userName" name="userName">
            </td>
            <td>
                <label><?=_('Vorname')?></label><br>
                <input id="userVorname" name="userVorname">
            </td>
            <td>
                <label><?=_('Nachname')?></label><br>
                <input id="userNachname" name="userNachname">
            </td>
        </tr>
                <tr>
            <td>
               <label><?=_('E-Mail')?></label><br>
               <input id="userMail" name="userMail">
            </td>
            <td>
                <label><?=_('Nutzergruppe')?></label><br>
                <select id="userGroup" name="userGroup">
            <? foreach(array("alle","user", "autor", "tutor", "dozent", "admin", "root") as $one) : ?>
                <option value="<?= $one ?>"><?= $one ?></option>
            <? endforeach ?>                
                </select>
            </td>
            <td>
                <?= Button::createAccept(_('Suche'),'search') ?>
            </td>
        </tr>
    </tbody>
    
</table>    

<table class="default zebra-hover cronjobs">
    <colgroup><!--
        <col width="20px">
        <col width="40px">
        <col width="100px">
        <col width="30px">
        <col width="30px">
        <col width="30px">
        <col width="15px">
        <col width="5px">
        <col width="5px">
        <col width="30px">
        -->
        
        <col width="auto">
        <col width="auto">
        <col width="auto">
        <col width="auto">
        <col width="auto">
        <col width="auto">
        <col width="auto">
        <col width="auto">
        <col width="auto">
        <col width="auto">
        
    </colgroup>
    <thead>
        <tr>
            <th><?= _('Vorname') ?></th>
            <th><?= _('Nachname') ?></th>
            <th><?= _('Nutzername') ?></th>
            <th><?= _('E-Mail') ?></th>
            <th><?= _('Max. Upload') ?></th>
            <th><?= _('Nutzerquota') ?></th>
            <th><?= _('Untersagte Typen') ?></th>
            <th><?= _('Upload verboten') ?></th>
            <th><?= _('Bereich gesperrt') ?></th>
            <th><?= _('Aktion') ?></th>

        </tr>
    </thead>
    <tbody>
        <?foreach($this->viewData['users'] as $u) : ?>
                <tr>
                    <td><?=$u['vorname']?></td>
                    <td><?=$u['nachname']?></td>
                    <td><?=$u['username']?></td>
                    <td><?=$u['email']?></td>
                    <td><?=$u['upload']?> <?=$u['upload_unit']?></td>                    
                    <td><?=$u['quota']?> <?=$u['quota_unit']?></td>
                    <td>
                        <?foreach($u['types'] as $typ) :?>
                            <?= $typ['type'] ?>
                        <? endforeach;?>
                    </td>
                    <td>
                        <input type="checkbox" name="box" disabled
                            <?if($u['forbidden']==1) :?> 
                                checked
                            <? endif;?>
                        >
                    </td>
                    <td>
                        <input type="checkbox" name="box" disabled
                            <?if($u['area_close']==1): ?>
                                checked
                                <? endif;?>
                           >
                    </td>
                    <td>
                        <a href="<?=$controller->url_for('document/administration/individualEdit/'.$u['user_id'])?>
                           "title="Erstellen oder Bearbeiten der individuellen Einstellungen">
                           <?=Assets::img('icons/16/blue/edit.png')?></a>
                        <?if($u['deleteIcon'] == 1) :?>
                        <br>
                            <a href="<?=$controller->url_for('document/administration/delete/'.$u['user_id'].'/userConfig')?>
                                "title="Einstellungen löschen"><?=Assets::img('icons/16/blue/trash.png')?></a>
                        <?  endif;?>
                        
                    </td>
                    </tr>
        <?  endforeach;?>
    </tbody>
    <tfoot>        
    </tfoot>
</table>
    </form>