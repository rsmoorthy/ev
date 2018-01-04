<?php

require_once "/home/journo/browser/lib/curl.php";


$secret_key = "5a3d15f72af893d57d89ed2d";
$src = "prs";

$request = ["name"=>"Thi ThuTrang Pham", "mobile"=>"61432417908", "email"=>"snowstorm927@gmail.com" ];
$req_md5 = md5(json_encode($request));
$hmac_signature = hash_hmac("sha256", $req_md5, $secret_key);

$curl = new cURL();
$curl->set_header("Authorization: hmac $src:$hmac_signature");
print "Authorization: hmac $src:$hmac_signature\n";
print "$\n";
print_r($request);
$resp = $curl->put("https://bl.isha.in/api.php/checkBlackList", json_encode($request));
print_r($resp);

