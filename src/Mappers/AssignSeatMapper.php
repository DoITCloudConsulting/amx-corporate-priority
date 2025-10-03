<?php

namespace Amx\CorporatePriority\Mappers;

class AssignSeatMapper
{
    static function request($data)
    {
        return [
            "transactionDate" => $data["transactionDate"],
            "isStandBy" => $data["isStandBy"],
            "reservationCode" => $data["reservationCode"],
            "passengers" => [
                [
                    "lastName" => $data["passenger"]["lastName"],
                    "firstName" => $data["passenger"]["firstName"],
                    "nameNumber" => $data["passenger"]["nameNumber"],
                    "type" => $data["passenger"]["type"],
                    "ffNumber" => $data["passenger"]["ffNumber"] ?? "",
                    "ffTierLevel" => $data["passenger"]["ffTierLevel"] ?? "",
                    "cobrandType" => $data["passenger"]["cobrandType"] ?? "",
                    "seats" => [
                        [
                            "id" => $data["seat"]["seatId"] ?? "",
                            "seatCode" => $data["seat"]["seatCode"],
                            "isChangeSeat" => $data["seat"]["isChangeSeat"],
                            "seatCodeOld" => $data["seat"]["seatCodeOld"] ?? "",
                            "segmentCode" => $data["seat"]["segmentCode"],
                            "isRedemptionCobrand" => $data["seat"]["isRedemptionCobrand"],
                            "isRedemptionTier" => $data["seat"]["isRedemptionTier"],
                            "isRedemptionCorporate" => $data["seat"]["isRedemptionCorporate"],
                            "emd" => $data["seat"]["emd"] ?? "",
                            "status" => $data["seat"]["status"] ?? "",
                            "currency" => [
                                "currencyCode" => $data["seat"]["currencyCode"],
                                "base" => $data["seat"]["base"],
                                "taxes" => $data["seat"]["taxes"],
                                "total" => $data["seat"]["total"],
                            ],
                            "redemptionType" => $data["seat"]["redemptionType"] ?? "",
                        ]
                    ],
                    "ticketInfo" => [
                        "eticket" => $data["ticketNumber"],
                        "coupons" => [
                            [
                                "couponNumber" => $data["segment"]["coupon"],
                                "couponStatus" => $data["segment"]["status"],
                                "segmentCode" => $data["segment"]["segmentCode"],
                            ]
                        ],
                    ],
                    "eticket" => $data["ticketNumber"],
                ]
            ],
            "legs" => [
                [
                    "legCode" => $data["segment"]["legCode"],
                    "segments" => [
                        [
                            "segmentCode" => $data["segment"]["segmentCode"],
                            "aircraftType" => $data["segment"]["aircraftType"],
                            "origin" => $data["segment"]["origin"],
                            "destination" => $data["segment"]["destination"],
                            "marketingFlightCode" => $data["segment"]["marketingFlightCode"],
                            "marketingCarrier" => $data["segment"]["marketingCarrier"],
                            "operatingCarrier" => $data["segment"]["operatingCarrier"],
                            "operatingFlightCode" => $data["segment"]["operatingFlightCode"],
                            "departureDate" => $data["segment"]["departureDate"],
                            "arrivalDate" => $data["segment"]["arrivalDate"],
                            "farebasis" => $data["segment"]["farebasis"],
                            "fareFamily" => $data["segment"]["fareFamily"],
                            "bookingClass" => $data["segment"]["bookingClass"],
                            "cabinClass" => $data["segment"]["cabinClass"],
                            "bookingCabin" => $data["segment"]["bookingCabin"],
                            "segmentNumber" => $data["segment"]["segmentNumber"],
                        ]
                    ],
                ]
            ],
            "rollback" => $data["rollback"],
        ];
    }
}
