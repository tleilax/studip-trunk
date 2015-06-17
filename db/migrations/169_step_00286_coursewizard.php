<?php

require_once('lib/models/CourseWizardStepRegistry.php');

class Step00286CourseWizard extends Migration
{

    function description() {
        return 'new course creation wizard';
    }

    function up()
    {
        // Create table for step registry.
        DBManager::get()->execute("CREATE TABLE IF NOT EXISTS `coursewizardsteps` (
            `id` VARCHAR(32) NOT NULL,
            `name` VARCHAR(255) NOT NULL,
            `classname` VARCHAR(255) NOT NULL UNIQUE,
            `number` TINYINT(1) NOT NULL,
            `enabled` TINYINT(1) NOT NULL DEFAULT 1,
            `mkdate` INT NOT NULL DEFAULT 0,
            `chdate` INT NOT NULL DEFAULT 0,
            PRIMARY KEY (`id`))");
        // Add the default steps:
        // Step 1: Basic data.
        if (!CourseWizardStepRegistry::findByClassName('CourseWizardBasicData')) {
            CourseWizardStepRegistry::registerStep('Grunddaten', 'BasicDataWizardStep', 1, true);
        }
        // Step 2: Study area assignment (there are course classes requiring this).
        if (!CourseWizardStepRegistry::findByClassName('CourseWizardStudyAreas')) {
            CourseWizardStepRegistry::registerStep('Studienbereiche', 'StudyAreasWizardStep', 2, true);
        }
    }

    function down()
    {
        DBManager::get()->exec("DROP TABLE IF EXISTS `coursewizardsteps`");
    }

}
