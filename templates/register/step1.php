<section class="contentbox">
    <header>
        <h1><?= _('Stimmen Sie den Nutzungsbedinungen zu?') ?></h1>
    </header>
    <section>
        <?= _('Stud.IP ist ein Open Source Projekt und steht unter der Gnu General Public License (GPL). Das System befindet sich in der st�ndigen Weiterentwicklung.') ?>

        <? printf(_('F�r Vorschl�ge und Kritik findet sich immer ein Ohr. Wenden Sie sich hierzu entweder an die %sStud.IP Crew%s oder direkt an die %sEntwickler%s.'),
            "<a href=\"mailto:studip-users@lists.sourceforge.net\">", "</a>",
            "<a href=\"dispatch.php/siteinfo/show\">", "</a>") ?>
        <br><br>
        <?= _('Um den vollen Funktionsumfang von Stud.IP nutzen zu k�nnen, m�ssen Sie sich am System anmelden.') ?><br>
        <?= _('Das hat viele Vorz�ge:') ?><br>
        <blockquote>
            <ul>
                <li><?= _('Zugriff auf Ihre Daten von jedem internetf�higen Rechner weltweit,') ?>
                <li><?= _('Anzeige neuer Mitteilungen oder Dateien seit Ihrem letzten Besuch,') ?>
                <li><?= _('Eine eigenes Profil im System,') ?>
                <li><?= _('die M�glichkeit anderen Personen Nachrichten zu schicken oder mit ihnen zu chatten,') ?>
                <li><?= _('und vieles mehr.') ?></li>
        </blockquote>
        <br>
        </ul>

        <?= _('Mit der Anmeldung werden die nachfolgenden Nutzungsbedingungen akzeptiert:') ?>
    </section>
</section>

<? include('locale/' . $GLOBALS['_language_path'] . '/LC_HELP/pages/nutzung.html'); ?>

<footer>
    <div class="button-group">
        <?= Studip\LinkButton::create(_('Ich erkenne die Nutzungsbedingungen an'), URLHelper::getLink('register2.php')) ?>
        <?= Studip\LinkButton::create(_('Registrierung abbrechen'), URLHelper::getLink('index.php')) ?>
    </div>
</footer>