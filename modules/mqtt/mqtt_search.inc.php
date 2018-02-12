<?php
/*
* @version 0.1 (wizard)
*/
 global $session;
  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }
  $qry="1";
  // search filters
  //searching 'TITLE' (varchar)
  global $title;
  if ($title!='') {
   $qry.=" AND (TITLE LIKE '%".DBSafe($title)."%' OR VALUE LIKE '%".DBSafe($title)."%' OR PATH LIKE '%".DBSafe($title)."%')";
   $out['TITLE']=$title;
  }

  global $location_id;
  if ($location_id) {
   $qry.=" AND LOCATION_ID='".(int)$location_id."'";
   $out['LOCATION_ID']=(int)$location_id;
  }

  if (IsSet($this->location_id)) {
   $location_id=$this->location_id;
   $qry.=" AND LOCATION_ID='".$this->location_id."'";
  } else {
   global $location_id;
  }
  // QUERY READY
  global $save_qry;
  if ($save_qry) {
   $qry=$session->data['mqtt_qry'];
  } else {
   $session->data['mqtt_qry']=$qry;
  }
  if (!$qry) $qry="1";
  // FIELDS ORDER
  global $sortby_mqtt;
  if (!$sortby_mqtt) {
   $sortby_mqtt=$session->data['mqtt_sort'];
  } else {
   if ($session->data['mqtt_sort']==$sortby_mqtt) {
    if (Is_Integer(strpos($sortby_mqtt, ' DESC'))) {
     $sortby_mqtt=str_replace(' DESC', '', $sortby_mqtt);
    } else {
     $sortby_mqtt=$sortby_mqtt." DESC";
    }
   }
   $session->data['mqtt_sort']=$sortby_mqtt;
  }
  //if (!$sortby_mqtt) $sortby_mqtt="ID DESC";
  $sortby_mqtt="UPDATED DESC";
  $out['SORTBY']=$sortby_mqtt;
  // SEARCH RESULTS
  $res=SQLSelect("SELECT * FROM mqtt WHERE $qry ORDER BY ".$sortby_mqtt);
  if ($res[0]['ID']) {
   paging($res, 50, $out); // search result paging
   colorizeArray($res);
   $total=count($res);
   $dir = array();
   $last = & $dir;
   for($i=0;$i<$total;$i++) {
    // some action for every record if required
    $tmp=explode(' ', $res[$i]['UPDATED']);
    $res[$i]['UPDATED']=fromDBDate($tmp[0])." ".$tmp[1];
	
	$result = $res[$i]['TITLE'];
	
	mb_internal_encoding("UTF-8");
	if (mb_substr($res[$i]['TITLE'], 0, 1)=='/') $result = mb_substr( $res[$i]['TITLE'], 1); else $result = $res[$i]['TITLE'];
	
	$dump[$i] = explode('/', $result);
	$last = & $dir;
	$level=0;
	foreach ($dump[$i] as $p) {
		$exists=0;
		for ($j=0;$j<count($last);$j++) if (isset($last[$j]['TITLE']) && ($last[$j]['TITLE']==$p)) { $exists=1; break; }
		if (!$exists) { $last[$j]['TITLE'] = $p; $last[$j]['DIR'] = array(); }
		if ($level!=(count($dump[$i])-1)) $last = & $last[$j]['DIR']; else $last = & $last[$j];
		$level++;
	}
	$last = $res[$i];
   }
   
   $out['DIR'] = $dir;
   $out['RESULT']=$res;
  }

  $out['LOCATIONS']=SQLSelect("SELECT * FROM locations ORDER BY TITLE");
  
?>