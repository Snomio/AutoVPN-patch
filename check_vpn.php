<?php
/*
 * Array containing all the VPN patch/device map.
 * Please double check the URLs here.
 */
$VPN_PATCH_URL = array (
    "snom710" => "http://downloads.snom.com/fw/snom710-vpnfeature-r.bin",
    "snom715" => "http://downloads.snom.com/fw/snom715-vpnfeature-r.bin",
    "snom720" => "http://downloads.snom.com/fw/snom720-vpnfeature-r.bin",
    "snom725" => "http://downloads.snom.com/fw/snom725-vpnfeature-r.bin",
    "snomD745" => "http://downloads.snom.com/fw/snomD745-vpnfeature-r.bin",
    "snom760" => "http://downloads.snom.com/fw/snom760-vpnfeature-r.bin",
    "snomD765" => "http://downloads.snom.com/fw/snomD765-vpnfeature-r.bin",

    "snomD315" => "http://downloads.snom.com/fw/snomD315-vpnfeature-r.bin",
    "snomD345" => "http://downloads.snom.com/fw/snomD345-vpnfeature-r.bin",
    "snomD375" => "http://downloads.snom.com/fw/snomD375-vpnfeature-r.bin",

    "snom821" => "http://downloads.snom.com/fw/snom821-vpnfeature-r.bin",
    "snom870" => "http://downloads.snom.com/fw/snom870-vpnfeature-r.bin",
    
    "snomMP" => "http://downloads.snom.com/fw/snomMP-vpnfeature-r.bin"
);

/*
 * This function returns the local URL from where this script is served 
 */
function get_current_url() {
    $protocol = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ? "https" : "http");
    $base_url = $protocol . "://" . $_SERVER['HTTP_HOST'];
    $complete_url =   $base_url . $_SERVER["PHP_SELF"];
    return $complete_url;
}

/*
 * This function performs the fw update in case the header X-snom-vpn is not present
 * or differs from "available". In case the request comes from a version < than 8.7.5.15 
 * or 8.9.3.5 we dont' perform any update.
 *
 * If the VPN feature is not supported this function generates the XML setting the
 * firmware_status pointing to this script setting the snom-vpn-patch-fw GET parameter
 *
 * If the the URI contains the snom-vpn-patch-fw=true parameter the script returns the
 * XML firmware container
 *
 * If debug is True the funcion logs some debug messages into the error log.
 *
 * IMPORTANT NOTE: in case the script detects that the VPN feature is not available
 * after sending the XML response the script stops the execution calling the die() function.
 */
function vpn_auto_patch ($debug = False){
    global $VPN_PATCH_URL;

    if($_SERVER['HTTP_X_SNOM_VPN'] == "available"){
        if($debug){
            error_log("VPN patch already installed, ignoring");
        }
        return;
    }

    if($debug){
        error_log("VPN patch not installed");
    }
    
    /* get the user agent to grab the firmware version */
    $agent = $_SERVER['HTTP_USER_AGENT'];

    if (!preg_match("/(snom[^-]+)-SIP ([0-9.]+)/", $agent, $matches)) {
        die("ERROR: Unknown user agent (Not a snom phone? Not a regular release?): $agent");
    }

    $phone_type = $matches[1];
    $software_version = $matches[2];
    $software_version_parts = explode('.', $software_version);
    $major = $software_version_parts[0];
    $minor = $software_version_parts[1];
    $release = $software_version_parts[2];
    $patch = $software_version_parts[3];
   
    if($debug){
        error_log("Version: $software_version");
    } 
    if($major < 8){ /* FW < 8.X */
        if($debug){
            error_log("Software major version < 8, ignoring");
        }
        return;
    }
    if($major == 8 and $minor < 7){ /* FW < 8.7.X */
        if($debug){
            error_log("Software minor version < 8.7, ignoring");
        }
        return;
    }
    if($major == 8 and $minor == 7 and $release < 5){ /* FW < 8.7.5.X */
        if($debug){
            error_log("Software release version < 8.7.5, ignoring");
        }
        return;
    }
    if($major == 8 and $minor == 7 and $release == 5 and $patch < 15){ /* FW < 8.7.5.15 */
        if($debug){
            error_log("Software patch version < 8.7.5.15, ignoring");
        }
        return;
    }
    if($major == 8 and $minor == 9 and $release < 3){
        if($debug){
            error_log("Software release version < 8.9.3, ignoring"); /* FW < 8.9.3.X */
        }
        return;
    }
    if($major == 8 and $minor == 9 and $release == 3 and $patch < 5){ /* FW < 8.9.3.5 */
        if($debug){
            error_log("Software patch version < 8.9.3.5, ignoring");
        }
        return;
    }
    
    if($debug){
        error_log("Phone Type: $phone_type");
    }    
    
    header("Content-type: text/xml");
    
    if($_GET["snom-vpn-patch-fw"] == "true"){
        if($debug){
            error_log("Generating the firmware container");
            error_log("VPN Patch URL: $VPN_PATCH_URL[$phone_type]");
        }
?>
<?xml version="1.0" encoding="utf-8"?>
<firmware-settings>
    <firmware perm=""><?php echo $VPN_PATCH_URL[$phone_type]?></firmware>
</firmware-settings>
<?php
    die();
    } else {
        if($debug){
            error_log("Generating the settings");
        }
        $fw_status = get_current_url() . "?snom-vpn-patch-fw=true";
?>
<?xml version="1.0" encoding="utf-8"?>
<settings e="2">
    <phone-settings e="2">
        <update_policy>auto_update</update_policy>
        <firmware_status><?php echo $fw_status?></firmware_status>
    </phone-settings>
</settings>
<?php
    die();
    }
}
?>
