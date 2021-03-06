<?php if ($appprotos[0] == "https") { ?>
<p>It is possible to have a trusted SSL certificate for free from StartSSL. Follow <a href="http://arstechnica.com/security/2009/12/how-to-get-set-with-a-secure-sertificate-for-free/" target="_new">this article</a> to get the step-by-step instructions.</p>
<p>Once you have the certificate files, place them in <code>/mnt/DroboFS/Shares/DroboApps/.AppData/<?php echo $app; ?>/certs/</code>, either following the naming convention in place (<code>cert.pem</code> for the certificate and <code>key.pem</code> for the private key) or edit <code>/mnt/DroboFS/Shares/DroboApps/<?php echo $app; ?>/etc/<?php echo $app; ?>app.conf</code> to <a href="http://httpd.apache.org/docs/2.4/ssl/ssl_howto.html" target="_new">adjust the SSL configuration</a>.</p>
<?php } ?>
