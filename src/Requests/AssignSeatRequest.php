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
            // Raíz
            'transactionDate'   => 'required|date',
            'isStandBy'         => 'required|boolean',
            'reservationCode'   => 'required|string|max:10',
            'rollback'          => 'required|boolean',
            'ticketNumber'      => 'required|string',

            'passenger'                              => 'required|array|min:1',
            'passenger.lastName'                     => 'required|string',
            'passenger.firstName'                    => 'required|string',
            'passenger.nameNumber'                   => 'required|string',
            'passenger.type'                         => 'required|string', // ejemplo de tipos
            'passenger.ffNumber'                     => 'nullable|string',
            'passenger.ffTierLevel'                  => 'nullable|string',
            'passenger.cobrandType'                  => 'nullable|string',

            'seat'                        => 'required|array|min:1',
            'seat.id'                     => 'nullable|string',
            'seat.seatCode'               => 'required|string',
            'seat.isChangeSeat'           => 'required|boolean',
            'seat.seatCodeOld'            => 'nullable|string',
            'seat.segmentCode'            => 'required|string',
            'seat.isRedemptionCobrand'    => 'required|boolean',
            'seat.isRedemptionTier'       => 'required|boolean',
            'seat.isRedemptionCorporate'  => 'required|boolean',
            'seat.emd'                    => 'nullable|string',
            'seat.status'                 => 'nullable|string',
            'seat.currencyCode'           => 'required|string',
            'seat.base'                   => 'required|numeric',
            'seat.taxes'                  => 'required|numeric',
            'seat.total'                  => 'required|numeric',
            'seat.redemptionType'         => 'nullable|string',


            'segment.segmentCode'         => 'required|string',
            'segment.coupon'              => 'required|integer',
            'segment.status'              => 'required|string',
            'segment.legCode'             => 'required|string',
            'segment.aircraftType'        => 'required|string',
            'segment.origin'              => 'required|string',
            'segment.destination'         => 'required|string',
            'segment.marketingFlightCode' => 'required|string',
            'segment.marketingCarrier'    => 'required|string',
            'segment.operatingCarrier'    => 'required|string',
            'segment.operatingFlightCode' => 'required|string',
            'segment.departureDate'       => 'required|date',
            'segment.arrivalDate'         => 'required|date',
            'segment.farebasis'           => 'required|string',
            'segment.fareFamily'          => 'required|string',
            'segment.bookingClass'        => 'required|string',
            'segment.cabinClass'          => 'required|string',
            'segment.bookingCabin'        => 'required|string',
            'segment.segmentNumber'       => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'The :attribute field is required',
            'boolean'  => 'The :attribute must be true or false',
            'integer'  => 'The :attribute must be an integer',
            'numeric'  => 'The :attribute must be a number',
            'date'     => 'The :attribute must be a valid date',
        ];
    }
}
