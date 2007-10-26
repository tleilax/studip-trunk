<?php
    /**
     * This file contains the following classes: {@link SimpleCollector},
     * {@link SimplePatternCollector}.
     *
     * @author Travis Swicegood <development@domain51.com>
     * @package SimpleTest
     * @subpackage UnitTester
     * @version $Id$
     */
    
    /**
     * The basic collector for {@link GroupTest}
     *
     * @see collect(), GroupTest::collect()
     * @package SimpleTest
     * @subpackage UnitTester
     */
    class SimpleCollector {
    
        /**
         * Strips off any kind of slash at the end so as to normalise the path.
         * @param string $path    Path to normalise.
         * @return string         Path without trailing slash.
         */
        function _removeTrailingSlash($path) {
            if (substr($path, -1) == DIRECTORY_SEPARATOR) {
                return substr($path, 0, -1);
            } elseif (substr($path, -1) == '/') {
                return substr($path, 0, -1);
            } else {
                return $path;
            }
        }
    
        /**
         * Scans the directory and adds what it can.
         * @param object $test    Group test with {@link GroupTest::addTestFile()} method.
         * @param string $path    Directory to scan.
         * @see _attemptToAdd()
         */
        function collect(&$test, $path) {
            $path = $this->_removeTrailingSlash($path);
            if ($handle = opendir($path)) {
                while (($entry = readdir($handle)) !== false) {
                    if ($this->_isHidden($entry)) {
                        continue;
                    }
                    $this->_handle($test, $path . DIRECTORY_SEPARATOR . $entry);
                }
                closedir($handle);
            }
        }
    
        /**
         * This method determines what should be done with a given file and adds
         * it via {@link GroupTest::addTestFile()} if necessary.
         *
         * This method should be overriden to provide custom matching criteria,
         * such as pattern matching, recursive matching, etc.  For an example, see
         * {@link SimplePatternCollector::_handle()}.
         *
         * @param object $test      Group test with {@link GroupTest::addTestFile()} method.
         * @param string $filename  A filename as generated by {@link collect()}
         * @see collect()
         * @access protected
         */
        function _handle(&$test, $file) {
            if (is_dir($file)) {
                return;
            }
            $test->addTestFile($file);
        }
        
        /**
         *  Tests for hidden files so as to skip them. Currently
         *  only tests for Unix hidden files.
         *  @param string $filename        Plain filename.
         *  @return boolean                True if hidden file.
         *  @access private
         */
        function _isHidden($filename) {
            return strncmp($filename, '.', 1) == 0;
        }
    }
    
    /**
     * An extension to {@link SimpleCollector} that only adds files matching a
     * given pattern.
     *
     * @package SimpleTest
     * @subpackage UnitTester
     * @see SimpleCollector
     */
    class SimplePatternCollector extends SimpleCollector {
        var $_pattern;
    
        /**
         *
         * @param string $pattern   Perl compatible regex to test name against
         *  See {@link http://us4.php.net/manual/en/reference.pcre.pattern.syntax.php PHP's PCRE}
         *  for full documentation of valid pattern.s
         */
        function SimplePatternCollector($pattern = '/php$/i') {
            $this->_pattern = $pattern;
        }
    
        /**
         * Attempts to add files that match a given pattern.
         *
         * @see SimpleCollector::_handle()
         * @param object $test    Group test with {@link GroupTest::addTestFile()} method.
         * @param string $path    Directory to scan.
         * @access protected
         */
        function _handle(&$test, $filename) {
            if (preg_match($this->_pattern, $filename)) {
                parent::_handle($test, $filename);
            }
        }
    }
?>