<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace mod_kpoint\task;

use repository_kpoint;

require_once($CFG->dirroot .'/config.php');
require_once($CFG->dirroot . '/mod/kpoint/lib.php');

defined('MOODLE_INTERNAL') || die();

/**
 * scheduled task for kpoint mark activity completion
 */
class mark_activity extends \core\task\scheduled_task
{
    public function get_name()
    {
        // Shown in admin screens.
        return get_string('activity_mark', 'mod_kpoint');
    }

    public function execute()
    {
        $enable_analytics = get_config('kpoint', 'enable_analytics');
        if($enable_analytics !='1') {
            echo "\nAnalytics tracking is not enabled, exiting.\n\n";
            return true;
        }
        global $DB;

        try {
            $moduleId = $DB->get_field('modules', 'id', array('name'=>'kpoint'));
            $sql = "SELECT distinct lg.objectid, lg.objecttable, cm.id as cm_id, cm.module as cm_module,
                        cm.course as course_id, kp.id as activity_id, kp.name as activity_name,
                        kp.externalurl as externalurl, lg.userid as user_id,
                        u.username as username, u.email as user_email
                        FROM {logstore_standard_log} as lg
                        JOIN {course_modules} as cm on cm.id = lg.contextinstanceid
                        JOIN {user} as u on u.id = lg.userid
                        JOIN {kpoint} as kp on kp.id = lg.objectid
                        WHERE cm.module = $moduleId AND lg.action in ('started','viewed')
                        AND cm.id NOT IN (SELECT coursemoduleid FROM {course_modules_completion} WHERE userid = u.id and completionstate=1)";

            $records = $DB->get_recordset_sql($sql);
            $this->kpoint_mark_activity_complete($records);
        } catch (Exception $e) {
            echo "\n Exc :: ".$e->getMessage();
        }

        return true;
    }

    /**
    * function to mark activity complete
    *
    * @param type $records
    */
    public function kpoint_mark_activity_complete($records)
    {
        global $DB, $CFG;

        require_once $CFG->libdir.'/completionlib.php';
        
        if(!empty($records)) {
            $auth_via_accountno = get_config('kpoint', 'auth_via_accountno');
            $accountno ='';
            if($auth_via_accountno !='1') {
                $accountno ='';
            } else {
                $accountno = get_config('kpoint','account_no');
            }
            $enable_userid = get_config('kpoint', 'enable_userid');
            $objKp = new \repository_kpoint\kpointapi_mdl($auth_via_accountno,$accountno,$enable_userid);
            $msg='Cron running for: ';
            $moduleinstance = null;
            $modulecontext = null;
            $noofrecords=0;
            foreach ($records as $record) {
                $noofrecords++;
                $cm     = get_coursemodule_from_id('kpoint', $record->cm_id, 0, false, MUST_EXIST);
                $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
                $moduleinstance = $DB->get_record('kpoint', array('id' => $cm->instance), '*', MUST_EXIST);
                $modulecontext  = \context_module::instance($cm->id);
                
                $videoUrl = $record->externalurl;
                $videoId  = extract_video_id($videoUrl);

                $response = $objKp->get_video_viewership($videoId, $record->user_email);
                $content_response = null;
                if(!empty($response['response'])) {
                    $content_response   = json_decode($response['response']);
                } 
                
                echo "\n\nChecking for user: ".$record->user_email." (id:".$record->user_id.")"." for activity  ".$record->activity_name;
                $msg.='<br />[ '.$record->user_email.', '.$record->activity_name.', '.$videoId;
                
                if ($response['error'] === false &&
                   !empty($response['response']) && empty($content_response->{'error'})) {
                    $arrData = json_decode($response['response'], true);
                    if (!empty($arrData)) {
                        $arrData = $arrData[0];

                        $pcViewed = $arrData['percentage_viewed'];
                        $pcViewed = trim($pcViewed, '%');
                        $pcViewed = (int)$pcViewed;

                        if ($pcViewed > 95) {
                            $cm     = get_coursemodule_from_id('kpoint', $record->cm_id, 0, false, MUST_EXIST);
                            $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);

                            $completion = new \completion_info($course);
                            $completion->set_module_viewed($cm, $record->user_id);
                        }
                        
                        echo "\nViewed Percentage: ".$pcViewed."%";
                        $msg.=', '.$pcViewed.'%';
                    }
                } else {
                    
                    if($response['error'] === true && !empty($response['error_message'])){
                        $msg.=', Viewership Error: '.$response['error_message'];
                    }
                    
                    if(!empty($content_response->{'error'})) {
                        echo ' [Viewership Error:'.$content_response->{'error'}->{'message'}.']';
                        $msg.=', Viewership Error: ( Code:'.$content_response->{'error'}->{'code'}.', Message:'.$content_response->{'error'}->{'message'}.')';
                    }
                    
                }
                
                $msg.=' ]';
            }
            if($noofrecords>0) {
                $params = array(
                    'context' => \context_system::instance(),
                    'objectid' => $moduleinstance->id,
                    'other' => $msg
                );

                // Trigger kpoint_cron_log event
                $event = \mod_kpoint\event\kpoint_cron_log::create($params);
                $event->trigger();
            }
        }
    }
}
