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
            `system` TINYINT(1) NOT NULL DEFAULT 0,
            `enabled` TINYINT(1) NOT NULL DEFAULT 1,
            `mkdate` INT NOT NULL DEFAULT 0,
            `chdate` INT NOT NULL DEFAULT 0,
            PRIMARY KEY (`id`))");
        // Add the default steps:
        // Step 1: Basic data.
        if (!CourseWizardStepRegistry::findByClassName('CourseWizardBasicData')) {
            $step1 = new CourseWizardStepRegistry();
            $step1->name = 'Grunddaten';
            $step1->classname = 'BasicDataWizardStep';
            $step1->number = 1;
            $step1->system = 1;
            $step1->enabled = 1;
            $step1->store();
        }
        // Step 2: Study area assignment (there are course classes requiring this).
        if (!CourseWizardStepRegistry::findByClassName('CourseWizardStudyAreas')) {
            $step2 = new CourseWizardStepRegistry();
            $step2->name = 'Studienbereiche';
            $step2->classname = 'StudyAreasWizardStep';
            $step2->number = 2;
            $step2->system = 1;
            $step2->enabled = 1;
            $step2->store();
        }
    }

    function down()
    {
        DBManager::get()->exec("DROP TABLE IF EXISTS `coursewizardsteps`");
    }

}
