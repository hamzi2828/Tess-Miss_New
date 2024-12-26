<?php
namespace App\Mail;

use Exception;

class graphMailer
{
    private $tenantID;
    private $clientID;
    private $clientSecret;
    private $baseURL;
    private $Token;

    public function __construct($tenantID, $clientID, $clientSecret)
    {
        $this->tenantID = $tenantID;
        $this->clientID = $clientID;
        $this->clientSecret = $clientSecret;
        $this->baseURL = 'https://graph.microsoft.com/v1.0/';
        $this->Token = $this->getToken();
    }

    /**
     * Get OAuth token from Microsoft Graph API.
     */
    private function getToken()
    {
        $oauthRequest = http_build_query([
            'client_id' => $this->clientID,
            'scope' => 'https://graph.microsoft.com/.default',
            'client_secret' => $this->clientSecret,
            'grant_type' => 'client_credentials',
        ]);

        $url = 'https://login.microsoftonline.com/' . $this->tenantID . '/oauth2/v2.0/token';

        $response = $this->sendPostRequest($url, $oauthRequest, ['Content-Type: application/x-www-form-urlencoded']);

        if (isset($response['data'])) {
            $decodedResponse = json_decode($response['data'], true);
            if (isset($decodedResponse['access_token'])) {
                return $decodedResponse['access_token'];
            } else {
                throw new Exception('Token retrieval failed: ' . $response['data']);
            }
        }

        throw new Exception('Failed to retrieve token. Response: ' . json_encode($response));
    }

    /**
     * Send an email using Microsoft Graph API.
     */
    public function sendMail($userPrincipalName, $messageArgs)
    {
        if (!$this->Token) {
            throw new Exception('No token defined');
        }

        $messageJSON = $this->createMessageJSON($messageArgs);

        $response = $this->sendPostRequest(
            $this->baseURL . 'users/' . urlencode($userPrincipalName) . '/sendMail',
            $messageJSON,
            ['Content-Type: application/json', 'Authorization: Bearer ' . $this->Token]
        );

        if ($response['code'] == 202) {
            return true; // Successfully sent
        } else {
            throw new Exception('Error sending email: ' . $response['data']);
        }
    }

    /**
     * Format the email message body into JSON format.
     */
    private function createMessageJSON($messageArgs)
    {
        $messageArray = [
            'message' => [
                'subject' => $messageArgs['subject'],
                'body' => [
                    'contentType' => 'HTML',
                    'content' => $messageArgs['body'],
                ],
                'toRecipients' => array_map(function ($recipient) {
                    return [
                        'emailAddress' => [
                            'address' => $recipient['address'],
                        ],
                    ];
                }, $messageArgs['toRecipients']),
            ],
            'saveToSentItems' => 'true',
        ];

        return json_encode($messageArray);
    }

    /**
     * Send POST request to Graph API.
     */
    private function sendPostRequest($url, $fields, $headers = [])
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        $responseCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        return [
            'code' => $responseCode,
            'data' => $response,
        ];
    }
}
