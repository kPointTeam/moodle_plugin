<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once($CFG->dirroot . '/repository/lib.php');
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');

/**
 * Repository configuration form
 */
class repository_kpoint extends repository
{
    //this value should not exceed 25,because kpoint put restriction to give max 25 records per request.
    const PER_PAGE = 20;

    public function __construct($repositoryid, $context = SYSCONTEXTID, $options = array())
    {
        global $SESSION, $CFG, $USER;

        $options['page']    = optional_param('p', 1, PARAM_INT);
        $options['ajax']    = true;

        parent::__construct($repositoryid, $context, $options);
    }

    /**
     * Add Plugin settings input to Moodle form
     * @param object $mform
     */
    public static function type_config_form($mform, $classname = 'repository')
    {
        global $CFG, $PAGE,$DB;

        $PAGE->requires->js('/repository/kpoint/js/kpoint.js');
        $PAGE->requires->js_init_call('get_moodleroot',array($CFG->wwwroot));
        $clientId = $secret = $domain = $email = $displayName = $accountNo = $enableAnalytics = $enableUserID = '';

        $clientId    = get_config('kpoint', 'client_id');
        $secret      = get_config('kpoint', 'secret');
        $domain      = get_config('kpoint', 'domain');
        $email       = get_config('kpoint', 'email');
        $displayName = get_config('kpoint', 'displayName');
        $accountNo = get_config('kpoint', 'accountNo');
        $user_account_no_field = get_config('kpoint', 'user_account_no_field');
        $auth_via_accountno = get_config('kpoint', 'auth_via_accountno');
        $enableAnalytics = get_config('kpoint', '$enable_analytics');
        $enableUserID = get_config('kpoint', '$enable_userid');
        if(empty($clientId)) {
            $clientId = '';
        }
        if(empty($secret)) {
            $secret = '';
        }
        if(empty($domain)) {
            $domain = '';
        }
        if(empty($email)) {
            $email = '';
        }
        if(empty($displayName)) {
            $displayName = '';
        }
        if(empty($accountNo)) {
            $accountNo = '';
        }
        if(empty($user_account_no_field)) {
            $user_account_no_field = '';
        }
        if(empty($auth_via_accountno)) {
            $auth_via_accountno = '';
        }
        if(empty($enableUserID)) {
            $enableUserID = '';
        }
        parent::type_config_form($mform);

        $strrequired = get_string('required');
        $mform->addElement('text', 'client_id', get_string('clientid', 'repository_kpoint'), array('value'=>$clientId,'size' => '40', 'maxlength' => 128));
        $mform->setType('client_id', PARAM_RAW_TRIMMED);
        $mform->addElement('text', 'secret', get_string('secret', 'repository_kpoint'), array('value'=>$secret,'size' => '40', 'maxlength' => 128));
        $mform->setType('secret', PARAM_RAW_TRIMMED);
        $mform->addElement('text', 'domain', get_string('domain', 'repository_kpoint'), array('value'=>$domain,'size' => '40', 'maxlength' => 128));
        $mform->setType('domain', PARAM_RAW_TRIMMED);
        $mform->addElement('static', null, '', get_string('lbl_info_domain', 'repository_kpoint'));
        $mform->addElement('text', 'email', get_string('email', 'repository_kpoint'), array('value'=>$email,'size' => '40', 'maxlength' => 128));
        $mform->setType('email', PARAM_RAW_TRIMMED);
        $mform->addElement('text', 'display_name', get_string('displayname', 'repository_kpoint'), array('value'=>$displayName,'size' => '40', 'maxlength' => 128));
        $mform->setType('display_name', PARAM_RAW_TRIMMED);
        $mform->addElement('checkbox', 'auth_via_accountno', get_string('auth_via_accountno', 'repository_kpoint'));
        $mform->addElement('text', 'account_no', get_string('account_no', 'repository_kpoint'), array('value'=>$accountNo,'size' => '40', 'maxlength' => 60));
        $mform->setType('account_no', PARAM_RAW_TRIMMED);
        $mform->addElement('select', 'user_account_no_field', get_string('user_accno_field', 'repository_kpoint'), $DB->get_records_select_menu('user_info_field', 'datatype="text"', null , $sort='', $fields='id,name', $limitfrom=0, $limitnum=0));
        $mform->addElement('checkbox', 'enable_userid', get_string('enable_userid', 'repository_kpoint'));
        $mform->addElement('checkbox', 'enable_analytics', get_string('enable_analytics', 'repository_kpoint'));
        $mform->hideIf('account_no', 'auth_via_accountno');
        $mform->hideIf('user_account_no_field', 'auth_via_accountno');
         
        $mform->addRule('client_id', $strrequired, 'required', null, 'client');
        $mform->addRule('secret', $strrequired, 'required', null, 'client');
        $mform->addRule('domain', $strrequired, 'required', null, 'client');
        $mform->addRule('email', $strrequired, 'required', null, 'client');
        $mform->addRule('display_name', $strrequired, 'required', null, 'client');
        $mform->addElement('button', 'btn_test', get_string('test_credentials', 'repository_kpoint'), array("onclick"=>"testCredentials();"));
        $mform->addElement('static', null, '', get_string('lbl_info_credentials', 'repository_kpoint'));
       
      
    }

    /**
     *
     * @param type $mform
     * @param type $data
     * @param type $errors
     * @return type
     */
    public static function type_form_validation($mform, $data, $errors)
    {
        return $errors;
    }

    /**
     * Names of the plugin settings
     * @return array
     */
    public static function get_type_option_names()
    {
        return array('client_id', 'secret', 'domain', 'email', 'display_name','auth_via_accountno', 'account_no', 'user_account_no_field', 'enable_userid', 'enable_analytics', 'pluginname');
    }

    /**
     * Get video listing matching search criteria
     *
     * @param type $path
     * @param type $page
     * @return type
     */
    public function get_listing($path = '', $page = '')
    {
        global $USER,$DB,$CFG;

        $userEmail       = $USER->email;
        $userDisplayName = $USER->username;
        $auth_via_accountno = get_config('kpoint', 'auth_via_accountno');
        $accountno ='';

        if($auth_via_accountno !='1') {
            $accountno ='';
        } else {
            $user_info_data         = $DB->get_record('user_info_data', array('userid' => ($USER->id),'fieldid' => (get_config('kpoint', 'user_account_no_field'))), '*', IGNORE_MISSING);
            if($user_info_data) {
                $accountno =$user_info_data->data;
            } else {
                $accountno ='';
            }
        }
        $enable_userid = get_config('kpoint', 'enable_userid');
        $objKp = new \repository_kpoint\kpointapi_mdl($auth_via_accountno, $accountno, $enable_userid);
        $page = ($page == '') ? 1 : $page;

        $arrData = [];

        if($objKp !== null) {
            $perPage    = self::PER_PAGE;
            $startIndex = ($page-1)*$perPage;

            $arrData = $objKp->get_data($userEmail, $userDisplayName, $this->keyword, $startIndex, $perPage, $this->searchfilter );
        }

        $list['list'] = $arrData;

        $list['page'] = $page;
        $list['pages'] = -1;
        $list['nologin'] = true;
        $list['nosearch'] = true;

        if(!empty($list['list'])) {
            $list['pages'] = -1; // means we don't know exactly how many pages there are but we can always jump to the next page
        } elseif ($list['page'] > 1) {
            $list['pages'] = $list['page']; // no images available on this page, this is the last page
        } else {
            $list['pages'] = 0; // no paging
        }

        return $list;
    }

    public function check_login()
    {
        global $SESSION;
        $this->keyword = optional_param('kpoint_keyword', '', PARAM_RAW);
        $this->searchfilter = optional_param('kpoint_searchfilter', '', PARAM_RAW);

        if(empty($this->keyword)) {
            $this->keyword = optional_param('s', '', PARAM_RAW);
        }
        $sess_keyword = 'kpoint_'.$this->id.'_keyword';
		$sess_searchfilter = 'kpoint_'.$this->id.'_searchfilter';
        if((empty($this->keyword) || empty($this->searchfilter)) && optional_param('page', '', PARAM_RAW)) {
            // This is the request of another page for the last search, retrieve the cached keyword.
            if (isset($SESSION->{$sess_keyword})) {
                $this->keyword = $SESSION->{$sess_keyword};
            }
            if (isset($SESSION->{$sess_searchfilter})) {
                $this->searchfilter = $SESSION->{$sess_searchfilter};
            }
        } elseif (!empty($this->keyword)) {
            // Save the search keyword in the session so we can retrieve it later.
            $SESSION->{$sess_keyword} = $this->keyword;
            $SESSION->{$sess_searchfilter} = $this->searchfilter;
        }
        return !empty($this->keyword);
    }

    public function print_login()
    {
        $keyword = new stdClass();
        $keyword->label = get_string('keyword', 'repository_kpoint').': ';
        $keyword->id    = 'input_text_keyword';
        $keyword->type  = 'text';
        $keyword->name  = 'kpoint_keyword';
        $keyword->value = '';

        
        $filter = new stdClass();
        $filter->label = get_string('searchfilter', 'repository_kpoint').': ';
        $filter->id    = 'input_text_searchfilter';
        $filter->type  = 'radio';
        $filter->name  = 'kpoint_searchfilter';
        $filter->value = '1|2';
        $filter->value_label = get_string('allvideo', 'repository_kpoint').'|'.get_string('myvideo', 'repository_kpoint'); 

        if($this->options['ajax']) {
            $form = array();
            $form['login'] = array($keyword, $filter);
            $form['nologin'] = true;
            $form['norefresh'] = true;
            $form['nosearch'] = true;
            $form['allowcaching'] = false; // indicates that login form can NOT
            // be cached in filepicker.js (maxwidth and maxheight are dynamic)
            return $form;
        } else {
            echo <<<EOD
<table>
<tr>
<td>{$keyword->label}</td><td><input name="{$keyword->name}" type="text" /></td>
</tr>
</table>
<input type="submit" />
EOD;
        }
    }
}

