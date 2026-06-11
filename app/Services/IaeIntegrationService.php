<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IaeIntegrationService
{
    protected $baseUrl;
    protected $teamId = 'TEAM-09';

    public function __construct()
    {
        $this->baseUrl = config('services.iae.sso_url');
        $this->teamId = config('services.iae.team_id', 'TEAM-09');
    }

    // --- Translasi dari Postman: Tes SOAP Audit ---
    public function sendSoapAudit($token, $transactionData)
    {
        $xmlBody = '<?xml version="1.0" encoding="utf-8"?>
        <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">
           <soapenv:Body>
              <AuditRequest>
                  <TeamID>TEAM-09</TeamID>
                 <ActivityName>KrsSubmitted</ActivityName>
                 <LogContent><![CDATA[' . json_encode($transactionData) . ']]></LogContent>
              </AuditRequest>
           </soapenv:Body>
        </soapenv:Envelope>';

        $response = Http::withToken($token)
            ->withHeaders(['Content-Type' => 'text/xml; charset=UTF8'])
            ->send('POST', $this->baseUrl . '/soap/v1/audit', [
                'body' => $xmlBody
            ]);

        if (!$response->successful() || !str_contains($response->body(), 'SUCCESS')) {
            Log::error('SOAP Error', ['response' => $response->body()]);
            throw new \Exception("Gagal mencatat audit di sistem dosen.");
        }

        return true;
    }

    // --- Translasi dari Postman: Tes RabbitMQ ---
    public function publishEvent($token, $payload)
    {
        try {
            $response = Http::withToken($token)
                ->post($this->baseUrl . '/api/v1/messages/publish', [
                    'exchange' => 'iae.central.exchange',
                    'routing_key' => 'krs.submitted.event',
                    'payload' => $payload
                ]);

            if (!$response->successful()) {
                Log::error('RabbitMQ Error', ['response' => $response->body()]);
            }
            
            return $response->successful();
        } catch (\Exception $e) {
            Log::error('RabbitMQ Connection/Network Error', ['message' => $e->getMessage()]);
            return false;
        }
    }
}
