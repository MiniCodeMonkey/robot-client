<?php

namespace Hetzner\Robot;

use stdClass;

/**
 * Client class for robot webservice
 *
 * Documentation: https://robot.your-server.de/doc/webservice/en.html
 *
 * Copyright (c) 2013-2017 Hetzner Online GmbH
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
class Client extends RobotRestClient
{
  const VERSION = '2017.05';

  /**
   * Class constructor
   *
   * @param $url      Robot webservice url
   * @param $login    Robot login name
   * @param $password Robot password
   * @param $verbose
   */
  public function __construct($url, $login, $password, $verbose = false)
  {
    parent::__construct($url, $login, $password, $verbose);
    $this->setHttpHeader('Accept', 'application/json');
    $this->setHttpHeader('User-Agent', 'HetznerRobotClient/' . self::VERSION);
  }

  /**
   * Execute HTTP request
   *
   * @return object Response
   *
   * @throws RobotClientException
   */
  protected function executeRequest()
  {
    $result = parent::executeRequest();

    if ($result['response'] === false)
    {
      throw new RobotClientException('robot not reachable', 'NOT_REACHABLE');
    }

    if (empty($result['response']))
    {
      $response = new stdClass();
    }
    else
    {
      $response = json_decode($result['response']);
    }

    if ($response === null)
    {
      throw new RobotClientException('response can not be decoded', 'RESPONSE_DECODE_ERROR');
    }

    if ($result['response_code'] >= 400 && $result['response_code'] <= 503)
    {
      throw new RobotClientException($response->error->message, $response->error->code);
    }

    return $response;
  }

  /**
   * Get failover
   *
   * @param $ip Failover ip address
   * @param $query additional query string
   *
   * @return object Failover object
   *
   * @throws RobotClientException
   */
  public function failoverGet($ip = null, array $query = null)
  {
    $url = $this->baseUrl . '/failover';

    if ($ip)
    {
      $url .= '/' . $ip;
    }
    if ($query)
    {
      $url .= '?' . http_build_query($query);
    }

    return $this->get($url);
  }

  /**
   * Get failover by server ip
   *
   * @param $serverIp Server main ip address
   *
   * @return object Failover object
   *
   * @throws RobotClientException
   */
  public function failoverGetByServerIp($serverIp)
  {
    return $this->failoverGet(null, array('server_ip' => $serverIp));
  }

  /**
   * Route failover
   *
   * @param $failoverIp Failover ip address
   * @param $activeServerIp Target server ip address
   *
   * @return object Failover object
   *
   * @throws RobotClientException
   */
  public function failoverRoute($failoverIp, $activeServerIp)
  {
    $url = $this->baseUrl . '/failover/' . $failoverIp;

    return $this->post($url, array('active_server_ip' => $activeServerIp));
  }

  /**
   * Get server reset
   *
   * @param $ip Server main ip
   *
   * @return object Reset object
   *
   * @throws RobotClientException
   */
  public function resetGet($ip = null)
  {
    $url = $this->baseUrl . '/reset';
    if ($ip)
    {
      $url .= '/' . $ip;
    }

    return $this->get($url);
  }

  /**
   * Execute server reset
   *
   * @param $ip Server main ip
   * @param $type Reset type
   *
   * @return object Reset object
   *
   * @throws RobotClientException
   */
  public function resetExecute($ip, $type)
  {
    $url = $this->baseUrl . '/reset/' . $ip;

    return $this->post($url, array('type' => $type));
  }

  /**
   * Get current boot config
   *
   * @param $ip Server main ip
   *
   * @return object Boot object
   *
   * @throws RobotClientException
   */
  public function bootGet($ip)
  {
    $url = $this->baseUrl . '/boot/' . $ip;

    return $this->get($url);
  }

  /**
   * Get server rescue data
   *
   * @param $ip Server main ip
   *
   * @return object Rescue object
   *
   * @throws RobotClientException
   */
  public function rescueGet($ip)
  {
    $url = $this->baseUrl . '/boot/' . $ip . '/rescue';

    return $this->get($url);
  }

  /**
   * Activate rescue system for a server
   *
   * @param $ip Server main ip
   * @param $os Operating system to boot
   * @param $arch Architecture of operating system
   * @param $authorized_keys Public SSH keys
   *
   * @return object Rescue object
   *
   * @throws RobotClientException
   */
  public function rescueActivate($ip, $os, $arch, array $authorizedKeys = array())
  {
    $url = $this->baseUrl . '/boot/' . $ip . '/rescue';

    return $this->post($url, array('os' => $os, 'arch' => $arch, 'authorized_key' => $authorizedKeys));
  }

  /**
   * Deactivate rescue system for a server
   *
   * @param $ip Server main ip
   *
   * @return object Rescue object
   *
   * @throws RobotClientException
   */
  public function rescueDeactivate($ip)
  {
    $url = $this->baseUrl . '/boot/' . $ip . '/rescue';

    return $this->delete($url);
  }

  /**
   * Get data of last rescue system activation
   *
   * @param $ip Server main ip
   *
   * @return object Rescue object
   *
   * @throws RobotClientException
   */
  public function rescueGetLast($ip)
  {
    $url = $this->baseUrl . '/boot/' . $ip . '/rescue/last';

    return $this->get($url);
  }

  /**
   * Get linux data
   *
   * @param $ip Server main ip
   *
   * @return object Linux object
   *
   * @throws RobotClientException
   */
  public function linuxGet($ip)
  {
    $url = $this->baseUrl . '/boot/' . $ip . '/linux';

    return $this->get($url);
  }

  /**
   * Activate linux installation
   *
   * @param $ip Server main ip
   * @param $dist Distribution identifier
   * @param $arch Architecture
   * @param $lang Language
   * @param $authorized_keys Public SSH keys
   *
   * @return object Linux object
   *
   * @throws RobotClientException
   */
  public function linuxActivate($ip, $dist, $arch, $lang, array $authorizedKeys = array())
  {
    $url = $this->baseUrl . '/boot/' . $ip . '/linux';

    return $this->post($url, array(
      'dist'           => $dist,
      'arch'           => $arch,
      'lang'           => $lang,
      'authorized_key' => $authorizedKeys
    ));
  }

  /**
   * Deactivate linux installation
   *
   * @param $ip Server main ip
   *
   * @return object Linux object
   *
   * @throws RobotClientException
   */
  public function linuxDeactivate($ip)
  {
    $url = $this->baseUrl . '/boot/' . $ip . '/linux';

    return $this->delete($url);
  }

  /**
   * Get data of last linux installation activation
   *
   * @param $ip Server main ip
   *
   * @return object Rescue object
   *
   * @throws RobotClientException
   */
  public function linuxGetLast($ip)
  {
    $url = $this->baseUrl . '/boot/' . $ip . '/linux/last';

    return $this->get($url);
  }

  /**
   * Get vnc data
   *
   * @param $ip Server main ip
   *
   * @return object Vnc object
   *
   * @throws RobotClientException
   */
  public function vncGet($ip)
  {
    $url = $this->baseUrl . '/boot/' . $ip . '/vnc';

    return $this->get($url);
  }

  /**
   * Activate vnc installation
   *
   * @param $ip Server main ip
   * @param $dist Distribution identifier
   * @param $arch Architecture
   * @param $lang Language
   *
   * @return object Vnc object
   *
   * @throws RobotClientException
   */
  public function vncActivate($ip, $dist, $arch, $lang)
  {
    $url = $this->baseUrl . '/boot/' . $ip . '/vnc';

    return $this->post($url, array(
      'dist' => $dist,
      'arch' => $arch,
      'lang' => $lang
    ));
  }

  /**
   * Deactivate vnc installation
   *
   * @param $ip Server main ip
   *
   * @return object Vnc object
   *
   * @throws RobotClientException
   */
  public function vncDeactivate($ip)
  {
    $url = $this->baseUrl . '/boot/' . $ip . '/vnc';

    return $this->delete($url);
  }

  /**
   * Get windows data
   *
   * @param $ip Server main ip
   *
   * @return object Windows object
   *
   * @throws RobotClientException
   */
  public function windowsGet($ip)
  {
    $url = $this->baseUrl . '/boot/' . $ip . '/windows';

    return $this->get($url);
  }

  /**
   * Activate windows installation
   *
   * @param $ip Server main ip
   * @param $lang Language
   *
   * @return object Windows object
   *
   * @throws RobotClientException
   */
  public function windowsActivate($ip, $lang)
  {
    $url = $this->baseUrl . '/boot/' . $ip . '/windows';

    return $this->post($url, array('lang' => $lang));
  }

  /**
   * Deactivate windows installation
   *
   * @param $ip Server main ip
   *
   * @return object Windows object
   *
   * @throws RobotClientException
   */
  public function windowsDeactivate($ip)
  {
    $url = $this->baseUrl . '/boot/' . $ip . '/windows';

    return $this->delete($url);
  }

  /**
   * Get cPanel data
   *
   * @param $ip Server main ip
   *
   * @return object cPanel object
   *
   * @throws RobotClientException
   */
  public function cpanelGet($ip)
  {
    $url = $this->baseUrl . '/boot/' . $ip . '/cpanel';

    return $this->get($url);
  }

  /**
   * Activate cPanel installation
   *
   * @param $ip Server main ip
   * @param $dist Linux distribution
   * @param $arch Architecture
   * @param $lang Language
   * @param $hostname Hostname
   *
   * @return object cPanel object
   *
   * @throws RobotClientException
   */
  public function cpanelActivate($ip, $dist, $arch, $lang, $hostname)
  {
    $url = $this->baseUrl . '/boot/' . $ip . '/cpanel';

    return $this->post($url, array(
      'dist'     => $dist,
      'arch'     => $arch,
      'lang'     => $lang,
      'hostname' => $hostname
    ));
  }

  /**
   * Deactivate cPanel installation
   *
   * @param $ip Server main ip
   *
   * @return object cPanel object
   *
   * @throws RobotClientException
   */
  public function cpanelDeactivate($ip)
  {
    $url = $this->baseUrl . '/boot/' . $ip . '/cpanel';

    return $this->delete($url);
  }

  /**
   * Get plesk data
   *
   * @param $ip Server main ip
   *
   * @return object Plesk object
   *
   * @throws RobotClientException
   */
  public function pleskGet($ip)
  {
    $url = $this->baseUrl . '/boot/' . $ip . '/plesk';

    return $this->get($url);
  }

  /**
   * Activate plesk installation
   *
   * @param $ip Server main ip
   * @param $dist Linux distribution
   * @param $arch Architecture
   * @param $lang Language
   * @param $hostname Hostname
   *
   * @return object Plesk object
   *
   * @throws RobotClientException
   */
  public function pleskActivate($ip, $dist, $arch, $lang, $hostname)
  {
    $url = $this->baseUrl . '/boot/' . $ip . '/plesk';

    return $this->post($url, array(
      'dist'     => $dist,
      'arch'     => $arch,
      'lang'     => $lang,
      'hostname' => $hostname
    ));
  }

  /**
   * Deactivate plesk installation
   *
   * @param $ip Server main ip
   *
   * @return object Plesk object
   *
   * @throws RobotClientException
   */
  public function pleskDeactivate($ip)
  {
    $url = $this->baseUrl . '/boot/' . $ip . '/plesk';

    return $this->delete($url);
  }

  /**
   * Get Wake On Lan data
   *
   * @param $ip Server main ip
   *
   * @return object Wol object
   *
   * @throws RobotClientException
   */
  public function wolGet($ip)
  {
    $url = $this->baseUrl . '/wol/' . $ip;

    return $this->get($url);
  }

  /**
   * Send Wake On Lan packet to server
   *
   * @param $ip Server main ip
   *
   * @return object Wol object
   *
   * @throws RobotClientException
   */
  public function wolSend($ip)
  {
    $url = $this->baseUrl . '/wol/' . $ip;

    return $this->post($url, array('server_ip' => $ip));
  }

  /**
   * Get rdns entry for ip
   *
   * @param $ip
   *
   * @return object Rdns object
   *
   * @throws RobotClientException
   */
  public function rdnsGet($ip)
  {
    $url = $this->baseUrl . '/rdns/' . $ip;

    return $this->get($url);
  }

  /**
   * Create rdns entry for ip
   *
   * @param $ip
   * @param $ptr
   *
   * @return object Rdns object
   *
   * @throws RobotClientException
   */
  public function rdnsCreate($ip, $ptr)
  {
    $url = $this->baseUrl . '/rdns/' . $ip;

    return $this->put($url, array('ptr' => $ptr));
  }

  /**
   * Update rdns entry for ip
   *
   * @param $ip
   * @param $ptr
   *
   * @return object Rdns object
   *
   * @throws RobotClientException
   */
  public function rdnsUpdate($ip, $ptr)
  {
    $url = $this->baseUrl . '/rdns/' . $ip;

    return $this->post($url, array('ptr' => $ptr));
  }

  /**
   * Delete rdns entry for ip
   *
   * @param $ip
   *
   * @throws RobotClientException
   */
  public function rdnsDelete($ip)
  {
    $url = $this->baseUrl . '/rdns/' . $ip;

    $this->delete($url);
  }

  /**
   * Get all servers
   *
   * @return array Array of server objects
   *
   * @throws RobotClientException
   */
  public function serverGetAll()
  {
    $url = $this->baseUrl . '/server';

    return $this->get($url);
  }

  /**
   * Get server by main ip
   *
   * @param $ip Server main ip
   *
   * @return object Server object
   *
   * @throws RobotClientException
   */
  public function serverGet($ip)
  {
    $url = $this->baseUrl . '/server/' . $ip;

    return $this->get($url);
  }

  /**
   *  Update servername
   *
   *  @param $ip Server main ip
   *  @param $name Servername
   *
   *  @return object Server object
   *
   *  @throws RobotClientException
   */
  public function servernameUpdate($ip, $name)
  {
    $url = $this->baseUrl . '/server/' . $ip;

    return $this->post($url, array('server_name' => $name));
  }

  /**
   * Get cancellation data of a server
   *
   * @param $ip Server main ip
   *
   * @return object Cancellation object
   *
   * @throws RobotClientException
   */
  public function serverCancellationGet($ip)
  {
    $url = $this->baseUrl . '/server/' . $ip . '/cancellation';

    return $this->get($url);
  }

  /**
   * Cancel a server
   *
   * @param $ip Server main ip
   * @param $cancellationDate Date to which the server should be cancelled
   * @param $cancellationReason Optional cancellation reason
   *
   * @return object Cancellation object
   *
   * @throws RobotClientException
   */
  public function serverCancel($ip, $cancellationDate, $cancellationReason = null)
  {
    $url = $this->baseUrl . '/server/' . $ip . '/cancellation';
    $data = array('cancellation_date' => $cancellationDate);
    if ($cancellationReason)
    {
      $data['cancellation_reason'] = $cancellationReason;
    }

    return $this->post($url, $data);
  }

  /**
   * Revoke a server cancellation
   *
   * @param $ip Server main ip
   *
   * @throws RobotClientException
   */
  public function serverCancellationDelete($ip)
  {
    $url = $this->baseUrl . '/server/' . $ip . '/cancellation';

    return $this->delete($url);
  }

  /**
   * Get all single ips
   *
   * @return array Array of ip objects
   *
   * @throws RobotClientException
   */
  public function ipGetAll()
  {
    $url = $this->baseUrl . '/ip';

    return $this->get($url);
  }

  /**
   * Get all single ips of specific server
   *
   * @param $serverIp Server main ip
   *
   * @return array Array of ip objects
   *
   * @throws RobotClientException
   */
  public function ipGetByServerIp($serverIp)
  {
    $url = $this->baseUrl . '/ip?server_ip=' . $serverIp;

    return $this->get($url);
  }

  /**
   * Get ip
   *
   * @param $ip
   *
   * @return object Ip object
   *
   * @throws RobotClientException
   */
  public function ipGet($ip)
  {
    $url = $this->baseUrl . '/ip/' . $ip;

    return $this->get($url);
  }

  /**
   * Enable traffic warnings for single ip
   *
   * @param $ip
   *
   * @return object Ip object
   *
   * @throws RobotClientException
   */
  public function ipEnableTrafficWarnings($ip)
  {
    $url = $this->baseUrl . '/ip/' . $ip;

    return $this->post($url, array('traffic_warnings' => 'true'));
  }

  /**
   * Disable traffic warnings for single ip
   *
   * @param $ip
   *
   * @return object Ip object
   */
  public function ipDisableTrafficWarnings($ip)
  {
    $url = $this->baseUrl . '/ip/' . $ip;

    return $this->post($url, array('traffic_warnings' => 'false'));
  }

  /**
   * Set traffic warning limits for single ip
   *
   * @param $ip
   * @param $hourly  Hourly traffic in megabyte
   * @param $daily   Daily traffic in megabyte
   * @param $monthly Montly traffic in gigabyte
   *
   * @return object Ip object
   *
   * @throws RobotClientException
   */
  public function ipSetTrafficWarningLimits($ip, $hourly, $daily, $monthly)
  {
    $url = $this->baseUrl . '/ip/' . $ip;

    return $this->post($url, array(
      'traffic_hourly'  => $hourly,
      'traffic_daily'   => $daily,
      'traffic_monthly' => $monthly
    ));
  }

  /**
   * Get all subnets
   *
   * @return array Array of subnet objects
   *
   * @throws RobotClientException
   */
  public function subnetGetAll()
  {
    $url = $this->baseUrl . '/subnet';

    return $this->get($url);
  }

  /**
   * Get all subnets of specific server
   *
   * @param $serverIp Server main ip
   *
   * @return array Array of subnet objects
   *
   * @throws RobotClientException
   */
  public function subnetGetByServerIp($serverIp)
  {
    $url = $this->baseUrl . '/subnet?server_ip=' . $serverIp;

    return $this->get($url);
  }

  /**
   * Get subnet
   *
   * @param $ip Net ip
   *
   * @return object Subnet object
   *
   * @throws RobotClientException
   */
  public function subnetGet($ip)
  {
    $url = $this->baseUrl . '/subnet/' . $ip;

    return $this->get($url);
  }

  /**
   * Enable traffic warnings for subnet
   *
   * @param $ip Net ip
   *
   * @return object Subnet object
   *
   * @throws RobotClientException
   */
  public function subnetEnableTrafficWarnings($ip)
  {
    $url = $this->baseUrl . '/subnet/' . $ip;

    return $this->post($url, array('traffic_warnings' => 'true'));
  }

  /**
   * Disable traffic warnings for subnet
   *
   * @param $ip Net ip
   *
   * @return object Subnet object
   *
   * @throws RobotClientException
   */
  public function subnetDisableTrafficWarnings($ip)
  {
    $url = $this->baseUrl . '/subnet/' . $ip;

    return $this->post($url, array('traffic_warnings' => 'false'));
  }

  /**
   * Set traffic warning limits for subnet
   *
   * @param $ip Net ip
   * @param $hourly  Hourly traffic in megabyte
   * @param $daily   Daily traffic in megabyte
   * @param $monthly Monthly traffic in gigabyte
   *
   * @return object Subnet object
   *
   * @throws RobotClientException
   */
  public function subnetSetTrafficWarningLimits($ip, $hourly, $daily, $monthly)
  {
    $url = $this->baseUrl . '/subnet/' . $ip;

    return $this->post($url, array(
      'traffic_hourly'  => $hourly,
      'traffic_daily'   => $daily,
      'traffic_monthly' => $monthly
    ));
  }

  /**
   * Get traffic for single ips
   *
   * @param $ip   Single ip address or array of ip addresses
   * @param $type Traffic report type
   * @param $from Date from
   * @param $to   Date to
   *
   * @return object Traffic object
   *
   * @throws RobotClientException
   */
  public function trafficGetForIp($ip, $type, $from, $to)
  {
    return $this->trafficGet(array(
      'ip'   => $ip,
      'type' => $type,
      'from' => $from,
      'to'   => $to
    ));
  }

  /**
   * Get traffic for subnets
   *
   * @param $subnet Net ip address of array of ip addresses
   * @param $type   Traffic report type
   * @param $from   Date from
   * @param $to     Date to
   *
   * @return object Traffic object
   *
   * @throws RobotClientException
   */
  public function trafficGetForSubnet($subnet, $type, $from, $to)
  {
    return $this->trafficGet(array(
      'subnet' => $subnet,
      'type'   => $type,
      'from'   => $from,
      'to'     => $to
    ));
  }

  /**
   * Get traffic for single ips and subnets
   *
   * @param $options Array of options
   *  'ip'     => ip address or array of ip addresses
   *  'subnet' => ip address or array of ip addresses
   *  'type'   => Traffic report type (day, month, year)
   *  'from'   => Date from
   *  'to'     => Date to
   *
   *  Date format:
   *    [YYYY]-[MM] for type year
   *    [YYYY]-[MM]-[DD] for type month
   *    [YYYY]-[MM]-[DD]T[HH] for type day
   *
   * @return object Traffic object
   *
   * @throws RobotClientException
   */
  public function trafficGet(array $options)
  {
    $url = $this->baseUrl . '/traffic';

    return $this->post($url, $options);
  }

  /**
   * Get separate mac for a single ip
   *
   * @param $ip
   *
   * @return object Mac object
   *
   * @throws RobotClientException
   */
  public function separateMacGet($ip)
  {
    $url = $this->baseUrl . '/ip/' . $ip . '/mac';

    return $this->get($url);
  }

  /**
   * Create separate mac for a single ip
   *
   * @param $ip
   *
   * @return object Mac object
   *
   * @throws RobotClientException
   */
  public function separateMacCreate($ip)
  {
    $url = $this->baseUrl . '/ip/' . $ip . '/mac';

    return $this->put($url);
  }

  /**
   * Delete separate mac for a single ip
   *
   * @param $ip
   *
   * @return object Mac object
   *
   * @throws RobotClientException
   */
  public function separateMacDelete($ip)
  {
    $url = $this->baseUrl . '/ip/' . $ip . '/mac';

    return $this->delete($url);
  }

  /**
   * Get the mac address of a ipv6 subnet
   *
   * @param $ip
   *
   * @return object Mac object
   *
   * @throws RobotClientException
   */
  public function subnetMacGet($ip)
  {
    $url = $this->baseUrl . '/subnet/' . $ip . '/mac';

    return $this->get($url);
  }

  /**
   * Set the mac address of a ipv6 subnet
   *
   * @param $ip
   * @param $mac
   *
   * @return object Mac object
   *
   * @throws RobotClientException
   */
  public function subnetMacSet($ip, $mac)
  {
    $url = $this->baseUrl . '/subnet/' . $ip . '/mac';

    return $this->put($url, array('mac' => $mac));
  }

  /**
   * Reset the mac address of a ipv6 subnet to the
   * default value (the servers real mac address)
   *
   * @param $ip
   *
   * @return object Mac object
   *
   * @throws RobotClientException
   */
  public function subnetMacReset($ip)
  {
    $url = $this->baseUrl . '/subnet/' . $ip . '/mac';

    return $this->delete($url);
  }

  /**
   * Get all ssh public keys
   *
   * @return array Array of key objects
   *
   * @throws RobotClientException
   */
  public function keyGetAll()
  {
    $url = $this->baseUrl . '/key';

    return $this->get($url);
  }

  /**
   * Get a specific ssh public key
   *
   * @param $fingerprint
   *
   * @return object The key object
   *
   * @throws RobotClientException
   */
  public function keyGet($fingerprint)
  {
    $url = $this->baseUrl . '/key/' . $fingerprint;

    return $this->get($url);
  }

  /**
   * Save a new ssh public key
   *
   * @param $name Key name
   * @param $data Key data in OpenSSH or SSH2 (RFC4716) format
   *
   * @return object The key object
   *
   * @throws RobotClientException
   */
  public function keyCreate($name, $data)
  {
    $url = $this->baseUrl . '/key';

    return $this->post($url, array(
      'name' => $name,
      'data' => $data
    ));
  }

  /**
   * Update the name of a key
   *
   * @param $fingerprint The key fingerprint
   * @param $name The key name
   *
   * @return object The key object
   *
   * @throws RobotClientException
   */
  public function keyUpdate($fingerprint, $name)
  {
    $url = $this->baseUrl . '/key/' . $fingerprint;

    return $this->post($url, array(
      'name' => $name
    ));
  }

  /**
   * Remove a ssh public key
   *
   * @param $fingerprint The key fingerprint
   *
   * @throws RobotClientException
   */
  public function keyDelete($fingerprint)
  {
    $url = $this->baseUrl . '/key/' . $fingerprint;

    return $this->delete($url);
  }

  /**
   * Get all currently offered standard server products
   *
   * @return array Array of product objects
   *
   * @throws RobotClientException
   */
  public function orderServerProductGetAll()
  {
    $url = $this->baseUrl . '/order/server/product';

    return $this->get($url);
  }

  /**
   * Get data of a specific standard server product
   *
   * @param $productId The product id
   *
   * @return object The product object
   *
   * @throws RobotClientException
   */
  public function orderServerProductGet($productId)
  {
    $url = $this->baseUrl . '/order/server/product/' . $productId;

    return $this->get($url);
  }

  /**
   * Get all standard server orders of the last 30 days
   *
   * @return array Array of transaction objects
   *
   * @throws RobotClientException
   */
  public function orderServerTransactionGetAll()
  {
    $url = $this->baseUrl . '/order/server/transaction';

    return $this->get($url);
  }

  /**
   * Query the status of a specific server order
   *
   * @param $transactionId
   *
   * @return object The transaction object
   *
   * @throws RobotClientException
   */
  public function orderServerTransactionGet($transactionId)
  {
    $url = $this->baseUrl . '/order/server/transaction/' . $transactionId;

    return $this->get($url);
  }

  /**
   * Order a standard server
   *
   * @param $productId
   * @param $authorizedKeys Array of ssh public key fingerprints
   * @param $password Root password for server, can only be used when no keys have been supplied
   * @param $dist Distribution name, optional, defaults to rescue system
   * @param $arch Architecture, optional, defaults to 64
   * @param $lang Language of distribution, optional, defaults to en
   *
   * @return object The transaction object
   *
   * @throws RobotClientException
   */
  public function orderServer($productId, array $authorizedKeys = array(), $password = null, $dist = null, $arch = null, $lang = null, $test = false)
  {
    $url = $this->baseUrl . '/order/server/transaction';
    $data = array('product_id' => $productId);
    if ($authorizedKeys)
    {
      $data['authorized_key'] = $authorizedKeys;
    }
    elseif ($password !== null)
    {
      $data['password'] = $password;
    }
    if ($dist !== null)
    {
      $data['dist'] = $dist;
    }
    if ($arch !== null)
    {
      $data['arch'] = $arch;
    }
    if ($lang !== null)
    {
      $data['lang'] = $lang;
    }
    if ($test)
    {
      $data['test'] = 'true';
    }

    return $this->post($url, $data);
  }

  /**
   * Get all currently offered server market products
   *
   * @return array Array of product objects
   *
   * @throws RobotClientException
   */
  public function orderServerMarketProductGetAll()
  {
    $url = $this->baseUrl . '/order/server_market/product';

    return $this->get($url);
  }

  /**
   * Get data of a specifi server market product
   *
   * @param $productId The product id
   *
   * @return object The product object
   *
   * @throws RobotClientException
   */
  public function orderServerMarketProductGet($productId)
  {
    $url = $this->baseUrl . '/order/server_market/product/' . $productId;

    return $this->get($url);
  }

  /**
   * Get all server market orders of the last 30 days
   *
   * @return array Array of transaction objects
   *
   * @throws RobotClientException
   */
  public function orderServerMarketTransactionGetAll()
  {
    $url = $this->baseUrl . '/order/server_market/transaction';

    return $this->get($url);
  }

  /**
   * Query the status of a specific server market order
   *
   * @param $transactionId
   *
   * @return object The transaction object
   *
   * @throws RobotClientException
   */
  public function orderServerMarketTransactionGet($transactionId)
  {
    $url = $this->baseUrl . '/order/server_market/transaction/' . $transactionId;

    return $this->get($url);
  }

  /**
   * Order a server from the server market
   *
   * @param $productId
   * @param $authorizedKeys Array of ssh public key fingerprints
   * @param $password Root password for server, can only be used when no keys have been supplied
   *
   * @return object The transaction object
   *
   * @throws RobotClientException
   */
  public function orderMarketServer($productId, array $authorizedKeys = array(), $password = null, $test = false)
  {
    $url = $this->baseUrl . '/order/server_market/transaction';
    $data = array('product_id' => $productId);
    if ($authorizedKeys)
    {
      $data['authorized_key'] = $authorizedKeys;
    }
    elseif ($password !== null)
    {
      $data['password'] = $password;
    }
    if ($test)
    {
      $data['test'] = 'true';
    }

    return $this->post($url, $data);
  }

  /**
   * Get all snapshots from a server
   *
   * @param $ip
   *
   * @return array Array of snapshot objects
   *
   * @throws RobotClientException
   */
  public function snapshotGet($ip)
  {
    $url = $this->baseUrl . '/snapshot/' . $ip;
    
    return $this->get($url);
  }

  /**
   * Creates a new snapshot from a server
   *
   * @param $ip
   *
   * @return object The snapshot object
   *
   * @throws RobotClientException
   */
  public function snapshotCreate($ip)
  {
    $url = $this->baseUrl . '/snapshot/' . $ip;

    return $this->post($url);
  }

  /**
   * Deletes a snapshot from a server
   *
   * @param $ip
   * @param $id The snapshot id
   *
   * @throws RobotClientException
   */
  public function snapshotDelete($ip, $id)
  {
    $url = $this->baseUrl . '/snapshot/' . $ip . '/' . $id;

    return $this->delete($url);
  }

  /**
   * Reverts a snapshot from a server
   *
   * @param $ip
   * @param $id The snapshot id
   *
   * @throws RobotClientException
   */
  public function snapshotRevert($ip, $id)
  {
    $url = $this->baseUrl . '/snapshot/' . $ip . '/' . $id;
    $data = array('revert' => true);

    return $this->post($url, $data);
  }

  /**
   * Update snapshot name
   *
   * @param $ip
   * @param $id The snapshot id
   * @param $name new name
   *
   * @throws RobotClientException
   */
  public function snapshotNameUpdate($ip, $id, $name)
  {
    $url = $this->baseUrl . '/snapshot/' . $ip . '/' . $id;

    return $this->post($url, array('name' => $name));
  }

  /**
   * Get all snapshots from a Storage Box
   *
   * @param $id
   *
   * @return array Array of snapshot objects
   *
   * @throws RobotClientException
   */
  public function storageboxSnapshotGet($id)
  {
    $url = $this->baseUrl . '/storagebox/' . $id . '/snapshot';

    return $this->get($url);
  }

  /**
   * Creates a new snapshot from a Storage Box
   *
   * @param $id
   *
   * @return object|array The snapshot object
   *
   * @throws RobotClientException
   */
  public function storageboxSnapshotCreate($id)
  {
    $url = $this->baseUrl . '/storagebox/' . $id . '/snapshot';

    return $this->post($url);
  }

  /**
   * Deletes a snapshot from a Storage Box
   *
   * @param $id
   * @param $name The snapshot name
   *
   * @throws RobotClientException
   */
  public function storageboxSnapshotDelete($id, $name)
  {
    $url = $this->baseUrl . '/storagebox/' . $id . '/snapshot/' . $name;

    return $this->delete($url);
  }

  /**
   * Reverts a snapshot from a Storage Box
   *
   * @param $id
   * @param $name The snapshot name
   *
   * @throws RobotClientException
   */
  public function storageboxSnapshotRevert($id, $name)
  {
    $url = $this->baseUrl . '/storagebox/' . $id . '/snapshot/' . $name;
    $data = array('revert' => 'true');

    return $this->post($url, $data);
  }
  /**
   * Set comment for a snapshot
   *
   * @param $id
   * @param $name The snapshot name
   * @param $comment The snapshot comment
   *
   * @throws RobotClientException
   */
  public function storageboxSnapshotSetComment($id, $name, $comment)
  {
    $url = $this->baseUrl . '/storagebox/' . $id . '/snapshot/' . $name . '/comment';
    $data = array('comment' => $comment);

    return $this->post($url, $data);
  }

  /**
   * Get Storage Box by id
   *
   * @param $id Storagebox id
   *
   * @return object The storagebox object
   *
   * @throws RobotClientException
   */
  public function storageboxGet($id)
  {
    $url = $this->baseUrl . '/storagebox/' . $id;

    return $this->get($url);
  }

  /**
   * Get all Storage Boxes
   *
   * @return array Array of storagebox objects
   *
   * @throws RobotClientException
   */
  public function storageboxGetAll()
  {
    $url = $this->baseUrl . '/storagebox';

    return $this->get($url);
  }

  /**
   *  Update Storage Box name
   *
   *  @param $id Storagebox id
   *  @param $name new Name
   *
   *  @return object storagebox object
   *
   *  @throws RobotClientException
   */
  public function storageboxnameUpdate($id, $name)
  {
    $url = $this->baseUrl . '/storagebox/' . $id;

    return $this->post($url, array('storagebox_name' => $name));
  }

  /**
   *  Get directory listing of a StorageBox
   *
   *  @param $id Storagebox id
   *
   *  @return array Array of directory names
   *
   *  @throws RobotClientException
   */
  public function storageboxDirectoryListing($id)
  {
    $url = $this->baseUrl . '/storagebox/' . $id . '/dir';

    return $this->get($url);
  }

  /**
   * Starts the vServer
   *
   * @param $ip Server main ip
   *
   * @throws RobotClientException
   */
  public function vServerStart($ip)
  {
    $url = $this->baseUrl . '/vserver/' . $ip . '/command';

    return $this->post($url, array('type' => 'start'));
  }

  /**
   * Stops the vServer
   *
   * @param $ip Server main ip
   *
   * @throws RobotClientException
   */
  public function vServerStop($ip)
  {
    $url = $this->baseUrl . '/vserver/' . $ip . '/command';

    return $this->post($url, array('type' => 'stop'));
  }

  /**
   * Shutdown the vServer
   *
   * @param $ip Server main ip
   *
   * @throws RobotClientException
   */
  public function vServerShutdown($ip)
  {
    $url = $this->baseUrl . '/vserver/' . $ip . '/command';

    return $this->post($url, array('type' => 'shutdown'));
  }

  /**
   * Get all snapshot plans for a Storage Box
   *
   * @param $id
   *
   * @return array Array of snapshot plans objects
   *
   * @throws RobotClientException
   */
  public function storageboxSnapshotPlanGet($id)
  {
    $url = $this->baseUrl . '/storagebox/' . $id . '/snapshotplan';

    return $this->get($url);
  }

  /**
   * Creates a new snapshot plan for a Storage Box
   *
   * @param $id
   *
   * @return array Array of snapshot plans objects
   *
   * @throws RobotClientException
   */
  public function storageboxSnapshotPlanEdit($id, $data)
  {
    $url = $this->baseUrl . '/storagebox/' . $id . '/snapshotplan';

    return $this->post($url, $data);
  }

  /**
   * Get firewall of server
   *
   * @param $ip Server main ip address
   * @param $port Switch port, only needed when server has multiple ports, e.g. for KVM
   *
   * @return object firewall object
   *
   * @throws RobotClientException
   */
  public function firewallGet($ip, $port = 'main')
  {
    $url = $this->baseUrl . '/firewall/' . $ip . '/' . $port;

    return $this->get($url);
  }

  /**
   * Create new firewall or update existing firewall
   *
   * @param $ip Server main ip address
   * @param $status Activate or disable firewall ('active' or 'disabled')
   * @param $whitelistHos Do allow all Hetzner Online Services by default (e.g. DHCP, DNS, Backup)
   * @param $inputRules Array of input rules
   *        array(
   *          'input' => array(
   *            array(
   *              'name'          => (string) 'name of rule',
   *              'ip_version'    => (string) 'ipv4'
   *              'dst_ip'        => (string) destination IP address or subnet,
   *              'src_ip'        => (string) source IP address or subnet,
   *              'dst_port'      => (string) destination TCP/UDP port,
   *              'src_port'      => (string) source TCP/UDP port,
   *              'protocol'      => (string) Protocol after IP,
   *              'tcp_flags'     => (string) TCP flags,
   *              'action'        => (string) 'accept' or 'discard'
   *            )
   *          )
   *        )
   * @param $port Switch port, only needed when server has multiple ports, e.g. for KVM
   *
   * @return object firewall object
   *
   * @throws RobotClientException
   */
  public function firewallCreateOrUpdate($ip, $status, $whitelistHos, array $rules, $port = 'main')
  {
    $url = $this->baseUrl . '/firewall/' . $ip . '/' . $port;

    return $this->post($url, array(
      'status'        => $status,
      'whitelist_hos' => $whitelistHos,
      'rules'         => $rules,
    ));
  }

  /**
   * Craete new firewall or update existing firewall from template
   *
   * @param $ip Server main IP address
   * @param $templateId Firewall template ID
   * @param $port Switch port, only needed when server has multiple ports, e.g. for KVM
   *
   * @return object firewall object
   *
   * @throws RobotClientException
   */
  public function firewallCreateOrUpdateFromTemplate($ip, $templateId, $port = 'main')
  {
    $url = $this->baseUrl . '/firewall/' . $ip . '/' . $port;

    return $this->post($url, array(
      'template_id' => $templateId,
    ));
  }

  /**
   * Delete firewall
   *
   * @param $ip Server main IP address
   * @param $port Switch port, only needed when server has multiple ports, e.g. for KVM
   *
   * @throws RobotClientException
   */
  public function firewallDelete($ip, $port = 'main')
  {
    $url = $this->baseUrl . '/firewall/' . $ip . '/' . $port;

    return $this->delete($url);
  }

  /**
   * Get all existing firewall templates
   *
   * @return array of firewall template objects
   *
   * @throws RobotClientException
   */
  public function firewallTemplateGetAll()
  {
    $url = $this->baseUrl . '/firewall/template';

    return $this->get($url);
  }

  /**
   * Create a new firewall template
   *
   * @param $name Name of template
   * @param $whitelistHos Whitelist Hetzner services
   * @param $isDefault Use this template as default
   * @param $rules Array of rules
   *        array(
   *          'input' => array(
   *            array(
   *              'name'          => (string) 'name of rule',
   *              'ip_version'    => (string) 'ipv4'
   *              'dst_ip'        => (string) destination IP address or subnet,
   *              'src_ip'        => (string) source IP address or subnet,
   *              'dst_port'      => (string) destination TCP/UDP port,
   *              'src_port'      => (string) source TCP/UDP port,
   *              'protocol'      => (string) Protocol after IP,
   *              'tcp_flags'     => (string) TCP flags,
   *              'action'        => (string) 'accept' or 'discard'
   *            )
   *          )
   *        )
   *
   * @return firewall template object
   *
   * @throws RobotClientException
   */
  public function firewallTemplateCreate($name, $whitelistHos, $isDefault, $rules)
  {
    $url = $this->baseUrl . '/firewall/template';

    return $this->post($url, array(
      'name'          => $name,
      'whitelist_hos' => $whitelistHos,
      'is_default'    => $isDefault,
      'rules'         => $rules,
    ));
  }

  /**
   * Get a specific firewall template by ID
   *
   * @param $templateID Firewall template id
   *
   * @return firewall template object
   *
   * @throws RobotClientException
   */
  public function firewallTemplateGet($templateId)
  {
    $url = $this->baseUrl . '/firewall/template/' . $templateId;

    return $this->get($url);
  }

  /**
   * Update a existing firewall template
   *
   * @param $templateId Firewall template ID
   * @param $name Name of template
   * @param $whitelistHos Whitelist Hetzner services
   * @param $isDefault Use this template as default
   * @param $rules Array of input rules
   *        array(
   *          'input' => array(
   *            array(
   *              'name'          => (string) 'name of rule',
   *              'ip_version'    => (string) 'ipv4' or 'ipv6',
   *              'dst_ip'        => (string) destination IP address or subnet,
   *              'src_ip'        => (string) source IP address or subnet,
   *              'dst_port'      => (string) destination TCP/UDP port,
   *              'src_port'      => (string) source TCP/UDP port,
   *              'protocol'      => (string) Protocol after IP,
   *              'tcp_flags'     => (string) TCP flags,
   *              'action'        => (string) 'accept' or 'discard'
   *            )
   *          )
   *        )
   *
   * @return firewall template object
   *
   * @throws RobotClientException
   */
  public function firewallTemplateUpdate($templateId, $name, $whitelistHos, $isDefault, $rules)
  {
    $url = $this->baseUrl . '/firewall/template/' . $templateId;

    return $this->post($url, array(
      'name'          => $name,
      'whitelist_hos' => $whitelistHos,
      'is_default'    => $isDefault,
      'rules'         => $rules,
    ));
  }

  /**
   * Update a existing firewall template name
   *
   * @param $templateId Firewall template ID
   * @param $name Name of template
   *
   * @return firewall template object
   *
   * @throws RobotClientException
   */
  public function firewallTemplateUpdateName($templateId, $name)
  {
    $url = $this->baseUrl . '/firewall/template/' . $templateId;

    return $this->post($url, array(
      'name' => $name,
    ));
  }

  /**
   * Delete a firewall template
   *
   * @param $templateId Firewall template ID
   *
   * @throws RobotClientException
   */
  public function firewallTemplateDelete($templateId)
  {
    $url = $this->baseUrl . '/firewall/template/' . $templateId;

    return $this->delete($url);
  }
  
  
  /**
   * Get all sub accounts for a Storage Box
   *
   * @param $id
   *
   * @return array Array of sub accounts objects
   *
   * @throws RobotClientException
   */
  public function storageboxSubAccountGet($id)
  {
    $url = $this->baseUrl . '/storagebox/' . $id . '/subaccount';

    return $this->get($url);
  }

  /**
   * Creates a new sub account for a Storage Box
   *
   * @param $id
   * @param $data
   *
   * @return array Array of sub account object
   *
   * @throws RobotClientException
   */
  public function storageboxSubAccountCreate($id, $data)
  {
    $url = $this->baseUrl . '/storagebox/' . $id . '/subaccount';

    return $this->post($url, $data);
  }

  /**
   * Updates a new sub account for a Storage Box
   *
   * @param $id
   * @param $username
   * @param $data
   *
   * @throws RobotClientException
   */
  public function storageboxSubAccountUpdate($id, $username, $data)
  {
    $url = $this->baseUrl . '/storagebox/' . $id . '/subaccount/' . $username;

    return $this->put($url, $data);
  }
  /**
   * Resets the password of a sub account
   *
   * @param $id
   * @param $username
   *
   * @return string password
   *
   * @throws RobotClientException
   */
  public function storageboxSubAccountResetPassword($id, $username)
  {
    $url = $this->baseUrl . '/storagebox/' . $id . '/subaccount/' . $username . '/password';

    return $this->post($url);
  }

  /**
   * Deletes a sub account for a Storage Box
   *
   * @param $id
   * @param $username
   *
   * @throws RobotClientException
   */
  public function storageboxSubAccountDelete($id, $username)
  {
    $url = $this->baseUrl . '/storagebox/' . $id . '/subaccount/' . $username;

    return $this->delete($url);
  }
}
