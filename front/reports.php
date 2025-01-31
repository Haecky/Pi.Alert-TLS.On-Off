<!-- ---------------------------------------------------------------------------
#  Pi.Alert
#  Open Source Network Guard / WIFI & LAN intrusion detector 
#
#  reports.php - Front module. WebGUI Notification page
#-------------------------------------------------------------------------------
#  leiweibau 2023                                          GNU GPLv3
#--------------------------------------------------------------------------- -->

<?php
session_start();
// Turn off php errors
error_reporting(0);
if ($_SESSION["login"] != 1)
  {
      header('Location: ./index.php');
      exit;
  }
require 'php/templates/header.php';
require 'php/server/db.php';
?>

<!-- Page ------------------------------------------------------------------ -->
<div class="content-wrapper">

<!-- Content header--------------------------------------------------------- -->
    <section class="content-header">
    <?php require 'php/templates/notification.php'; ?>
      <h1 id="pageTitle">
         <?php echo $pia_lang['Reports_Title'];?>
      </h1>
    </section>

    <!-- Main content ---------------------------------------------------------- -->
    <section class="content">
      <div class="box">
          <div class="box-body" id="RemoveAllNotifications" style="text-align: center; padding-top: 5px; padding-bottom: 5px; height: 45px;">
              <button type="button" id="rqwejwedewjpjo" class="btn btn-danger" onclick="askdeleteAllNotifications()"><?php echo $pia_lang['Reports_delete_all'];?></button>
        </div>
      </div>

<?php

$directory = './reports/';
$scanned_directory = array_diff(scandir($directory), array('..', '.'));
rsort($scanned_directory);
foreach ($scanned_directory as $file) {
  if (substr($file, -4) == '.txt') {
    //echo $file.'<br>';
    $headtitle = explode("-", $file);
    $headeventtype = explode("_", $file);
    $webgui_report = file_get_contents($directory.$file);
    $webgui_report = str_replace("\n\n\n", "", $webgui_report);
    echo '<div class="box box-solid">
            <div class="box-header with-border">
              <h3 class="box-title">'.substr($headtitle[0], 0, 4).'.'.substr($headtitle[0], 4, 2).'.'.substr($headtitle[0], 6, 2).' - '.substr($headtitle[1], 0, 2).':'.substr($headtitle[1], 2, 2).' - '.substr($headeventtype[1], 0, -4).'</h3>
                <div class="pull-right">
                  <a href="./reports.php?remove_report='.substr($file, 0, -4).'" class="btn btn-sm btn-danger"><i class="fa fa-fw fa-trash-o"></i></a>
                </div>
            </div>
            <div class="box-body"><pre style="background-color: transparent; border: solid 1px #777">';
    echo $webgui_report;       
    echo '</pre></div>
          </div>';
  }
}

?>
    <div style="width: 100%; height: 20px;"></div>
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->

<!-- ----------------------------------------------------------------------- -->
<?php
  require 'php/templates/footer.php';
?>

<script>
function askdeleteAllNotifications () {
  // Ask 
  showModalWarning('<?php echo $pia_lang['Reports_delete_all_noti'];?>', '<?php echo $pia_lang['Reports_delete_all_noti_text'];?>',
    '<?php echo $pia_lang['Gen_Cancel'];?>', '<?php echo $pia_lang['Gen_Delete'];?>', 'deleteAllNotifications');
}
function deleteAllNotifications()
{ 
  // Delete
  $.get('php/server/devices.php?action=deleteAllNotifications', function(msg) {
    showMessage (msg);
  });
}
</script>