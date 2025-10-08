<?php

namespace Amx\CorporatePriority\Services;

use Amx\CorporatePriority\Mappers\AssignSeatMapper;
use Amx\CorporatePriority\Mappers\SeatMapMapper;
use App\Services\Aeromexico\TokenService;
use GuzzleHttp\Client;
use Ramsey\Uuid\Uuid;

class AeromexicoService
{

    protected $tokenService;

    public function __construct(TokenService $tokenService)
    {
        $this->tokenService = $tokenService;
    }

    public function getReservation($data)
    {
        $MS_RESERVATION = config("corporate-priority.MS_RESERVATION");

        $token = $this->tokenService->grantAccess();
        
        $client = new Client();

        $uuid_v1 = Uuid::uuid1()->toString();

        $response = $client->get($MS_RESERVATION, [
            "headers" => [
                "channel" => "web",
                "pnr" => $data["pnr"],
                "lastName" => $data["lastname"],
                "ticket" => $data["ticketNumber"],
                "x-transactionId" => $uuid_v1,
                "platform" => "web",
                "workflow" => "ambusiness",
                "app-client" => "ecommerce",
                "Authorization" => $token
            ]
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    public function getSeatMap($data)
    {
        $MS_SEAT_MAP = config("corporate-priority.MS_SEAT_MAP");

        $token = $this->tokenService->grantAccess();

        $client = new Client();

        $uuid_v1 = Uuid::uuid1()->toString();

        $payload = SeatMapMapper::request($data);


        $response = $client->post($MS_SEAT_MAP, [
            "headers" => [
                "channel" => "web",
                "flow" => "myb",
                "x-transactionId" => $uuid_v1,
                "store" => "mx",
                "platform" => "web",
                "workflow" => "ambusiness",
                "app-client" => "ecommerce",
                "Authorization" => "Bearer $token"
            ],
            "json" => $payload
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }


    public function assignSeat($data)
    {
        $MS_SEAT = config("corporate-priority.MS_SEAT");

        $token = $this->tokenService->grantAccess();

        $client = new Client();

        $uuid_v1 = Uuid::uuid1()->toString();

        $payload = AssignSeatMapper::request($data);

        $response = $client->post($MS_SEAT, [
            "headers" => [
                "channel" => "web",
                "flow" => "myb",
                "x-transactionId" => $uuid_v1,
                "store" => "mx",
                "platform" => "web",
                "workflow" => "ambusiness",
                "app-client" => "ecommerce",
                "Authorization" => "Bearer $token"
            ],
            "json" => $payload
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    public function eksToken()
    {
        $MS_EKS_CORPORATE_AUTH = config("corporate-priority.MS_EKS_AUTH");
        $EKS_BASIC_TOKEN = config("corporate-priority.EKS_BASIC_TOKEN");
        $EKS_SECRET = config("corporate-priority.EKS_SECRET");

        $client = new Client();

        $response = $client->post($MS_EKS_CORPORATE_AUTH, [
            "headers" => [
                "Authorization" => $EKS_BASIC_TOKEN,
                "Content-Type" => "application/json"
            ],
            "json" => [
                "apiName" => "eks-api-corporate",
                "secret" => $EKS_SECRET
            ]
        ]);

        return json_decode($response->getBody()->getContents(), true)["message"];
    }

    public function corporateBookings(string $rloc)
    {
        $MS_CORPORATE_VALIDATION = config("corporate-priority.MS_CORPORATE_VALIDATION");

        $eks_token = $this->eksToken();
        
        $client = new Client();

        $response = $client->post($MS_CORPORATE_VALIDATION, [
            "headers" => [
                "Authorization" => "Bearer $eks_token",
                "Content-Type" => "application/json"
            ],
            "json" => [
                "rloc" => $rloc
            ]
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }
}
