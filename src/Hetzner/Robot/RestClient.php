<?php

namespace Hetzner\Robot;

/**
 * Basic REST client
 * 
 * Copyright (c) 2013-2016 Hetzner Online GmbH
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
class RobotRestClient
{
  private $curl;
  private $curlOptions  = array();
  protected $httpHeader = array();
  protected $baseUrl;

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
    $this->baseUrl = rtrim($url, '/');
    $this->curl = curl_init();
    $this->setCurlOption(CURLOPT_RETURNTRANSFER, true);
    $this->setCurlOption(CURLOPT_USERPWD, $login . ':' . $password);
    $this->setCurlOption(CURLOPT_VERBOSE, $verbose);
  }

  /**
   * Class destructor
   */
  public function __destruct()
  {
    curl_close($this->curl);
  }

  /**
   * Set a curl option
   *
   * @param $option CURLOPT option constant
   * @param $value
   */
  protected function setCurlOption($option, $value)
  {
    $this->curlOptions[$option] = $value;
  }

  /**
   * Get value for a curl option
   *
   * @param $option CURLOPT option constant
   * @return mixed The value
   */
  protected function getCurlOption($option)
  {
    return isset($this->curlOptions[$option]) ? $this->curlOptions[$option] : null;
  }
 
  /**
   * Set a HTTP header
   * 
   * @param $name
   * @param $value
   */
  public function setHttpHeader($name, $value)
  {
    $this->httpHeader[$name] = $name . ': ' . $value;
  }
    
  /**
   * Do a GET request
   * 
   * @param $url
   * @return array Array with keys 'response_code' and 'response'
   *   On error 'response' is false
   */
  protected function get($url)
  {
    $this->setCurlOption(CURLOPT_URL, $url);
    $this->setCurlOption(CURLOPT_HTTPGET, true);
    $this->setCurlOption(CURLOPT_CUSTOMREQUEST, 'GET');

    return $this->executeRequest();
  }

  /**
   * Do a POST request
   * 
   * @param $url
   * @param $data Post data
   * @return array Array with keys 'response_code' and 'response'
   *   On error 'response' is false
   */
  protected function post($url, array $data = array())
  {
    $this->setCurlOption(CURLOPT_URL, $url);
    $this->setCurlOption(CURLOPT_POST, true);
    $this->setCurlOption(CURLOPT_CUSTOMREQUEST, 'POST');
    if ($data)
    {
      $this->setCurlOption(CURLOPT_POSTFIELDS, http_build_query($data));
    }

    return $this->executeRequest();
  }

  /**
   * Do a PUT request
   *
   * @param $url
   * @param $data Put data
   * @return array Array with keys 'response_code' and 'response'
   *   On error 'response' is false
   */
  protected function put($url, array $data = array())
  {
    $this->setCurlOption(CURLOPT_URL, $url);
    $this->setCurlOption(CURLOPT_HTTPGET, true);
    $this->setCurlOption(CURLOPT_CUSTOMREQUEST, 'PUT');
    if ($data)
    {
      $this->setCurlOption(CURLOPT_POSTFIELDS, http_build_query($data));
    }

    return $this->executeRequest();
  }

  /**
   * Do a DELETE request
   *
   * @param $url
   * @return array Array with keys 'response_code' and 'response'
   *   On error 'response' is false
   */
  protected function delete($url)
  {
    $this->setCurlOption(CURLOPT_URL, $url);
    $this->setCurlOption(CURLOPT_HTTPGET, true);
    $this->setCurlOption(CURLOPT_CUSTOMREQUEST, 'DELETE');

    return $this->executeRequest();
  }
  
  /**
   * Execute HTTP request
   * 
   * @return array Array with keys 'response_code' and 'response'
   *   On error 'response' is false
   */
  protected function executeRequest()
  {
    $this->setCurlOption(CURLOPT_HTTPHEADER, array_values($this->httpHeader));
    curl_setopt_array($this->curl, $this->curlOptions);
    $response = curl_exec($this->curl);

    return array(
      'response_code' => curl_getinfo($this->curl, CURLINFO_HTTP_CODE),
      'response'      => $response
    );
  }
}
