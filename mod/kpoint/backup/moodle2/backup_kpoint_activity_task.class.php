<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/mod/kpoint/backup/moodle2/backup_kpoint_stepslib.php');    // Because it exists (must)
//require_once($CFG->dirroot.'/mod/book/backup/moodle2/backup_book_settingslib.php'); // Because it exists (optional)

class backup_kpoint_activity_task extends backup_activity_task
{

    /**
     * Define (add) particular settings this activity can have
     *
     * @return void
     */
    protected function define_my_settings()
    {
        // No particular settings for this activity
    }

    /**
     * Define (add) particular steps this activity can have
     *
     * @return void
     */
    protected function define_my_steps()
    {
        // book only has one structure step
        $this->add_step(new backup_kpoint_activity_structure_step('kpoint_structure', 'kpoint.xml'));
    }

    /**
     * Code the transformations to perform in the activity in
     * order to get transportable (encoded) links
     *
     * @param string $content
     * @return string encoded content
     */
    public static function encode_content_links($content)
    {
        global $CFG;

        $base = preg_quote($CFG->wwwroot, "/");

        // Link to the list of books
        $search  = "/($base\/mod\/kpoint\/index.php\?id=)([0-9]+)/";
        $content = preg_replace($search, '$@KPOINTINDEX*$2@$', $content);

        $search  = "/($base\/mod\/kpoint\/view.php\?id=)([0-9]+)/";
        $content = preg_replace($search, '$@KPOINTVIEWBYID*$2@$', $content);

        return $content;
    }
}
