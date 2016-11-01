<?php
/**
 * @author Till Gl�ggler <tgloeggl@uos.de>
 */
class StudipVersion
{
    private $version;

    public function __construct()
    {
        $this->version = $GLOBALS['SOFTWARE_VERSION'];
    }


    /**
     * Returns true if passed version is newer than the current Stud.IP version
     *
     * @param string $version
     * @return bool
     */
    public function newerThan($version)
    {
        return (version_compare($this->version, $version, '>'));
    }

    /**
     * Returns true if passed version is older than the current Stud.IP version
     *
     * @param string $version
     * @return bool
     */
    public function olderThan($version)
    {
        return (version_compare($this->version, $version, '<'));
    }

    /**
     * Returns true if passed version matches the current Stud.IP version
     *
     * @param string $version
     * @return bool
     */
    public function matches($version)
    {
        return (version_compare($this->version, $version, '='));
    }

    /**
     * Returns true if version equals or is between the two passed versions
     *
     * @param string $from_version
     * @param string $to_version
     *
     * @return bool
     */
    public function range($from_version, $to_version)
    {
        return version_compare($this->version, $from_version, '>=')
                && version_compare($this->version, $to_version, '<=');
    }
}
