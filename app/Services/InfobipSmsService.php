<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class InfobipSmsService
{
    protected $client;
    protected $baseUrl;
    protected $apiKey;
    protected $from;

    public function __construct()
    {
        $this->baseUrl = env('INFOBIP_BASE_URL');
        $this->apiKey = env('INFOBIP_API_KEY');
        $this->from = env('INFOBIP_FROM', 'ServiceSMS');

        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'headers' => [
                'Authorization' => 'App ' . $this->apiKey,
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ]
        ]);
    }

    public function sendSms($to, $message)
    {
        try {
            $response = $this->client->post('/sms/2/text/advanced', [
                'json' => [
                    'messages' => [
                        [
                            'from' => $this->from,
                            'destinations' => [
                                ['to' => $to]
                            ],
                            'text' => $message,
                        ]
                    ]
                ]
            ]);

            $body = json_decode($response->getBody(), true);
            Log::info('Infobip SMS Response: ', $body);

            return $body;
        } catch (\Exception $e) {
            Log::error('Infobip SMS Error: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }
}
