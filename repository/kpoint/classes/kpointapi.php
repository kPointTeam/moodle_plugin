<?php

namespace repository_kpoint;
/**
 * class for kpoint api
 */
class kpointapi
{
    /**
    * HTTP protocol to be used while making REST calls
    */
    private $HTTP_PROTO = "https://";
    /**
     * clientId
     * @var type string
     */
    private $clientId;

    /**
     * secret
     * @var type string
     */
    private $secret;

    /**
     * domain
     * @var type string
     */
    private $domain;
    
    /**
     * siteadminName
     * @var string
     */
    private $isAuthViaAccountno;
    
    /**
     * siteadminName
     * @var string
     */
    private $enableUserID;
    
    /**
     * siteadminName
     * @var string
     */
    private $accountNo;

    /**
     * siteadminEmail
     * @var string
     */
    private $siteadminEmail;

    /**
     * siteadminName
     * @var string
     */
    private $siteadminName;

    /**
     * api endpoint
     */
    const API_ENDPOINT            = '/api/v1/xapi/kapsule';
    const API_ENDPOINT_SEARCH     = '/api/v1/xapi/search';
    const API_ENDPOINT_VIERERSHIP = '/api/v1/xapi/kapsule/<VIDEOID>/viewership';
    const API_ENDPOINT_VIEW       = '/api/v1/xapi/view';

    /**
     * constructor to initialize object with required params
     *
     * @param type $clientId
     * @param type $secret
     * @param type $domain
     * @param type $email
     * @param type $displayName
     */
    public function __construct($clientId, $secret, $domain, $isAuthViaAccountno,$accountNo='', $enable_userid, $siteadminEmail = '', $siteadminName = '')
    {
        $this->clientId            = $clientId;
        $this->secret              = $secret;
        $this->domain              = $domain;
        $this->isAuthViaAccountno  = $isAuthViaAccountno;
        $this->accountNo           = $accountNo;
        $this->siteadminEmail      = $siteadminEmail;
        $this->siteadminName       = $siteadminName;
        $this->enableUserID        = $enable_userid;
    }

    /**
     * generate token for authentication
     *
     * @return type string
     */
    public function generate_token($email, $displayName, $challenge = null)
    {
        if (!$challenge) {
            $challenge = time();
        }

        $b64token = $this->get_xauth_token($email, $displayName, $challenge);

        $xtencode="";
        if($this->isAuthViaAccountno == '1'){
            $user_id = '';
            if($this->enableUserID == '1') {
                $user_id = '&user_id='.$this->accountNo;
            }
            $xtencode= "client_id=$this->clientId&user_email=$email&user_name=".rawurlencode($displayName)."&challenge=$challenge&account_number=$this->accountNo".$user_id."&xauth_token=$b64token";
        } else {
            $xtencode= "client_id=$this->clientId&user_email=$email&user_name=".rawurlencode($displayName)."&challenge=$challenge&xauth_token=$b64token";
        }
        
        $xt = base64_encode($xtencode);
        $xt = str_replace("=", "", $xt);
        $xt = str_replace("+", "-", $xt);
        $xt = str_replace("/", "_", $xt);

        return $xt;
    }

    /**
     * function to get xauth token
     *
     * @param type $email
     * @param type $displayName
     * @return type
     */
    public function get_xauth_token($email, $displayName, $challenge = null)
    {
        global $DB;
        if (!$challenge) {
            $challenge = time();
        }
        $data = "";
        if($this->isAuthViaAccountno == '1'){
            $data = "$this->clientId:$email:$displayName:$challenge:$this->accountNo";
        } else {
            $data = "$this->clientId:$email:$displayName:$challenge";
        }            
        
        $xauth_token = hash_hmac("md5", $data, $this->secret, true);

        $b64token = base64_encode($xauth_token);
        $b64token = str_replace("=", "", $b64token);
        $b64token = str_replace("+", "-", $b64token);
        $b64token = str_replace("/", "_", $b64token);

        return $b64token;
    }

    /**
     * call kpoint search api to fetch data
     *
     * @param type $qText
     * @param type $first
     * @param type $max
     * @return type array
     */
    public function get_data($email, $displayName, $qText = '', $first = 0, $max = 25, $searchfilter = '')
    {
        //  prepare query params
        $queryParams = $this->get_query_params($qText, $first, $max);

        // append auth token in query
        $queryParams .= '&xt='.$this->generate_token($email, $displayName);

        // prepare api call
        $apiURL = $this->HTTP_PROTO.$this->domain.self::API_ENDPOINT_SEARCH.'?type=videos&'.$queryParams;
        
        if($searchfilter == 2) {
            $apiURL .='&only_my=1';
        }
        
        return $this->call_api($apiURL);
    }

    /**
     * prepare search query based on given params
     *
     * @param type $qText
     * @param type $first
     * @param type $max - per page elements
     * @return string
     */
    private function get_query_params($qText, $first, $max)
    {
        $queryParams = '';
        if(!empty($qText)) {
            $queryParams .= 'qtext='.rawurlencode($qText);
        }

        $queryParams .= '&first='.$first;
        $queryParams .= '&max='.$max;
        
        return $queryParams;
    }

    /**
     * Get data from kpoint api for given video
     *
     * @param type $videoId
     * @return type array video json data
     */
    public function get_video_data($videoId, $email, $displayName)
    {
        $curl = curl_init();

        //  prepare api call
        $apiURL = $this->HTTP_PROTO.$this->domain.self::API_ENDPOINT.'/'.$videoId.'?'.'&xt='.$this->generate_token($email, $displayName);
        return $this->call_api($apiURL);
    }

    /**
     * Get data from kpoint api for given video
     *
     * @param type $videoId
     * @return type array video json data
     */
    public function get_video_viewership($videoId, $email,$details = false)
    {
        $curl = curl_init();

        $apiURL = self::API_ENDPOINT_VIERERSHIP;
        $apiURL = str_replace('<VIDEOID>', $videoId, $apiURL);
        $detailParam = '';
        if($details) {
            $detailParam = '&details=true';
        }
        $apiURL = $this->HTTP_PROTO.$this->domain.$apiURL.'?by=user&email='.$email.'&xt='.$this->generate_token($this->siteadminEmail, $this->siteadminName).$detailParam;
        return $this->call_api($apiURL);
    }

    /**
     * prepare video viewership params
     *
     * @param string $userEmail
     * @return string
     */
    public function prepare_viewership_params($userEmail, $details, $via_xauthTocken= false)
    {
        $challenge = time();

        $detailParam = '';
        if($details) {
            $detailParam = '&details=true';
        }
        
        return "by=user&user_name=".rawurlencode($this->siteadminName)."&client_id=".$this->clientId.
                "&user_email=".$this->siteadminEmail."&email=".$userEmail.
                "&challenge=".$challenge.
                "&xauth_token=".$this->get_xauth_token($this->siteadminEmail, $this->siteadminName, $challenge, $via_xauthTocken).$detailParam;
        
    }

    /**
     * Get data from kpoint api for given video
     *
     * @param type $kvtocken
     * @return type array video json data
     */
    public function get_video_view($kvtocken)
    {
        $curl        = curl_init();
        $apiURL      = self::API_ENDPOINT_VIEW;
        $queryParams = $kvtocken.'?xt='.$this->generate_token($this->siteadminEmail, $this->siteadminName);

        //prepare api call
        $apiURL      = $this->HTTP_PROTO.$this->domain.$apiURL.'/'.$queryParams;
        return $this->call_api($apiURL);
    }


    /**
     * Get data from api
     *
     * @param type $apiURL
     * @return type array video json data,error
     */
    public function call_api($apiURL)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $apiURL,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
            "cache-control: no-cache"
            ),
        ));
        
        $response = curl_exec($curl);
        $err      = curl_error($curl);
        curl_close($curl);
         if($err) {
            return ["error" => true, "error_message" => $err];
        } elseif (!empty($response->error)) {
            return ["error" => true, "error_message" => "Some error occured"]; // $response->error->message
        } else {
            return ["error" => false, "response" => $response];
        }
    }

    /**
     *
     * @return type string
     */
    public function get_client_id()
    {
        return $this->clientId;
    }

    /**
     *
     * @return type string
     */
    public function get_secret()
    {
        return $this->secret;
    }

    /**
     *
     * @return type string
     */
    public function get_domain()
    {
        return $this->domain;
    }
    
    /**
     *
     * @return type string
     */
    public function get_accountNo()
    {
        return $this->accountNo;
    }
    
    
    /**
     * set email
     * @return type
     */
    public function set_email($email)
    {
        $this->email = $email;
    }
}
