<?php
/**
* tcl TV 
* @package project
* @author Wizard <sergejey@gmail.com>
* @copyright http://majordomo.smartliving.ru/ (c)
* @version 0.1 (wizard, 21:10:20 [Oct 13, 2019])
*/
//
//
include_once(DIR_MODULES . 'tcl_tv/lib/MQTTClient.php');

class tcl_tv extends module {
/**
* tcl_tv
*
* Module class constructor
*
* @access private
*/
function __construct() {
  $this->name="tcl_tv";
  $this->title="TCL TV";
  $this->client_name = "Majordomo";
  $this->module_category="<#LANG_SECTION_DEVICES#>";
  $this->checkInstalled();
}
/**
* saveParams
*
* Saving module parameters
*
* @access public
*/
function saveParams($data=1) {
 $p=array();
 if (IsSet($this->id)) {
  $p["id"]=$this->id;
 }
 if (IsSet($this->view_mode)) {
  $p["view_mode"]=$this->view_mode;
 }
 if (IsSet($this->edit_mode)) {
  $p["edit_mode"]=$this->edit_mode;
 }
 if (IsSet($this->data_source)) {
  $p["data_source"]=$this->data_source;
 }
 if (IsSet($this->tab)) {
  $p["tab"]=$this->tab;
 }
 return parent::saveParams($p);
}
/**
* getParams
*
* Getting module parameters from query string
*
* @access public
*/
function getParams() {
  global $id;
  global $mode;
  global $view_mode;
  global $edit_mode;
  global $data_source;
  global $tab;
  if (isset($id)) {
   $this->id=$id;
  }
  if (isset($mode)) {
   $this->mode=$mode;
  }
  if (isset($view_mode)) {
   $this->view_mode=$view_mode;
  }
  if (isset($edit_mode)) {
   $this->edit_mode=$edit_mode;
  }
  if (isset($data_source)) {
   $this->data_source=$data_source;
  }
  if (isset($tab)) {
   $this->tab=$tab;
  }
}
/**
* Run
*
* Description
*
* @access public
*/
function run() {
 global $session;
  $out=array();
  if ($this->action=='admin') {
   $this->admin($out);
  } else {
   $this->usual($out);
  }
  if (IsSet($this->owner->action)) {
   $out['PARENT_ACTION']=$this->owner->action;
  }
  if (IsSet($this->owner->name)) {
   $out['PARENT_NAME']=$this->owner->name;
  }
  $out['VIEW_MODE']=$this->view_mode;
  $out['EDIT_MODE']=$this->edit_mode;
  $out['MODE']=$this->mode;
  $out['ACTION']=$this->action;
  $out['DATA_SOURCE']=$this->data_source;
  $out['TAB']=$this->tab;
  $this->data=$out;
  $p=new parser(DIR_TEMPLATES.$this->name."/".$this->name.".html", $this->data, $this);
  $this->result=$p->result;
}
/**
* BackEnd
*
* Module backend
*
* @access public
*/

function admin(&$out) {
 $this->getConfig();
 if (!gg('cycle_tcl_tvRun')) {
   setGlobal('cycle_tcl_tvRun',1);
 }
 if ((time() - gg('cycle_tcl_tvRun')) < 60 ) {
   $out['CYCLERUN'] = 1;
 } else {
   $out['CYCLERUN'] = 0;
 } 
 $out['API_URL']=$this->config['API_URL'];
 if (!$out['API_URL']) {
  $out['API_URL']='http://';
 }
 $out['API_KEY']=$this->config['API_KEY'];
 $out['API_USERNAME']=$this->config['API_USERNAME'];
 $out['API_PASSWORD']=$this->config['API_PASSWORD'];
 if ($this->view_mode=='update_settings') {
   global $api_url;
   $this->config['API_URL']=$api_url;
   global $api_key;
   $this->config['API_KEY']=$api_key;
   global $api_username;
   $this->config['API_USERNAME']=$api_username;
   global $api_password;
   $this->config['API_PASSWORD']=$api_password;
   $this->saveConfig();
   $this->redirect("?");
 }
 if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
  $out['SET_DATASOURCE']=1;
 }
 if ($this->data_source=='tcl_device' || $this->data_source=='') {
  if ($this->view_mode=='' || $this->view_mode=='search_tcl_device') {
   $this->search_tcl_device($out);
  }
  if ($this->view_mode=='edit_tcl_device') {
   $this->edit_tcl_device($out, $this->id);
  }
  if ($this->view_mode=='delete_tcl_device') {
   $this->delete_tcl_device($this->id);
   $this->redirect("?data_source=tcl_device");
  }
 }
 if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
  $out['SET_DATASOURCE']=1;
 }
 if ($this->data_source=='tcl_device_data') {
  if ($this->view_mode=='' || $this->view_mode=='search_tcl_device_data') {
   $this->search_tcl_device_data($out);
  }
  if ($this->view_mode=='edit_tcl_device_data') {
   $this->edit_tcl_device_data($out, $this->id);
  }
 }
}
/**
* FrontEnd
*
* Module frontend
*
* @access public
*/
function usual(&$out) {
 $this->admin($out);
}
/**
* tcl_device search
*
* @access public
*/
 function search_tcl_device(&$out) {
  require(DIR_MODULES.$this->name.'/tcl_device_search.inc.php');
 }
/**
* tcl_device edit/add
*
* @access public
*/
 function edit_tcl_device(&$out, $id) {
  require(DIR_MODULES.$this->name.'/tcl_device_edit.inc.php');
 }
/**
* tcl_device delete record
*
* @access public
*/
 function delete_tcl_device($id) {
  $rec=SQLSelectOne("SELECT * FROM tcl_device WHERE ID='$id'");
  // some action for related tables
  SQLExec("DELETE FROM tcl_device WHERE ID='".$rec['ID']."'");
 }
/**
* tcl_device_data search
*
* @access public
*/
 function search_tcl_device_data(&$out) {
  require(DIR_MODULES.$this->name.'/tcl_device_data_search.inc.php');
 }
/**
* tcl_device_data edit/add
*
* @access public
*/
 function edit_tcl_device_data(&$out, $id) {
  require(DIR_MODULES.$this->name.'/tcl_device_data_edit.inc.php');
 }
 
 function propertySetHandle($object, $property, $value) {
  $this->getConfig();
   DebMes($object.".".$property."=".$value, 'tcl_tv'); 
   $table='tcl_device_data';
   $properties=SQLSelect("SELECT * FROM $table WHERE LINKED_OBJECT LIKE '".DBSafe($object)."' AND LINKED_PROPERTY LIKE '".DBSafe($property)."'");
   $total=count($properties);
   if ($total) {
    for($i=0;$i<$total;$i++) {
      $device_id = $properties[$i]["DEVICE_ID"];
      $table='tcl_device';
      $device=SQLSelectOne("SELECT * FROM $table WHERE ID=$device_id"); 
      //DebMes($device['IP']." ".$device['MAC'], 'tcl_tv'); 
      $new = $value;
      $old = $properties[$i]['VALUE'];
        
      if ($new != $old)
      {
          
      
      }
    }
   }
 }
 
 
 function processCycle() {
    $this->getConfig();
    $port = 6559;
    $table_name='tcl_device';
    $devices=SQLSelect("SELECT * FROM $table_name");
    foreach ($devices as $device)
    {
        $id = $device['ID'];
        //connect socket
        $ip = $device['IP'];
        /* Create a TCP/IP socket. */
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($socket === false) {
            $data['state'] = 0;
            $data['error'] = "socket_create() failed: reason: " . socket_strerror(socket_last_error());
            $this->updateData($id,$data);
            continue;
        }

        $result = socket_connect($socket, $ip, $port);
        if ($result === false) {
            $data['state'] = 0;
            $data['error'] = "socket_connect() failed.\nReason: ($result) " . socket_strerror(socket_last_error($socket));
            $this->updateData($id,$data);
            continue;
        }

        $in = "cp3_start=>10=>10=>cp3_end#37"; // connect command
        $out = '';

        socket_write($socket, $in, strlen($in));
        
        $out = socket_read($socket, 2048));
        $this->processMessage($id,$out);

        socket_close($socket);
    }
  
 }
 
 function updateData($id, $data)
 {
        $table_name='tcl_device_data';
        $values=SQLSelect("SELECT * FROM $table_name WHERE DEVICE_ID='$id'");
        foreach ($data as $key => $val)
        {
            echo $key."-".$val."\n";
            $value_ind = array_search($key, array_column($values, 'TITLE'));
            if ($value_ind !== False)
                $value = $values[$value_ind];
            else
                $value = array();
            //print_r($value);
            $value["TITLE"] = $key;
            $value["DEVICE_ID"] = $id;
            $value["UPDATED"] = date('Y-m-d H:i:s');
            if ($value['ID']) {
                if ($value["VALUE"] != $val)
                {   
                    $value["VALUE"] = $val;
                    SQLUpdate($table_name, $value);
                    if ($value['LINKED_OBJECT'] && $value['LINKED_PROPERTY']) {
                        setGlobal($value['LINKED_OBJECT'] . '.' . $value['LINKED_PROPERTY'], $val, array($this->name => '0'));
                    }
                }
            }
            else{
                $value["VALUE"] = $val;
                SQLInsert($table_name, $value);
            }
        }
 } 
 
function processMessage($id, $msg)
    {
        DebMes("ID: $id Msg: $msg", 'tcl_tv'); 
        print_r ($value);
            
        $data['state'] = 1;
        $data['error'] = '';
        $this->updateData($id,$data);
        
    }
    
//     
function wakeOnLan($broadcast, $mac)
{
    DebMes("Wake on lan - ".$broadcast ." ". $mac, 'tcl_tv'); 
    $hwaddr = pack('H*', preg_replace('/[^0-9a-fA-F]/', '', $mac));
    
    // Create Magic Packet
    $packet = sprintf(
        '%s%s',
        str_repeat(chr(255), 6),
        str_repeat($hwaddr, 16)
    );

    $sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);

    if ($sock !== false) {
        $options = socket_set_option($sock, SOL_SOCKET, SO_BROADCAST, true);

        if ($options !== false) {
            socket_sendto($sock, $packet, strlen($packet), 0, $broadcast, 7);
            socket_close($sock);
        }
    }
} 


/**
* Install
*
* Module installation routine
*
* @access private
*/
 function install($data='') {
  parent::install();
 }
/**
* Uninstall
*
* Module uninstall routine
*
* @access public
*/
 function uninstall() {
  SQLExec('DROP TABLE IF EXISTS tcl_device');
  SQLExec('DROP TABLE IF EXISTS tcl_device_data');
  parent::uninstall();
 }
/**
* dbInstall
*
* Database installation routine
*
* @access private
*/
 function dbInstall($data) {
/*
tcl_device - 
tcl_device_data - 
*/
  $data = <<<EOD
 tcl_device: ID int(10) unsigned NOT NULL auto_increment
 tcl_device: TITLE varchar(100) NOT NULL DEFAULT ''
 tcl_device: IP varchar(255) NOT NULL DEFAULT ''
 tcl_device: MAC varchar(255) NOT NULL DEFAULT ''
 tcl_device_data: ID int(10) unsigned NOT NULL auto_increment
 tcl_device_data: TITLE varchar(100) NOT NULL DEFAULT ''
 tcl_device_data: VALUE varchar(1024) NOT NULL DEFAULT ''
 tcl_device_data: DEVICE_ID int(10) NOT NULL DEFAULT '0'
 tcl_device_data: LINKED_OBJECT varchar(100) NOT NULL DEFAULT ''
 tcl_device_data: LINKED_PROPERTY varchar(100) NOT NULL DEFAULT ''
 tcl_device_data: LINKED_METHOD varchar(100) NOT NULL DEFAULT ''
 tcl_device_data: UPDATED datetime
EOD;
  parent::dbInstall($data);
 }
// --------------------------------------------------------------------
}
/*
*
* TW9kdWxlIGNyZWF0ZWQgT2N0IDEzLCAyMDE5IHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
