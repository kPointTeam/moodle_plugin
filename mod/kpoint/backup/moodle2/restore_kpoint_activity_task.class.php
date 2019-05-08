<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once($CFG->dirroot . '/mod/kpoint/backup/moodle2/restore_kpoint_stepslib.php');

/**
 * Description of restore_kpoint_activity_task
 *
 * @author ktpl0105
 */
class restore_kpoint_activity_task extends restore_activity_task
{
    //put your code here
    /**
     * Define (add) particular settings this activity can have
     */
    protected function define_my_settings()
    {
        // No particular settings for this activity
    }

    /**
     * Define (add) particular steps this activity can have
     */
    protected function define_my_steps()
    {
        // Choice only has one structure step
        $this->add_step(new restore_kpoint_activity_structure_step('kpoint_structure', 'kpoint.xml'));
    }

    /**
     * Define the contents in the activity that must be
     * processed by the link decoder
     */
    public static function define_decode_contents()
    {
        $contents = array();

        $contents[] = new restore_decode_content('kpoint', array('intro'), 'kpoint');

        return $contents;
    }

    /**
     * Define the decoding rules for links belonging
     * to the activity to be executed by the link decoder
     */
    public static function define_decode_rules()
    {
        $rules = array();

        $rules[] = new restore_decode_rule('KPOINTVIEWBYID', '/mod/kpoint/view.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('KPOINTINDEX', '/mod/kpoint/index.php?id=$1', 'course');

        return $rules;
    }

    /**
     * Define the restore log rules that will be applied
     * by the {@link restore_logs_processor} when restoring
     * choice logs. It must return one array
     * of {@link restore_log_rule} objects
     */
    public static function define_restore_log_rules()
    {
        $rules = array();

        $rules[] = new restore_log_rule('kpoint', 'add', 'view.php?id={course_module}', '{kpoint}');
        $rules[] = new restore_log_rule('kpoint', 'update', 'view.php?id={course_module}', '{kpoint}');
        $rules[] = new restore_log_rule('kpoint', 'view', 'view.php?id={course_module}', '{kpoint}');

        return $rules;
    }

    /**
     * Define the restore log rules that will be applied
     * by the {@link restore_logs_processor} when restoring
     * course logs. It must return one array
     * of {@link restore_log_rule} objects
     *
     * Note this rules are applied when restoring course logs
     * by the restore final task, but are defined here at
     * activity level. All them are rules not linked to any module instance (cmid = 0)
     */
    public static function define_restore_log_rules_for_course()
    {
        $rules = array();

        $rules[] = new restore_log_rule('kpoint', 'view all', 'index.php?id={course}', null);

        return $rules;
    }
}
