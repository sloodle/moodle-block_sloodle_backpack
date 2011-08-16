<?php
$plugin->version  = 2011072301;
$plugin->requires = 2007021500;  // Requires Moodle 1.8

// For Moodle 2, we have to pretend we don't work on Moodle 1.9 or Moodle gets upset.
global $CFG;
if ($CFG->version > 2010060800) {
    $plugin->requires = 2010000000;  // Requires Moodle 2.0
}
?>
