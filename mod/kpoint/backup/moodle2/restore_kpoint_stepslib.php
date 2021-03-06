<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Structure step to restore one choice activity
 */
class restore_kpoint_activity_structure_step extends restore_activity_structure_step
{
    protected function define_structure()
    {
        $paths = array();
        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('kpoint', '/activity/kpoint');

        /*
        $paths[] = new restore_path_element('choice_option', '/activity/choice/options/option');
        if ($userinfo) {
            $paths[] = new restore_path_element('choice_answer', '/activity/choice/answers/answer');
        }*/

        // Return the paths wrapped into standard activity structure
        return $this->prepare_activity_structure($paths);
    }

    protected function process_kpoint($data)
    {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        $data->timeopen = $this->apply_date_offset($data->timeopen);
        $data->timeclose = $this->apply_date_offset($data->timeclose);

        // insert the choice record
        $newitemid = $DB->insert_record('kpoint', $data);
        // immediately after inserting "activity" record, call this
        $this->apply_activity_instance($newitemid);
    }
    /*
    protected function process_choice_option($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->choiceid = $this->get_new_parentid('choice');

        $newitemid = $DB->insert_record('choice_options', $data);
        $this->set_mapping('choice_option', $oldid, $newitemid);
    }

    protected function process_choice_answer($data) {
        global $DB;

        $data = (object)$data;

        $data->choiceid = $this->get_new_parentid('choice');
        $data->optionid = $this->get_mappingid('choice_option', $data->optionid);
        $data->userid = $this->get_mappingid('user', $data->userid);

        $newitemid = $DB->insert_record('choice_answers', $data);
        // No need to save this mapping as far as nothing depend on it
        // (child paths, file areas nor links decoder)
    }*/

    protected function after_execute()
    {
        // Add choice related files, no need to match by itemname (just internally handled context)
        $this->add_related_files('mod_kpoint', 'intro', null);
    }
}
