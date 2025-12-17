<?php

namespace Amx\CorporatePriority\Mappers;

class AssignSeatMapper
{
    static function request($data)
    {
        return [
            "transactionDate" => $data["transactionDate"],
            "requestFrom" => "FE",
            "isStandBy" => $data["isStandBy"],
            "reservationCode" => $data["reservationCode"],
            "passengers" => [
                [
                    "lastName" => $data["passenger"]["lastName"],
                    "firstName" => $data["passenger"]["firstName"],
                    "nameNumber" => $data["passenger"]["nameNumber"],
                    "type" => $data["passenger"]["type"],
                    "beneficiaryTier" => "",
                    "corporateAccount" => "",
                    "ffNumber" => "",
                    "ffTierLevel" => "",
                    "cobrandType" => "",
                    "seats" => [
                        [
                            "id" => $data["seat"]["id"] ?? "",
                            "seatCode" => $data["seat"]["seatCode"],
                            "isChangeSeat" => $data["seat"]["isChangeSeat"],
                            "seatCodeOld" => $data["seat"]["seatCodeOld"] ?? "",
                            "segmentCode" => $data["seat"]["segmentCode"],
                            "isRedemptionCobrand" => false,
                            "isRedemptionTier" => false,
                            "isRedemptionCorporate" => false,
                            "emd" => $data["seat"]["emd"] ?? "",
                            "status" => "",
                            "currency" => [
                                "currencyCode" => $data["seat"]["currencyCode"],
                                "base" => $data["seat"]["base"],
                                "taxes" => $data["seat"]["taxes"],
                                "total" => $data["seat"]["total"],
                            ],
                            "redemptionType" => "",
                            "isRedemptionBeneficiaryTier" => false,
                            "deleteProcess" => false
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
                    "legEntity" => $data["segment"]["entity"],
                    "remainingTimeToCheckin" => $data["segment"]["remainingTimeToCheckIn"],
                    "legCode" => $data["segment"]["legCode"],
                    "segments" => [
                        [
                            "segmentEntity" => $data["segment"]["entity"],
                            "remainingSegmentTimeToCheckin" => $data["segment"]["remainingTimeToCheckIn"],
                            "departureAirportTimeZoneId" => "America/Mexico_City",
                            "windowsCheckin" => false,
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
