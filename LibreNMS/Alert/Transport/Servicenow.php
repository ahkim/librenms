<?php
/*Copyright (c) 2019 Aaron Kim <https://github.com/ahkim>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details. */
 /**
 * ServiceNow Transport
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
        $url = $this->config['servicenow-url'];
        $body = $this->config['servicenow-body'];
		$username = $this->config['servicenow-auth-username'];
		$password = $this->config['servicenow-auth-password'];
		
        return $this->contactSERVICENOW($obj, $url, $body, $username, $password);
    }
    private function contactSERVICENOW($obj, $url, $body, $username, $password)
    {
        $request_opts = [];

        $client = new \GuzzleHttp\Client([
			'auth' => [$username, $password],
		]); 
		
		$res = $client->post($url, [
			'headers' => [
				'Content-Type' => 'application/json',
				'accept' => '*/*',
				'accept-encoding' => 'gzip, deflate'
			],			
			'body' => $body
		]);

        $code = $res->getStatusCode();
        if ($code != 201) {
            var_dump("ServiceNow '$url' returned Error");
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
                    'title' => 'ServiceNow object URL',
                    'name' => 'servicenow-url',
                    'descr' => 'ServiceNow object URL',
                    'type' => 'text',
                ],
                [
                    'title' => 'Request Body',
                    'name' => 'servicenow-body',
                    'descr' => 'Enter the json body',
                    'type' => 'textarea',
                ],
                [
                    'title' => 'ServiceNow Username',
                    'name' => 'servicenow-auth-username',
                    'descr' => 'ServiceNow Username',
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
                'servicenow-url' => 'required|url',
				'servicenow-auth-username' => 'required',
				'servicenow-auth-password' => 'required'
            ]
        ];
    }
}