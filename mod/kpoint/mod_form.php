<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

/**
 * Module instance settings form
 */
class mod_kpoint_mod_form extends moodleform_mod
{

    /**
     * prepare form for kpoint module
     * @global type $CFG
     */
    private $embedcode;
    public function definition()
    {
        global $CFG;

        $mform = $this->_form;

        $mform->addElement('header', 'general', get_string('general', 'form'));
        $mform->addElement('text', 'name', get_string('name'), array('size'=>'48'));
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->setType('name', PARAM_RAW_TRIMMED);
        $mform->addElement('url', 'externalurl', get_string('selectvideo', 'kpoint'), array('size'=>'80'), array('usefilepicker'=>true));
        $mform->setType('externalurl', PARAM_RAW_TRIMMED);
        $mform->addRule('externalurl', null, 'required', null, 'client');
        $this->standard_intro_elements(get_string('moduleintro'));
        $this->standard_coursemodule_elements();
        $this->add_action_buttons();
    }

    /**
     * @param type $default_values
     */
    public function data_preprocessing(&$default_values)
    {
        parent::data_preprocessing($default_values);
    }

    /**
     * override this fn to update/process form data before saved into db
     *
     * @param type $data
     */
    public function data_postprocessing($data)
    {
        $data->embedcode = $this->embedcode;
    }

    /**
     * form validation
     *
     * @param type $data
     * @param type $files
     * @return type array
     */
    public function validation($data, $files)
    {
	global $USER,$DB;
        $videoId  = extract_video_id($data['externalurl']);
        $auth_via_accountno = get_config('kpoint', 'auth_via_accountno');
        $accountno ='';
        if($auth_via_accountno !='1') {
            $accountno ='';
        } else {
            $user_info_data         = $DB->get_record('user_info_data', array('userid' => ($USER->id),'fieldid' => (get_config('kpoint', 'user_account_no_field'))), '*', IGNORE_MISSING);
            if($user_info_data ) {
                $accountno =$user_info_data->data;
            } else {
                $accountno ='';
            }  
        }

        $objKp    = new \repository_kpoint\kpointapi_mdl($auth_via_accountno, $accountno);
        $response = $objKp->get_video_data($videoId, $USER->email, $USER->username);

        $embedCode = '';
        if ($response['error'] === FALSE && 
            !empty($response['response'])) {
                $arrData   = json_decode($response['response'], TRUE);
                if(isset($arrData['standard_embed_code'])) {
                    $embedCode = $arrData['standard_embed_code'];
                }
        } elseif($response['error'] === true) {
            $errors['externalurl']=$response['error_message'];
             
        }
        $data['embedcode'] = $embedCode;
        $this->embedcode = $data['embedcode'];

        $errors = parent::validation($data, $files);
        $errors = array();

        $external_url = trim($data['externalurl']);

        if(!(strpos($external_url, 'http://'.get_config('kpoint', 'domain').'/') === 0 ||
            strpos($external_url, 'https://'.get_config('kpoint', 'domain').'/') === 0)) {
            $errors['externalurl'] = get_string('urlnotmatching', 'kpoint');
        }
        if($data['embedcode']=='') {
           $errors['externalurl'] = get_string('generalerrormsg', 'kpoint');
        }
        return $errors;
    }
}
