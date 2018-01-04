<?php

// Provides functions for access - authentication and authorization

// This function responds to all authorization requests
function blacklist_html_auth($module, $roles, $def_points, $def_access)
{
  // Authentication and authorization for HTML
  // Points for Roles
  // Admin - 100, Coordinator - 80, Applicant - 0
  $points = $def_points;  // Accept default points
  $access = array(
      "#forms.html#" => 80,
      "#templates/formstd.html#" => 80,
      "#templates/bootstrap..html#" => 80,
      "#^/forms/blacklist/.*(json|yaml)#" => 80,
      );
  return array($points, $access);
}

function blacklist_db2_authorize($module, $loggedIn, $oper, $collname, $condition, $passcode=null)
{
  if(!$loggedIn)
    return false;

  return true;
}

