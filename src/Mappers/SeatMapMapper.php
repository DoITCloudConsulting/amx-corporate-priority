<?php

namespace Amx\CorporatePriority\Mappers;

class SeatMapMapper
{
    static function request(array $data): array
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
}
