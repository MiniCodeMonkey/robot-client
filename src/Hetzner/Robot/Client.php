<?php

namespace Hetzner\Robot;

use stdClass;

/**
 * Client class for robot webservice
 *
 * Documentation: https://robot.your-server.de/doc/webservice/en.html
 *
 * Copyright (c) 2013-2018 Hetzner Online GmbH
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
class Client extends RestClient
{
  const VERSION = '2018.06';

  /**
   * Class constructor
   *
   * @param string $url      Robot webservice url
   * @param string $user     Robot webservice username
   * @param string $password Robot password
   * @param bool   $verbose
   */
  public function __construct($url, $user, $password, $verbose = false)
  {
    parent::__construct($url, $user, $password, $verbose);
    $this->setHttpHeader('Accept', 'application/json');
    $this->setHttpHeader('User-Agent', 'HetznerRobotClient/' . self::VERSION);
  }

  /**
   * Execute HTTP request
   *
   * @return object Response
   *
   * @throws ClientException
   */
  protected function executeRequest()
  {
    $result = parent::executeRequest();

    if ($result['response'] === false)
    {
      throw new ClientException('robot not reachable', 'NOT_REACHABLE');
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
      throw new ClientException('response can not be decoded', 'RESPONSE_DECODE_ERROR');
    }

    if ($result['response_code'] >= 400 && $result['response_code'] <= 503)
    {
      if (isset($response->error) && isset($response->error->message, $response->error->code)) {
        throw new ClientException($response->error->message, $response->error->code);
      } else {
        throw new ClientException(null, $result['response_code']);
      }
    }

    return $response;
  }

  /**
   * Get failover
   *
   * @param string $ip Failover ip address
   * @param array  $query additional query string
   *
   * @return object Failover object
   *
   * @throws ClientException
   */
  public function failoverGet($ip = null, ?array $query = null)
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
   * @param string $serverIp Server main ip address
   *
   * @return object Failover object
   *
   * @throws ClientException
   */
  public function failoverGetByServerIp($serverIp)
  {
    return $this->failoverGet(null, array('server_ip' => $serverIp));
  }

  /**
   * Route failover
   *
   * @param string $failoverIp Failover ip address
   * @param string $activeServerIp Target server ip address
   *
   * @return object Failover object
   *
   * @throws ClientException
   */
  public function failoverRoute($failoverIp, $activeServerIp)
  {
    $url = $this->baseUrl . '/failover/' . $failoverIp;

    return $this->post($url, array('active_server_ip' => $activeServerIp));
  }

  /**
   * Delete failover routing
   *
   * @param string $failoverIp Failover IP address
   * @return object Failover object
   *
   * @throws ClientException
   */
  public function failoverDelete($failoverIp)
  {
    $url = $this->baseUrl . '/failover/' . $failoverIp;

    return $this->delete($url);
  }

  /**
   * Get server reset
   *
   * @param string $ip Server main ip
   *
   * @return object Reset object
   *
   * @throws ClientException
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
   * @param string $ip Server main ip
   * @param string $type Reset type
   *
   * @return object Reset object
   *
   * @throws ClientException
   */
  public function resetExecute($ip, $type)
  {
    $url = $this->baseUrl . '/reset/' . $ip;

    return $this->post($url, array('type' => $type));
  }

  /**
   * Get current boot config
   *
   * @param string $ip Server main ip
   *
   * @return object Boot object
   *
   * @throws ClientException
   */
  public function bootGet($ip)
  {
    $url = $this->baseUrl . '/boot/' . $ip;

    return $this->get($url);
  }

  /**
   * Get server rescue data
   *
   * @param string $ip Server main ip
   *
   * @return object Rescue object
   *
   * @throws ClientException
   */
  public function rescueGet($ip)
  {
    $url = $this->baseUrl . '/boot/' . $ip . '/rescue';

    return $this->get($url);
  }

  /**
   * Activate rescue system for a server
   *
   * @param string $ip Server main ip
   * @param string $os Operating system to boot
   * @param string $arch Architecture of operating system
   * @param array  $authorized_keys Public SSH keys
   *
   * @return object Rescue object
   *
   * @throws ClientException
   */
  public function rescueActivate($ip, $os, $arch, array $authorizedKeys = array())
  {
    $url = $this->baseUrl . '/boot/' . $ip . '/rescue';

    return $this->post($url, array('os' => $os, 'arch' => $arch, 'authorized_key' => $authorizedKeys));
  }

  /**
   * Deactivate rescue system for a server
   *
   * @param string $ip Server main ip
   *
   * @return object Rescue object
   *
   * @throws ClientException
   */
  public function rescueDeactivate($ip)
  {
    $url = $this->baseUrl . '/boot/' . $ip . '/rescue';

    return $this->delete($url);
  }

  /**
   * Get data of last rescue system activation
   *
   * @param string $ip Server main ip
   *
   * @return object Rescue object
   *
   * @throws ClientException
   */
  public function rescueGetLast($ip)
  {
    $url = $this->baseUrl . '/boot/' . $ip . '/rescue/last';

    return $this->get($url);
  }

  /**
   * Get linux data
   *
   * @param string $ip Server main ip
   *
   * @return object Linux object
   *
   * @throws ClientException
   */
  public function linuxGet($ip)
  {
    $url = $this->baseUrl . '/boot/' . $ip . '/linux';

    return $this->get($url);
  }

  /**
   * Activate linux installation
   *
   * @param string $ip Server main ip
   * @param string $dist Distribution identifier
   * @param string $arch Architecture
   * @param string $lang Language
   * @param array  $authorized_keys Public SSH keys
   *
   * @return object Linux object
   *
   * @throws ClientException
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
   * @param string $ip Server main ip
   *
   * @return object Linux object
   *
   * @throws ClientException
   */
  public function linuxDeactivate($ip)
  {
    $url = $this->baseUrl . '/boot/' . $ip . '/linux';

    return $this->delete($url);
  }

  /**
   * Get data of last linux installation activation
   *
   * @param string $ip Server main ip
   *
   * @return object Rescue object
   *
   * @throws ClientException
   */
  public function linuxGetLast($ip)
  {
    $url = $this->baseUrl . '/boot/' . $ip . '/linux/last';

    return $this->get($url);
  }

  /**
   * Get vnc data
   *
   * @param string $ip Server main ip
   *
   * @return object Vnc object
   *
   * @throws ClientException
   */
  public function vncGet($ip)
  {
    $url = $this->baseUrl . '/boot/' . $ip . '/vnc';

    return $this->get($url);
  }

  /**
   * Activate vnc installation
   *
   * @param string $ip Server main ip
   * @param string $dist Distribution identifier
   * @param string $arch Architecture
   * @param string $lang Language
   *
   * @return object Vnc object
   *
   * @throws ClientException
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
   * @param string $ip Server main ip
   *
   * @return object Vnc object
   *
   * @throws ClientException
   */
  public function vncDeactivate($ip)
  {
    $url = $this->baseUrl . '/boot/' . $ip . '/vnc';

    return $this->delete($url);
  }

  /**
   * Get windows data
   *
   * @param string $ip Server main ip
   *
   * @return object Windows object
   *
   * @throws ClientException
   */
  public function windowsGet($ip)
  {
    $url = $this->baseUrl . '/boot/' . $ip . '/windows';

    return $this->get($url);
  }

  /**
   * Activate windows installation
   *
   * @param string $ip Server main ip
   * @param string $lang Language
   *
   * @return object Windows object
   *
   * @throws ClientException
   */
  public function windowsActivate($ip, $lang)
  {
    $url = $this->baseUrl . '/boot/' . $ip . '/windows';

    return $this->post($url, array('lang' => $lang));
  }

  /**
   * Deactivate windows installation
   *
   * @param string $ip Server main ip
   *
   * @return object Windows object
   *
   * @throws ClientException
   */
  public function windowsDeactivate($ip)
  {
    $url = $this->baseUrl . '/boot/' . $ip . '/windows';

    return $this->delete($url);
  }

  /**
   * Get cPanel data
   *
   * @param string $ip Server main ip
   *
   * @return object cPanel object
   *
   * @throws ClientException
   */
  public function cpanelGet($ip)
  {
    $url = $this->baseUrl . '/boot/' . $ip . '/cpanel';

    return $this->get($url);
  }

  /**
   * Activate cPanel installation
   *
   * @param string $ip Server main ip
   * @param string $dist Linux distribution
   * @param string $arch Architecture
   * @param string $lang Language
   * @param string $hostname Hostname
   *
   * @return object cPanel object
   *
   * @throws ClientException
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
   * @param string $ip Server main ip
   *
   * @return object cPanel object
   *
   * @throws ClientException
   */
  public function cpanelDeactivate($ip)
  {
    $url = $this->baseUrl . '/boot/' . $ip . '/cpanel';

    return $this->delete($url);
  }

  /**
   * Get plesk data
   *
   * @param string $ip Server main ip
   *
   * @return object Plesk object
   *
   * @throws ClientException
   */
  public function pleskGet($ip)
  {
    $url = $this->baseUrl . '/boot/' . $ip . '/plesk';

    return $this->get($url);
  }

  /**
   * Activate plesk installation
   *
   * @param string $ip Server main ip
   * @param string $dist Linux distribution
   * @param string $arch Architecture
   * @param string $lang Language
   * @param string $hostname Hostname
   *
   * @return object Plesk object
   *
   * @throws ClientException
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
   * @param string $ip Server main ip
   *
   * @return object Plesk object
   *
   * @throws ClientException
   */
  public function pleskDeactivate($ip)
  {
    $url = $this->baseUrl . '/boot/' . $ip . '/plesk';

    return $this->delete($url);
  }

  /**
   * Get Wake On Lan data
   *
   * @param string $ip Server main ip
   *
   * @return object Wol object
   *
   * @throws ClientException
   */
  public function wolGet($ip)
  {
    $url = $this->baseUrl . '/wol/' . $ip;

    return $this->get($url);
  }

  /**
   * Send Wake On Lan packet to server
   *
   * @param string $ip Server main ip
   *
   * @return object Wol object
   *
   * @throws ClientException
   */
  public function wolSend($ip)
  {
    $url = $this->baseUrl . '/wol/' . $ip;

    return $this->post($url, array('server_ip' => $ip));
  }

  /**
   * Get rdns entry for ip
   *
   * @param string $ip
   *
   * @return object Rdns object
   *
   * @throws ClientException
   */
  public function rdnsGet($ip)
  {
    $url = $this->baseUrl . '/rdns/' . $ip;

    return $this->get($url);
  }

  /**
   * Create rdns entry for ip
   *
   * @param string $ip
   * @param string $ptr
   *
   * @return object Rdns object
   *
   * @throws ClientException
   */
  public function rdnsCreate($ip, $ptr)
  {
    $url = $this->baseUrl . '/rdns/' . $ip;

    return $this->put($url, array('ptr' => $ptr));
  }

  /**
   * Update rdns entry for ip
   *
   * @param string $ip
   * @param string $ptr
   *
   * @return object Rdns object
   *
   * @throws ClientException
   */
  public function rdnsUpdate($ip, $ptr)
  {
    $url = $this->baseUrl . '/rdns/' . $ip;

    return $this->post($url, array('ptr' => $ptr));
  }

  /**
   * Delete rdns entry for ip
   *
   * @param string $ip
   *
   * @throws ClientException
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
   * @throws ClientException
   */
  public function serverGetAll()
  {
    $url = $this->baseUrl . '/server';

    return $this->get($url);
  }

  /**
   * Get server by main ip
   *
   * @param string $ip Server main ip
   *
   * @return object Server object
   *
   * @throws ClientException
   */
  public function serverGet($ip)
  {
    $url = $this->baseUrl . '/server/' . $ip;

    return $this->get($url);
  }

  /**
   *  Update servername
   *
   *  @param string $ip Server main ip
   *  @param string $name Servername
   *
   *  @return object Server object
   *
   *  @throws ClientException
   */
  public function servernameUpdate($ip, $name)
  {
    $url = $this->baseUrl . '/server/' . $ip;

    return $this->post($url, array('server_name' => $name));
  }

  /**
   * Get cancellation data of a server
   *
   * @param string $ip Server main ip
   *
   * @return object Cancellation object
   *
   * @throws ClientException
   */
  public function serverCancellationGet($ip)
  {
    $url = $this->baseUrl . '/server/' . $ip . '/cancellation';

    return $this->get($url);
  }

  /**
   * Cancel a server
   *
   * @param string $ip Server main ip
   * @param string $cancellationDate Date to which the server should be cancelled
   * @param string $cancellationReason Optional cancellation reason
   *
   * @return object Cancellation object
   *
   * @throws ClientException
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
   * @param string $ip Server main ip
   *
   * @throws ClientException
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
   * @throws ClientException
   */
  public function ipGetAll()
  {
    $url = $this->baseUrl . '/ip';

    return $this->get($url);
  }

  /**
   * Get all single ips of specific server
   *
   * @param string $serverIp Server main ip
   *
   * @return array Array of ip objects
   *
   * @throws ClientException
   */
  public function ipGetByServerIp($serverIp)
  {
    $url = $this->baseUrl . '/ip?server_ip=' . $serverIp;

    return $this->get($url);
  }

  /**
   * Get ip
   *
   * @param string $ip
   *
   * @return object Ip object
   *
   * @throws ClientException
   */
  public function ipGet($ip)
  {
    $url = $this->baseUrl . '/ip/' . $ip;

    return $this->get($url);
  }

  /**
   * Enable traffic warnings for single ip
   *
   * @param string $ip
   *
   * @return object Ip object
   *
   * @throws ClientException
   */
  public function ipEnableTrafficWarnings($ip)
  {
    $url = $this->baseUrl . '/ip/' . $ip;

    return $this->post($url, array('traffic_warnings' => 'true'));
  }

  /**
   * Disable traffic warnings for single ip
   *
   * @param string $ip
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
   * @param string $ip
   * @param int    $hourly  Hourly traffic in megabyte
   * @param int    $daily   Daily traffic in megabyte
   * @param int    $monthly Montly traffic in gigabyte
   *
   * @return object Ip object
   *
   * @throws ClientException
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
   * @throws ClientException
   */
  public function subnetGetAll()
  {
    $url = $this->baseUrl . '/subnet';

    return $this->get($url);
  }

  /**
   * Get all subnets of specific server
   *
   * @param string $serverIp Server main ip
   *
   * @return array Array of subnet objects
   *
   * @throws ClientException
   */
  public function subnetGetByServerIp($serverIp)
  {
    $url = $this->baseUrl . '/subnet?server_ip=' . $serverIp;

    return $this->get($url);
  }

  /**
   * Get subnet
   *
   * @param string $ip Net ip
   *
   * @return object Subnet object
   *
   * @throws ClientException
   */
  public function subnetGet($ip)
  {
    $url = $this->baseUrl . '/subnet/' . $ip;

    return $this->get($url);
  }

  /**
   * Enable traffic warnings for subnet
   *
   * @param string $ip Net ip
   *
   * @return object Subnet object
   *
   * @throws ClientException
   */
  public function subnetEnableTrafficWarnings($ip)
  {
    $url = $this->baseUrl . '/subnet/' . $ip;

    return $this->post($url, array('traffic_warnings' => 'true'));
  }

  /**
   * Disable traffic warnings for subnet
   *
   * @param string $ip Net ip
   *
   * @return object Subnet object
   *
   * @throws ClientException
   */
  public function subnetDisableTrafficWarnings($ip)
  {
    $url = $this->baseUrl . '/subnet/' . $ip;

    return $this->post($url, array('traffic_warnings' => 'false'));
  }

  /**
   * Set traffic warning limits for subnet
   *
   * @param string $ip Net ip
   * @param int    $hourly  Hourly traffic in megabyte
   * @param int    $daily   Daily traffic in megabyte
   * @param int    $monthly Monthly traffic in gigabyte
   *
   * @return object Subnet object
   *
   * @throws ClientException
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
   * @param string $ip   Single ip address or array of ip addresses
   * @param string $type Traffic report type
   * @param string $from Date from
   * @param string $to   Date to
   *
   * @return object Traffic object
   *
   * @throws ClientException
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
   * @param string $subnet Net ip address of array of ip addresses
   * @param string $type   Traffic report type
   * @param string $from   Date from
   * @param string $to     Date to
   *
   * @return object Traffic object
   *
   * @throws ClientException
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
   * @param array $options Array of options
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
   * @throws ClientException
   */
  public function trafficGet(array $options)
  {
    $url = $this->baseUrl . '/traffic';

    return $this->post($url, $options);
  }

  /**
   * Get separate mac for a single ip
   *
   * @param string $ip
   *
   * @return object Mac object
   *
   * @throws ClientException
   */
  public function separateMacGet($ip)
  {
    $url = $this->baseUrl . '/ip/' . $ip . '/mac';

    return $this->get($url);
  }

  /**
   * Create separate mac for a single ip
   *
   * @param string $ip
   *
   * @return object Mac object
   *
   * @throws ClientException
   */
  public function separateMacCreate($ip)
  {
    $url = $this->baseUrl . '/ip/' . $ip . '/mac';

    return $this->put($url);
  }

  /**
   * Delete separate mac for a single ip
   *
   * @param string $ip
   *
   * @return object Mac object
   *
   * @throws ClientException
   */
  public function separateMacDelete($ip)
  {
    $url = $this->baseUrl . '/ip/' . $ip . '/mac';

    return $this->delete($url);
  }

  /**
   * Get the mac address of a ipv6 subnet
   *
   * @param string $ip
   *
   * @return object Mac object
   *
   * @throws ClientException
   */
  public function subnetMacGet($ip)
  {
    $url = $this->baseUrl . '/subnet/' . $ip . '/mac';

    return $this->get($url);
  }

  /**
   * Set the mac address of a ipv6 subnet
   *
   * @param string $ip
   * @param string $mac
   *
   * @return object Mac object
   *
   * @throws ClientException
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
   * @param string $ip
   *
   * @return object Mac object
   *
   * @throws ClientException
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
   * @throws ClientException
   */
  public function keyGetAll()
  {
    $url = $this->baseUrl . '/key';

    return $this->get($url);
  }

  /**
   * Get a specific ssh public key
   *
   * @param string $fingerprint
   *
   * @return object The key object
   *
   * @throws ClientException
   */
  public function keyGet($fingerprint)
  {
    $url = $this->baseUrl . '/key/' . $fingerprint;

    return $this->get($url);
  }

  /**
   * Save a new ssh public key
   *
   * @param string $name Key name
   * @param string $data Key data in OpenSSH or SSH2 (RFC4716) format
   *
   * @return object The key object
   *
   * @throws ClientException
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
   * @param string $fingerprint The key fingerprint
   * @param string $name The key name
   *
   * @return object The key object
   *
   * @throws ClientException
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
   * @param string $fingerprint The key fingerprint
   *
   * @throws ClientException
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
   * @throws ClientException
   */
  public function orderServerProductGetAll()
  {
    $url = $this->baseUrl . '/order/server/product';

    return $this->get($url);
  }

  /**
   * Get data of a specific standard server product
   *
   * @param string $productId The product id
   *
   * @return object The product object
   *
   * @throws ClientException
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
   * @throws ClientException
   */
  public function orderServerTransactionGetAll()
  {
    $url = $this->baseUrl . '/order/server/transaction';

    return $this->get($url);
  }

  /**
   * Query the status of a specific server order
   *
   * @param string $transactionId
   *
   * @return object The transaction object
   *
   * @throws ClientException
   */
  public function orderServerTransactionGet($transactionId)
  {
    $url = $this->baseUrl . '/order/server/transaction/' . $transactionId;

    return $this->get($url);
  }

  /**
   * Order a standard server
   *
   * @param string $productId
   * @param string|null $location The desired location
   * @param array $authorizedKeys Array of ssh public key fingerprints
   * @param string|null $password Root password for server, can only be used when no keys have been supplied
   * @param string|null $dist Distribution name, optional, defaults to rescue system
   * @param int|null $arch Architecture, optional, defaults to 64
   * @param string|null $lang Language of distribution, optional, defaults to en
   * @param bool $test
   *
   * @return object The transaction object
   *
   * @throws ClientException
   */
  public function orderServer($productId, $location, array $authorizedKeys = array(), $password = null, $dist = null, $arch = null, $lang = null, $test = false)
  {
    $url = $this->baseUrl . '/order/server/transaction';
    $data = [
        'product_id' => $productId,
        'location' => $location,
        ];
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
   * @throws ClientException
   */
  public function orderServerMarketProductGetAll()
  {
    $url = $this->baseUrl . '/order/server_market/product';

    return $this->get($url);
  }

  /**
   * Get data of a specifi server market product
   *
   * @param int $productId The product id
   *
   * @return object The product object
   *
   * @throws ClientException
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
   * @throws ClientException
   */
  public function orderServerMarketTransactionGetAll()
  {
    $url = $this->baseUrl . '/order/server_market/transaction';

    return $this->get($url);
  }

  /**
   * Query the status of a specific server market order
   *
   * @param int $transactionId
   *
   * @return object The transaction object
   *
   * @throws ClientException
   */
  public function orderServerMarketTransactionGet($transactionId)
  {
    $url = $this->baseUrl . '/order/server_market/transaction/' . $transactionId;

    return $this->get($url);
  }

  /**
   * Order a server from the server market
   *
   * @param int $productId
   * @param array $authorizedKeys Array of ssh public key fingerprints
   * @param string $password Root password for server, can only be used when no keys have been supplied
   *
   * @return object The transaction object
   *
   * @throws ClientException
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
   * @throws ClientException
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
   * @throws ClientException
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
   * @throws ClientException
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
   * @throws ClientException
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
   * @param string $ip
   * @param int $id The snapshot id
   * @param string $name new name
   *
   * @throws ClientException
   */
  public function snapshotNameUpdate($ip, $id, $name)
  {
    $url = $this->baseUrl . '/snapshot/' . $ip . '/' . $id;

    return $this->post($url, array('name' => $name));
  }

  /**
   * Get all snapshots from a Storage Box
   *
   * @param int $id
   *
   * @return array Array of snapshot objects
   *
   * @throws ClientException
   */
  public function storageboxSnapshotGet($id)
  {
    $url = $this->baseUrl . '/storagebox/' . $id . '/snapshot';

    return $this->get($url);
  }

  /**
   * Creates a new snapshot from a Storage Box
   *
   * @param int $id
   *
   * @return object|array The snapshot object
   *
   * @throws ClientException
   */
  public function storageboxSnapshotCreate($id)
  {
    $url = $this->baseUrl . '/storagebox/' . $id . '/snapshot';

    return $this->post($url);
  }

  /**
   * Deletes a snapshot from a Storage Box
   *
   * @param int    $id
   * @param string $name The snapshot name
   *
   * @throws ClientException
   */
  public function storageboxSnapshotDelete($id, $name)
  {
    $url = $this->baseUrl . '/storagebox/' . $id . '/snapshot/' . $name;

    return $this->delete($url);
  }

  /**
   * Reverts a snapshot from a Storage Box
   *
   * @param int    $id
   * @param string $name The snapshot name
   *
   * @throws ClientException
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
   * @param int    $id
   * @param string $name The snapshot name
   * @param string $comment The snapshot comment
   *
   * @throws ClientException
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
   * @param int $id Storagebox id
   *
   * @return object The storagebox object
   *
   * @throws ClientException
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
   * @throws ClientException
   */
  public function storageboxGetAll()
  {
    $url = $this->baseUrl . '/storagebox';

    return $this->get($url);
  }

  /**
   *  Update Storage Box name
   *
   *  @param int $id Storagebox id
   *  @param string $name new Name
   *
   *  @return object storagebox object
   *
   *  @throws ClientException
   */
  public function storageboxnameUpdate($id, $name)
  {
    $url = $this->baseUrl . '/storagebox/' . $id;

    return $this->post($url, array('storagebox_name' => $name));
  }

  /**
   *  Get directory listing of a StorageBox
   *
   *  @param int $id Storagebox id
   *
   *  @return array Array of directory names
   *
   *  @throws ClientException
   */
  public function storageboxDirectoryListing($id)
  {
    $url = $this->baseUrl . '/storagebox/' . $id . '/dir';

    return $this->get($url);
  }

  /**
   * Get all snapshot plans for a Storage Box
   *
   * @param int $id
   *
   * @return array Array of snapshot plans objects
   *
   * @throws ClientException
   */
  public function storageboxSnapshotPlanGet($id)
  {
    $url = $this->baseUrl . '/storagebox/' . $id . '/snapshotplan';

    return $this->get($url);
  }

  /**
   * Creates a new snapshot plan for a Storage Box
   *
   * @param int $id
   *
   * @return array Array of snapshot plans objects
   *
   * @throws ClientException
   */
  public function storageboxSnapshotPlanEdit($id, $data)
  {
    $url = $this->baseUrl . '/storagebox/' . $id . '/snapshotplan';

    return $this->post($url, $data);
  }

  /**
   * Get firewall of server
   *
   * @param string $ip Server main ip address
   * @param string $port Switch port, only needed when server has multiple ports, e.g. for KVM
   *
   * @return object firewall object
   *
   * @throws ClientException
   */
  public function firewallGet($ip, $port = 'main')
  {
    $url = $this->baseUrl . '/firewall/' . $ip . '/' . $port;

    return $this->get($url);
  }

  /**
   * Create new firewall or update existing firewall
   *
   * @param string $ip Server main ip address
   * @param string $status Activate or disable firewall ('active' or 'disabled')
   * @param string $whitelistHos Do allow all Hetzner Online Services by default (e.g. DHCP, DNS, Backup)
   * @param array  $inputRules Array of input rules
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
   * @param string $port Switch port, only needed when server has multiple ports, e.g. for KVM
   *
   * @return object firewall object
   *
   * @throws ClientException
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
   * @param string $ip Server main IP address
   * @param int    $templateId Firewall template ID
   * @param string $port Switch port, only needed when server has multiple ports, e.g. for KVM
   *
   * @return object firewall object
   *
   * @throws ClientException
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
   * @param string $ip Server main IP address
   * @param string $port Switch port, only needed when server has multiple ports, e.g. for KVM
   *
   * @throws ClientException
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
   * @throws ClientException
   */
  public function firewallTemplateGetAll()
  {
    $url = $this->baseUrl . '/firewall/template';

    return $this->get($url);
  }

  /**
   * Create a new firewall template
   *
   * @param string $name Name of template
   * @param string $whitelistHos Whitelist Hetzner services
   * @param string $isDefault Use this template as default
   * @param array  $rules Array of rules
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
   * @throws ClientException
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
   * @param int $templateID Firewall template id
   *
   * @return firewall template object
   *
   * @throws ClientException
   */
  public function firewallTemplateGet($templateId)
  {
    $url = $this->baseUrl . '/firewall/template/' . $templateId;

    return $this->get($url);
  }

  /**
   * Update a existing firewall template
   *
   * @param int    $templateId Firewall template ID
   * @param string $name Name of template
   * @param string $whitelistHos Whitelist Hetzner services
   * @param string $isDefault Use this template as default
   * @param array  $rules Array of input rules
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
   * @throws ClientException
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
   * @param int    $templateId Firewall template ID
   * @param string $name Name of template
   *
   * @return firewall template object
   *
   * @throws ClientException
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
   * @param int $templateId Firewall template ID
   *
   * @throws ClientException
   */
  public function firewallTemplateDelete($templateId)
  {
    $url = $this->baseUrl . '/firewall/template/' . $templateId;

    return $this->delete($url);
  }


  /**
   * Get all sub accounts for a Storage Box
   *
   * @param int $id
   *
   * @return array Array of sub accounts objects
   *
   * @throws ClientException
   */
  public function storageboxSubAccountGet($id)
  {
    $url = $this->baseUrl . '/storagebox/' . $id . '/subaccount';

    return $this->get($url);
  }

  /**
   * Creates a new sub account for a Storage Box
   *
   * @param int   $id
   * @param array $data
   *
   * @return array Array of sub account object
   *
   * @throws ClientException
   */
  public function storageboxSubAccountCreate($id, $data)
  {
    $url = $this->baseUrl . '/storagebox/' . $id . '/subaccount';

    return $this->post($url, $data);
  }

  /**
   * Updates a new sub account for a Storage Box
   *
   * @param int    $id
   * @param string $username
   * @param array  $data
   *
   * @throws ClientException
   */
  public function storageboxSubAccountUpdate($id, $username, $data)
  {
    $url = $this->baseUrl . '/storagebox/' . $id . '/subaccount/' . $username;

    return $this->put($url, $data);
  }
  /**
   * Resets the password of a sub account
   *
   * @param int    $id
   * @param string $username
   *
   * @return string password
   *
   * @throws ClientException
   */
  public function storageboxSubAccountResetPassword($id, $username)
  {
    $url = $this->baseUrl . '/storagebox/' . $id . '/subaccount/' . $username . '/password';

    return $this->post($url);
  }

  /**
   * Deletes a sub account for a Storage Box
   *
   * @param int    $id
   * @param string $username
   *
   * @throws ClientException
   */
  public function storageboxSubAccountDelete($id, $username)
  {
    $url = $this->baseUrl . '/storagebox/' . $id . '/subaccount/' . $username;

    return $this->delete($url);
  }
}
