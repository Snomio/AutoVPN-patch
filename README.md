# AutoVPN-patch PHP script

Since version greater than 8.7.5.15 and 8.9.3.5 the OpenVPN feature has been removed from the default Snom firmware binary.
In order to enable the OpenVPN feature you will need to install a patch activating the feature.

Once the VPN patcvh is installed the phone will include the `X-snom-vpn: available` HTTP header in every HTTP request.

This PHP script will check the firmware version and the presence of the `X-snom-vpn` header and will do the following actions:
* if the firmware version is prior to **8.7.5.15** => do nothing
* if the firmware version is prior to **8.9.3.5** => do nothing
* if the request contains the `X-snom-vpn: available` => do nothing
* in all the other cases provides the XML to perform the upgrade applying the patch

## Usage

* clone this repository or download the `check_vpn.php` script
* check the  device-URL map at the beginning of the script
* move the script into an HTTP server with PHP enabled
* configure the URL pointing to the `check_vpn.php` script as a `setting_server` on the phone
* reboot the phone
