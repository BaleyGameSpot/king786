<?php
require_once ('../common.php');
$siteUrl = $tconfig["tsite_url"];
$fav_icon_image = "favicon.ico";
if (file_exists($tconfig["tpanel_path"] . $logogpath . $fav_icon_image)) {
    $fav_icon_image = $tconfig["tsite_url"] . $logogpath . $fav_icon_image;
} else {
    $fav_icon_image = $tconfig["tsite_url"] . '' . ADMIN_URL_CLIENT . '/' . 'images/' . $fav_icon_image;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Socket Cluster Diagnostics</title>
    <style type="text/css">
        html, body {
            font-family: 'Verdana';
        }
    </style>
    <link rel="icon" href="<?php echo $fav_icon_image; ?>" type="image/x-icon">
    <script type='text/javascript' src="<?= $siteUrl; ?>assets/js/jquery.min.js"></script>
    <script type="text/javascript" src="<?= $siteUrl; ?>assets/js/getDataFromApi.js"></script>
    <script type="text/javascript" src="<?= $siteUrl;?>assets/libraries/scClient-js/socketcluster-client.js"></script>
    <script type="text/javascript">
        var TSITE_SC_PROTOCOL = "<?php echo($tconfig['tsite_sc_protocol']); ?>";
        var TSITE_SC_HOST = "<?php echo($tconfig['tsite_sc_host']); ?>";
        var TSITE_HOST_SC_PATH = "<?php echo($tconfig['tsite_host_sc_path']); ?>";
        var TSITE_HOST_SC_PORT = "<?php echo($tconfig['tsite_host_sc_port']); ?>";
    </script>
    <script type="text/javascript" src="<?= $siteUrl;?>assets/js/socketclustercls.js"></script>
    <script>
        var channel = "SOCKET_CLUSTER_STATUS_CHECK";
        
        var sc_js = sc_php = 0;

        var messageData = {
            message: 'Socket Cluster PubSub JS working',
            type: 'JS',
            ChannelName: channel
        }
        messageData1 = JSON.stringify(messageData);
        SOCKET_OBJ.publish(channel, messageData1);
        
        var sc_js_interval, sc_php_interval;

        SOCKET_OBJ.subscribe(channel, function (result) {
            //var result = JSON.parse(data);

            if(result.type == "JS") {
                sc_js = 1;
            }
            if(result.type == "PHP") {
                sc_php = 1;
            }
            // console.log("Socket Cluster Message Found");
            // console.log(result);
            // console.log("SC JS: "+sc_js);
            // console.log("SC PHP: "+sc_php);    

            if(sc_js == 0) {  
                sc_js_interval = setInterval(function(){ 
                    publishFromClient(channel, messageData1);   
                }, 2000);
            }
            else {
                clearInterval(sc_js_interval);
            }

            if(sc_php == 0) {
                sc_php_interval = setInterval(function(){ 
                    publishFromServer();   
                }, 2000);
            }
            else {
                clearInterval(sc_php_interval);
            }

            if(sc_js == 1 && sc_php == 1) {
                var statusArr = {
                    message: 'Socket Cluster Working',
                    statusName: 'SC_STATUS',
                    success: true
                }

                $('h1').html("Socket Cluster Working");
            }
        });

        function publishFromServer() {

            var ajaxData = {
                'URL': '<?= $tconfig['tsite_url_main_admin'] ?>sc_publish.php',
                'AJAX_DATA': "",
            };
            getDataFromAjaxCall(ajaxData, function(response) {
                if(response.action == "1") {
                    var data = response.result;
                }
                else {
                    console.log(response.result);
                }
            });
        }      
        
    </script>
</head>
<body>
<h1>Socket Cluster Status Checking ...</h1>
<p>Please wait for few seconds. If the status doesn't change then please contact technical team.</p>
<p><a href="javascript:void(0);" onclick="window.close()">Close</a></p>
</body>

</html>