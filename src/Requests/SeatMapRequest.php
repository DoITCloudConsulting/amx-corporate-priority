<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SeatMapRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // permite que cualquier usuario haga la request
    }

    public function rules(): array
    {
        return [
            // Nivel raíz
            'reservationCode' => 'required|string',
            'isStandBy' => 'required|boolean',
            'transactionDate' => 'required|date',

            // Legs
            'legCode' => 'required|string',
            'legRegion' => 'required|string',
            'windowLegStatus' => 'required|string',

            // Segments
            'segmentCode' => 'required|string',
            'aircraftType' => 'required|string',
            'origin' => 'required|string',
            'destination' => 'required|string',
            'marketingCarrier' => 'required|string',
            'operatingCarrier' => 'required|string',
            'operatingFlightCode' => 'required|string',
            'departureDate' => 'required|date',
            'arrivalDate' => 'required|date',
            'farebasis' => 'required|string',
            'fareFamily' => 'required|string',
            'bookingClass' => 'required|string',
            'bookingCabin' => 'required|string',
            'segmentRegion' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'The :attribute field is required',
            'boolean' => 'The :attribute must be true or false',
            'string' => 'The :attribute must be a string',
            'date' => 'The :attribute must be a valid date',
        ];
    }
}
