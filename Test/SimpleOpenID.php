<?php
/*
  FREE TO USE
  Simple OpenID PHP Class
  Latest update by Remy Sharp / http://remysharp.com (fixes)
  Contributed by http://www.fivestores.com/
  Updated by http://extremeswank.com/
-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

This Class was written to make easy for you to integrate OpenID on your website.
This is just a client, which checks for user's identity. This Class Requires CURL Module.
It should be easy to use some other HTTP Request Method, but remember, often OpenID servers
are using SSL.
We need to be able to perform SSL Verification on the background to check for valid signature.

HOW TO USE THIS CLASS:
STEP 1)
  $openid = new SimpleOpenID;
  :: SET IDENTITY ::
    $openid->SetIdentity($_POST['openid_url']);
  :: SET RETURN URL ::
    $openid->SetApprovedURL('http://www.yoursite.com/return.php'); // Script which handles a response from OpenID Server
  :: SET TRUST ROOT ::
    $openid->SetTrustRoot('http://www.yoursite.com/');
  :: FETCH SERVER URL FROM IDENTITY PAGE ::  [Note: It is recomended to cache this (Session, Cookie, Database)]
    $openid->GetOpenIDServer(); // Returns false if server is not found
  :: REDIRECT USER TO OPEN ID SERVER FOR APPROVAL ::

  :: (OPTIONAL) SET OPENID SERVER ::
    $openid->SetOpenIDServer($server_url); // If you have cached previously this, you don't have to call GetOpenIDServer and set value this directly

  STEP 2)
  Once user gets returned we must validate signature
  :: VALIDATE REQUEST ::
    true|false = $openid->ValidateWithServer();

  ERRORS:
    array = $openid->GetError();  // Get latest Error code

  FIELDS:
    OpenID allowes you to retreive a profile. To set what fields you'd like to get use (accepts either string or array):
    $openid->SetRequiredFields(array('email','fullname','dob','gender','postcode','country','language','timezone'));
     or
    $openid->SetOptionalFields('postcode');

IMPORTANT TIPS:
OPENID as is now, is not trust system. It is a great single-sign on method. If you want to
store information about OpenID in your database for later use, make sure you handle url identities
properly.
For example:
  https://steve.myopenid.com/
  https://steve.myopenid.com
  http://steve.myopenid.com/
  http://steve.myopenid.com
  ... are representing one single user. Some OpenIDs can be in format openidserver.com/users/user/ - keep this in mind when storing identities

  To help you store an OpenID in your DB, you can use function:
    $openid_db_safe = $openid->OpenID_Standarize($upenid);
  This may not be comatible with current specs, but it works in current enviroment. Use this function to get openid
  in one format like steve.myopenid.com (without trailing slashes and http/https).
  Use output to insert Identity to database. Don't use this for validation - it may fail.

*/

class SimpleOpenID{
  var $openid_url_identity;
  var $openid_url_type;
  var $openid_url_orig;
  var $URLs = array();
  var $error = array();
  var $fields = array();

  function SimpleOpenID(){
    if (!function_exists('curl_exec')) {
      die('Error: Class SimpleOpenID requires curl extension to work');
    }
  }
  function SetOpenIDServer($a){
    $this->URLs['openid_server'] = $a;
  }
  function SetTrustRoot($a){
    $this->URLs['trust_root'] = $a;
  }
  function SetCancelURL($a){
    $this->URLs['cancel'] = $a;
  }
  function SetApprovedURL($a){
    $this->URLs['approved'] = $a;
  }
  function SetPolicyURL($a) {
    $this->URLs['policyurl'] = $a;
  }
  function SetRequiredFields($a){
    if (is_array($a)){
      $this->fields['required'] = $a;
    }else{
      $this->fields['required'][] = $a;
    }
  }
  function SetOptionalFields($a){
    if (is_array($a)){
      $this->fields['optional'] = $a;
    }else{
      $this->fields['optional'][] = $a;
    }
  }
  function SetIdentity($a){   // Set Identity URL
    $this->openid_url_orig = $a;
    $this->openid_url_type = 1;

    $xriprefixes = array("xri://", "xri://\$ip*", "xri://\$dns*");
    $inameprefixes = array("=", "@", "+", "$", "!");

    foreach ($inameprefixes as $prefix) {
      if (substr($a, 0, 1) == $prefix) {
        $this->openid_url_type = 2;
        $this->openid_url_identity = $a;
        return;
      }
    }
    foreach ($xriprefixes as $prefix) {
      if(substr($a, 0, strlen($prefix)) == $prefix) {
        $a = substr($a, strlen($prefix), strlen($a)-strlen($prefix));
        $this->openid_url_type = 2;
        $this->openid_url_identity = $a;
        return;
      }
    }
    if(substr($a, 0, 7) != 'http://') {
      $a = 'http://'.$a;
      // RS change - append a slash - Wordpress example remysharp.wordpress.com - not found + slash = ok.
      if (substr($a, -1) != '/') $a .= '/';
      $this->openid_url_type = 1;
      $this->openid_url_identity = $a;
      return;
    }
    $this->openid_url_identity = $a;
  }
  function GetIdentity(){   // Get Identity
    return $this->openid_url_identity;
  }
  function GetError(){
    $e = $this->error;
    return array('code'=>$e[0],'description'=>$e[1]);
  }

  function ErrorStore($code, $desc = null){
    $errs['OPENID_NOSERVERSFOUND'] = 'Cannot find OpenID Server using this identity.';
    if ($desc == null){
      $desc = $errs[$code];
    }
      $this->error = array($code,$desc);
  }

  function IsError(){
    if (count($this->error) > 0){
      return true;
    }else{
      return false;
    }
  }

  function splitResponse($response) {
    $r = array();
    $response = explode("\n", $response);
    foreach($response as $line) {
      $line = trim($line);
      if ($line != "") {
        @list($key, $value) = explode(":", $line, 2);
        $r[trim($key)] = trim($value);
      }
    }
    return $r;
  }

  function OpenID_Standarize($openid_identity){
    if ($this->openid_url_type == 2) {
      return $openid_identity;
    }

    $u = parse_url(strtolower(trim($openid_identity)));
    if ($u['path'] == '/'){
      $u['path'] = '';
    }
    if(substr($u['path'],-1,1) == '/'){
      $u['path'] = substr($u['path'], 0, strlen($u['path'])-1);
    }
    if (isset($u['query'])){ // If there is a query string, then use identity as is
      return $u['host'] . $u['path'] . '?' . $u['query'];
    }else{
      return $u['host'] . $u['path'];
    }
  }

  function array2url($arr){ // converts associated array to URL Query String
    if (!is_array($arr)){
      return false;
    }
    $query = '';
    foreach($arr as $key => $value){
      $query .= $key . "=" . $value . "&";
    }
    return $query;
  }
  function CURL_Request($url, $method="GET", $params = "") { // Remember, SSL MUST BE SUPPORTED
    if (is_array($params)) $params = $this->array2url($params);

    if ($this->openid_url_type == 2) { $url = 'http://xri.net/'.$url; }

    if ($method == 'GET' && $params != '') {
        // mod the URL - but first check whether there's existing args - RS change
        if (stripos($url, '?')) {
            $url .= '&' . $params;
        } else {
            $url .= '?' . $params;
        }
    }

    echo $url;
    $curl = curl_init($url);

    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($curl, CURLOPT_HEADER, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_HTTPGET, ($method == "GET"));
    curl_setopt($curl, CURLOPT_POST, ($method == "POST"));

    if ($this->openid_url_type == 2) {
      $headers = array("Accept: application/xrds+xml");
      curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    }

    if ($method == "POST") curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($curl);

    if (curl_errno($curl) == 0){
      $response;
    }else{
      $this->ErrorStore('OPENID_CURL', curl_error($curl));
    }
    return $response;
  }

  function HTML2OpenIDServer($content) {
    $get = array();
    // Get details of their OpenID server and (optional) delegate
    $reg1 = '/<link[^>]*rel="openid.server"[^>]*href="([^"]+)"[^>]*\/?>/i';
    $reg2 = '/<link[^>]*href="([^"]+)"[^>]*rel="openid.server"[^>]*\/?>/i';

    preg_match_all($reg1, $content, $matches1);
    preg_match_all($reg1, $content, $matches2);

    // match on non-xhtml - RS change
    preg_match_all(preg_replace('/"/', "'", $reg1), $content, $matches3);
    preg_match_all(preg_replace('/"/', "'", $reg2), $content, $matches4);

    $servers = array_merge($matches1[1], $matches2[1], $matches3[1], $matches4[1]);

    $reg1 = '/<link[^>]*rel="openid.delegate"[^>]*href="([^"]+)"[^>]*\/?>/i';
    $reg2 = '/<link[^>]*href="([^"]+)"[^>]*rel="openid.delegate"[^>]*\/?>/i';

    preg_match_all($reg1, $content, $matches1);
    preg_match_all($reg2, $content, $matches2);
    preg_match_all(preg_replace('/"/', "'", $reg1), $content, $matches3);
    preg_match_all(preg_replace('/"/', "'", $reg2), $content, $matches4);
    $delegates = array_merge($matches1[1], $matches2[1], $matches3[1], $matches4[1]);

    if (count($servers) == 0 && count($delegates) == 0) {
      preg_match_all('/<meta[^>]*http-equiv="X-XRDS-Location"[^>]*content="([^"]+)"[^>]*\/>/i', $content, $matches3);
      preg_match_all('/<meta[^>]*content="([^"]+)"[^>]*http-equiv="X-XRDS-Location"[^>]*\/>/i', $content, $matches4);
      if ($matches3[1][0] != "") { $url = $matches3[1][0]; }
      else if ($matches4[1][0] != "") { $url = $matches4[1][0]; }
      if ($url != "") {
        $response = $this->CURL_Request($url);
        list($servers, $delegates) = $this->XRDS2OpenIDServer($response);
      }
    }

    $ret = array($servers, $delegates);
    return $ret;
  }

  function XRDS2OpenIDServer($content) {
    $arrcon = explode("\n", $content);
    $services = array();
    $delegates = array();
    $i=0;
    while ($i < count($arrcon)) {
      if (substr(trim($arrcon[$i]),0,8) == "<Service") {
        $servstr = "";
        while (substr(trim($arrcon[$i]),0,10) != "</Service>") {
          $servstr = $servstr . trim($arrcon[$i]) . "\n";
          $i++;
        }
        $services[] = $servstr;
      }
      $i++;
    }

    $matches1 = array();
    $matches2 = array();

    foreach ($services as $service) {
      if (strstr($service, "http://openid.net/signon/1.")) {
        preg_match_all('/<URI[^>]*>([^<]+)<\/URI>/i', $service, $matches1);
        preg_match_all('/<openid:Delegate[^>]*>([^<]+)<\/openid:Delegate>/i', $service, $matches2);
      }
    }
    $servers = $matches1[1];
    $delegates = $matches2[1];
    $ret = array($servers, $delegates);
    return $ret;
  }

  function CheckHeadersForXRDS($content) {
    $arrcon = explode("\n", $content);
    $i = 0;
    while ($i < count($arrcon)) {
      if (substr($arrcon[$i],0,16) == "X-XRDS-Location:") {
        $keyval = explode(':', $arrcon[$i], 2);
        $newurl = trim($keyval[1]);
        return $newurl;
      }
      $i++;
    }
    return "";
  }

  function GetOpenIDServer(){
    $response = $this->CURL_Request($this->openid_url_identity);

    $xrds_url = $this->CheckHeadersForXRDS($response);
    if ($xrds_url != "") {
      $response = $this->CURL_Request($xrds_url);
      list($servers, $delegates) = $this->XRDS2OpenIDServer($response);
    }
    else if ($this->openid_url_type == 1) {
      list($servers, $delegates) = $this->HTML2OpenIDServer($response);
    }
    else if ($this->openid_url_type == 2) {
      list($servers, $delegates) = $this->XRDS2OpenIDServer($response);
    }

    if (count($servers) == 0){
      $this->ErrorStore('OPENID_NOSERVERSFOUND');
      return false;
    }
    if ($delegates[0] != ""){
      $this->openid_url_identity = $delegates[0];
    }
    $this->SetOpenIDServer($servers[0]);
    return $servers[0];
  }

  function GetRedirectURL(){
    $params = array();
    $params['openid.return_to'] = urlencode($this->URLs['approved']);
    $params['openid.mode'] = 'checkid_setup';
    $params['openid.identity'] = urlencode($this->openid_url_identity);
    $params['openid.trust_root'] = urlencode($this->URLs['trust_root']);

    if (count($this->fields['required']) > 0){
      $params['openid.sreg.required'] = implode(',',$this->fields['required']);
    }
    if (count($this->fields['optional']) > 0){
      $params['openid.sreg.optional'] = implode(',',$this->fields['optional']);
    }
    $params['openid.sreg.policy_url'] = urlencode($this->URLs['policyurl']);

    $join = stripos($this->URLs['openid_server'], '?') ? '&' : '?';

    return $this->URLs['openid_server'] . $join. $this->array2url($params);
  }

  function Redirect(){
    $redirect_to = $this->GetRedirectURL();
    if (headers_sent()){ // Use JavaScript to redirect if content has been previously sent (not recommended, but safe)
      echo '<script language="JavaScript" type="text/javascript">window.location=\'';
      echo $redirect_to;
      echo '\';</script>';
    }else{  // Default Header Redirect
      header('Location: ' . $redirect_to);
    }
  }

  function ValidateWithServer(){
    $params = array(
      'openid.assoc_handle' => urlencode($_GET['openid_assoc_handle']),
      'openid.signed' => urlencode($_GET['openid_signed']),
      'openid.sig' => urlencode($_GET['openid_sig'])
    );
    // Send only required parameters to confirm validity
    $arr_signed = explode(",",str_replace('sreg.','sreg_',$_GET['openid_signed']));
    for ($i=0; $i<count($arr_signed); $i++){
      $s = str_replace('sreg_','sreg.', $arr_signed[$i]);
      $c = $_GET['openid_' . $arr_signed[$i]];
      // if ($c != ""){
        $params['openid.' . $s] = urlencode($c);
      // }
    }
    $params['openid.mode'] = "check_authentication";
    $openid_server = $this->GetOpenIDServer();

    // print "<pre>";
    // print_r($_GET);
    // print_r($params);
    // print_r($openid_server);
    // print "</pre>";

    if ($openid_server == false){
      return false;
    }
    // RS change - GET => POST http://openid.net/specs/openid-authentication-1_1.html#mode_check_authentication
    $response = $this->CURL_Request($openid_server,'POST',$params);
    $data = $this->splitResponse($response);


    if ($data['is_valid'] == "true") {
      return true;
    }else{
      return false;
    }
  }

}

?>