<?php
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

$app = "owncloud";
$appname = "ownCloud";
$appversion = "8.1.1";
$applogs = array("/tmp/DroboApps/".$app."/log.txt",
                 "/tmp/DroboApps/".$app."/access.log",
                 "/tmp/DroboApps/".$app."/error.log");
$appsite = "https://owncloud.org/";
$apppage = "https://".$_SERVER['SERVER_ADDR'].":8051/";
$apphelp = "https://owncloud.org/support/";

exec("/bin/sh /usr/bin/DroboApps.sh sdk_version", $out, $rc);
if ($rc === 0) {
  $sdkversion = $out[0];
} else {
  $sdkversion = "2.0";
}

$op = $_REQUEST['op'];
switch ($op) {
  case "start":
    unset($out);
    exec("/bin/sh /usr/bin/DroboApps.sh start_app ".$app, $out, $rc);
    if ($rc === 0) {
      $opstatus = "okstart";
    } else {
      $opstatus = "nokstart";
    }
    break;
  case "stop":
    unset($out);
    exec("/bin/sh /usr/bin/DroboApps.sh stop_app ".$app, $out, $rc);
    if ($rc === 0) {
      $opstatus = "okstop";
    } else {
      $opstatus = "nokstop";
    }
    break;
  case "logs":
    $opstatus = "logs";
    break;
  default:
    $opstatus = "noop";
    break;
}

$droboip = $_SERVER['SERVER_ADDR'];
unset($out);
exec("/usr/bin/timeout -t 1 /usr/bin/wget -qO- http://ipecho.net/plain", $out, $rc);
if ($rc === 0) {
  $publicip = $out[0];
} else {
  $publicip = "";
}
$portscansite = "http://mxtoolbox.com/SuperTool.aspx?action=https%3a".$publicip."%3a8051&run=toolpage";

unset($out);
exec("/usr/bin/DroboApps.sh status_app ".$app, $out, $rc);
if ($rc !== 0) {
  unset($out);
  exec("/mnt/DroboFS/Shares/DroboApps/".$app."/service.sh status", $out, $rc);
}
if (strpos($out[0], "running") !== FALSE) {
  $apprunning = TRUE;
} else {
  $apprunning = FALSE;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="cache-control" content="no-cache" />
  <meta http-equiv="expires" content="-1" />
  <meta http-equiv="pragma" content="no-cache" />
  <title><?php echo $appname; ?> DroboApp</title>
  <link rel="stylesheet" type="text/css" media="screen" href="css/bootstrap.min.css" />
  <link rel="stylesheet" type="text/css" media="screen" href="css/custom.css" />
  <script src="js/jquery.min.js"></script>
  <script src="js/bootstrap.min.js"></script>
</head>

<body>
<nav class="navbar navbar-default navbar-fixed-top">
  <div class="container-fluid">
    <div class="navbar-header">
      <a class="navbar-brand" href="<?php echo $appsite; ?>" target="_new"><img alt="<?php echo $appname; ?>" src="img/app_logo.png" /></a>
    </div>
    <div class="collapse navbar-collapse" id="navbar">
      <ul class="nav navbar-nav navbar-right">
        <li><a class="navbar-brand" href="http://www.drobo.com/" target="_new"><img alt="Drobo" src="img/drobo_logo.png" /></a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container top-toolbar">
  <div role="toolbar" class="btn-toolbar">
    <div role="group" class="btn-group">
      <p class="title">About <?php echo $app; ?> <?php echo $appversion; ?></p>
    </div>
    <div role="group" class="btn-group pull-right">
<?php if ($apprunning) { ?>
<?php if ($sdkversion != "2.0") { ?>
      <a role="button" class="btn btn-primary" href="?op=stop" onclick="$('#pleaseWaitDialog').modal(); return true"><span class="glyphicon glyphicon-stop"></span> Stop</a>
<?php } ?>
      <a role="button" class="btn btn-primary" href="<?php echo $apppage; ?>" target="_new"><span class="glyphicon glyphicon-globe"></span> Go to App</a>
<?php } else { ?>
<?php if ($sdkversion != "2.0") { ?>
      <a role="button" class="btn btn-primary" href="?op=start" onclick="$('#pleaseWaitDialog').modal(); return true"><span class="glyphicon glyphicon-play"></span> Start</a>
<?php } ?>
      <a role="button" class="btn btn-primary disabled" href="<?php echo $apppage; ?>" target="_new"><span class="glyphicon glyphicon-globe"></span> Go to App</a>
<?php } ?>
      <a role="button" class="btn btn-primary" href="<?php echo $apphelp; ?>" target="_new"><span class="glyphicon glyphicon-question-sign"></span> Help</a>
    </div>
  </div>
</div>

<div role="dialog" id="pleaseWaitDialog" class="modal animated bounceIn" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-body">
        <p id="myModalLabel">Operation in progress... please wait.</p>
        <div class="progress">
          <div class="progress-bar progress-bar-striped active" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%">
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="container">
  <div class="row">
    <div class="col-xs-3"></div>
    <div class="col-xs-6">
<?php switch ($opstatus) { ?>
<?php case "okstart": ?>
      <div class="alert alert-success fade in" id="opstatus">
        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
        <?php echo $appname; ?> was successfully started.
      </div>
<?php break; case "nokstart": ?>
      <div class="alert alert-error fade in" id="opstatus">
        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
        <?php echo $appname; ?> failed to start. See logs below for more information.
      </div>
<?php break; case "okstop": ?>
      <div class="alert alert-success fade in" id="opstatus">
        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
        <?php echo $appname; ?> was successfully stopped.
      </div>
<?php break; case "nokstop": ?>
      <div class="alert alert-error fade in" id="opstatus">
        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
        <?php echo $appname; ?> failed to stop. See logs below for more information.
      </div>
<?php break; case "okrootpass": ?>
      <div class="alert alert-success fade in" id="opstatus">
        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
        New root password successfully generated.
      </div>
<?php break; case "nokrootpass": ?>
      <div class="alert alert-error fade in" id="opstatus">
        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
        Failed to generate a new root password. See logs below for more information.
      </div>
<?php break; } ?>
      <script>
      window.setTimeout(function() {
        $("#opstatus").fadeTo(500, 0).slideUp(500, function() {
          $(this).remove(); 
        });
      }, 2000);
      </script>
    </div><!-- col -->
    <div class="col-xs-3"></div>
  </div><!-- row -->

  <div class="row">
    <div class="col-xs-12">

  <!-- description -->
  <div class="panel-group" id="description">
    <div class="panel panel-default">
      <div class="panel-heading">
        <h4 class="panel-title"><a data-toggle="collapse" data-parent="#description" href="#descriptionbody">Description</a></h4>
      </div>
      <div id="descriptionbody" class="panel-collapse collapse in">
        <div class="panel-body">
          <p>This DroboApp deploys ownCloud on your Drobo.</p>
          <p>ownCloud is a self-hosted file sync and share server. It provides access to your data through a web interface, sync clients or WebDAV.</p>
        </div>
      </div>
    </div>
  </div>

  <!-- shorthelp -->
  <div class="panel-group" id="shorthelp">
    <div class="panel panel-default">
      <div class="panel-heading">
        <h4 class="panel-title"><a data-toggle="collapse" data-parent="#shorthelp" href="#shorthelpbody">Getting started</a></h4>
      </div>
      <div id="shorthelpbody" class="panel-collapse collapse in">
        <div class="panel-body">
          <p>To access ownCloud on your Drobo click the &quot;Go to App&quot; button above.</p>
          <p>To ensure data privacy, ownCloud is only accessible over HTTPS. If no SSL certificates are available, ownCloud will automatically generate a self-signed certificate. <strong>Using self-signed certificates will cause warnings from some web browsers:</strong></p>
          <ul>
            <li>In Google Chrome, there will be a page indicating that &quot;your connection is not private&quot;. Click the &quot;Advanced&quot; link at the bottom of that page, and then the &quot;Proceed to <?php echo $droboip; ?> (unsafe)&quot; link further below.</li>
            <li>In Firefox, there will be a page indicating that &quot;this connection is untrusted&quot;. Click the &quot;I understand the risks&quot; link at the bottom of that page, then the &quot;Add Exception...&quot; button further below, and then the &quot;Confirm Security Exception&quot; button at the bottom of the dialog window.</li>
            <li>In Safari, there will be a dialog window indicating that &quot;Safari can&apos;t verify the identity of the wesite &ldquo;<?php echo $droboip; ?>&rdquo;.&quot;. Click the &quot;Continue&quot; button.</li>
          </ul>
          <p>To avoid browser warnings, please:</p>
          <ul>
            <li>Either replace the server certificate and private key with a certificate from a certification authority (see below),</li>
            <li>Or add the self-signed certificate to your browser&apos;s certificate store.</li>
          </ul>
          <p>During the first access ownCloud will ask information to configure itself. This information includes:</p>
          <ul>
            <li>An admin account name and password. Please choose a strong password, especially if ownCloud will be exposed to the internet.</li>
            <li>Storage configuration. For small deployments, leave the default values (SQLite).</li>
          </ul>
          <p>If everything went fine after clicking &quot;Finish setup&quot;, there will be a &quot;Welcome to ownCloud&quot; page.</p>
        </div>
      </div>
    </div>
  </div>

  <!-- moreinfo -->
  <div class="panel-group" id="moreinfo">
    <div class="panel panel-default">
      <div class="panel-heading">
        <h4 class="panel-title"><a data-toggle="collapse" data-parent="#moreinfo" href="#moreinfobody">Next steps</a></h4>
      </div>
      <div id="moreinfobody" class="panel-collapse collapse in">
        <div class="panel-body">
          <p>Information about creating users, sharing files and folders can be found in the <a href="<?php echo $apphelp; ?>" target="_new">ownCloud support pages</a>.</p>
          <p>ownCloud has mobile clients for both <a href="https://play.google.com/store/apps/details?id=com.owncloud.android" target="_new">Android</a> and <a href="https://itunes.apple.com/us/app/owncloud/id543672169" target="_new">iOS</a>, and a <a href="https://owncloud.org/install/#install-clients" target="_new">desktop clients</a> for Windows, OS X, and Linux.</p>
          <p>A few extra steps are necessary to connect to ownCloud on your Drobo using a mobile app or the desktop client from outside your home network. Those are:</p>
          <ol>
            <li>Make sure your Drobo is reachable from the internet. The following <a href="https://en.wikipedia.org/wiki/List_of_TCP_and_UDP_port_numbers" target="_new">port</a> must be reachable from the internet (check your <a href="http://portforward.com/" target="_new">router and/or firewall documentation</a>), and must be forwarded to the Drobo:</li>
              <ul>
                <li>ownCloud: TCP:8051</li>
              </ul>
            <li>If your public IP address changes over time<?php if ($publicip != "") { ?> (currently <?php echo $publicip; ?>)<?php } ?>, configure a <a href="http://www.howtogeek.com/66438/how-to-easily-access-your-home-network-from-anywhere-with-ddns/" target="_new">dynamic DNS address</a> to your public IP address.</li>
          </ol>
          <p>It is possible to have a trusted SSL certificate for free from StartSSL. Follow <a href="http://arstechnica.com/security/2009/12/how-to-get-set-with-a-secure-sertificate-for-free/" target="_new">this article</a> to get the step-by-step instructions.</p>
          <p>Once you have the certificate files, place them in <code>/mnt/DroboFS/Shares/DroboApps/owncloud/etc/certs/</code>, either following the naming convention in place (<code>cert.pem</code> for the certificate and <code>key.pem</code> for the private key) or edit <code>/mnt/DroboFS/Shares/DroboApps/owncloud/etc/owncloudapp.conf</code> to <a href="http://httpd.apache.org/docs/2.4/ssl/ssl_howto.html" target="_new">adjust the SSL configuration</a>.</p>
          <p>For larger deployments (that is, many simultaneous users) it is recommended to install the MySQL DroboApp and configure ownCloud to use MySQL as the storage database instead of SQLite.</p>
        </div>
      </div>
    </div>
  </div>

  <!-- troubleshooting -->
  <div class="panel-group" id="troubleshooting">
    <div class="panel panel-default">
      <div class="panel-heading">
        <h4 class="panel-title"><a data-toggle="collapse" data-parent="#troubleshooting" href="#troubleshootingbody">Troubleshooting</a></h4>
      </div>
      <div id="troubleshootingbody" class="panel-collapse collapse">
        <div class="panel-body">
          <?php if (! $apprunning) { ?><p><strong>I cannot connect to owncloud on the Drobo.</strong></p>
          <p>Make sure that owncloud is running. Currently it seems to be <strong>stopped</strong>.</p><?php } ?>
          <p><strong>I cannot connect to ownCloud on the Drobo from outside my home network.</strong></p>
          <?php if ($publicip == "") { ?><p>Make sure that your internet connection is working. Currently it seems your Drobo cannot retrieve its public IP address.</p><?php } ?>
          <p>Make sure that your ports are correctly forwarded and <a href="<?php echo $portscansite; ?>" target="_new">reachable from the internet</a>. If not, please contact your ISP to unblock them.</p>
        </div>
      </div>
    </div>
  </div>

  <!-- logfile -->
  <div class="panel-group" id="logfile">
    <div class="panel panel-default">
      <div class="panel-heading">
        <h4 class="panel-title"><a data-toggle="collapse" data-parent="#logfile" href="#logfilebody">Log information</a></h4>
      </div>
      <div id="logfilebody" class="panel-collapse collapse <?php if ($opstatus == "logs") { ?>in<?php } ?>">
        <div class="panel-body">
          <div role="toolbar" class="btn-toolbar">
            <div role="group" class="btn-group  pull-right">
              <a role="button" class="btn btn-default" href="?op=logs" onclick="$('#pleaseWaitDialog').modal(); return true"><span class="glyphicon glyphicon-refresh"></span> Reload logs</a>
            </div>
          </div>
<?php foreach ($applogs as $applog) { ?>
          <p>This is the content of <code><?php echo $applog; ?></code>:</p>
          <pre class="pre-scrollable">
<?php if (substr($applog, 0, 1) === ":") {
  echo shell_exec(substr($applog, 1));
} else {
  echo file_get_contents($applog);
} ?>
          </pre>
<?php } ?>
        </div>
      </div>
    </div>
  </div>

  <!-- summary -->
  <div class="panel-group" id="summary">
    <div class="panel panel-default">
      <div class="panel-heading">
        <h4 class="panel-title"><a data-toggle="collapse" data-parent="#summary" href="#summarybody">Summary of changes</a></h4>
      </div>
      <div id="summarybody" class="panel-collapse collapse">
        <div class="panel-body">
          <p>This is the first release of the DroboApp:</p>
          <ol>
            <li>ownCloud 8.1.1 (<a href="https://owncloud.org/changelog/" target="_new">full changelog</a>).</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

    </div><!-- col -->
  </div><!-- row -->
</div><!-- container -->

<footer>
  <div class="container">
    <div class="pull-right">
      <small>All copyrighted materials and trademarks are the property of their respective owners.</small>
    </div>
  </div>
</footer>
</body>
</html>
