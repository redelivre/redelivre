<?php
/*
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations under
 * the License.
 */

/**
 * Service definition for Cloudlatencytest (v2).
 *
 * <p>
 * A Test API to report latency data.</p>
 *
 * <p>
 * For more information about this service, see the API
 * <a href="" target="_blank">Documentation</a>
 * </p>
 *
 * @author Google, Inc.
 */
class Forminator_Google_Service_Cloudlatencytest extends Forminator_Google_Service
{
  /** View monitoring data for all of your Google Cloud and API projects. */
  const MONITORING_READONLY =
      "https://www.googleapis.com/auth/monitoring.readonly";

  public $statscollection;
  

  /**
   * Constructs the internal representation of the Cloudlatencytest service.
   *
   * @param Forminator_Google_Client $client
   */
  public function __construct(Forminator_Google_Client $client)
  {
    parent::__construct($client);
    $this->rootUrl = 'https://cloudlatencytest-pa.googleapis.com/';
    $this->servicePath = 'v2/statscollection/';
    $this->version = 'v2';
    $this->serviceName = 'cloudlatencytest';

    $this->statscollection = new Forminator_Google_Service_Cloudlatencytest_Statscollection_Resource(
        $this,
        $this->serviceName,
        'statscollection',
        array(
          'methods' => array(
            'updateaggregatedstats' => array(
              'path' => 'updateaggregatedstats',
              'httpMethod' => 'POST',
              'parameters' => array(),
            ),'updatestats' => array(
              'path' => 'updatestats',
              'httpMethod' => 'POST',
              'parameters' => array(),
            ),
          )
        )
    );
  }
}


/**
 * The "statscollection" collection of methods.
 * Typical usage is:
 *  <code>
 *   $cloudlatencytestService = new Forminator_Google_Service_Cloudlatencytest(...);
 *   $statscollection = $cloudlatencytestService->statscollection;
 *  </code>
 */
class Forminator_Google_Service_Cloudlatencytest_Statscollection_Resource extends Forminator_Google_Service_Resource
{

  /**
   * RPC to update the new TCP stats. (statscollection.updateaggregatedstats)
   *
   * @param Forminator_Google_AggregatedStats $postBody
   * @param array $optParams Optional parameters.
   * @return Forminator_Google_Service_Cloudlatencytest_AggregatedStatsReply
   */
  public function updateaggregatedstats(Forminator_Google_Service_Cloudlatencytest_AggregatedStats $postBody, $optParams = array())
  {
    $params = array('postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('updateaggregatedstats', array($params), "Forminator_Google_Service_Cloudlatencytest_AggregatedStatsReply");
  }

  /**
   * RPC to update the new TCP stats. (statscollection.updatestats)
   *
   * @param Forminator_Google_Stats $postBody
   * @param array $optParams Optional parameters.
   * @return Forminator_Google_Service_Cloudlatencytest_StatsReply
   */
  public function updatestats(Forminator_Google_Service_Cloudlatencytest_Stats $postBody, $optParams = array())
  {
    $params = array('postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('updatestats', array($params), "Forminator_Google_Service_Cloudlatencytest_StatsReply");
  }
}




class Forminator_Google_Service_Cloudlatencytest_AggregatedStats extends Forminator_Google_Collection
{
  protected $collection_key = 'stats';
  protected $internal_gapi_mappings = array(
  );
  protected $statsType = 'Forminator_Google_Service_Cloudlatencytest_Stats';
  protected $statsDataType = 'array';


  public function setStats($stats)
  {
    $this->stats = $stats;
  }
  public function getStats()
  {
    return $this->stats;
  }
}

class Forminator_Google_Service_Cloudlatencytest_AggregatedStatsReply extends Forminator_Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $testValue;


  public function setTestValue($testValue)
  {
    $this->testValue = $testValue;
  }
  public function getTestValue()
  {
    return $this->testValue;
  }
}

class Forminator_Google_Service_Cloudlatencytest_DoubleValue extends Forminator_Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $label;
  public $value;


  public function setLabel($label)
  {
    $this->label = $label;
  }
  public function getLabel()
  {
    return $this->label;
  }
  public function setValue($value)
  {
    $this->value = $value;
  }
  public function getValue()
  {
    return $this->value;
  }
}

class Forminator_Google_Service_Cloudlatencytest_IntValue extends Forminator_Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $label;
  public $value;


  public function setLabel($label)
  {
    $this->label = $label;
  }
  public function getLabel()
  {
    return $this->label;
  }
  public function setValue($value)
  {
    $this->value = $value;
  }
  public function getValue()
  {
    return $this->value;
  }
}

class Forminator_Google_Service_Cloudlatencytest_Stats extends Forminator_Google_Collection
{
  protected $collection_key = 'stringValues';
  protected $internal_gapi_mappings = array(
  );
  protected $doubleValuesType = 'Forminator_Google_Service_Cloudlatencytest_DoubleValue';
  protected $doubleValuesDataType = 'array';
  protected $intValuesType = 'Forminator_Google_Service_Cloudlatencytest_IntValue';
  protected $intValuesDataType = 'array';
  protected $stringValuesType = 'Forminator_Google_Service_Cloudlatencytest_StringValue';
  protected $stringValuesDataType = 'array';
  public $time;


  public function setDoubleValues($doubleValues)
  {
    $this->doubleValues = $doubleValues;
  }
  public function getDoubleValues()
  {
    return $this->doubleValues;
  }
  public function setIntValues($intValues)
  {
    $this->intValues = $intValues;
  }
  public function getIntValues()
  {
    return $this->intValues;
  }
  public function setStringValues($stringValues)
  {
    $this->stringValues = $stringValues;
  }
  public function getStringValues()
  {
    return $this->stringValues;
  }
  public function setTime($time)
  {
    $this->time = $time;
  }
  public function getTime()
  {
    return $this->time;
  }
}

class Forminator_Google_Service_Cloudlatencytest_StatsReply extends Forminator_Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $testValue;


  public function setTestValue($testValue)
  {
    $this->testValue = $testValue;
  }
  public function getTestValue()
  {
    return $this->testValue;
  }
}

class Forminator_Google_Service_Cloudlatencytest_StringValue extends Forminator_Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $label;
  public $value;


  public function setLabel($label)
  {
    $this->label = $label;
  }
  public function getLabel()
  {
    return $this->label;
  }
  public function setValue($value)
  {
    $this->value = $value;
  }
  public function getValue()
  {
    return $this->value;
  }
}
