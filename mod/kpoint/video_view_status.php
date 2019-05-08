<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use repository_kpoint;

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once($CFG->dirroot . '/lib/completionlib.php');
require_once($CFG->dirroot . '/mod/kpoint/lib.php');

defined('MOODLE_INTERNAL') || die();
$enable_analytics = get_config('kpoint', 'enable_analytics');
if($enable_analytics !='1') {
    $response = NULL;
    $response['isComplete'] = false;
    $response['isError']=false;
    echo json_encode($response);
    exit();
}
global $COURSE,$DB,$USER;

$videoID            = $_GET['videoID'];
$kvTocken           = $_GET['kvTocken'];
$videoDuration      = $_GET['duration'];
$courseModuleID     = $_GET['courseModuleID'];
$userEmail          = $USER->email;
$errormsg='';

$objKp = new \repository_kpoint\kpointapi_mdl();
$response = NULL;
$response['isError']=true;

//fetch viewership data from kpoint API
$response_savedViewership = $objKp->get_video_viewership($videoID, $userEmail, true,true);
$response_currentViewership = $objKp->get_video_view($kvTocken);

if (($response_savedViewership['error'] == false) && ($response_currentViewership['error'] == false)) {
    $content_savedViewership   = json_decode($response_savedViewership['response']);
    $content_currentViewership = json_decode($response_currentViewership['response']);
    if(!empty($content_savedViewership->{'error'})){
        $errormsg.='<br/>[Viewership Error: Video id->'.$videoID.', Error code->'.$content_savedViewership->{'error'}->{'code'}.', Error Message->'.$content_savedViewership->{'error'}->{'message'}.']';
    }
    if(!empty($content_currentViewership->{'error'})){
        $errormsg.='<br/>[View Error: Video id->'.$videoID.', Error code->'.$content_currentViewership->{'error'}->{'code'}.', Error Message->'.$content_currentViewership->{'error'}->{'message'}.']';
    }
    if(empty($errormsg)){
        $response['isError']=false;    
        $offset_per_videocomplete  = 95;
        $intervals                 = array();

        //push elements from Saved Viewership in intervals
        if(!empty($content_savedViewership[0]->{'heatmap'})) {
            for ($i = 0 ; $i < count($content_savedViewership[0]->{'heatmap'}) ; $i++) {
                $obj['s'] = $content_savedViewership[0]->{'heatmap'}[$i]->{'s'};
                $obj['e'] = $content_savedViewership[0]->{'heatmap'}[$i]->{'e'};
                array_push($intervals, $obj);
            }
        }
        //push elements from Current Viewership in intervals
        if(!empty($content_currentViewership->{'intervals'})) {
            for ($i = 0 ; $i < count($content_currentViewership->{'intervals'}) ; $i++) {
                $obj['s'] = $content_currentViewership->{'intervals'}[$i]->{'soff'};
                $obj['e'] = $content_currentViewership->{'intervals'}[$i]->{'eoff'};
                array_push($intervals, $obj);
            }
        }

        function cmp($a, $b)
        {
            if ($a == $b) {
                return 0;
            }
            return ($a['s'] < $b['s']) ? -1 : 1;
        }

        //sort $intervals by 's'
        usort($intervals, "cmp");

        //repeat unteal merging happens
        for ($i = 0 ; $i < count($intervals) ; $i++) {
            for ($j = ($i + 1) ; $j < count($intervals) ; $j++) {
                //merge 2 intervals
                if ($intervals[$j]['s'] <= $intervals[$i]['e']) {
                    if ($intervals[$j]['e'] > $intervals[$i]['e']) {
                        $intervals[$i]['e']=$intervals[$j]['e'];
                    }
                    $i--;
                    array_splice($intervals, $j, 1);
                    break;
                }
            }
        }
        $totalviewedtime=0;

        //count total video view time
        for ($i = 0; $i < count($intervals); $i++) {
            $totalviewedtime += $intervals[$i]["e"] - $intervals[$i]["s"];
        }

        $videoviewpercentage = (($totalviewedtime*100) / $videoDuration);
        //check video viewed completely or not
        $response['isComplete'] = false;

        if ($videoviewpercentage >= $offset_per_videocomplete) {
            $cm             = get_coursemodule_from_id('kpoint', $courseModuleID, 0, false, MUST_EXIST);
            $course         = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
            $completion     = new \completion_info($course);
            $completion->set_module_viewed($cm, $USER->id);
            $moduleinstance = $DB->get_record('kpoint', array('id' => $cm->instance), '*', MUST_EXIST);
            $modulecontext  = context_module::instance($cm->id);

            // Completion and trigger video end event.
            kpoint_videocompleted($moduleinstance, $course, $cm, $modulecontext);

            $response['isComplete'] = true;
        } else {
            $response['isComplete'] = false;
        }
    }

}
    

if(($response_savedViewership['error'] == true) || ($response_currentViewership['error'] == true) || (!(empty($errormsg)))) {
        $response['isError'] = true;
        $cm             = get_coursemodule_from_id('kpoint', $courseModuleID, 0, false, MUST_EXIST);
        $moduleinstance = $DB->get_record('kpoint', array('id' => $cm->instance), '*', MUST_EXIST);
        $modulecontext  = context_module::instance($cm->id);
        //$errormsg='';
        if($response_savedViewership['error'] == true) {
            $errormsg .='<br/>Viewership: '.$response_savedViewership['error_message'];
        }
        if($response_currentViewership['error'] == true) {
            $errormsg .='<br/>View: '.$response_currentViewership['error_message'];
        }
        // Trigger kpoint_log event.
        $params = array(
            'context' => $modulecontext,
            'objectid' => $moduleinstance>id,
            'other' => $errormsg
        );
        
        $event = \mod_kpoint\event\kpoint_log::create($params);
        $event->trigger();
}
echo json_encode($response);
