<article id="globalsearch-hints">
    <section>
        <header>
            <h1><?= _('[STRG] + [Leertaste]') ?></h1>
        </header>
        <p>
            <?= _('Tastenkombination zum Öffnen und Schließen') ?>
        </p>
    </section>
    <section>
        <header>
            <h1><?= _('[ALT] oder Klick auf Überschrift') ?>
        </header>
        <p>
            <?= _('Erweitert die ausgewählte Suchkategorie. ' .
                ' Bei einem weiteren Klick wird an die entsprechende Vollsuche weitergeleitet.') ?>
        </p>
    </section>
    <section>
        <header>
            <h1><?= _('Dateisuche') ?></h1>
        </header>
        <p>
            <?= _('Die Dateisuche kann über einen Schrägstrich (/) verfeinert werden. ' .
                'Beispiel: "Meine Veranstaltung/Datei" zeigt alle Dateien, die das Wort ' .
                '"Datei" enthalten und in "Meine Veranstaltung" sind, an. Die ' .
                'Veranstaltung kann auch auf einen Teil (z.B. "Veran/Datei") oder auf ' .
                'die Großbuchstaben bzw. auch deren Abkürzung (z.B. "MV/Datei" oder ' .
                '"V/Datei") beschränkt werden.') ?>
        </p>
    </section>
    <section>
        <header>
            <h1><?= _('Platzhalter') ?></h1>
        </header>
        <p>
            <?= _('"_" ist Platzhalter für ein beliebiges Zeichen.') ?>
            <br>
            <?= _('"%" ist Platzhalter für beliebig viele Zeichen.') ?>
            <br>
            <?= _('"Me_er" findet Treffer für "Meyer" und "Meier". "M__er" findet ' .
                'zusätzlich auch "Mayer" und "Maier". "M%er" findet alle vorherigen ' .
                'Treffer, aber auch "Müller".') ?>
        </p>
    </section>
</article>
