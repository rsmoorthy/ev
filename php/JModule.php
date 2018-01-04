<?php

// This is the Class of actions for Any JModule
// This is meant to be a super class, subclassed
// The location of the file will be moved to /home/journo/browser/lib/ later

class JModule
{
  var $message = "";
  public function __construct($params=[])
  {
    foreach(["hook", "oper", "collName", "condition", "fields"] as $p) {
      if(array_key_exists($p, $params))
        $this->$p = $params[$p];
    }

    $this->error = "";
    $this->message = "";
    $this->mod = $this;
    $this->apimode = false;
    $this->origFields = array_merge([], $this->fields);
    $this->guests = [];
    $this->doneThings = [];
    $this->dontSave = false;
    $this->arrayFields = []; // like payments, transact etc

    $this->init();
  }

  public function basicValidation()
  {
    $mod = $this->mod;
    $fields = $mod->fields;

    // Remove all guests.0.name if empty
    for($i=0; $i < 10; $i++) {
      // if name or firstname is there, no need to remove it.
      if($mod->fields["guests.$i.name"] or $mod->fields["guests.$i.firstname"])
        continue;
      // remove all guests.0.xxx
      foreach($mod->fields as $key=>$val) {
        if(preg_match("/^guests.$i./", $key)) {
          unset($mod->fields[$key]);
          unset($mod->crow[$key]);
        }
      }
    }
    return true;
  }

  public function init()
  {
    // Get the row
    $db = new DB2($this->collName);
    if($this->oper == "edit") {
      $this->row = $db->findOne($this->condition);
      if(!$this->row)
        return $this->setError("Invalid condition");
    }
    else {
      if($this->condition[_id]) {
        $this->drow = $db->findOne($this->condition);
        if(!$this->crow["_id"])
          $this->crow["_id"] = $this->condition["_id"];
      }
      $this->row = [];
    }

    $this->crow = array_merge($this->row, $this->fields);
    $this->orig_crow = $this->crow;
    if(!($this->drow))
      $this->drow = $this->crow;
    return true;
  }

  public function register()
  {
    if(!$this->basicValidation())
      return false;
    return true;
  }

  public function confirm()
  {
    $mod = $this->mod;
    $mod->crow["status"] = $mod->fields["status"] = "Confirmed";
    return true;
  }

  public function setError($error, $params=[])
  {
    $this->error = $error;
    $this->logError($error, $params);
    return false;
  }

  private function logError($error, $params=[])
  {
    error_log("$error");
    logAlerts($error, $params);
    return false;
  }

  public function _diff($old=null, $new=null)
  {
    $diff = [];
    // now let's do a diff between orig_crow and crow
    foreach($new as $key=>$val) {
      // This is like our payments[].
      if(is_array($val) and count($val) and is_assoc($val[0])) {
        if(!in_array($key, ["payments"])) // For now limit only to payments
          continue;
        foreach($val as $_val) {
          // match -- _id
          if($_val["_id"] and is_array($old[$key])) {
            $mv = P::toArray(P::filter(function($a) use ($_val) { if(is_array($a) and $a[_id] == $_val[_id]) return true; }, $old[$key]));
            if(count($mv)) {
              $d = P::reduce(function($acc, $v,$k) use ($mv) { if($mv[0][$k] != $v) $acc[$k]=$v; return $acc; }, ["_id"=>$_val["_id"]], $_val);
              if(count($d) > 1) {
                $diff[$key][] = $d;
              }
            }
          }
          else {
            $diff[$key][] = $_val;
          }
        }
      }
      else {
        if(!array_key_exists($key, $old) or $old[$key] != $new[$key]) {
          if(array_key_exists($key, $diff) and $diff[$key] == $val)
            continue;
          $diff[$key] = $val;
        }
      }
    }
    return $diff;
  }

  // This function is needed, as the whole registration process will be split into
  // normal registration and then online part. The online part will be set in
  // background
  public function save()
  {
    if($this->dontSave)
      return true;
    $mod = $this->mod;
    if($this->oper == "add") {
      $db = new DB2($mod->collName);
      $ret = $db->insert($mod->crow);
      if(!$ret)
        return $this->setError("Insert failed " . $db->error);
      $mod->crow["_id"] = $ret;
      $mod->oper = "edit";
      $mod->origFields = $mod->fields;
      $mod->orig_crow = $mod->crow;
      return true;
    }

    // If fields is different (actions based on onlinePayment and confirm)
    // $diff = array_diff_assoc($mod->fields, $mod->origFields);
    $diff = array_merge(array_diff_assoc($mod->fields, $mod->origFields), $this->_diff($mod->orig_crow, $mod->crow));
    if(count($diff)) {
      json_log($diff, "save");
      $dbs = new DB2($mod->collName);
      $condition = ["_id"=>$mod->crow["_id"]];
      $ret = $dbs->update($condition, $diff);
      if(!$ret)
        return $this->setError("Save to DB error ". $dbs->error,
          ["subject"=>"Save to DB", "details"=>["error"=>$db->error, "condition"=>$condition, "fields"=>$diff], "cause"=>"Developer Error", "severity"=>"Error"]);

      // Now read it again from DB and update payments._id
      foreach([] as $arrayField) {
        if($mod->crow[$arrayField]) {
          $nrow = $dbs->findOne($condition);
          for($ii=0; $ii < count($nrow[$arrayField]); $ii++) {
            if($nrow[$arrayField][$ii]["_id"] and !$mod->crow[$arrayField][$ii]["_id"])
              $mod->crow[$arrayField][$ii]["_id"] = $nrow[$arrayField][$ii]["_id"];
          }
        }
      }
    }
    return true;
  }

  private function  _matchCondition($match, $val)
  {
    if($match == "NONEMPTY" and !empty($val))
      return true;
    elseif($match == "EMPTY" and empty($val))
      return true;
    elseif(preg_match("/^\/(.*)\/$/", $match, $m) and preg_match("/" . $m[1] . "/", $val))
      return true;
    elseif(preg_match("/^!=(.*)$/", $match, $m) and $m[1] != $val)
      return true;
    elseif($match == $val)
      return true;
    return false;
  }

  // Add transaction logs
  public function addTransaction($tr=[])
  {
    $p = [];
    foreach(["type","subject", "subdetails", "mode"] as $fld) {
      if($tr[$fld])
        $p["transact.$fld"] = $tr[$fld];
    }

    (new DB2($this->collName))->update(["_id"=>$this->mod->crow["_id"]], $p);
  }
}
