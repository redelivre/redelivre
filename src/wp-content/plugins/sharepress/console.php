<?php
if (!defined('ABSPATH')) exit; /* silence is golden */ 

// path to sharepress
$path = dirname(__FILE__);

// get a list of all the log files
$logs = array();
$dir = opendir($path);
while($file = readdir($dir)) {
  if (preg_match('/sharepress-((\d\d\d\d)(\d\d)(\d\d))\.log/i', $file, $matches)) {
    $logs["{$matches[1]}"] = (object) array(
      'date' => date('Y/m/d', mktime(0, 0, 0, $matches[3], $matches[4], $matches[2])),
      'path' => $path.DIRECTORY_SEPARATOR.$file
    );
  }
}
closedir($dir);

if (!$logs) {
  wp_die('There are no SharePress log files to view.');
}

// reverse order - newest at the top (by name)
krsort($logs);
// which file are we viewing?
$keys = array_keys($logs);
$file = isset($logs[$_REQUEST['log']]) ? $logs[$_REQUEST['log']] : $logs[$keys[0]];

?>
<div class="wrap">
  <div id="icon-edit-pages" class="icon32 icon32-posts-page"><br /></div>
  <h2>SharePress Logs</h2>
  <div class="fileedit-sub">
    <div class="alignleft">
      <big>Viewing <strong><?php echo pathinfo($file->path, PATHINFO_FILENAME) ?>.log</strong></big>
    </div>
    <div class="alignright">
      <form method="get">
        <input type="hidden" name="page" value="sharepress" />
        <strong><label for="log">Select log file to view: </label></strong>
        <select name="log" id="log">
          <?php foreach($logs as $ts => $log) { ?>
            <option value="<?php echo $ts ?>" <?php if ($log->date == $file->date) echo 'selected="selected"' ?>><?php echo $log->date ?></option>
          <?php } ?>
        </select>
        <input type="submit" id="Submit" class="button" value="Select">
      </form>
    </div>
    <br class="clear" />
  </div>
  <form name="template" id="template">
    <textarea cols="70" rows="25" name="newcontent" id="newcontent" tabindex="1" style="width:100%;"><?php echo file_get_contents($file->path) ?></textarea>
  </form>
</div>
