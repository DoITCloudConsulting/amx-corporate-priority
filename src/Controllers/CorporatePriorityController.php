<?php

namespace Amx\CorporatePriority\Controllers;

use Amx\CorporatePriority\Requests\AssignSeatRequest;

use Amx\CorporatePriority\Services\AeromexicoService;
use Amx\CorporatePriority\Requests\SeatMapRequest;
use Illuminate\Http\Request;
use App\Traits\HandleApiErrors;
use Amx\Salesforce\Objects\Standard\SFCase;
use Amx\Salesforce\Objects\Standard\SFAccount;
use Exception;

class CorporatePriorityController
{
    use HandleApiErrors;

    private $aeromexicoService;
    protected $N2_AGENCY_RECORD_TYPE_ID;

    public function __construct(AeromexicoService $aeromexicoService)
    {
        $this->aeromexicoService = $aeromexicoService;
        $this->N2_AGENCY_RECORD_TYPE_ID = config('app.N2_AGENCY_RECORD_TYPE_ID');
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
            return $this->aeromexicoService->getSeatMap($request->all());
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

    public function createCase(Request $request)
    {
        try {
            //code...
            $data = $request->all();

            $issuerAccount = (new SFAccount([
                'IATA__c' => $data["case"]["iata"],
                'Branches__c' => $data["case"]["iata"],
                'RecordTypeId' => $this->N2_AGENCY_RECORD_TYPE_ID
            ]))->getByStationNumber();

            if (!$issuerAccount) {
                throw new Exception("Issuer account not found.", 404);
            }

            $applicantAccount = (new SFAccount(['Branches__c' => $data["case"]["iataSolicitante"]]))->getByStationNumber();

            if (!$applicantAccount) {
                throw new Exception("Applicant account not found.", 404);
            }

            $data["case"]["originalAgency"] = $issuerAccount->Id;
            $data["case"]["agency"] = $applicantAccount->Id;

            $case = (new SFCase())->createCase($data, $issuerAccount, $applicantAccount);

            return response()->json([
                'status' => 200,
                'data' => $case,
                'timestamp' => now()->toIso8601String(),
            ]);
        } catch (\Throwable $th) {
            //throw $th;

            return response()->json([
                "message" => $th->getMessage(),
                "errors" => $th->getTrace()
            ], 500);
        }
    }
}
