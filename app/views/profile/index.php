<? if ($msg) : ?>
    <?= parse_msg($msg) ?>
<? endif ?>
<table class="default nohover">
    <tr>
        <td valign="top">
            <?= $avatar ?>
            <br>
            <br>
            <?= _('Profilbesuche:') ?>
            <?= object_return_views($current_user->user_id) ?>
            <br>
            <? if(!empty($score) && !empty($score_title)) :?>
                <br>
                <a href="<?=URLhelper::getLink("dispatch.php/score")?>" <?=tooltip(_("Zur Rangliste"))?>><?=_("Stud.IP-Punkte:")?> <?=$score?><br>
                    <?=_("Rang:")?> <?=$score_title?>
                </a>
            <? endif?>

        <?if ($current_user->username != $user->username) : ?>
            <?if (!$user->isFriendOf($current_user)) : ?>
                <br>
                <a href="<?= URLHelper::getLink($controller->url_for('profile/add_buddy?username=' . $current_user->username)) ?>">
                    <?=Icon::create('person', 'clickable', ['title' =>_("zu den Kontakten hinzuf�gen"), 'class' => 'middle'])->asImg(16)?>
                    <?=_("zu den Kontakten hinzuf�gen")?>
                </a>
            <? endif?>

                <br>
                <a  href="<?=URLHelper::getLink('dispatch.php/messages/write', array('rec_uname'=>$current_user->username))?>" data-dialog="button">
                    <?=Icon::create('mail', 'clickable', ['title' => _("Nachricht an Nutzer verschicken"), 'class' => 'middle'])->asImg(16) ?>
                    <?= _('Nachricht schreiben') ?>
                </a>
                
            <? if (class_exists('Blubber')): ?>
                <br>
                <a href="<?= URLHelper::getLink('plugins.php/blubber/streams/global', array('mention' => $current_user->username)) ?>">
                    <?= Icon::create('blubber', 'clickable', ['title' => _('Blubber diesen Nutzer an'), 'class' => 'middle'])->asImg(16) ?>
                    <?= _('Anblubbern') ?>
                </a>
            <? endif; ?>
        <?endif?>

            <br>
            <a href="<?=$controller->link_for("contact/vcard", array('user[]' => $current_user->username))?>">
                <?=Icon::create('vcard', 'clickable', ['title' => _("vCard herunterladen"), 'class' => 'middle'])->asImg(16)?>
                <?=_("vCard herunterladen")?>
            </a>

        <? if ($current_user->username != $user->username && $perm->have_perm('root')): ?>
            <br>
            <a href="<?=URLHelper::getLink('dispatch.php/admin/user/edit/'.$current_user->user_id)?>">
                <?=Icon::create('edit', 'clickable', ['title' => _('Diese Person bearbeiten'), 'class' => 'middle'])->asImg(16)?>
                <?=_('Dieses Konto bearbeiten')?>
            </a>
        <? endif; ?>
        </td>


        <td width="99%" valign="top" style="padding: 10px;">
            <h1><?= htmlReady($current_user->getFullname()) ?></h1>

            <? if(!empty($motto)) : ?>
                 <h3><?= htmlReady($motto) ?></h3>
            <?endif?>

            <? if (!get_visibility_by_id($current_user->user_id)) : ?>
                <? if ($current_user->user_id != $user->user_id) : ?>
                    <p>
                        <font color="red"><?= _("(Dieser Nutzer ist unsichtbar.)") ?></font>
                    </p>
                <? else : ?>
                    <p>
                        <font color="red"><?= _("(Sie sind unsichtbar. Deshalb k�nnen nur Sie diese Seite sehen.)") ?></font>
                    </p>
                <? endif ?>
            <? endif ?>
            <? if ($current_user->auth_plugin === null) : ?>
                <p>
                    <font color="red"><?= _("(vorl�ufiger Benutzer)") ?></font>
                </p>
            <? endif ?>
            <? if ($public_email != '') : ?>
                <b><?= _("E-Mail:") ?></b>
                <a href="mailto:<?= htmlReady($public_email) ?>"><?= htmlReady($public_email) ?></a>
                <br />
            <? endif ?>

            <? if(!empty($private_nr)) : ?>
                <b><?= _("Telefon (privat):") ?></b>
                <?= htmlReady($private_nr) ?>
                <br />
            <?endif?>

            <? if(!empty($private_cell)) : ?>
                <b><?= _("Mobiltelefon:") ?></b>
                <?= htmlReady($private_cell) ?>
                <br />
            <?endif?>

            <? if(!empty($skype_name)) : ?>
                <b><?= _("Skype:") ?></b>
                <? if($skype_status) : ?>
                    <img src="http://mystatus.skype.com/smallicon/<?= htmlReady($skype_name) ?>" style="vertical-align:middle;" width="16" height="16" alt="My status">
                <? else :?>
                    <?= Assets::img('icon_small_skype.gif', array('style' => 'vertical-align:middle;')) ?>
                <?endif?>
                <?= htmlReady($skype_name) ?>
                <br />
            <?endif?>

            <? if(!empty($privadr)) : ?>
                <b><?= _("Adresse (privat):") ?></b>
                <?= htmlReady($privadr) ?>
                <br />
            <?endif?>

            <? if(!empty($homepage)) : ?>
                <b><?= _("Homepage:") ?></b>
                <?= formatLinks($homepage) ?>
                <br />
            <?endif?>

            <? if ($perm->have_perm("root") && $current_user['locked']) : ?>
                <br>
                <b><font color="red"><?= _("BENUTZER IST GESPERRT!") ?></font></b>
                <br>
            <? endif ?>

            <? if(count($study_institutes) > 0): ?>
                <br><b><?=_("Wo ich studiere:")?></b><br>
                <? foreach($study_institutes as $inst_result) :?>
                    <a href="<?=URLHelper::getLink('dispatch.php/institute/overview', array('auswahl' => $inst_result["Institut_id"]))?>"><?=htmlReady($inst_result["Name"])?></a><br>
                <?endforeach?>
                    <br />
            <?endif?>

            <? if(count($institutes) > 0) : ?>
                <?= $this->render_partial("profile/working_place") ?>
            <? endif?>

            <? if($has_denoted_fields): ?>
                <br>
                * Diese Felder sind nur f�r Sie und Admins sichtbar.<br>
            <?endif?>
               <br>
            <? if (isset($kings)): ?>
                <?= $kings ?><br>
            <? endif; ?>
        <? if(!empty($shortDatafields)) : ?>
            <? foreach ($shortDatafields as $name => $entry) : ?>
                <strong><?= htmlReady($name) ?>:</strong>
                <?= $entry['content'] ?>
                <span class="minor"><?= $entry['visible'] ?></span>
                <br>
            <? endforeach ?>
        <?endif?>
        </td>
    </tr>
</table>
<br />

<?= $news ?>

<?= $dates ?>

<?= $votes ?>

<? if(!empty($ausgabe_inhalt)) : ?>
<? foreach($ausgabe_inhalt as $key => $inhalt) :?>
<section class="contentbox">
    <header>
        <h1><?= htmlReady($key) ?></h1>
    </header>
    <section>
        <?= formatReady($inhalt) ?>
    </section>
</section>
<?endforeach?>
<? endif?>

<? if ($current_user['perms'] == 'dozent' && !empty($seminare)) : ?>
    <?= $this->render_partial("profile/seminare") ?>
<? endif?>

<?if($show_lit && $lit_list) :?>
<section class="contentbox">
    <header>
        <h1><?= _('Literaturlisten') ?></h1>
    </header>
    <section>
        <?= formatReady($lit_list) ?>
    </section>
</section>
<?endif?>

<? if(!empty($longDatafields)) :?>
    <? foreach ($longDatafields as $name => $entry) : ?>
        <section class="contentbox">
        <header>
            <h1><?= htmlReady($name .' '. $entry['visible']) ?></h1>
        </header>
        <section>
            <?= formatReady($entry['content']) ?>
        </section>
    </section>
    <? endforeach ?>
<?endif?>

<?=$hompage_plugin?>

<?if(!empty($categories)) :?>
    <? foreach($categories as $cat) : ?>
    <section class="contentbox">
        <header>
            <h1><?= htmlReady($cat['head'].$cat['zusatz']) ?></h1>
        </header>
        <section>
            <?= formatReady($cat['content']) ?>
        </section>
    </section>
    <?endforeach?>
<? endif; ?>
