<?php
/*Copyright (c) 2019 Aaron Kim <https://github.com/ahkim>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details. */
 /**
 * ServiceNow Transport to issue an incident out of alerts froms LibreNMS
 * @author Aaron Kim <https://github.com/ahkim>
 * @license GPL
 * @package LibreNMS
 * @subpackage Alerts
 */

namespace LibreNMS\Alert\Transport;
use LibreNMS\Alert\Transport;
use GuzzleHttp\Client;
class Servicenow extends Transport
{
    public function deliverAlert($obj, $opts)
    {
        $opts['instance'] = $this->config['servicenow-instance'];
        $opts['impact'] = $this->config['servicenow-impact'];
        $opts['urgency'] = $this->config['servicenow-urgency'];
        $opts['caller'] = $this->config['servicenow-caller'];
		$opts['username'] = $this->config['servicenow-auth-username'];
		$opts['password'] = $this->config['servicenow-auth-password'];
		
        return $this->contactSERVICENOW($obj, $opts);
    }
    private function contactSERVICENOW($obj, $opts)
    {
        $request_opts = [];

        $client = new \GuzzleHttp\Client([
			'auth' => [$opts['username'], $opts['password']],
		]); 

        $arr = array('caller_id' => $opts['caller'],
                     'short_description' => $obj['title'], 
                     'impact' => $opts['impact'], 
                     'urgency' => $opts['urgency'], 
                     'description' => $obj['msg']); # This passes body text built from the alert template

		$res = $client->post("https://".$opts['instance'].".service-now.com/api/now/v1/table/incident", [
			'headers' => [
				'Content-Type' => 'application/json',
				'accept' => '*/*',
				'accept-encoding' => 'gzip, deflate'
			],			
			'body' => json_encode($arr)
		]);

        $code = $res->getStatusCode();
        if ($code != 201) {
            var_dump("ServiceNow " .$opts['instance']. " returned Error");
            var_dump("Response headers:");
            var_dump($res->getHeaders());
            var_dump("Return: ".$res->getReasonPhrase());
            return 'HTTP Status code '.$code;
        }
        return true;
    }
    public static function configTemplate()
    {
        return [
            'config' => [
                [
                    'title' => 'ServiceNow Instance',
                    'name' => 'servicenow-instance',
                    'descr' => 'ServiceNow Instance',
                    'type' => 'text',
                ],
                [
                    'title' => 'Caller',
                    'name' => 'servicenow-caller',
                    'descr' => 'First & last name of the user',
                    'type' => 'text',
                ],
                [
                    'title' => 'Impact',
                    'name' => 'servicenow-impact',
                    'descr' => '1 - High/2 - Medium/3 - Low',
                    'type' => 'text',
                ],
                [
                    'title' => 'Urgency',
                    'name' => 'servicenow-urgency',
                    'descr' => '1 - High/2 - Medium/3 - Low',
                    'type' => 'text',
                ],
                [
                    'title' => 'ServiceNow Username',
                    'name' => 'servicenow-auth-username',
                    'descr' => 'The user must have itil & itil_admin roles',
                    'type' => 'text',
                ],
                [
                    'title' => 'ServiceNow Password',
                    'name' => 'servicenow-auth-password',
                    'descr' => 'ServiceNow Password',
                    'type' => 'password',
                ]
            ],
            'validation' => [
                'servicenow-instance' => 'required|string',
                'servicenow-impact' => 'required|numeric',
                'servicenow-urgency' => 'required|numeric',
				'servicenow-auth-username' => 'required|string',
				'servicenow-auth-password' => 'required|string'
            ]
        ];
    }
}