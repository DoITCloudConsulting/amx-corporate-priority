<?php

namespace Amx\CorporatePriority\Services;

use Amx\CorporatePriority\Mappers\AssignSeatMapper;
use Amx\CorporatePriority\Mappers\SeatMapMapper;
use App\Services\Aeromexico\TokenService;
use GuzzleHttp\Client;
use Ramsey\Uuid\Uuid;
use Config;

class AeromexicoService
{

    protected $tokenService;
    private string $environment;


    public function __construct(TokenService $tokenService)
    {
        $this->tokenService = $tokenService;
        $this->environment = Config::get("app.env");
    }

    public function getClient(array $config = [],)
    {
        $headers = [];

        if ($this->environment === "production") {
            $headers["User-Agent"] = "Aeromexico/1.0";
        }

        $config["headers"] = $headers;

        return new Client(array_merge([], $config));
    }
    public function getReservation($data)
    {
        $MS_RESERVATION = config("corporate-priority.MS_RESERVATION");
        $uuid_v1 = Uuid::uuid1()->toString();

        $token = $this->tokenService->grantAccess();

        $client = $this->getClient([
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


        $response = $client->get($MS_RESERVATION);

        return json_decode($response->getBody()->getContents(), true);
    }

    public function getSeatMap($data)
    {
        $MS_SEAT_MAP = config("corporate-priority.MS_SEAT_MAP");
        $uuid_v1 = Uuid::uuid1()->toString();
        $token = $this->tokenService->grantAccess();
        $payload = SeatMapMapper::request($data);

        $client = $this->getClient([
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

        $response = $client->post($MS_SEAT_MAP);

        return json_decode($response->getBody()->getContents(), true);
    }


    public function assignSeat($data)
    {
        $MS_SEAT = config("corporate-priority.MS_SEAT");
        $uuid_v1 = Uuid::uuid1()->toString();
        $payload = AssignSeatMapper::request($data);

        $token = $this->tokenService->grantAccess();

        $client = $this->getClient([
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

        $response = $client->post($MS_SEAT);

        return json_decode($response->getBody()->getContents(), true);
    }

    public function eksToken()
    {
        $MS_EKS_CORPORATE_AUTH = config("corporate-priority.MS_EKS_AUTH");
        $EKS_BASIC_TOKEN = config("corporate-priority.EKS_BASIC_TOKEN");
        $EKS_SECRET = config("corporate-priority.EKS_SECRET");

        $client = $this->getClient([
            "headers" => [
                "Authorization" => $EKS_BASIC_TOKEN,
                "Content-Type" => "application/json"
            ],
            "json" => [
                "apiName" => "eks-api-corporate",
                "secret" => $EKS_SECRET
            ]
        ]);

        $response = $client->post($MS_EKS_CORPORATE_AUTH);

        return json_decode($response->getBody()->getContents(), true)["message"];
    }

    public function corporateBookings(string $rloc)
    {
        $MS_CORPORATE_VALIDATION = config("corporate-priority.MS_CORPORATE_VALIDATION");

        $eks_token = $this->eksToken();

        $client = $this->getClient([
            "headers" => [
                "Authorization" => "Bearer $eks_token",
                "Content-Type" => "application/json"
            ],
            "json" => [
                "rloc" => $rloc
            ]
        ]);

        $response = $client->post($MS_CORPORATE_VALIDATION);

        return json_decode($response->getBody()->getContents(), true);
    }

    public function preferredProcessBooking(string $pnr)
    {
        $MS_CONDONATE_SEATS_RESERVATION = config("corporate-priority.MS_CONDONATE_SEATS_RESERVATION");

        $client = $this->getClient([
            "headers" => [
                "Content-Type" => "application/json"
            ],
            "json" => [
                "rloc" => $pnr
            ]
        ]);

        $response = $client->post($MS_CONDONATE_SEATS_RESERVATION);


        return json_decode($response->getBody()->getContents(), true);
    }
}
