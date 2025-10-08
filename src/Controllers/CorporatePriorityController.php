<?php

namespace Amx\CorporatePriority\Controllers;

use Amx\CorporatePriority\Requests\AssignSeatRequest;

use Amx\CorporatePriority\Services\AeromexicoService;
use Amx\CorporatePriority\Requests\SeatMapRequest;
use Illuminate\Http\Request;
use App\Traits\HandleApiErrors;

class CorporatePriorityController
{
    use HandleApiErrors;

    private $aeromexicoService;

    public function __construct(AeromexicoService $aeromexicoService)
    {
        $this->aeromexicoService = $aeromexicoService;
    }

    public function getReservation(Request $request)
    {
        return $this->handleApiErrors(function () use ($request) {
            $data = $request->validate([
                'pnr' => 'required|string',
                'lastname' => 'required|string',
                'ticketNumber' => 'required|integer',
            ]);

            return $this->aeromexicoService->getReservation($data);
        });
    }

    public function getSeatMap(SeatMapRequest $request)
    {
        return $this->handleApiErrors(function () use ($request) {
            $response = $this->aeromexicoService->getSeatMap($request->all());

            $filter_data = array_values(array_filter($response["seatMap"], function ($seat) {
                return $seat["type"] === "PREFERRED";
            }));

            return $filter_data;
        });
    }

    public function assignSeat(AssignSeatRequest $request)
    {
        return $this->handleApiErrors(function () use ($request) {
            $response = $this->aeromexicoService->assignSeat($request->all());
            return $response;
        });
    }

    public function validateCorporate(Request $request)
    {
        return $this->handleApiErrors(function () use ($request) {
            $data = $request->validate([
                "pnr" => "required|string|min:6|max:6"
            ]);

            $response = $this->aeromexicoService->corporateBookings($data["pnr"]);

            return $response;
        });
    }

    public function condonateReservation(Request $request)
    {
        return $this->handleApiErrors(function () use ($request) {
            $data = $request->validate([
                "pnr" => "required|string|min:6|max:6"
            ]);

            return $this->aeromexicoService->preferredProcessBooking($data["pnr"]);
        });
    }
}
