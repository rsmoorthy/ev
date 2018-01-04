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


function ev_main()
{
  global $a, $parts, $role, $roles;

  if(!checkLoggedin()) {  // Not Logged in
    common_main(false);
  }
  else {  // Logged In
    common_main(true);
  }
  _errormsg("Invalid action");
}


