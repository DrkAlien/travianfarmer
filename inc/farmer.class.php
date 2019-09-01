<?php
#################################################################################
##                                                                             ##
##              -=  This is a travian farm scrip, it's free  =-                ##
##                                                                             ##
## --------------------------------------------------------------------------- ##
##                                                                             ##
##  Developer:     Halmagean Daniel                                            ##
##  Email:         halmageandaniel@yahoo.com                                   ##
##  Class Version: 1.0.0                                                       ##
##                                                                             ##
#################################################################################

class travianFarmer {

    public $setTroupsUrl = TRAVIAN_SERVER . '/build.php?id=39&tt=2&z='; // 39 - rally point, // 2 - send troups tab id
    public $serverLoginLink = TRAVIAN_SERVER . '/login.php'; // to post credentials, login url
    public $username = TRAVIAN_USER;
    public $password = TRAVIAN_PASSWORD;

    public $myCityIdFrom = MY_CITY_ID;
    public $timestamp; //
    public $timestamp_checksum; // something related to session
    public $a; // -//-
    public $currentCityId;
    public $cityIdToX;
    public $cityIdToY;
    public $troupsAmount = TROUPS_AMOUNT;

    /**
     * construct
     */
    public function _construct() { }

    /**
     * Login to travian
     */
    public function login() {
        # set the directory for the cookie using defined document root var
        $path = getcwd().'/';
        # login form action url
        $url = $this->serverLoginLink;
        $postinfo = "name=".$this->username."&password=".$this->password.'&s1=Conectare&w=1920:1080&login='.time().'&lowRes=0';
        $cookie_file_path = $path."/cookie.txt";
        // headers
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_NOBODY, false);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file_path);
        # set the cookie the site has for certain features, this is optional
        curl_setopt($ch, CURLOPT_COOKIE, "cookiename=0");
        curl_setopt($ch, CURLOPT_USERAGENT, USER_AGENT);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_REFERER, $this->serverLoginLink);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postinfo);
        curl_exec($ch);
        $login_info = curl_getinfo($ch);
        if($login_info['redirect_url'] == TRAVIAN_SERVER.'/dorf1.php') {
            return true;
        }
        return false;
    }

    /**
     *  Curl function
     */
    public function callCurl($url,$data,$type = 'GET') {

        $ch = curl_init($url);
        curl_setopt($ch,CURLOPT_USERAGENT,USER_AGENT);

        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_REFERER, TRAVIAN_SERVER.'/build.php?id=39&tt=2');
        # login cookie file
        curl_setopt($ch, CURLOPT_COOKIEFILE, getcwd().'/cookie.txt');

        if($type == 'POST') {
            $data_string = '';
            if(!empty($data)) {
                $data_string = http_build_query($data);

            }
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        }
        $r = curl_exec($ch);
        $info = curl_getinfo($ch);
        return $r;
    }

    /*
     * This step is where you set how many troups and what attack tipe
     * get timestamp and session shits
     */
    public function setTroups( $cityId = 0 ) {

        $this->currentCityId = $cityId;
        $url = $this->setTroupsUrl.$cityId;
        $html = $this->callCurl($url,array());
        // get timestamp
        $tag = '<input type="hidden" name="timestamp" value="';
        $startPos = stripos($html, $tag);
        if($startPos !== false) {
            $startPos += strlen($tag);
            $endPos = stripos($html, '"/>', $startPos);
            $value = trim(substr($html, $startPos, $endPos - $startPos));
        }
        $this->timestamp = $value;

        // get timestamp checksum
        $tag = '<input type="hidden" name="timestamp_checksum" value="';
        $startPos = stripos($html, $tag);
        if($startPos !== false) {
            $startPos += strlen($tag);
            $endPos = stripos($html, '"/>', $startPos);
            $value = trim(substr($html, $startPos, $endPos - $startPos));
        }
        $this->timestamp_checksum = $value;

        // get cityId X
        $tag = 'X:</label>';
        $startPos = stripos($html, $tag);
        if($startPos !== false) {
            $startPos += strlen($tag);
            $endPos = stripos($html, 'id="xCoordInput"', $startPos);
            $value = substr($html, $startPos, $endPos - $startPos);
            #echo '<pre/>_';print_r($endPos);exit;
            $value = trim($value);
            $value = trim(str_replace(array('<input type="text" maxlength="4" value="','name="x"'), '', $value));
            $value = trim($value,'"');
        }
        $this->cityIdToX = $value;
        // get cityId Y
        $tag = 'Y:</label>';
        $startPos = stripos($html, $tag);
        if($startPos !== false) {
            $startPos += strlen($tag);
            $endPos = stripos($html, 'id="yCoordInput"', $startPos);
            $value = substr($html, $startPos, $endPos - $startPos);
            $value=trim($value);
            $value = trim(str_replace(array('<input type="text" maxlength="4" value="','name="y"'), '', $value));
            $value = trim($value,'"');
        }
        $this->cityIdToY = $value;

        $data = array('timestamp'=>$this->timestamp,
                      'timestamp_checksum'=>$this->timestamp_checksum,
                      'b'=>'1',
                      'currentDid'=>$this->myCityIdFrom,
                      't1'=>$this->troupsAmount, // set to send only Clubswinger or first unit of the rase
                      't4'=>'',
                      't7'=>'',
                      't9'=>'',
                      't2'=>'',
                      't5'=>'',
                      't8'=>'',
                      't10'=>'',
                      't3'=>'',
                      't6'=>'',
                      't11'=>'',
                      'dname'=>'',
                      'x'=>$this->cityIdToX,
                      'y'=>$this->cityIdToY,
                      'c'=>'4', // raid
                      's1'=>'ok'
        );
        sleep(rand(3,5));
        $html = $this->callCurl(TRAVIAN_SERVER.'/build.php?id=39&tt=2', $data,'POST');
        return $html;
    }

    /**
     * After you set troups , you need to set attac: Raid and submit Send
     * submit data recived earlyer and get new data (timestamp...etc)
     */
    public function confirmAttac( $html = '') {

        // get timestamp
        $tag = '<input type="hidden" name="timestamp" value="';
        $startPos = stripos($html, $tag);
        if($startPos !== false) {
            $startPos += strlen($tag);
            $endPos = stripos($html, '" />', $startPos);
            $value = trim(substr($html, $startPos, $endPos - $startPos));
        }
        $this->timestamp = $value;

        // get timestamp checksum
        $tag = '<input type="hidden" name="timestamp_checksum"';
        $startPos = stripos($html, $tag);
        if($startPos !== false) {
            $startPos += strlen($tag);
            $endPos = stripos($html, '" />', $startPos);
            $value = trim(substr($html, $startPos, $endPos - $startPos));
            $value = str_replace('value="', '', $value);
            $value = trim($value);
        }
        $this->timestamp_checksum = $value;

        // get (a) value
        $tag = '<input type="hidden" name="a" value="';
        $startPos = stripos($html, $tag);
        if($startPos !== false) {
            $startPos += strlen($tag);
            $endPos = stripos($html, '" />', $startPos);
            $value = trim(substr($html, $startPos, $endPos - $startPos));
        }
        $this->a = $value;

        $data = array('redeployHero'=>'',
                      'timestamp'=>$this->timestamp,
                      'timestamp_checksum'=>$this->timestamp_checksum,
                      'id'=>'39',
                      'a'=>$this->a,
                      'b'=>'1',
                      'c'=>'4', // raid
                      'kid'=>$this->currentCityId,
                      't1'=>$this->troupsAmount, // set to send only Clubswinger or first unit of the rase
                      't2'=>'0',
                      't3'=>'0',
                      't4'=>'0',
                      't5'=>'0',
                      't6'=>'0',
                      't7'=>'0',
                      't8'=>'0',
                      't9'=>'0',
                      't10'=>'0',
                      't11'=>'0',
                      'dname'=>'0',
                      'sendReally'=>'0',
                      'troopsSent'=>'1',
                      'currentDid'=>$this->myCityIdFrom,
                      'b'=>'2',
                      'dname'=>'',
                      'x'=>$this->cityIdToX,
                      'y'=>$this->cityIdToY,
                      's1'=>'ok'
        );
        echo 'Attac on: #'.$this->currentCityId.' (x: '.$this->cityIdToX.', y: '.$this->cityIdToY.') sent. <br/>';
        sleep(rand(1,2));
        $html = $this->callCurl(TRAVIAN_SERVER.'/build.php?id=39&tt=2', $data,'POST');

    }

}