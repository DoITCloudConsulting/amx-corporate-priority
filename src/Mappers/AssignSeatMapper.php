<?php

namespace Amx\CorporatePriority\Mappers;

class AssignSeatMapper
{
    static function request(array $data): array
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
