<?php

namespace Widgets\FilesDashboard;

use Widgets\Element;
use Widgets\Response;

require_once 'BaseWidget.php';

/**
 * This widget shows the latest files a user may see.
 *
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 4.1
 */
class LatestFilesWidget extends BaseWidget
{
    // how many latest files should be shown by default
    const DEFAULT_LIMIT = 3;

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return _('Neue Dateien');
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return _('Zeigt die neuesten Dateien aus dem eigenen Dateibereich und den Dateibereichen der eigenen Veranstaltungen und Einrichtungen an.');
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function getFilesAndFolders(\Range $range, $scope)
    {
        $userId = $GLOBALS['user']->id;

        extract(
            array_reduce(
                $this->findTopFolders($userId),
                function ($result, $folder) use ($userId) {
                    $typedFolder = $folder->getTypedFolder();
                    if ($typedFolder->isReadable($userId)) {
                        $allFiles = \FileManager::getFolderFilesRecursive(
                            $typedFolder,
                            $userId,
                            true
                        );
                        $result['files'] = array_merge($result['files'], $allFiles['files']);
                        $result['folders'] = array_merge($result['folders'], $allFiles['folders']);
                    }

                    return $result;
                },
                ['files' => [], 'folders' => []]
            )
        );

        $files = array_slice($this->sortFilesByChdate($files), 0, 10);

        return compact('files', 'folders');
    }

    /**
     * This is a singleton widget - use at most once per container.
     *
     * {@inheritdoc}
     */
    public function mayBeDuplicated()
    {
        return false;
    }

    /**
    * This is a singleton widget - use at most once per container.
    *
    * {@inheritdoc}
     */
    public function mayBeRemoved()
    {
        return false;
    }

    // ***** CONFIGURATION *****

    /**
     * {@inheritdoc}
     */
    protected function hasConfiguration()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getConfigurationTemplate(Element $element, Response $response)
    {
        $response->addHeader('X-Title', _('"Neue Dateien" konfigurieren'));

        return $this->getTemplate('latest-config.php', []);
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function saveConfiguration(Element $element, Response $response)
    {
        $limit = \Request::int('limit', self::DEFAULT_LIMIT);
        $this->setOptions(array_merge($this->getOptions(), compact('limit')));

        $response->addHeader('X-Dialog-Close', 1);

        return false;
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultOptions()
    {
        return [
            'limit' => self::DEFAULT_LIMIT,
        ];
    }

    // ***** HELPERS *****

    private function sortFilesByChdate($files)
    {
        usort($files, function ($fileA, $fileB) {
            if ($fileA->chdate === $fileB->chdate) {
                return 0;
            }

            return $fileA->chdate > $fileB->chdate ? -1 : +1;
        });

        return $files;
    }

    private function findTopFolders($userId)
    {
        $folders = [\Folder::findTopFolder($userId)];
        $folders = array_merge($folders, $this->findMyCoursesTopFolders($userId));
        $folders = array_merge($folders, $this->findMyInstitutesTopFolders($userId));

        return array_filter($folders);
    }

    private function findMyCoursesTopFolders($userId)
    {
        return array_reduce(
            $this->findMyCourses($userId),
            function ($folders, $course) use ($userId) {
                if ($folder = \Folder::findTopFolder($course->id)) {
                    $folders[] = $folder;
                }

                return $folders;
            },
            []
        );
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function findMyCourses($userId)
    {
        $courses = [];
        if (!$GLOBALS['perm']->have_perm('admin')) {
            $query = 'SELECT seminare.*
                      FROM seminare
                      INNER JOIN seminar_user ON (seminar_user.Seminar_id = seminare.Seminar_id)
                      WHERE seminar_user.user_id = :user_id';
            if (\Config::get()->DEPUTIES_ENABLE) {
                $query .= ' UNION
                    SELECT `seminare`.*
                    FROM `seminare`
                    INNER JOIN `deputies` ON (`deputies`.`range_id` = `seminare`.`Seminar_id`)
                    WHERE `deputies`.`user_id` = :user_id';
            }
            $query .= ' ORDER BY duration_time = -1, start_time DESC, Name ASC';
            $statement = \DBManager::get()->prepare($query);
            $statement->execute([':user_id' => $userId]);

            foreach ($statement->fetchAll(\PDO::FETCH_ASSOC) as $coursedata) {
                $courses[] = \Course::buildExisting($coursedata);
            }
        }

        return $courses;
    }

    private function findMyInstitutesTopFolders($userId)
    {
        return array_reduce(
            $this->findMyInstitutes($userId),
            function ($folders, $institute) use ($userId) {
                if ($folder = \Folder::findTopFolder($institute->id)) {
                    $folders[] = $folder;
                }

                return $folders;
            },
            []
        );
    }

    private function findMyInstitutes($userId)
    {
        return \Institute::getMyInstitutes($userId);
    }
}
