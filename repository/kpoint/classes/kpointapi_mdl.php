<?php

namespace repository_kpoint;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once(__DIR__ . '/kpointapi.php');

/**
 * class to override kpointapi to convert to moodle specific format
 */
class kpointapi_mdl extends kpointapi
{
    /**
     * constructor
     * @param type $email
     */
    public function __construct($isAuthViaAccountno = '',$accountNo = '',$enable_userid='')
    {
        $clientId       = get_config('kpoint', 'client_id');
        $secret         = get_config('kpoint', 'secret');
        $domain         = get_config('kpoint', 'domain');
        $siteadminEmail = get_config('kpoint', 'email');
        $siteadminName  = get_config('kpoint', 'display_name');
        $user_account_no_field = get_config('kpoint', 'user_account_no_field');
        
        parent::__construct($clientId, $secret, $domain ,$isAuthViaAccountno, $accountNo, $enable_userid, $siteadminEmail, $siteadminName);
    }

    /**
     * override generate_token
     */
    public function generate_token($email, $displayName, $challenge = null)
    {
        return parent::generate_token($email, $displayName, $challenge);
    }

    public function get_admin_xauth_token()
    {
        $email = get_config('kpoint', 'email');
        $displayName = get_config('kpoint', 'display_name');

        return parent::generate_admin_token($email, $displayName);
    }

    /**
     * override get_data in order to format in moodle way
     *
     * @param type $qText
     * @param type $first
     * @param type $max
     * @return type Array
     */
    public function get_data($email, $displayName, $qText = '', $first = 0, $max = 25, $searchfilter = '')
    {
        $arrData = parent::get_data($email, $displayName, $qText, $first, $max, $searchfilter);

        if ($arrData['error'] === false) {
            return $this->format_data($arrData['response']);
        } else {
            //TODO
            return [];
        }
    }

    /**
     * format response in moodle-way
     *
     * @param type $response
     * @return int
     */
    private function format_data($response)
    {
        $arrResponse = json_decode($response, true);

        $arrData = [];

        foreach ($arrResponse['list'] as $key => $val) {
            $arrData[] = [
                            'title' => $val['displayname'].'.mp4',
                            'source' => $val['app_url'],
                            'id' => $val['kapsule_id'],
                            'thumbnail' => $val['thumbnail_url'],
                            'thumbnail_width' => 100,
                            'thumbnail_height' => 100,
                            'date' => strtotime($val['time_publish']),
                            'url' => $val['share_url'],
                            'author' => (isset($val['author']) && !empty($val['author'])) ? $val['author'] : $val['owner_displayname'],
                            'size' => $val['size_mb']*1024*1024,
                            'license' => '',
                         ];
        }

        return $arrData;
    }

    /**
     * override parent function
     *
     * @param type $videoId
     * @return type
     */
    public function get_video_data($videoId, $email, $displayName)
    {
        return parent::get_video_data($videoId, $email, $displayName);
    }

    /**
     * override parent function
     *
     * @param type $videoId
     * @return type
     */
    public function get_video_viewership($videoId, $email, $details = false)
    {
        return parent::get_video_viewership($videoId, $email, $details);
    }

    /**
     * override parent function
     *
     * @param type $kvtocken
     * @return type array video json data
     */
    public function get_video_view($kvtocken)
    {
        return parent::get_video_view($kvtocken);
    }
}
