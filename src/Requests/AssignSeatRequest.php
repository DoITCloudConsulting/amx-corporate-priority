<?php

namespace Amx\CorporatePriority\Requests;

use Illuminate\Foundation\Http\FormRequest;


class AssignSeatRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Cambia a true si cualquier usuario puede enviar la request
        return true;
    }

    public function rules(): array
    {
        return [
            // Nivel raíz
            'transactionDate' => 'required|date',
            'isStandBy' => 'required|boolean',
            'reservationCode' => 'required|string',
            'rollback' => 'required|boolean',

            // Pasajero (solo un pasajero por simplicidad)
            'lastName' => 'required|string',
            'firstName' => 'required|string',
            'nameNumber' => 'required|string',
            'type' => 'required|string',
            'ffNumber' => 'nullable|string',
            'ffTierLevel' => 'nullable|string',
            'cobrandType' => 'nullable|string',

            // Asiento
            'seatId' => 'required|string',
            'seatCode' => 'required|string',
            'isChangeSeat' => 'required|boolean',
            'seatCodeOld' => 'nullable|string',
            'segmentCode' => 'required|string',
            'isRedemptionCobrand' => 'required|boolean',
            'isRedemptionTier' => 'required|boolean',
            'isRedemptionCorporate' => 'required|boolean',
            'emd' => 'nullable|string',
            'status' => 'nullable|string',
            'currencyCode' => 'required|string',
            'base' => 'required|numeric',
            'taxes' => 'required|numeric',
            'total' => 'required|numeric',
            'redemptionType' => 'nullable|string',

            // Ticket info
            'eticket' => 'required|string',
            'couponNumber' => 'required|integer',
            'couponStatus' => 'required|string',
            'couponSegmentCode' => 'required|string',

            // Legs / Segmento
            'legCode' => 'required|string',
            'aircraftType' => 'required|string',
            'origin' => 'required|string',
            'destination' => 'required|string',
            'marketingFlightCode' => 'required|string',
            'marketingCarrier' => 'required|string',
            'operatingCarrier' => 'required|string',
            'operatingFlightCode' => 'required|string',
            'departureDate' => 'required|date',
            'arrivalDate' => 'required|date',
            'farebasis' => 'required|string',
            'fareFamily' => 'required|string',
            'bookingClass' => 'required|string',
            'cabinClass' => 'required|string',
            'bookingCabin' => 'required|string',
            'segmentNumber' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'The :attribute field is required',
            'boolean' => 'The :attribute must be true or false',
            'integer' => 'The :attribute must be an integer',
            'numeric' => 'The :attribute must be a number',
            'date' => 'The :attribute must be a valid date',
        ];
    }
}
