<?php

namespace Amx\CorporatePriority\Controllers;

use Amx\CorporatePriority\Requests\AssignSeatRequest;

use Amx\CorporatePriority\Services\AeromexicoService;
use Amx\CorporatePriority\Requests\SeatMapRequest;
use Illuminate\Http\Request;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Foundation\Http\FormRequest;

class CorporatePriorityController
{

    private $aeromexicoService;

    public function __construct(AeromexicoService $aeromexicoService)
    {
        $this->aeromexicoService = $aeromexicoService;
    }

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
        } catch (RequestException $e) {

            $message = $e->getResponse()->getReasonPhrase();
            $body = json_decode($e->getResponse()->getBody(), true);
            $statusCode = $e->getResponse()->getStatusCode();

            return response()->json([
                "message" => $message,
                "body" => $body,
            ], $statusCode);
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

    public function getSeatMap(SeatMapRequest $request)
    {
        try {
            $response = $this->aeromexicoService->getSeatMap($request->all());

            return response()->json($response);
        } catch (RequestException $e) {

            $message = $e->getResponse()->getReasonPhrase();
            $body = json_decode($e->getResponse()->getBody(), true);
            $statusCode = $e->getResponse()->getStatusCode();

            return response()->json([
                "message" => $message,
                "body" => $body,
            ], $statusCode);
        } catch (\Throwable $th) {

            return response()->json([
                "message" => $th->getMessage(),
                "errors" => method_exists($th, 'errors') ? $th->errors() : null
            ], 500);
        }
    }

    public function assignSeat(AssignSeatRequest $request)
    {
        try {
            $response = $this->aeromexicoService->assignSeat($request->all());
            return response()->json($response);
        } catch (RequestException $e) {

            $message = $e->getResponse()->getReasonPhrase();
            $body = json_decode($e->getResponse()->getBody(), true);
            $statusCode = $e->getResponse()->getStatusCode();

            return response()->json([
                "message" => $message,
                "body" => $body,
            ], $statusCode);
        } catch (\Throwable $th) {

            return response()->json([
                "message" => $th->getMessage(),
                "errors" => method_exists($th, 'errors') ? $th->errors() : null
            ], 500);
        }
    }

    public function validateCorporate(Request $request)
    {

        try {
            $data = $request->validate([
                "pnr" => "required|string|min:6|max:6"
            ]);

            $response = $this->aeromexicoService->corporateBookings($data["pnr"]);

            return response()->json($response);
        } catch (RequestException $e) {

            $message = $e->getResponse()->getReasonPhrase();
            $body = json_decode($e->getResponse()->getBody(), true);
            $statusCode = $e->getResponse()->getStatusCode();

            return response()->json([
                "message" => $message,
                "body" => $body,
            ], $statusCode);
        } catch (\Throwable $th) {

            $statusCode = method_exists($th, 'getStatusCode')
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
}
