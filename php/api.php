<?php

set_include_path(get_include_path() . PATH_SEPARATOR .
  realpath(__DIR__) . PATH_SEPARATOR .
  realpath("/home/journo/browser")
);

/* For all requires, follow this convention (Path is setup above correctly)
 * For local modules:
 * require_once "Blacklist.php";
 * require_once "hook/somehook.php";
 * For journo files:
 * require_once "lib/certs.php";
 * */


function blacklist_main()
{
  global $a, $parts, $role, $roles;

  if(!checkLoggedin()) {  // Not Logged in
    if($a === "checkBlacklist" or $a == "checkBlackList")
      apiCheckBlacklist($parts);
  }
  else {  // Logged In
    common_main();
  }
  _errormsg("Unauthorized access");
}

// Direct API call for checkBlacklist
/*
Endpoint: https://bl.isha.in/api.php/checkBlacklist
Method: PUT or POST
Authentication: Hmac Signature using Secret Key
  HTTP Header
    Authorization: hmac <src>:<hmac_signature>

Parameters Sent as JSON (as body of the request)
JSON:
{
    "request": "checkBlacklist"  (should repeat this)
    "requestId": "<an id that identifies this person">  (will be logged in audit log)
                 ex: like "prs:567890asf234"
    "name": "name of the person",
    "gender": "gender of the person",
    "email": "email of the person",
    "mobile": "mobile of the person",
    "city": "city of the person",
    "address": "address of the person",
    "pincode": "pincode of the person",
    "country": "country of the person",
    "nationality": "nationality of the person"
}

The API response will be JSON:

{
    "status": "ok|error",
    "error": "error message, if status is error"
    "result": "<one of the status>" (Allow, Block etc - needs to be finalized)
}

Note: To send this request using PHP, use this code
  $secret_key = "<provided>";
  $src = "<known like prs/oco>";

  $request = ["name"=>"some", "gender"=>"M", "email"=>"some@some.com".... ];
  $req_md5 = md5(json_encode($request));
  $hmac_signature = hash_hmac("sha256", $req_md5, $secret_key);

  $curl = new cURL();
  $curl->set_header("Authorization: hmac $src:$hmac_signature");
  $resp = $curl->put("https://bl.isha.in/api.php/checkBlackList", json_encode($request));

*/
function apiCheckBlacklist($parts)
{
  $secret_key = geticfg()->getValue("BlacklistAPIKey", "Root");
  $jobj = hmacAPIAuthentication($secret_key, []);
  json_log($jobj, "API Request");

  // TODO Now do some levenshtein matching algorithm and return
  $simpleCheck = function($jobj) {

    $db = new DB2("blacklist");
    if($jobj[mobile]) {
      $row = $db->findOne(["mobile"=>"/{$jobj[mobile]}/"]);
      if($row)
        return [$row[status], ["name"=>$row[name], "email"=>$row[email], "mobile"=>$row[mobile]]];
    }

    if($jobj[email]) {
      $row = $db->findOne(["email"=>"/{$jobj[email]}/i"]);
      if($row)
        return [$row[status], ["name"=>$row[name], "email"=>$row[email], "mobile"=>$row[mobile]]];
    }

    if($jobj[name]) {
      $row = $db->findOne(["name"=>"/^{$jobj[name]}$/i"]);
      if($row)
        return [$row[status], ["name"=>$row[name], "email"=>$row[email], "mobile"=>$row[mobile]]];
    }

    return ["None", []];
  };

  // TODO some logging is needed (Audit Trail). Log all check requests for
  // later processing

  $ret = $simpleCheck($jobj);
  json_log($ret, "API Response");
  print json_encode(array("status"=>"ok", "result"=>$ret[0], "details"=>$ret[1]));
  exit(0);
}

