<?php

namespace Amx\CorporatePriority\Services;

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

        try {
            $token = $this->tokenService->get();

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
                    "Authorization" => "Bearer $token"
                ]
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function getSeatMap($data)
    {
        $MS_SEAT_MAP = config("corporate-priority.MS_SEAT_MAP");

        try {
            $token = $this->tokenService->get();

            $client = new Client();

            $uuid_v1 = Uuid::uuid1()->toString();

            $payload = $this->mapSeatMapData($data);

            $response = $client->get($MS_SEAT_MAP, [
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
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    private function mapSeatMapData($data)
    {
        return  [
            "reservationCode" => $data["reservationCode"],
            "isStandBy" => $data["isStandBy"],
            "transactionDate" => $data["transactionDate"],
            "legs" => [
                [
                    "legCode" => $data["legCode"],
                    "legRegion" => $data["legRegion"],
                    "windowLegStatus" => $data["windowLegStatus"],
                    "segments" => [
                        [
                            "segmentCode" => $data["segmentCode"],
                            "aircraftType" => $data["aircraftType"],
                            "origin" => $data["origin"],
                            "destination" => $data["destination"],
                            "marketingCarrier" => $data["marketingCarrier"],
                            "operatingCarrier" => $data["operatingCarrier"],
                            "operatingFlightCode" => $data["operatingFlightCode"],
                            "departureDate" => $data["departureDate"],
                            "arrivalDate" => $data["arrivalDate"],
                            "farebasis" => $data["farebasis"],
                            "fareFamily" => $data["fareFamily"],
                            "bookingClass" => $data["bookingClass"],
                            "bookingCabin" => $data["bookingCabin"],
                            "segmentRegion" => $data["segmentRegion"],
                        ]
                    ],
                ]
            ]
        ];
    }

    public function assignSeat($data)
    {
        $MS_SEAT = config("corporate-priority.MS_SEAT");

        try {
            $token = $this->tokenService->get();

            $client = new Client();

            $uuid_v1 = Uuid::uuid1()->toString();

            $payload = $this->mapSeatData($data);

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
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    private function mapSeatData($data)
    {
        return [
            "transactionDate" => $data["transactionDate"],
            "isStandBy" => $data["isStandBy"],
            "reservationCode" => $data["reservationCode"],
            "passengers" => [
                [
                    "lastName" => $data["lastName"],
                    "firstName" => $data["firstName"],
                    "nameNumber" => $data["nameNumber"],
                    "type" => $data["type"],
                    "ffNumber" => $data["ffNumber"],
                    "ffTierLevel" => $data["ffTierLevel"],
                    "cobrandType" => $data["cobrandType"],
                    "seats" => [
                        [
                            "id" => $data["seatId"],
                            "seatCode" => $data["seatCode"],
                            "isChangeSeat" => $data["isChangeSeat"],
                            "seatCodeOld" => $data["seatCodeOld"],
                            "segmentCode" => $data["segmentCode"],
                            "isRedemptionCobrand" => $data["isRedemptionCobrand"],
                            "isRedemptionTier" => $data["isRedemptionTier"],
                            "isRedemptionCorporate" => $data["isRedemptionCorporate"],
                            "emd" => $data["emd"],
                            "status" => $data["status"],
                            "currency" => [
                                "currencyCode" => $data["currencyCode"],
                                "base" => $data["base"],
                                "taxes" => $data["taxes"],
                                "total" => $data["total"],
                            ],
                            "redemptionType" => $data["redemptionType"],
                        ]
                    ],
                    "ticketInfo" => [
                        "eticket" => $data["eticket"],
                        "coupons" => [
                            [
                                "couponNumber" => $data["couponNumber"],
                                "couponStatus" => $data["couponStatus"],
                                "segmentCode" => $data["couponSegmentCode"],
                            ]
                        ],
                    ],
                    "eticket" => $data["eticket"],
                ]
            ],
            "legs" => [
                [
                    "legCode" => $data["legCode"],
                    "segments" => [
                        [
                            "segmentCode" => $data["segmentCode"],
                            "aircraftType" => $data["aircraftType"],
                            "origin" => $data["origin"],
                            "destination" => $data["destination"],
                            "marketingFlightCode" => $data["marketingFlightCode"],
                            "marketingCarrier" => $data["marketingCarrier"],
                            "operatingCarrier" => $data["operatingCarrier"],
                            "operatingFlightCode" => $data["operatingFlightCode"],
                            "departureDate" => $data["departureDate"],
                            "arrivalDate" => $data["arrivalDate"],
                            "farebasis" => $data["farebasis"],
                            "fareFamily" => $data["fareFamily"],
                            "bookingClass" => $data["bookingClass"],
                            "cabinClass" => $data["cabinClass"],
                            "bookingCabin" => $data["bookingCabin"],
                            "segmentNumber" => $data["segmentNumber"],
                        ]
                    ],
                ]
            ],
            "rollback" => $data["rollback"],
        ];
    }
}
