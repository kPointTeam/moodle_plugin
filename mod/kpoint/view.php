<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use repository_kpoint;

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once('lib.php');

global $PAGE, $USER, $DB;
$PAGE->requires->js('/mod/kpoint/js/kpoint.js');

$id = optional_param('id', 0, PARAM_INT);
$s  = optional_param('s', 0, PARAM_INT);
$page_title = '';

if ($id) {
    $cm             = get_coursemodule_from_id('kpoint', $id, 0, false, MUST_EXIST);
    $course         = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $moduleinstance = $DB->get_record('kpoint', array('id' => $cm->instance), '*', MUST_EXIST);

    $page_title = ($moduleinstance) ? get_string('pluginname', 'kpoint').' :: '.$moduleinstance->name : get_string('kpoint', 'pluginname');
} elseif ($s) {
    $moduleinstance = $DB->get_record('mod_kpoint', array('id' => $n), '*', MUST_EXIST);
    $course         = $DB->get_record('course', array('id' => $moduleinstance->course), '*', MUST_EXIST);
    $cm             = get_coursemodule_from_instance('kpoint', $moduleinstance->id, $course->id, false, MUST_EXIST);
    print_error(get_string('missingidandcmid', mod_kpoint));
} else {
    print_error(get_string('missingidandcmid', mod_kpoint));
}
require_login($course, true, $cm);

$modulecontext = context_module::instance($cm->id);

$PAGE->set_url('/mod/kpoint/view.php', array('id' => $cm->id));
$PAGE->set_title($page_title);
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);

echo $OUTPUT->header();

if ($moduleinstance) {
    echo "<h3>".$moduleinstance->name."</h3>";
    echo "<div style='margin-bottom: 15px;'>".format_module_intro('kpoint', $moduleinstance, $cm->id)."</div>";
    echo "<div id='msg' style='display:none; background-color:#b7d7e1; color:#034f84; padding: 10px; margin-bottom: 15px;'></div>";
    $strEmbedCode = $moduleinstance->embedcode;
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

    $objKp = new \repository_kpoint\kpointapi_mdl($auth_via_accountno, $accountno);
    $authToken = $objKp->generate_token($USER->email, $USER->username);
    $strEmbedCode = preg_replace("/style=(\'|\")width:(\d+)px;height:(\d+)px(\'|\")/", "style='width:100%'", $strEmbedCode);

    $strAuthParam = 'data-video-params=\'{"xt":"'.$authToken.'"}\'';

    $strEmbedCode = str_replace('></div>', $strAuthParam." ></div> ", $strEmbedCode);

    echo '<div id="kp_video_embed">'.$strEmbedCode.'</div>';
    echo '<style>#kp_video_embed .player-controls.row {display:block;} #kp_video_embed .player-controls.row.sleep-over {display:none;} #kp_video_embed .col {flex-basis:auto;}</style>';
}

$activity_complete_msg=get_string('activity_competed', 'kpoint');
$general_error_msg=get_string('generalerrormsg', 'kpoint');
$wait_msg=get_string('wait_msg', 'kpoint');

//send coursemoduleID,activity comletion, general error and wait msg to kpoint.js
$PAGE->requires->js_init_call(
    'get_parameters',
    array($id, $activity_complete_msg, $general_error_msg,$wait_msg)
);
echo $OUTPUT->footer();
