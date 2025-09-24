<?php

namespace Amx\CorporatePriority\Controllers;

use Amx\CorporatePriority\Requests\AssignSeatRequest;

use Amx\CorporatePriority\Services\AeromexicoService;
use Illuminate\Http\Request;


class CorporatePriorityController
{

    private $aeromexicoService;

    public function __construct(AeromexicoService $aeromexicoService)
    {
        $this->aeromexicoService = $aeromexicoService;
    }

    public function authenticate() {}

    public function getReservation(Request $request)
    {

        try {
            $data = $request->validate([
                'pnr' => 'required|string',
                'lastname' => 'required|string',
                'ticketNumber' => 'required|integer',
            ]);

            $response = $this->aeromexicoService->getReservation($data);

            return response()->json($response);
        } catch (\Throwable $th) {

            $statusCode  =   method_exists($th, 'getStatusCode')
                ? $th->getStatusCode()
                : 500;


            if ($th instanceof \Illuminate\Validation\ValidationException) {
                return response()->json([
                    'message' => $th->getMessage(),
                    'errors' => $th->errors(),
                ], 422);
            }

            return response()->json([
                "message" => $th->getMessage()
            ],  $statusCode);
        }
    }

    public function assignSeat(AssignSeatRequest $request)
    {
        $data = $request->validated();

        $response = $this->aeromexicoService->assignSeat($data);

        return response()->json($response);
    }
}
