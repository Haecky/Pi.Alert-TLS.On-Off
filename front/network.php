<!-- ---------------------------------------------------------------------------
#  Pi.Alert
#  Open Source Network Guard / WIFI & LAN intrusion detector 
#
#  network.php - Front module. network relationship
#-------------------------------------------------------------------------------
#  leiweibau 2023                                          GNU GPLv3
#--------------------------------------------------------------------------- -->

<?php
session_start();

// Turn off php errors
error_reporting(0);

if ($_SESSION["login"] != 1)
  {
      header('Location: /pialert/index.php');
      exit;
  }

require 'php/templates/header.php';
require 'php/server/db.php';

$DBFILE = '../db/pialert.db';
OpenDB();
// #####################################
// ## Create Table if not exists'
// #####################################
$sql = 'CREATE TABLE IF NOT EXISTS "network_infrastructure" (
	"device_id"	INTEGER,
	"net_device_name"	TEXT NOT NULL,
	"net_device_typ"	TEXT NOT NULL,
  "net_device_port"  INTEGER,
  "net_downstream_devices" TEXT,
	PRIMARY KEY("device_id" AUTOINCREMENT)
)';

$result = $db->query($sql);
// #####################################
// ## Expand Devices Table
// #####################################
$sql = 'ALTER TABLE "Devices" ADD "dev_Infrastructure" INTEGER';
$result = $db->query($sql);
$sql = 'ALTER TABLE "Devices" ADD "dev_Infrastructure_port" INTEGER';
$result = $db->query($sql);
$sql = 'ALTER TABLE "network_infrastructure" ADD "net_downstream_devices" TEXT';
$result = $db->query($sql);

// #####################################
// Add New Network Devices
// #####################################
if ($_REQUEST['Networkinsert'] == "yes") {
	if (isset($_REQUEST['NetworkDeviceName']) && isset($_REQUEST['NetworkDeviceTyp']))
	{
		$sql = 'INSERT INTO "network_infrastructure" ("net_device_name", "net_device_typ", "net_device_port") VALUES("'.$_REQUEST['NetworkDeviceName'].'", "'.$_REQUEST['NetworkDeviceTyp'].'", "'.$_REQUEST['NetworkDevicePort'].'")';	
		$result = $db->query($sql);
	}
}
// #####################################
// Add New Network Devices
// #####################################
if ($_REQUEST['Networkedit'] == "yes") {
  if (($_REQUEST['NewNetworkDeviceName'] != "") && isset($_REQUEST['NewNetworkDeviceTyp']))
  {
    $sql = 'UPDATE "network_infrastructure" SET "net_device_name" = "'.$_REQUEST['NewNetworkDeviceName'].'", "net_device_typ" = "'.$_REQUEST['NewNetworkDeviceTyp'].'", "net_device_port" = "'.$_REQUEST['NewNetworkDevicePort'].'", "net_downstream_devices" = "'.$_REQUEST['NetworkDeviceDownlink'].'" WHERE "device_id"="'.$_REQUEST['NetworkDeviceID'].'"';
    //$sql = 'INSERT INTO "network_infrastructure" ("net_device_name", "net_device_typ", "net_device_port") VALUES("'.$_REQUEST['NetworkDeviceName'].'", "'.$_REQUEST['NetworkDeviceTyp'].'", "'.$_REQUEST['NetworkDevicePort'].'")'; 
    $result = $db->query($sql);
  }

  if (($_REQUEST['NewNetworkDeviceName'] == "") && isset($_REQUEST['NewNetworkDeviceTyp']))
  {
    $sql = 'UPDATE "network_infrastructure" SET "net_device_typ" = "'.$_REQUEST['NewNetworkDeviceTyp'].'", "net_device_port" = "'.$_REQUEST['NewNetworkDevicePort'].'", "net_downstream_devices" = "'.$_REQUEST['NetworkDeviceDownlink'].'" WHERE "device_id"="'.$_REQUEST['NetworkDeviceID'].'"';
    //$sql = 'INSERT INTO "network_infrastructure" ("net_device_name", "net_device_typ", "net_device_port") VALUES("'.$_REQUEST['NetworkDeviceName'].'", "'.$_REQUEST['NetworkDeviceTyp'].'", "'.$_REQUEST['NetworkDevicePort'].'")'; 
    $result = $db->query($sql);
  }

}
// #####################################
// remove Network Devices
// #####################################
if ($_REQUEST['Networkdelete'] == "yes") {
	if (isset($_REQUEST['NetworkDeviceID']))
	{
		$sql = 'DELETE FROM "network_infrastructure" WHERE "device_id"="'.$_REQUEST['NetworkDeviceID'].'"';	
		$result = $db->query($sql);	
	}
}

?>

<!-- Page ------------------------------------------------------------------ -->
<div class="content-wrapper">

<!-- Content header--------------------------------------------------------- -->
    <section class="content-header">
    <?php require 'php/templates/notification.php'; ?>
      <h1 id="pageTitle">
         <?php echo $pia_lang['Network_Title'];?>
      </h1>
    </section>

    <!-- Main content ---------------------------------------------------------- -->
    <section class="content">
		<div class="box box-default collapsed-box"> <!-- collapsed-box -->
        <div class="box-header with-border" data-widget="collapse">
          <h3 class="box-title"><?php echo $pia_lang['Network_ManageDevices'];?></h3>
          <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i></button>
          </div>
        </div>
        <!-- /.box-header -->
        <div class="box-body" style="">
          <div class="row">
            <div class="col-md-4">
            <h4 class="box-title"><?php echo $pia_lang['Network_ManageAdd'];?></h4>
            <form role="form" method="post" action="./network.php">
              <div class="form-group has-success">

                  <label for="NetworkDeviceName"><?php echo $pia_lang['Network_ManageAdd_Name'];?>:</label>
                  <div class="input-group">
                      <input class="form-control" id="txtNetworkNodeMac" name="NetworkDeviceName" type="text" placeholder="<?php echo $pia_lang['Network_ManageAdd_Name_text'];?>">
                          <div class="input-group-btn">

                            <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-expanded="false" id="buttonNetworkNodeMac">
                                <span class="fa fa-caret-down"></span>
                            </button>
                            <ul id="dropdownNetworkNodeMac" class="dropdown-menu dropdown-menu-right">
                              <li class="divider"></li>

                              <?php
                              network_infrastructurelist();
                              ?>

                            </ul>
                          </div>
                  </div>

              </div>
              <!-- /.form-group -->
              <div class="form-group has-success">
               <label><?php echo $pia_lang['Network_ManageAdd_Type'];?>:</label>
                  <select class="form-control" name="NetworkDeviceTyp">
                    <option value=""><?php echo $pia_lang['Network_ManageAdd_Type_text'];?></option>
                    <option value="0_Internet">Internet</option>
                    <option value="1_Router">Router</option>
                    <option value="2_Switch">Switch</option>
                    <option value="3_WLAN">WLAN</option>
                    <option value="4_Powerline">Powerline</option>
                  </select>
              </div>
              <div class="form-group has-success">
                <label for="NetworkDevicePort"><?php echo $pia_lang['Network_ManageAdd_Port'];?>:</label>
                <input type="text" class="form-control" id="NetworkDevicePort" name="NetworkDevicePort" placeholder="<?php echo $pia_lang['Network_ManageAdd_Port_text'];?>">
              </div>
              <div class="form-group">
              <button type="submit" class="btn btn-success" name="Networkinsert" value="yes"><?php echo $pia_lang['Network_ManageAdd_Submit'];?></button>
          	  </div>
          </form>
              <!-- /.form-group -->
            </div>
            <!-- /.col -->
            <div class="col-md-4">
              <h4 class="box-title"><?php echo $pia_lang['Network_ManageEdit'];?></h4>
              <form role="form" method="post" action="./network.php">
              <div class="form-group has-warning">
              	<label><?php echo $pia_lang['Network_ManageEdit_ID'];?>:</label>
                  <select class="form-control" name="NetworkDeviceID">
                    <option value=""><?php echo $pia_lang['Network_ManageEdit_ID_text'];?></option>
					<?php
					$sql = 'SELECT "device_id", "net_device_name", "net_device_typ" FROM "network_infrastructure" ORDER BY "net_device_typ" ASC'; 
					$result = $db->query($sql);//->fetchArray(SQLITE3_ASSOC); 
					while($res = $result->fetchArray(SQLITE3_ASSOC)){
						if(!isset($res['device_id'])) continue; 
					    echo '<option value="'.$res['device_id'].'">'.$res['net_device_name'].' / '.substr($res['net_device_typ'], 2).'</option>';
					} 
					?>
                  </select>
              </div>
              <div class="form-group has-warning">
                <label for="NetworkDeviceName"><?php echo $pia_lang['Network_ManageEdit_Name'];?>:</label>
                <input type="text" class="form-control" id="NewNetworkDeviceName" name="NewNetworkDeviceName" placeholder="<?php echo $pia_lang['Network_ManageEdit_Name_text'];?>">
              </div>
              <div class="form-group has-warning">
               <label><?php echo $pia_lang['Network_ManageEdit_Type'];?>:</label>
                  <select class="form-control" name="NewNetworkDeviceTyp">
                    <option value=""><?php echo $pia_lang['Network_ManageEdit_Type_text'];?></option>
                    <option value="0_Internet">Internet</option>
                    <option value="1_Router">Router</option>
                    <option value="2_Switch">Switch</option>
                    <option value="3_WLAN">WLAN</option>
                    <option value="4_Powerline">Powerline</option>
                  </select>
              </div>
              <div class="form-group has-warning">
                <label for="NetworkDevicePort"><?php echo $pia_lang['Network_ManageEdit_Port'];?>:</label>
                <input type="text" class="form-control" id="NewNetworkDevicePort" name="NewNetworkDevicePort" placeholder="<?php echo $pia_lang['Network_ManageEdit_Port_text'];?>">
              </div>
              <div class="form-group has-warning">
<!--                 <label for="NetworkDeviceDownlink"><?php echo $pia_lang['Network_ManageEdit_Downlink'];?>:</label>
                <input type="text" class="form-control" id="NetworkDeviceDownlink" name="NetworkDeviceDownlink" placeholder="<?php echo $pia_lang['Network_ManageEdit_Downlink_text'];?>"> -->

                  <label for="NetworkDeviceDownlink"><?php echo $pia_lang['Network_ManageEdit_Downlink'];?>:</label>
                  <div class="input-group">
                      <input class="form-control" id="txtNetworkDeviceDownlinkMac" name="NetworkDeviceDownlink" type="text" placeholder="<?php echo $pia_lang['Network_ManageEdit_Downlink_text'];?>">
                          <div class="input-group-btn">

                            <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-expanded="false" id="buttonNetworkDeviceDownlinkMac">
                                <span class="fa fa-caret-down"></span>
                            </button>
                            <ul id="dropdownNetworkDeviceDownlinkMac" class="dropdown-menu dropdown-menu-right">
                              <li class="divider"></li>

                              <?php
                              //network_infrastructurelist();
                              network_device_downlink_mac();
                              ?>

                            </ul>
                          </div>
                  </div>




              </div>
              <div class="form-group">
                <button type="submit" class="btn btn-warning" name="Networkedit" value="yes"><?php echo $pia_lang['Network_ManageEdit_Submit'];?></button>
              </div>
         	 </form>
              <!-- /.form-group -->
            </div>
            <!-- /.col -->
           <div class="col-md-4">
            <h4 class="box-title"><?php echo $pia_lang['Network_ManageDel'];?></h4>
              <form role="form" method="post" action="./network.php">
              <div class="form-group has-error">
                <label><?php echo $pia_lang['Network_ManageDel_Name'];?>:</label>
                  <select class="form-control" name="NetworkDeviceID">
                    <option value=""><?php echo $pia_lang['Network_ManageDel_Name_text'];?></option>
          <?php
          $sql = 'SELECT "device_id", "net_device_name", "net_device_typ" FROM "network_infrastructure" ORDER BY "net_device_typ" ASC'; 
          $result = $db->query($sql);//->fetchArray(SQLITE3_ASSOC); 
          while($res = $result->fetchArray(SQLITE3_ASSOC)){
            if(!isset($res['device_id'])) continue; 
              echo '<option value="'.$res['device_id'].'">'.$res['net_device_name'].' / '.substr($res['net_device_typ'], 2).'</option>';
          } 
          ?>
                  </select>
              </div>
              <!-- /.form-group -->
              <div class="form-group">
                <button type="submit" class="btn btn-danger" name="Networkdelete" value="yes"><?php echo $pia_lang['Network_ManageDel_Submit'];?></button>
              </div>
           </form>
              <!-- /.form-group -->
            </div>
          </div>
          <!-- /.row -->
        </div>
        <!-- /.box-body -->
      </div>

<script>
function setTextValue (textElement, textValue) {
  $('#'+textElement).val (textValue);
}
</script>

<?php
// #####################################
// ## Start Function Setup
// #####################################
function network_infrastructurelist() {
  global $db;
  $func_sql = 'SELECT * FROM "Devices" WHERE "dev_DeviceType" = "Router" OR "dev_DeviceType"  = "Switch" OR "dev_DeviceType"  = "AP" OR "dev_DeviceType"  = "Access Point" OR "dev_MAC"  = "Internet"';
  $func_result = $db->query($func_sql);//->fetchArray(SQLITE3_ASSOC); 
  while($func_res = $func_result->fetchArray(SQLITE3_ASSOC)) {
    echo '<li><a href="javascript:void(0)" onclick="setTextValue(\'txtNetworkNodeMac\',\''.$func_res['dev_Name'].'\')">'.$func_res['dev_Name'].'/'.$func_res['dev_DeviceType'].'</a></li>';
  }
}

function network_device_downlink_mac() {
  global $db;
  $func_sql = 'SELECT * FROM "Devices" WHERE "dev_DeviceType" = "Router" OR "dev_DeviceType"  = "Switch" OR "dev_DeviceType"  = "AP" OR "dev_DeviceType"  = "Access Point" OR "dev_MAC"  = "Internet"';
  $func_result = $db->query($func_sql);//->fetchArray(SQLITE3_ASSOC); 
  while($func_res = $func_result->fetchArray(SQLITE3_ASSOC)) {
    echo '<li><a href="javascript:void(0)" onclick="setTextValue(\'txtNetworkDeviceDownlinkMac\',\''.$func_res['dev_MAC'].',\')">'.$func_res['dev_Name'].'</a></li>';
  }
}

function unassigned_devices() {
  global $db;
  $func_sql = 'SELECT * FROM "Devices" WHERE "dev_Infrastructure" = "" OR "dev_Infrastructure" IS NULL';
  $func_result = $db->query($func_sql);//->fetchArray(SQLITE3_ASSOC); 
  while($func_res = $func_result->fetchArray(SQLITE3_ASSOC)) {
    echo '<a href="./deviceDetails.php?mac='.$func_res['dev_MAC'].'"><div style="display: inline-block; padding: 5px 15px; font-weight: bold;">'.$func_res['dev_Name'].'</div></a>';
  }
}

function get_downstream_devices($pia_func_down_devid) {
  global $db;

  $manual_downstream_ports = array();
  $func_sql = 'SELECT * FROM "network_infrastructure" WHERE "device_id" = "'.$pia_func_down_devid.'"';
  $func_result = $db->query($func_sql);//->fetchArray(SQLITE3_ASSOC); 
  while($func_res = $func_result->fetchArray(SQLITE3_ASSOC)) {
    $temp_group_array = explode(';',$func_res['net_downstream_devices']);
  }
  $clean_group_array = array_filter($temp_group_array);
  unset($temp_group_array);
  for($x=0;$x<sizeof($clean_group_array);$x++) {
    $temp_port_array = explode(',',trim($clean_group_array[$x]));
    $downstream_devices[trim($temp_port_array[1])] = trim(strtolower($temp_port_array[0]));
  }
  return $downstream_devices;
}

function get_downstream_from_mac($pia_func_down_mac) {
  global $db;

  $func_sql = 'SELECT * FROM "Devices" WHERE "dev_MAC" = "'.$pia_func_down_mac.'"';
  $func_result = $db->query($func_sql);//->fetchArray(SQLITE3_ASSOC); 
  while($func_res = $func_result->fetchArray(SQLITE3_ASSOC)) {
    return $func_res;
  }
}

function getNodeOnlineState($pia_node_name) {
  global $db;
  $func_sql = 'SELECT * FROM "Devices" WHERE "dev_Name" = "'.$pia_node_name.'"';
  $func_result = $db->query($func_sql);//->fetchArray(SQLITE3_ASSOC); 
  while($func_res = $func_result->fetchArray(SQLITE3_ASSOC)) {
     if ($func_res['dev_PresentLastScan'] == 1) {return '<i class="fa fa-w fa-circle text-green-light fa-gradient-green"></i>&nbsp;';} else {return '<i class="fa fa-w fa-circle text-red fa-gradient-red"></i>&nbsp;';}
  }
}

function createnetworktab($pia_func_netdevid, $pia_func_netdevname, $pia_func_netdevtyp, $pia_func_netdevport, $activetab) {
	echo '<li class="'.$activetab.'">';
  echo '<a href="#'.$pia_func_netdevid.'" data-toggle="tab">';
  echo getNodeOnlineState($pia_func_netdevname);
  echo $pia_func_netdevname.' / ';
  if (substr($pia_func_netdevtyp, 2) == 'WLAN') {
    echo '<i class="bi bi-wifi" style="font-size: 16px; position: relative; top: 1px;"></i>';}
    elseif (substr($pia_func_netdevtyp, 2) == 'Powerline') {
      echo '<i class="bi bi-plug-fill" style="font-size: 16px; position: relative; top: 2px;"></i>';}
    elseif (substr($pia_func_netdevtyp, 2) == 'Router') {
      echo '<i class="bi bi-router-fill" style="font-size: 16px; position: relative; top: 2px;"></i>';}
    elseif (substr($pia_func_netdevtyp, 2) == 'Switch') {
      echo '<i class="bi bi-ethernet" style="font-size: 16px; position: relative; top: 2px;"></i>';}
    elseif (substr($pia_func_netdevtyp, 2) == 'Internet') {
      echo '<i class="bi bi-globe" style="font-size: 16px; position: relative; top: 2px;"></i>';}
    else {echo substr($pia_func_netdevtyp, 2);}
  // Enable the display of the complete Portcount
  //if ($pia_func_netdevport != "") {echo ' ('.$pia_func_netdevport.')';}
  echo '</a></li>';
}

function createnetworktabcontent($pia_func_netdevid, $pia_func_netdevname, $pia_func_netdevtyp, $pia_func_netdevport, $activetab) {
	global $pia_lang;
  echo '<div class="tab-pane '.$activetab.'" id="'.$pia_func_netdevid.'">
	      <h4>'.$pia_func_netdevname.'</h4><br>';
  
  $downstream_devices = get_downstream_devices($pia_func_netdevid);
  
  echo '<div class="box-body no-padding">
    <table class="table table-striped table-hover">
      <tbody><tr>
        <th style="width: 40px">Port</th>
        <th style="width: 75px">'.$pia_lang['Network_Table_State'].'</th>
        <th>'.$pia_lang['Network_Table_Hostname'].'</th>
        <th>'.$pia_lang['Network_Table_IP'].'</th>
      </tr>';
  // Prepare Array for Devices with Port value
  // If no Port is set, the Port number is set to 1
  if ($pia_func_netdevport == "") {$pia_func_netdevport = 1;}
  // Create Array with specific length
  $network_device_portname = array();
  $network_device_portmac = array();
  $network_device_portip = array();
  $network_device_portstate = array();
  // make sql query for Network Hardware ID
	global $db;
	$func_sql = 'SELECT * FROM "Devices" WHERE "dev_Infrastructure" = "'.$pia_func_netdevid.'"';
	$func_result = $db->query($func_sql);//->fetchArray(SQLITE3_ASSOC); 
	while($func_res = $func_result->fetchArray(SQLITE3_ASSOC)) {
    //if(!isset($func_res['dev_Name'])) continue;
		if ($func_res['dev_PresentLastScan'] == 1) {$port_state = '<div class="badge bg-green text-white" style="width: 60px;">Online</div>';} else {$port_state = '<div class="badge bg-gray text-white" style="width: 60px;">Offline</div>';}
		// Prepare Table with Port > push values in array
    if ($pia_func_netdevport > 1)
      {
        if (stristr($func_res['dev_Infrastructure_port'], ',') == '') {
        if ($network_device_portname[$func_res['dev_Infrastructure_port']] != '') {$network_device_portname[$func_res['dev_Infrastructure_port']] = $network_device_portname[$func_res['dev_Infrastructure_port']].','.$func_res['dev_Name'];} else {$network_device_portname[$func_res['dev_Infrastructure_port']] = $func_res['dev_Name'];}
        if ($network_device_portmac[$func_res['dev_Infrastructure_port']] != '') {$network_device_portmac[$func_res['dev_Infrastructure_port']] = $network_device_portmac[$func_res['dev_Infrastructure_port']].','.$func_res['dev_MAC'];} else {$network_device_portmac[$func_res['dev_Infrastructure_port']] = $func_res['dev_MAC'];}
        if ($network_device_portip[$func_res['dev_Infrastructure_port']] != '') {$network_device_portip[$func_res['dev_Infrastructure_port']] = $network_device_portip[$func_res['dev_Infrastructure_port']].','.$func_res['dev_LastIP'];} else {$network_device_portip[$func_res['dev_Infrastructure_port']] = $func_res['dev_LastIP'];}
        if (isset($network_device_portstate[$func_res['dev_Infrastructure_port']])) {$network_device_portstate[$func_res['dev_Infrastructure_port']] = $network_device_portstate[$func_res['dev_Infrastructure_port']].','.$func_res['dev_PresentLastScan'];} else {$network_device_portstate[$func_res['dev_Infrastructure_port']] = $func_res['dev_PresentLastScan'];}
        } else {
          $multiport = array();
          $multiport = explode(',',$func_res['dev_Infrastructure_port']);
          foreach($multiport as $row) {
              $network_device_portname[trim($row)] = $func_res['dev_Name'];
              $network_device_portmac[trim($row)] = $func_res['dev_MAC'];
              $network_device_portip[trim($row)] = $func_res['dev_LastIP'];
              $network_device_portstate[trim($row)] = $func_res['dev_PresentLastScan'];
          }
          unset($multiport);
        }
      } else {
        // Table without Port > echo values
        // Specific icon for devicetype
        if (substr($pia_func_netdevtyp, 2) == "WLAN") {$dev_port_icon = 'fa-wifi';}
        if (substr($pia_func_netdevtyp, 2) == "Powerline") {$dev_port_icon = 'fa-flash';}
        echo '<tr><td style="text-align: center;"><i class="fa '.$dev_port_icon.'"></i></td><td>'.$port_state.'</td><td style="padding-left: 10px;"><a href="./deviceDetails.php?mac='.$func_res['dev_MAC'].'"><b>'.$func_res['dev_Name'].'</b></a></td><td>'.$func_res['dev_LastIP'].'</td></tr>';
      }
	}
  // Create table with Port
  if ($pia_func_netdevport > 1)
    {
      for ($x=1; $x<=$pia_func_netdevport; $x++) 
        {
          // Manual Entry Processing
          if (isset($downstream_devices[$x])) {
            $downstream_device_resolved = get_downstream_from_mac($downstream_devices[$x]);
            $network_device_portmac[$x] = $downstream_devices[$x];
            $network_device_portname[$x] = $downstream_device_resolved['dev_Name'];
            $network_device_portstate[$x] = $downstream_device_resolved['dev_PresentLastScan'];
            $network_device_portip[$x] =  $downstream_device_resolved['dev_LastIP'];
          }
          // Prepare online/offline badge for later functions
          $online_badge = '<div class="badge bg-green text-white" style="width: 60px;">Online</div>';
          $offline_badge = '<div class="badge bg-gray text-white" style="width: 60px;">Offline</div>';
          // Set online/offline badge
          echo '<tr>';
          echo '<td style="text-align: right; padding-right:16px;">'.$x.'</td>';
          // Set online/offline badge
          // Check if multiple badges necessary
          if (stristr($network_device_portstate[$x],',') == '') {
            // Set single online/offline badge
            if ($network_device_portstate[$x] == 1) {$port_state = $online_badge;} else {$port_state = $offline_badge;}
            echo '<td>'.$port_state.'</td>';
          } else {
            // Set multiple online/offline badges
            $multistate = array();
            $multistate = explode(',',$network_device_portstate[$x]);
            echo '<td>';
            foreach($multistate as $key => $value) {
                if ($value == 1) {$port_state = $online_badge;} else {$port_state = $offline_badge;}
                echo $port_state.'<br>';
            }
            echo '</td>';
            unset($multistate);
          }  
          // Check if multiple Hostnames are set
          // print single hostname         
          if (stristr($network_device_portmac[$x],',') == '') {
            echo '<td style="padding-left: 10px;"><a href="./deviceDetails.php?mac='.$network_device_portmac[$x].'"><b>'.$network_device_portname[$x].'</b></a></td>';
          } else {
            // print multiple hostnames with separate links  
            $multimac = array();
            $multimac = explode(',',$network_device_portmac[$x]);
            $multiname = array();
            $multiname = explode(',',$network_device_portname[$x]);
            echo '<td style="padding-left: 10px;">';
            foreach($multiname as $key => $value) {
                echo '<a href="./deviceDetails.php?mac='.$multimac[$key].'"><b>'.$value.'</b></a><br>';
            }
            echo '</td>';
            unset($multiname, $multimac);
          }
          // Check if multiple IP are set
          // print single IP  
          if (stristr($network_device_portip[$x],',') == '') {
            echo '<td style="padding-left: 10px;">'.$network_device_portip[$x].'</td>';
          } else {
            // print multiple IPs
            $multiip = array();
            $multiip = explode(',',$network_device_portip[$x]);
            echo '<td style="padding-left: 10px;">';
            foreach($multiip as $key => $value) {
                echo $value.'<br>';
            }
            echo '</td>';
            unset($multiip);
          }
          echo '</tr>';
        }
    }
  echo '        </tbody></table>
            </div>';
	echo '</div> ';
}
// #####################################
// ## End Function Setup
// #####################################

// #####################################
// ## Create Tabs
// #####################################
$sql = 'SELECT "device_id", "net_device_name", "net_device_typ", "net_device_port" FROM "network_infrastructure" ORDER BY "net_device_typ" ASC, "net_device_name" ASC'; 
$result = $db->query($sql);//->fetchArray(SQLITE3_ASSOC); 
?>
      <div class="nav-tabs-custom" style="">
            <ul class="nav nav-tabs">
<?php
$i = 0;
while($res = $result->fetchArray(SQLITE3_ASSOC)){
	if(!isset($res['device_id'])) continue;
	if ($i == 0) {$active = 'active';} else {$active = '';}
    createnetworktab($res['device_id'], $res['net_device_name'], $res['net_device_typ'], $res['net_device_port'], $active);
    $i++;
}
?>              
            </ul>
			<div class="tab-content">
<?php
// #####################################
// ## Create Tab Content
// #####################################
$i = 0;
while($res = $result->fetchArray(SQLITE3_ASSOC)){
	if(!isset($res['device_id'])) continue; 
	if ($i == 0) {$active = 'active';} else {$active = '';}
  createnetworktabcontent($res['device_id'], $res['net_device_name'], $res['net_device_typ'], $res['net_device_port'], $active);
    $i++;
}
unset($i);
?>
              <!-- /.tab-pane -->
            </div>
            <!-- /.tab-content -->
  </div>

<div class="box box-default collapsed-box">
    <div class="box-header with-border" data-widget="collapse">
        <h3 class="box-title"><i class="fa"></i><?php echo $pia_lang['Network_UnassignedDevices'];?></h3>
          <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i></button>
          </div>        
    </div>
    <div class="box-body">
<?php
unassigned_devices();
?>
    </div>
    <!-- /.box-body -->
</div>

  <div style="width: 100%; height: 20px;"></div>
</section>

    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->

<!-- ----------------------------------------------------------------------- -->
<?php
  require 'php/templates/footer.php';
?>