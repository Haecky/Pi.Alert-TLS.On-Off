<?php
//------------------------------------------------------------------------------
//  Pi.Alert
//  Open Source Network Guard / WIFI & LAN intrusion detector 
//
//  maintenance.php - Front module. Server side. Manage Devices
//------------------------------------------------------------------------------
//  Puche      2021        pi.alert.application@gmail.com   GNU GPLv3
//  jokob-sk   2022        jokob.sk@gmail.com               GNU GPLv3
//  leiweibau  2023        https://github.com/leiweibau     GNU GPLv3
//------------------------------------------------------------------------------

session_start();

// Turn off php errors
error_reporting(0);

if ($_SESSION["login"] != 1)
  {
      header('Location: /pialert/index.php');
      exit;
  }

require 'php/templates/header.php';
?>
<!-- Page ------------------------------------------------------------------ -->
<div class="content-wrapper">

<!-- Content header--------------------------------------------------------- -->
    <section class="content-header">
    <?php require 'php/templates/notification.php'; ?>
      <h1 id="pageTitle">
         <?php echo $pia_lang['Maintenance_Title'];?>
      </h1>
    </section>

    <!-- Main content ---------------------------------------------------------- -->
    <section class="content">

<?php
// Get API-Key ------------------------------------------------------------------

$config_file = "../config/pialert.conf";
$config_file_lines = file($config_file);
$config_file_lines_bypass = array_values(preg_grep('/^PIALERT_APIKEY\s.*/', $config_file_lines));
if ($config_file_lines_bypass != False) {
    $apikey_line = explode("'", $config_file_lines_bypass[0]);
    $pia_apikey = trim($apikey_line[1]);
} else {$pia_apikey = $pia_lang['Maintenance_Tool_setapikey_false'];}

// Size and last mod of DB ------------------------------------------------------

$pia_db = str_replace('front', 'db', getcwd()).'/pialert.db';
$pia_db_size = number_format((filesize($pia_db) / 1000000),2,",",".") . ' MB';
$pia_db_mod = date ("d.m.Y, H:i:s", filemtime($pia_db)).' Uhr';

// Count Config Backups -------------------------------------------------------

$pia_config_Path = str_replace('front', 'config', getcwd()).'/';
$files = glob($pia_config_Path."pialert-20*.bak");
if ($files){
 $pia_config_count = count($files);
}

// Count and Calc DB Backups -------------------------------------------------------

$Pia_Archive_Path = str_replace('front', 'db', getcwd()).'/';
$Pia_Archive_count = 0;
$Pia_Archive_diskusage = 0;
$files = glob($Pia_Archive_Path."pialertdb_*.zip");
if ($files){
 $Pia_Archive_count = count($files);
}
foreach ($files as $result) {
    $Pia_Archive_diskusage = $Pia_Archive_diskusage + filesize($result);
}
$Pia_Archive_diskusage = number_format(($Pia_Archive_diskusage / 1000000),2,",",".") . ' MB';

// Find latest Backup for restore -----------------------------------------------

$latestfiles = glob($Pia_Archive_Path."pialertdb_*.zip");
if (sizeof($latestfiles) == 0) {
    $latestbackup_date = $pia_lang['Maintenance_Tool_restore_blocked'];
    $block_restore_button = true;
} else {
        natsort($latestfiles);
        $latestfiles = array_reverse($latestfiles,False);
        $latestbackup = $latestfiles[0];
        $latestbackup_date = date ("Y-m-d H:i:s", filemtime($latestbackup));
    }

// Aprscan read Timer -----------------------------------------------------------------

function read_arpscan_timer() {
    //$pia_lang_set_dir = '../db/';
    $file = '../db/setting_stoparpscan';
    if (file_exists($file)) {
        $timer_arpscan = file_get_contents($file, true);
        if ($timer_arpscan == 10 || $timer_arpscan == 15 || $timer_arpscan == 30) {
            $timer_output = ' ('.$timer_arpscan.'min)';
        }
        if ($timer_arpscan == 60 || $timer_arpscan == 120 || $timer_arpscan == 720 || $timer_arpscan == 1440) {
            $timer_arpscan = $timer_arpscan / 60;
            $timer_output = ' ('.$timer_arpscan.'h)';
        }
        if ($timer_arpscan == 1051200) {
            $timer_output = ' (very long)';
        }
    }
    $timer_output = '<span style="color:red;">'.$timer_output.'</span>';
    echo $timer_output;
}

// Get Device List Columns -----------------------------------------------------------------

function read_DevListCol() {
    //$pia_lang_set_dir = '../db/';
    $file = '../db/setting_devicelist';
    if (file_exists($file)) {
        $get = file_get_contents($file, true);
        $output_array = json_decode($get, true);
    } else {
        $output_array = array('Favorites' => 1, 'Group' => 1, 'Owner' => 1, 'Type' => 1, 'FirstSession' => 1, 'LastSession' => 1, 'LastIP' => 1, 'MACType' => 1, 'Location' => 0);
    }
    return $output_array;
}

// Set Tab ----------------------------------------------------------------------------

if ($_REQUEST['tab'] == '1') {
    $pia_tab_setting = 'active'; $pia_tab_tool = ''; $pia_tab_backup = '';
} elseif ($_REQUEST['tab'] == '2') {
    $pia_tab_setting = ''; $pia_tab_tool = 'active'; $pia_tab_backup = '';
} elseif ($_REQUEST['tab'] == '3') {
    $pia_tab_setting = ''; $pia_tab_tool = ''; $pia_tab_backup = 'active';
} else { $pia_tab_setting = 'active'; $pia_tab_tool = ''; $pia_tab_backup = '';}

?>

    <div class="row">
      <div class="col-md-12">

<!-- Status Box ----------------------------------------------------------------- -->

    <div class="box" id="Maintain-Status">
        <div class="box-header with-border">
            <h3 class="box-title">Status</h3>
        </div>
        <div class="box-body" style="padding-bottom: 5px;">
            <div class="db_info_table">
                <div class="db_info_table_row">
                    <div class="db_info_table_cell" style="min-width: 140px"><?php echo $pia_lang['Maintenance_database_path'];?></div>
                    <div class="db_info_table_cell">
                        <?php echo $pia_db;?>
                    </div>
                </div>
                <div class="db_info_table_row">
                    <div class="db_info_table_cell"><?php echo $pia_lang['Maintenance_database_size'];?></div>
                    <div class="db_info_table_cell">
                        <?php echo $pia_db_size;?>
                    </div>
                </div>
                <div class="db_info_table_row">
                    <div class="db_info_table_cell"><?php echo $pia_lang['Maintenance_database_lastmod'];?></div>
                    <div class="db_info_table_cell">
                        <?php echo $pia_db_mod;?>
                    </div>
                </div>
                <div class="db_info_table_row">
                    <div class="db_info_table_cell"><?php echo $pia_lang['Maintenance_database_backup'];?></div>
                    <div class="db_info_table_cell">
                        <?php echo $Pia_Archive_count.' '.$pia_lang['Maintenance_database_backup_found'].' / '.$pia_lang['Maintenance_database_backup_total'].': '.$Pia_Archive_diskusage;?>
                    </div>
                </div>
                <div class="db_info_table_row">
                    <div class="db_info_table_cell"><?php echo $pia_lang['Maintenance_config_backup'];?></div>
                    <div class="db_info_table_cell">
                        <?php echo $pia_config_count.' '.$pia_lang['Maintenance_database_backup_found'];?>
                    </div>
                </div>
                <div class="db_info_table_row">
                    <div class="db_info_table_cell"><?php echo $pia_lang['Maintenance_arp_status'];?></div>
                    <div class="db_info_table_cell">
                        <?php echo $_SESSION['arpscan_result']; read_arpscan_timer();?></div>
                </div>
                <div class="db_info_table_row">
                    <div class="db_info_table_cell" style="min-width: 140px">Api-Key</div>
                    <div class="db_info_table_cell" style="overflow-wrap: anywhere;">
                        <input readonly value="<?php echo $pia_apikey;?>" style="width:100%; overflow-x: scroll; border: none; background: transparent; margin: 0px; padding: 0px;">
                    </div>
                </div>
            </div>                
        </div>
          <!-- /.box-body -->
    </div>

      </div>
    </div>

<!-- Update Check ----------------------------------------------------------------- -->

    <div class="box">
        <div class="box-body" id="updatecheck" style="text-align: center; padding-top: 5px; padding-bottom: 5px; height: 45px;">
            <button type="button" id="rewwejwejpjo" class="btn btn-primary" onclick="check_github_for_updates()"><?php echo $pia_lang['Maintenance_Tools_Updatecheck'];?></button>
      </div>
    </div>

    <script>
    function check_github_for_updates() {
        $("#updatecheck").empty();
        $.ajax({
            method: "POST",
            url: "./php/server/updatecheck.php",
            data: "",
            beforeSend: function() { $('#updatecheck').addClass("ajax_scripts_loading"); },
            complete: function() { $('#updatecheck').removeClass("ajax_scripts_loading"); },
            success: function(data, textStatus) {
                $("#updatecheck").html(data);
            }
        })
    }
    </script>

<!-- Log Viewer ----------------------------------------------------------------- -->

    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title">Log Viewer</h3>
        </div>
        <div class="box-body" id="logviewer" style="text-align: center; padding-top: 5px; padding-bottom: 5px;">
            <button type="button" id="oisjmofeirfj" class="btn btn-primary" data-toggle="modal" data-target="#modal-logviewer-scan" style="margin: 5px;"><?php echo $pia_lang['Maintenance_Tools_Logviewer_Scan'];?></button>
            <button type="button" id="wefwfwefewdf" class="btn btn-primary" data-toggle="modal" data-target="#modal-logviewer-iplog" style="margin: 5px;"><?php echo $pia_lang['Maintenance_Tools_Logviewer_IPLog'];?></button>
            <button type="button" id="tzhrsreawefw" class="btn btn-primary" data-toggle="modal" data-target="#modal-logviewer-vendor" style="margin: 5px;"><?php echo $pia_lang['Maintenance_Tools_Logviewer_Vendor'];?></button>
            <button type="button" id="arzuozhrsfga" class="btn btn-primary" data-toggle="modal" data-target="#modal-logviewer-cleanup" style="margin: 5px;"><?php echo $pia_lang['Maintenance_Tools_Logviewer_Cleanup'];?></button>
            <button type="button" id="arzuozhrsfga" class="btn btn-primary" data-toggle="modal" data-target="#modal-logviewer-nmap" style="margin: 5px;"><?php echo $pia_lang['Maintenance_Tools_Logviewer_Nmap'];?></button>
<?php
if ($_SESSION['Scan_WebServices'] == True) {
    echo '<button type="button" id="erftttwrdwqqq" class="btn btn-primary" data-toggle="modal" data-target="#modal-logviewer-webservices" style="margin: 5px;">'.$pia_lang['Maintenance_Tools_Logviewer_WebServices'].'</button>';
}
?>
      </div>
    </div>

<!-- Log Viewer - Modals Scan ----------------------------------------------------------------- -->

    <div class="modal fade" id="modal-logviewer-scan">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span></button>
                    <h4 class="modal-title">Viewer: pialert.1.log (File)</h4>
                </div>
                <div class="modal-body" style="text-align: left;">
                    <div style="border: none; overflow-y: scroll;">
                    <?php
                    $file = file_get_contents('./php/server/pialert.1.log', true);
                    if ($file == "") {echo $pia_lang['Maintenance_Tools_Logviewer_Scan_empty'];}
                    echo str_replace("\n",'<br>',$file);
                    ?>
                    <br></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $pia_lang['Gen_Close'];?></button>
                </div>
            </div>
        </div>
    </div>

<!-- Log Viewer - Modals IP ----------------------------------------------------------------- -->

    <div class="modal fade" id="modal-logviewer-iplog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span></button>
                    <h4 class="modal-title">Viewer: pialert.IP.log (File)</h4>
                </div>
                <div class="modal-body" style="text-align: left;">
                    <div style="border: none; overflow-y: scroll;">
                    <?php
                    $file = file_get_contents('./php/server/pialert.IP.log', true);
                    if ($file == "") {echo $pia_lang['Maintenance_Tools_Logviewer_IPLog_empty'];}
                    echo str_replace("\n",'<br>',$file);
                    ?>
                    <br></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $pia_lang['Gen_Close'];?></button>
                </div>
            </div>
        </div>
    </div>

<!-- Log Viewer - Modals Vendor Update ----------------------------------------------------------------- -->

    <div class="modal fade" id="modal-logviewer-vendor">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span></button>
                    <h4 class="modal-title">Viewer: pialert.vendors.log (File)</h4>
                </div>
                <div class="modal-body" style="text-align: left;">
                    <div style="border: none; overflow-y: scroll;">
                    <?php
                    $file = file_get_contents('./php/server/pialert.vendors.log');
                    if ($file == "") {echo $pia_lang['Maintenance_Tools_Logviewer_Vendor_empty'];} 
                       else {
                            $temp_log = explode("\n", $file);
                            $x=0;
                            while($x < sizeof($temp_log)) {
                              if (strlen($temp_log[$x]) == 0) 
                                {
                                    $y = $x;
                                    while($y < sizeof($temp_log)) {
                                        echo $temp_log[$y].'<br>';
                                       $y++;
                                    } 
                                    break;
                                }
                              $x++;
                            }
                        }
                    ?>
                    <br></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $pia_lang['Gen_Close'];?></button>
                </div>
            </div>
        </div>
    </div>

<!-- Log Viewer - Modals Cleanup ----------------------------------------------------------------- -->

    <div class="modal fade" id="modal-logviewer-cleanup">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span></button>
                    <h4 class="modal-title">Viewer: pialert.cleanup.log (File)</h4>
                </div>
                <div class="modal-body" style="text-align: left;">
                    <div style="border: none; overflow-y: scroll;">
                    <?php
                    $file = file_get_contents('./php/server/pialert.cleanup.log', true);
                    if ($file == "") {echo $pia_lang['Maintenance_Tools_Logviewer_Cleanup_empty'];}
                    echo str_replace("\n",'<br>',$file);
                    ?>
                    <br></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $pia_lang['Gen_Close'];?></button>
                </div>
            </div>
        </div>
    </div>

<!-- Log Viewer - Modals Nmap ----------------------------------------------------------------- -->

    <div class="modal fade" id="modal-logviewer-nmap">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span></button>
                    <h4 class="modal-title">Viewer: last Nmap Scan (Memory)</h4>
                </div>
                <div class="modal-body" style="text-align: left;">
                    <div style="border: none; overflow-y: scroll;">
                    <?php
                    if (!isset($_SESSION['ScanShortMem'])) {echo $pia_lang['Maintenance_Tools_Logviewer_Nmap_empty'];} else {echo $_SESSION['ScanShortMem'];}
                    ?>
                    <br></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $pia_lang['Gen_Close'];?></button>
                </div>
            </div>
        </div>
    </div>

<!-- Log Viewer - Modals WebServices ----------------------------------------------------------------- -->
<?php
if ($_SESSION['Scan_WebServices'] == True) {
    echo '<div class="modal fade" id="modal-logviewer-webservices">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span></button>
                    <h4 class="modal-title">Viewer: pialert.webservices.log (File)</h4>
                </div>
                <div class="modal-body" style="text-align: left;">
                    <div style="border: none; overflow-y: scroll;">';

                    $file = file_get_contents('./php/server/pialert.webservices.log', true);
                    if ($file == "") {echo $pia_lang['Maintenance_Tools_Logviewer_WebServices_empty'];}
                    echo str_replace("\n",'<br>',$file);

    echo '                <br></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">'.$pia_lang['Gen_Close'].'</button>
                </div>
            </div>
        </div>
    </div>';
}
?>
<!-- Tabs ----------------------------------------------------------------- -->

    <div class="nav-tabs-custom">
    <ul class="nav nav-tabs">
        <li class="<?php echo $pia_tab_setting; ?>"><a href="#tab_Settings" data-toggle="tab"><?php echo $pia_lang['Maintenance_Tools_Tab_Settings'];?></a></li>
        <li class="<?php echo $pia_tab_tool; ?>"><a href="#tab_DBTools" data-toggle="tab"><?php echo $pia_lang['Maintenance_Tools_Tab_Tools'];?></a></li>
        <li class="<?php echo $pia_tab_backup; ?>"><a href="#tab_BackupRestore" data-toggle="tab"><?php echo $pia_lang['Maintenance_Tools_Tab_BackupRestore'];?></a></li>
    </ul>
    <div class="tab-content">
        <div class="tab-pane <?php echo $pia_tab_setting; ?>" id="tab_Settings">
            <table class="table_settings">
                <tr><td colspan="2"><h4 class="bottom-border-aqua"><?php echo $pia_lang['Maintenance_Tools_Tab_Subheadline_a'];?></h4></td></tr>
                <tr class="table_settings">
                    <td class="db_info_table_cell" colspan="2" style="text-align: justify;"><?php echo $pia_lang['Maintenance_Tools_Tab_Settings_Intro'];?></td>
                </tr>
                <tr class="table_settings_row">
                    <td class="db_info_table_cell" colspan="2" style="padding-bottom: 20px;">
                        <div style="display: flex; justify-content: center; flex-wrap: wrap;">
<!-- Language Selection ----------------------------------------------------------------- -->
                            <div class="settings_button_wrapper">
                                <div class="settings_button_box">
                                    <div class="form-group" style="width:160px; margin-bottom:5px;">
                                      <div class="input-group">
                                        <input class="form-control" id="txtLangSelection" type="text" value="<?php echo $pia_lang['Maintenance_lang_selector_empty'];?>" readonly >
                                        <div class="input-group-btn">
                                          <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-expanded="false" id="dropdownButtonLangSelection">
                                            <span class="fa fa-caret-down"></span></button>
                                          <ul id="dropdownLangSelection" class="dropdown-menu dropdown-menu-right">
                                            <li><a href="javascript:void(0)" onclick="setTextValue('txtLangSelection','en_us');"><?php echo $pia_lang['Maintenance_lang_en_us'];?></a></li>
                                            <li><a href="javascript:void(0)" onclick="setTextValue('txtLangSelection','de_de');"><?php echo $pia_lang['Maintenance_lang_de_de'];?></a></li>
                                            <li><a href="javascript:void(0)" onclick="setTextValue('txtLangSelection','fr_fr');"><?php echo $pia_lang['Maintenance_lang_fr_fr'];?></a></li>
                                            <li><a href="javascript:void(0)" onclick="setTextValue('txtLangSelection','es_es');"><?php echo $pia_lang['Maintenance_lang_es_es'];?></a></li>
                                          </ul>
                                        </div>
                                      </div>
                                    </div>                             
                                    <button type="button" class="btn btn-primary bg-green" style="margin-top:0px; width:160px;" id="btnSaveLangSelection" onclick="setPiAlertLanguage()" ><?php echo $pia_lang['Maintenance_lang_selector_apply'];?> </button>
                                </div>
                            </div>
<!-- Theme Selection ----------------------------------------------------------------- -->
                            <div class="settings_button_wrapper">
                                <div class="settings_button_box">
                                    <div class="form-group" style="width:160px; margin-bottom:5px;">
                                      <div class="input-group">
                                        <input class="form-control" id="txtSkinSelection" type="text" value="<?php echo $pia_lang['Maintenance_themeselector_empty'];?>" readonly >
                                        <div class="input-group-btn">
                                          <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-expanded="false" id="dropdownButtonSkinSelection">
                                            <span class="fa fa-caret-down"></span></button>
                                          <ul id="dropdownSkinSelection" class="dropdown-menu dropdown-menu-right">
                                            <li><a href="javascript:void(0)" onclick="setTextValue('txtSkinSelection','skin-black-light');">Black-light</a></li>
                                            <li><a href="javascript:void(0)" onclick="setTextValue('txtSkinSelection','skin-black');">Black</a></li>
                                            <li><a href="javascript:void(0)" onclick="setTextValue('txtSkinSelection','skin-blue-light');">Blue-light</a></li>
                                            <li><a href="javascript:void(0)" onclick="setTextValue('txtSkinSelection','skin-blue');">Blue</a></li>
                                            <li><a href="javascript:void(0)" onclick="setTextValue('txtSkinSelection','skin-green-light');">Green-light</a></li>
                                            <li><a href="javascript:void(0)" onclick="setTextValue('txtSkinSelection','skin-green');">Green</a></li>
                                            <li><a href="javascript:void(0)" onclick="setTextValue('txtSkinSelection','skin-purple-light');">Purple-light</a></li>
                                            <li><a href="javascript:void(0)" onclick="setTextValue('txtSkinSelection','skin-purple');">Purple</a></li>
                                            <li><a href="javascript:void(0)" onclick="setTextValue('txtSkinSelection','skin-red-light');">Red-light</a></li>
                                            <li><a href="javascript:void(0)" onclick="setTextValue('txtSkinSelection','skin-red');">Red</a></li>
                                            <li><a href="javascript:void(0)" onclick="setTextValue('txtSkinSelection','skin-yellow-light');">Yellow-light</a></li>
                                            <li><a href="javascript:void(0)" onclick="setTextValue('txtSkinSelection','skin-yellow');">Yellow</a></li>
                                          </ul>
                                        </div>
                                      </div>
                                    </div>
                                    <button type="button" class="btn btn-primary bg-green" style="margin-top:0px; width:160px;" id="btnSaveSkinSelection" onclick="setPiAlertTheme()" ><?php echo $pia_lang['Maintenance_themeselector_apply'];?> </button>
                                </div>
                            </div>
<!-- Toggle DarkMode ----------------------------------------------------------------- -->
                            <div class="settings_button_wrapper">
                                <div class="settings_button_box">
                                    <button type="button" class="btn bg-green dbtools-button" id="btnPiaEnableDarkmode" onclick="askPiaEnableDarkmode()"><?php echo $pia_lang['Maintenance_Tool_darkmode'];?></button>
                                </div>
                            </div>
<!-- Toggle History Graph ----------------------------------------------------------------- -->
                            <div class="settings_button_wrapper">
                                <div class="settings_button_box">
                                    <button type="button" class="btn bg-green dbtools-button" id="btnPiaEnableOnlineHistoryGraph" onclick="askPiaEnableOnlineHistoryGraph()"><?php echo $pia_lang['Maintenance_Tool_onlinehistorygraph'];?></button>      
                                </div>
                            </div>
<!-- Toggle Web Service Monitoring ----------------------------------------------------------------- -->
                            <div class="settings_button_wrapper">
                                <div class="settings_button_box">
                                    <button type="button" class="btn bg-green dbtools-button" id="btnPiaEnableWebServiceMon" onclick="askPiaEnableWebServiceMon()"><?php echo $pia_lang['Maintenance_Tool_webservicemon'];?></button>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr><td colspan="2"><h4 class="bottom-border-aqua"><?php echo $pia_lang['Maintenance_Tools_Tab_Subheadline_b'];?></h4></td></tr>
                <tr>
                    <td colspan="2" style="text-align: center;">

                        <link rel="stylesheet" href="lib/AdminLTE/plugins/iCheck/all.css">
                        <script src="lib/AdminLTE/plugins/iCheck/icheck.min.js"></script>

<?php
    $table_config = read_DevListCol();
    if ($table_config['Favorites'] == 1) {$checkbox_fav = "checked";}
    if ($table_config['Group'] == 1) {$checkbox_grp = "checked";}
    if ($table_config['Owner'] == 1) {$checkbox_own = "checked";}
    if ($table_config['Type'] == 1) {$checkbox_typ = "checked";}
    if ($table_config['FirstSession'] == 1) {$checkbox_first_sess = "checked";}
    if ($table_config['LastSession'] == 1) {$checkbox_last_sess = "checked";}
    if ($table_config['LastIP'] == 1) {$checkbox_lastip = "checked";}
    if ($table_config['MACType'] == 1) {$checkbox_mactype = "checked";}
    if ($table_config['Location'] == 1) {$checkbox_loc = "checked";}
?>

                        <div class="form-group">
                            <div class="table_settings_col_box" style="">
                              <input class="icheckbox_minimal-blue" id="chkOwner" type="checkbox" <?php echo $checkbox_own; ?> style="position: relative; margin-top:-3px; margin-right: 5px;">
                              <label class="control-label"><?php echo $pia_lang['Device_TableHead_Owner'];?></label>
                            </div>

                            <div class="table_settings_col_box" style="">
                              <input class="icheckbox_minimal-blue" id="chkType" type="checkbox" <?php echo $checkbox_typ; ?> style="position: relative; margin-top:-3px; margin-right: 5px;">
                              <label class="control-label"><?php echo $pia_lang['Device_TableHead_Type'];?></label>
                            </div>

                            <div class="table_settings_col_box" style="">
                              <input class="icheckbox_minimal-blue" id="chkFavorite" type="checkbox" <?php echo $checkbox_fav; ?> style="position: relative; margin-top:-3px; margin-right: 5px;">
                              <label class="control-label"><?php echo $pia_lang['Device_TableHead_Favorite'];?></label>
                            </div>
                            
                            <div class="table_settings_col_box" style="">
                              <input class="icheckbox_minimal-blue" id="chkGroup" type="checkbox" <?php echo $checkbox_grp; ?> style="position: relative; margin-top:-3px; margin-right: 5px;">
                              <label class="control-label"><?php echo $pia_lang['Device_TableHead_Group'];?></label>
                            </div>

                            <div class="table_settings_col_box" style="">
                              <input class="icheckbox_minimal-blue" id="chkLocation" type="checkbox" <?php echo $checkbox_loc; ?> style="position: relative; margin-top:-3px; margin-right: 5px;">
                              <label class="control-label"><?php echo $pia_lang['Device_TableHead_Location'];?></label>
                            </div>

                            <div class="table_settings_col_box" style="">
                              <input class="icheckbox_minimal-blue" id="chkfirstSess" type="checkbox" <?php echo $checkbox_first_sess; ?> style="position: relative; margin-top:-3px; margin-right: 5px;">
                              <label class="control-label"><?php echo $pia_lang['Device_TableHead_FirstSession'];?></label>
                            </div>
                            
                            <div class="table_settings_col_box" style="">
                              <input class="icheckbox_minimal-blue" id="chklastSess" type="checkbox" <?php echo $checkbox_last_sess; ?> style="position: relative; margin-top:-3px; margin-right: 5px;">
                              <label class="control-label"><?php echo $pia_lang['Device_TableHead_LastSession'];?></label>
                            </div>

                            <div class="table_settings_col_box" style="">
                              <input class="icheckbox_minimal-blue" id="chklastIP" type="checkbox" <?php echo $checkbox_lastip; ?> style="position: relative; margin-top:-3px; margin-right: 5px;">
                              <label class="control-label"><?php echo $pia_lang['Device_TableHead_LastIP'];?></label>
                            </div>
                            
                            <div class="table_settings_col_box" style="">
                              <input class="icheckbox_minimal-blue" id="chkMACtype" type="checkbox" <?php echo $checkbox_mactype; ?> style="position: relative; margin-top:-3px; margin-right: 5px;">
                              <label class="control-label"><?php echo $pia_lang['Device_TableHead_MAC'];?></label>
                            </div>
                            <br>
                            <button type="button" class="btn btn-primary bg-green" style="margin-top:10px; width:160px;" id="btnSaveDeviceListCol" onclick="askDeviceListCol()" ><?php echo $pia_lang['Gen_Save'];?></button>
                        </div>


                    </td>
                </tr>
                <tr><td colspan="2"><h4 class="bottom-border-aqua"><?php echo $pia_lang['Maintenance_Tools_Tab_Subheadline_c'];?></h4></td></tr>
                <tr class="table_settings_row">
                    <td class="db_info_table_cell db_tools_table_cell_a"><button type="button" class="btn bg-yellow dbtools-button" id="btnPiaSetAPIKey" onclick="askPiaSetAPIKey()"><?php echo $pia_lang['Maintenance_Tool_setapikey'];?></button></td>
                    <td class="db_info_table_cell db_tools_table_cell_b"><?php echo $pia_lang['Maintenance_Tool_setapikey_text'];?></td>
                </tr>
                <tr class="table_settings_row">
                    <td class="db_info_table_cell db_tools_table_cell_a"><button type="button" class="btn btn-default pa-btn pa-btn-delete bg-yellow dbtools-button" id="btnTestNotific" onclick="askTestNotificationSystem()"><?php echo $pia_lang['Maintenance_Tool_test_notification'];?></button></td>
                    <td class="db_info_table_cell db_tools_table_cell_b"><?php echo $pia_lang['Maintenance_Tool_test_notification_text'];?></td>
                </tr>
                <tr class="table_settings_row">
                    <td class="db_info_table_cell db_tools_table_cell_a">

                        <div style="display: inline-block; text-align: center;">
                              <div class="form-group" style="width:160px; margin-bottom:5px;">
                                <!-- <div class="col-sm-7"> -->
                                  <div class="input-group">
                                    <input class="form-control" id="txtPiaArpTimer" type="text" value="<?php echo $pia_lang['Maintenance_arpscantimer_empty'];?>" readonly >
                                    <div class="input-group-btn">
                                      <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-expanded="false" id="dropdownButtonPiaArpTimer">
                                        <span class="fa fa-caret-down"></span></button>
                                      <ul id="dropdownPiaArpTimer" class="dropdown-menu dropdown-menu-right">
                                        <li><a href="javascript:void(0)" onclick="setTextValue('txtPiaArpTimer','15');">15min</a></li>
                                        <li><a href="javascript:void(0)" onclick="setTextValue('txtPiaArpTimer','30');">30min</a></li>
                                        <li><a href="javascript:void(0)" onclick="setTextValue('txtPiaArpTimer','60');">1h</a></li>
                                        <li><a href="javascript:void(0)" onclick="setTextValue('txtPiaArpTimer','120');">2h</a></li>
                                        <li><a href="javascript:void(0)" onclick="setTextValue('txtPiaArpTimer','720');">12h</a></li>
                                        <li><a href="javascript:void(0)" onclick="setTextValue('txtPiaArpTimer','1440');">24h</a></li>
                                        <li><a href="javascript:void(0)" onclick="setTextValue('txtPiaArpTimer','999999');">Very long</a></li>
                                      </ul>
                                    </div>
                                  </div>
                              </div>
                            </div>
                            <div style="display: block;">
                            <button type="button" class="btn btn-primary bg-yellow" style="margin-top:0px; width:160px; height:36px" id="btnSavePiaArpTimer" onclick="setPiAlertArpTimer()" ><div id="Timeralertspinner" class="loader disablespinner"></div> 
                                <div id="TimeralertText" class=""><?php echo $pia_lang['Maintenance_Tool_arpscansw'];?></div></button>
                            </div>
                        </div>

                    </td>
                    <td class="db_info_table_cell db_tools_table_cell_b"><?php echo $pia_lang['Maintenance_Tool_arpscansw_text'];?></td>
                </tr>
                <tr class="table_settings_row">

<?php

if (strtolower($_SESSION['WebProtection']) != 'true') {
    echo '          <td class="db_info_table_cell db_tools_table_cell_a"><button type="button" class="btn bg-red dbtools-button" id="btnPiaLoginEnable" onclick="askPiaLoginEnable()">'.$pia_lang['Maintenance_Tool_loginenable'].'</button></td>
                    <td class="db_info_table_cell db_tools_table_cell_b">'.$pia_lang['Maintenance_Tool_loginenable_text'].'</td>';}
else {
        echo '      <td class="db_info_table_cell db_tools_table_cell_a"><button type="button" class="btn bg-red dbtools-button" id="btnPiaLoginDisable" onclick="askPiaLoginDisable()">'.$pia_lang['Maintenance_Tool_logindisable'].'</button></td>
                    <td class="db_info_table_cell db_tools_table_cell_b">'.$pia_lang['Maintenance_Tool_logindisable_text'].'</td>';}

?>

                </tr>
            </table>

        </div>
        <div class="tab-pane <?php echo $pia_tab_tool; ?>" id="tab_DBTools">
            <div class="db_info_table">
                <div class="db_info_table_row">
                    <div class="db_tools_table_cell_a" style="">
                        <button type="button" class="btn btn-default pa-btn pa-btn-delete bg-red dbtools-button" id="btnDeleteMAC" onclick="askDeleteDevicesWithEmptyMACs()"><?php echo $pia_lang['Maintenance_Tool_del_empty_macs'];?></button>
                    </div>
                    <div class="db_tools_table_cell_b"><?php echo $pia_lang['Maintenance_Tool_del_empty_macs_text'];?></div>
                </div>
                <div class="db_info_table_row">
                    <div class="db_tools_table_cell_a" style="">
                        <button type="button" class="btn btn-default pa-btn pa-btn-delete bg-red dbtools-button" id="btnDeleteMAC" onclick="askDeleteAllDevices()"><?php echo $pia_lang['Maintenance_Tool_del_alldev'];?></button>
                    </div>
                    <div class="db_tools_table_cell_b"><?php echo $pia_lang['Maintenance_Tool_del_alldev_text'];?></div>
                </div>
                <div class="db_info_table_row">
                    <div class="db_tools_table_cell_a" style="">
                        <button type="button" class="btn btn-default pa-btn pa-btn-delete bg-red dbtools-button" id="btnDeleteUnknown" onclick="askDeleteUnknown()"><?php echo $pia_lang['Maintenance_Tool_del_unknowndev'];?></button>
                    </div>
                    <div class="db_tools_table_cell_b"><?php echo $pia_lang['Maintenance_Tool_del_unknowndev_text'];?></div>
                </div>
                <div class="db_info_table_row">
                    <div class="db_tools_table_cell_a" style="">
                        <button type="button" class="btn btn-default pa-btn pa-btn-delete bg-red dbtools-button" id="btnDeleteEvents" onclick="askDeleteEvents()"><?php echo $pia_lang['Maintenance_Tool_del_allevents'];?></button>
                    </div>
                    <div class="db_tools_table_cell_b"><?php echo $pia_lang['Maintenance_Tool_del_allevents_text'];?></div>
                </div>
                <div class="db_info_table_row">
                    <div class="db_tools_table_cell_a" style="">
                        <button type="button" class="btn btn-default pa-btn pa-btn-delete bg-red dbtools-button" id="btnDeleteActHistory" onclick="askDeleteActHistory()"><?php echo $pia_lang['Maintenance_Tool_del_ActHistory'];?></button>
                    </div>
                    <div class="db_tools_table_cell_b"><?php echo $pia_lang['Maintenance_Tool_del_ActHistory_text'];?></div>
                </div>
                <div class="db_info_table_row">
                    <div class="db_tools_table_cell_a" style="">
                        <button type="button" class="btn btn-default pa-btn pa-btn-delete bg-red dbtools-button" id="btnDeleteInactiveHosts" onclick="askDeleteInactiveHosts()"><?php echo $pia_lang['Maintenance_Tool_del_Inactive_Hosts'];?></button>
                    </div>
                    <div class="db_tools_table_cell_b"><?php echo $pia_lang['Maintenance_Tool_del_Inactive_Hosts_text'];?></div>
                </div>
            </div>
        </div>
        <div class="tab-pane <?php echo $pia_tab_backup; ?>" id="tab_BackupRestore">
            <div class="db_info_table">
                <div class="db_info_table_row">
                    <div class="db_tools_table_cell_a" style="">
                        <button type="button" class="btn btn-default pa-btn pa-btn-delete bg-red dbtools-button" id="btnPiaBackupDBtoArchive" onclick="askPiaBackupDBtoArchive()"><?php echo $pia_lang['Maintenance_Tool_backup'];?></button>
                    </div>
                    <div class="db_tools_table_cell_b"><?php echo $pia_lang['Maintenance_Tool_backup_text'];?></div>
                </div>
                <div class="db_info_table_row">
                    <div class="db_tools_table_cell_a" style="">
<?php
if (!$block_restore_button) {
    echo '<button type="button" class="btn btn-default pa-btn pa-btn-delete bg-red dbtools-button" id="btnPiaRestoreDBfromArchive" onclick="askPiaRestoreDBfromArchive()">'.$pia_lang['Maintenance_Tool_restore'].'<br>'.$latestbackup_date.'</button>';
} else {
    echo '<button type="button" class="btn btn-default pa-btn pa-btn-delete bg-red dbtools-button disabled" id="btnPiaRestoreDBfromArchive">'.$pia_lang['Maintenance_Tool_restore'].'<br>'.$latestbackup_date.'</button>';
}

?>                            
                    </div>
                    <div class="db_tools_table_cell_b"><?php echo $pia_lang['Maintenance_Tool_restore_text'];?></div>
                </div>
                <div class="db_info_table_row">
                    <div class="db_tools_table_cell_a" style="">
                        <button type="button" class="btn btn-default pa-btn pa-btn-delete bg-red dbtools-button" id="btnPiaPurgeDBBackups" onclick="askPiaPurgeDBBackups()"><?php echo $pia_lang['Maintenance_Tool_purgebackup'];?></button>
                    </div>
                    <div class="db_tools_table_cell_b"><?php echo $pia_lang['Maintenance_Tool_purgebackup_text'];?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Config Editor ----------------------------------------------------------------- -->

 <div class="box">
        <div class="box-body" id="updatecheck" style="text-align: center; padding-top: 5px; padding-bottom: 5px; height: 45px;">
           <button type="button" id="oisggfjmofeirfj" class="btn btn-danger" data-toggle="modal" data-target="#modal-config-editor"><?php echo $pia_lang['Maintenance_ConfEditor_Start'];?></button>
      </div>
    </div>

    <div class="box box-solid box-danger collapsed-box" style="margin-top: -15px;">
    <div class="box-header with-border" data-widget="collapse">
           <h3 class="box-title"><?php echo $pia_lang['Maintenance_ConfEditor_Hint'];?></h3>  
          <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool"><i class="fa fa-plus"></i></button>
          </div>        
    </div>
    <div class="box-body">
           <table class="table" style="text-align: left; margin-top: 10px;">
              <tbody>
                <tr>
                  <th scope="row" class="text-nowrap text-danger"><?php echo $pia_lang['Maintenance_ConfEditor_Restore'];?></th>
                  <td class="db_tools_table_cell_b"><?php echo $pia_lang['Maintenance_ConfEditor_Restore_info'];?></td>
                </tr>
                <tr>
                  <th scope="row" class="text-nowrap text-danger"><?php echo $pia_lang['Maintenance_ConfEditor_Backup'];?></th>
                  <td class="db_tools_table_cell_b"><?php echo $pia_lang['Maintenance_ConfEditor_Backup_info'];?></td>
                </tr>
                <tr>
                  <th scope="row" class="text-nowrap text-danger"><?php echo $pia_lang['Gen_Save'];?></th>
                  <td class="db_tools_table_cell_b"><?php echo $pia_lang['Maintenance_ConfEditor_Save_info'];?></td>
                </tr>
              </tbody>
            </table>
    </div>
    <!-- /.box-body -->
</div>

<!-- Config Editor - Modals ----------------------------------------------------------------- -->

    <div class="modal fade" id="modal-config-editor">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
              <form role="form" accept-charset="utf-8" method="post" action="./index.php">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span></button>
                    <h4 class="modal-title">Config Editor</h4>
                </div>
                <div class="modal-body" style="text-align: left;">
                    <textarea class="form-control" name="txtConfigFileEditor" spellcheck="false" wrap="off" style="resize: none; font-family: monospace; height: 70vh;"><?php echo file_get_contents('../config/pialert.conf');?></textarea>
                </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-danger" id="btnPiaRestoreConfigFile" data-dismiss="modal" style="margin: 5px" onclick="askRestoreConfigFile()"><?php echo $pia_lang['Maintenance_ConfEditor_Restore'];?></button>
                    <button type="button" class="btn btn-success" id="btnPiaBackupConfigFile" data-dismiss="modal" style="margin: 5px" onclick="BackupConfigFile()"><?php echo $pia_lang['Maintenance_ConfEditor_Backup'];?></button>
                    <button type="submit" class="btn btn-danger" name="SubmitConfigFileEditor" value="SaveNewConfig" style="margin: 5px"><?php echo $pia_lang['Gen_Save'];?></button>
                    <button type="button" class="btn btn-default" id="btnPiaEditorClose" data-dismiss="modal" style="margin: 5px"><?php echo $pia_lang['Gen_Close'];?></button>
                  </div>
              </form>
            </div>
        </div>
    </div>

<div style="width: 100%; height: 20px;"></div>
    <!-- ----------------------------------------------------------------------- -->

</section>

    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->

<!-- ----------------------------------------------------------------------- -->
<?php
  require 'php/templates/footer.php';
?>

<script>

// delete devices with emty macs
function askDeleteDevicesWithEmptyMACs () {
  // Ask 
  showModalWarning('<?php echo $pia_lang['Maintenance_Tool_del_empty_macs_noti'];?>', '<?php echo $pia_lang['Maintenance_Tool_del_empty_macs_noti_text'];?>',
    '<?php echo $pia_lang['Gen_Cancel'];?>', '<?php echo $pia_lang['Gen_Delete'];?>', 'deleteDevicesWithEmptyMACs');
}
function deleteDevicesWithEmptyMACs()
{ 
  // Delete device
  $.get('php/server/devices.php?action=deleteAllWithEmptyMACs', function(msg) {
    showMessage (msg);
  });
}

// Test Notifications
function askTestNotificationSystem () {
  // Ask 
  showModalWarning('<?php echo $pia_lang['Maintenance_Tool_test_notification_noti'];?>', '<?php echo $pia_lang['Maintenance_Tool_test_notification_noti_text'];?>',
    '<?php echo $pia_lang['Gen_Cancel'];?>', '<?php echo $pia_lang['Gen_Run'];?>', 'TestNotificationSystem');
}
function TestNotificationSystem()
{ 
  // Delete device
  $.get('php/server/devices.php?action=TestNotificationSystem', function(msg) {
    showMessage (msg);
  });
}

// delete all devices 
function askDeleteAllDevices () {
  // Ask 
  showModalWarning('<?php echo $pia_lang['Maintenance_Tool_del_alldev_noti'];?>', '<?php echo $pia_lang['Maintenance_Tool_del_alldev_noti_text'];?>',
    '<?php echo $pia_lang['Gen_Cancel'];?>', '<?php echo $pia_lang['Gen_Delete'];?>', 'deleteAllDevices');
}
function deleteAllDevices()
{ 
  // Delete device
  $.get('php/server/devices.php?action=deleteAllDevices', function(msg) {
    showMessage (msg);
  });
}

// delete all (unknown) devices 
function askDeleteUnknown () {
  // Ask 
  showModalWarning('<?php echo $pia_lang['Maintenance_Tool_del_unknowndev_noti'];?>', '<?php echo $pia_lang['Maintenance_Tool_del_unknowndev_noti_text'];?>',
    '<?php echo $pia_lang['Gen_Cancel'];?>', '<?php echo $pia_lang['Gen_Delete'];?>', 'deleteUnknownDevices');
}
function deleteUnknownDevices()
{ 
  // Execute
  $.get('php/server/devices.php?action=deleteUnknownDevices', function(msg) {
    showMessage (msg);
  });
}

// delete all Events 
function askDeleteEvents () {
  // Ask 
  showModalWarning('<?php echo $pia_lang['Maintenance_Tool_del_allevents_noti'];?>', '<?php echo $pia_lang['Maintenance_Tool_del_allevents_noti_text'];?>',
    '<?php echo $pia_lang['Gen_Cancel'];?>', '<?php echo $pia_lang['Gen_Delete'];?>', 'deleteEvents');
}
function deleteEvents()
{ 
  // Execute
  $.get('php/server/devices.php?action=deleteEvents', function(msg) {
    showMessage (msg);
  });
}

// delete Hostory 
function askDeleteActHistory () {
  // Ask 
  showModalWarning('<?php echo $pia_lang['Maintenance_Tool_del_ActHistory_noti'];?>', '<?php echo $pia_lang['Maintenance_Tool_del_ActHistory_noti_text'];?>',
    '<?php echo $pia_lang['Gen_Cancel'];?>', '<?php echo $pia_lang['Gen_Delete'];?>', 'deleteActHistory');
}
function deleteActHistory()
{ 
  // Execute
  $.get('php/server/devices.php?action=deleteActHistory', function(msg) {
    showMessage (msg);
  });
}

// Backup DB to Archive 
function askPiaBackupDBtoArchive () {
  // Ask 
  showModalWarning('<?php echo $pia_lang['Maintenance_Tool_backup_noti'];?>', '<?php echo $pia_lang['Maintenance_Tool_backup_noti_text'];?>',
    '<?php echo $pia_lang['Gen_Cancel'];?>', '<?php echo $pia_lang['Gen_Backup'];?>', 'PiaBackupDBtoArchive');
}
function PiaBackupDBtoArchive()
{ 
  // Execute
  $.get('php/server/devices.php?action=PiaBackupDBtoArchive', function(msg) {
    showMessage (msg);
  });
}

// Restore DB from Archive 
function askPiaRestoreDBfromArchive () {
  // Ask 
  showModalWarning('<?php echo $pia_lang['Maintenance_Tool_restore_noti'];?>', '<?php echo $pia_lang['Maintenance_Tool_restore_noti_text'];?>',
    '<?php echo $pia_lang['Gen_Cancel'];?>', '<?php echo $pia_lang['Gen_Restore'];?>', 'PiaRestoreDBfromArchive');
}
function PiaRestoreDBfromArchive()
{ 
  // Execute
  $.get('php/server/devices.php?action=PiaRestoreDBfromArchive', function(msg) {
    showMessage (msg);
  });
}

// Purge Backups 
function askPiaPurgeDBBackups() {
  // Ask 
  showModalWarning('<?php echo $pia_lang['Maintenance_Tool_purgebackup_noti'];?>', '<?php echo $pia_lang['Maintenance_Tool_purgebackup_noti_text'];?>',
    '<?php echo $pia_lang['Gen_Cancel'];?>', '<?php echo $pia_lang['Gen_Purge'];?>', 'PiaPurgeDBBackups');
}
function PiaPurgeDBBackups()
{ 
  // Execute
  $.get('php/server/devices.php?action=PiaPurgeDBBackups', function(msg) {
    showMessage (msg);
  });
}

// Switch Darkmode 
function askPiaEnableDarkmode() {
  // Ask 
  showModalWarning('<?php echo $pia_lang['Maintenance_Tool_darkmode_noti'];?>', '<?php echo $pia_lang['Maintenance_Tool_darkmode_noti_text'];?>',
    '<?php echo $pia_lang['Gen_Cancel'];?>', '<?php echo $pia_lang['Gen_Switch'];?>', 'PiaEnableDarkmode');
}
function PiaEnableDarkmode()
{ 
  // Execute
  $.get('php/server/devices.php?action=PiaEnableDarkmode', function(msg) {
    showMessage (msg);
  });
}

// Switch Web Service Monitor 
function askPiaEnableWebServiceMon() {
  // Ask 
  showModalWarning('<?php echo $pia_lang['Maintenance_Tool_webservicemon_noti'];?>', '<?php echo $pia_lang['Maintenance_Tool_webservicemon_noti_text'];?>',
    '<?php echo $pia_lang['Gen_Cancel'];?>', '<?php echo $pia_lang['Gen_Switch'];?>', 'PiaEnableWebServiceMon');
}
function PiaEnableWebServiceMon()
{ 
  // Execute
  $.get('php/server/devices.php?action=EnableWebServiceMon', function(msg) {
    showMessage (msg);
  });
}

// Toggle Graph 
function askPiaEnableOnlineHistoryGraph() {
  // Ask 
  showModalWarning('<?php echo $pia_lang['Maintenance_Tool_onlinehistorygraph_noti'];?>', '<?php echo $pia_lang['Maintenance_Tool_onlinehistorygraph_noti_text'];?>',
    '<?php echo $pia_lang['Gen_Cancel'];?>', '<?php echo $pia_lang['Gen_Switch'];?>', 'PiaEnableOnlineHistoryGraph');
}
function PiaEnableOnlineHistoryGraph()
{ 
  // Execute
  $.get('php/server/devices.php?action=PiaEnableOnlineHistoryGraph', function(msg) {
    showMessage (msg);
  });
}

// Set API-Key 
function askPiaSetAPIKey() {
  // Ask 
  showModalWarning('<?php echo $pia_lang['Maintenance_Tool_setapikey_noti'];?>', '<?php echo $pia_lang['Maintenance_Tool_setapikey_noti_text'];?>',
    '<?php echo $pia_lang['Gen_Cancel'];?>', '<?php echo $pia_lang['Gen_Okay'];?>', 'PiaSetAPIKey');
}
function PiaSetAPIKey()
{ 
  // Execute
  $.get('php/server/devices.php?action=PiaSetAPIKey', function(msg) {
    showMessage (msg);
  });
}

// Enable Login 
function askPiaLoginEnable() {
  // Ask 
  showModalWarning('<?php echo $pia_lang['Maintenance_Tool_loginenable_noti'];?>', '<?php echo $pia_lang['Maintenance_Tool_loginenable_noti_text'];?>',
    '<?php echo $pia_lang['Gen_Cancel'];?>', '<?php echo $pia_lang['Gen_Switch'];?>', 'PiaLoginEnable');
}
function PiaLoginEnable()
{ 
  // Execute
  $.get('php/server/devices.php?action=PiaLoginEnable', function(msg) {
    showMessage (msg);
  });
}

// Disable Login 
function askPiaLoginDisable() {
  // Ask 
  showModalWarning('<?php echo $pia_lang['Maintenance_Tool_logindisable_noti'];?>', '<?php echo $pia_lang['Maintenance_Tool_logindisable_noti_text'];?>',
    '<?php echo $pia_lang['Gen_Cancel'];?>', '<?php echo $pia_lang['Gen_Switch'];?>', 'PiaLoginDisable');
}
function PiaLoginDisable()
{ 
  // Execute
  $.get('php/server/devices.php?action=PiaLoginDisable', function(msg) {
    showMessage (msg);
  });
}

function setTextValue (textElement, textValue) {
  $('#'+textElement).val (textValue);
}

// Set Theme 
function setPiAlertTheme () {
  // update data to server
  $.get('php/server/devices.php?action=setPiAlertTheme&PiaSkinSelection='+ $('#txtSkinSelection').val(), function(msg) {
    showMessage (msg);
  });
}

// Set Language 
function setPiAlertLanguage() {
  // update data to server
  $.get('php/server/devices.php?action=setPiAlertLanguage&PiaLangSelection='+ $('#txtLangSelection').val(), function(msg) {
    showMessage (msg);
  });
}

// Set ArpScanTimer 
function setPiAlertArpTimer() {
  $.ajax({
        method: "GET",
        url: "./php/server/devices.php?action=setPiAlertArpTimer&PiaArpTimer=" + $('#txtPiaArpTimer').val(),
        data: "",
        beforeSend: function() { $('#Timeralertspinner').removeClass("disablespinner"); $('#TimeralertText').addClass("disablespinner");  },
        complete: function() { $('#Timeralertspinner').addClass("disablespinner"); $('#TimeralertText').removeClass("disablespinner"); },
        success: function(data, textStatus) {
            showMessage (data);
        }
    })
}

// Backup Configfile 
function BackupConfigFile()  {
  // Execute
  $.get('php/server/devices.php?action=BackupConfigFile', function(msg) {
    showMessage (msg);
  });
}

// Restore Configfile
function askRestoreConfigFile() {
  // Ask 
  showModalWarning('<?php echo $pia_lang['Maintenance_ConfEditor_Restore_noti'];?>', '<?php echo $pia_lang['Maintenance_ConfEditor_Restore_noti_text'];?>',
    '<?php echo $pia_lang['Gen_Cancel'];?>', '<?php echo $pia_lang['Gen_Run'];?>', 'RestoreConfigFile');
}
function RestoreConfigFile() {
  // Execute
  $.get('php/server/devices.php?action=RestoreConfigFile', function(msg) {
    showMessage (msg);
  });
}

// Set Device List Column
function askDeviceListCol() {
  // Ask 
  showModalWarning('<?php echo $pia_lang['Maintenance_Tool_DevListCol_noti'];?>', '<?php echo $pia_lang['Maintenance_Tool_DevListCol_noti_text'];?>',
    '<?php echo $pia_lang['Gen_Cancel'];?>', '<?php echo $pia_lang['Gen_Save'];?>', 'setDeviceListCol');
}
function setDeviceListCol() {
  // Execute
    $.get('php/server/devices.php?action=setDeviceListCol&'
    + '&favorite='       + ($('#chkFavorite')[0].checked * 1)
    + '&group='          + ($('#chkGroup')[0].checked * 1)
    + '&type='           + ($('#chkType')[0].checked * 1)
    + '&owner='          + ($('#chkOwner')[0].checked * 1)
    + '&firstsess='      + ($('#chkfirstSess')[0].checked * 1)
    + '&lastsess='       + ($('#chklastSess')[0].checked * 1)
    + '&lastip='         + ($('#chklastIP')[0].checked * 1)
    + '&mactype='        + ($('#chkMACtype')[0].checked * 1)
    + '&location='       + ($('#chkLocation')[0].checked * 1)
    , function(msg) {
    showMessage (msg);
  });
}

// Delete Inactive Hosts
function askDeleteInactiveHosts() {
  // Ask 
  showModalWarning('<?php echo $pia_lang['Maintenance_Tool_del_Inactive_Hosts'];?>', '<?php echo $pia_lang['Maintenance_Tool_del_Inactive_Hosts_text'];?>',
    '<?php echo $pia_lang['Gen_Cancel'];?>', '<?php echo $pia_lang['Gen_Delete'];?>', 'DeleteInactiveHosts');
}
function DeleteInactiveHosts() {
  // Execute
  $.get('php/server/devices.php?action=DeleteInactiveHosts', function(msg) {
    showMessage (msg);
  });
}

</script>


