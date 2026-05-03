<?php

namespace App\Http\Controllers;

use App\Models\ServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QrCodeScanController extends Controller
{
    /**
     * Scan QR code and redirect/return request info.
     * Accepts either GET or POST with qr_token parameter.
     */
    public function scan(Request $request)
    {
        $qrToken = $request->input('token') ?? $request->route('token');

        $serviceRequest = ServiceRequest::where('qr_code_token', $qrToken)
            ->with(['citizen', 'service.office', 'service.category'])
            ->firstOrFail();

        // If user is authenticated
        if (Auth::check()) {
            $user = Auth::user();

            // Citizen viewing their own request
            if ($user->role === 'citizen' && $serviceRequest->citizen_id === $user->id) {
                return redirect()->route('citizen.service-requests.show', $serviceRequest);
            }

            // Office/Municipality staff viewing request
            if (in_array($user->role, ['office_staff', 'municipality'])) {
                $officeIds = $user->accessibleOfficeIds();
                if (in_array($serviceRequest->service->office_id, $officeIds)) {
                    return redirect()->route('municipality.requests.show', $serviceRequest);
                }
            }
        }

        // Return JSON for API/mobile usage
        return response()->json([
            'id' => $serviceRequest->id,
            'service' => $serviceRequest->service->name,
            'office' => $serviceRequest->service->office->name,
            'status' => $serviceRequest->citizenDisplayStatus(),
            'citizen' => $serviceRequest->citizen->name,
            'created_at' => $serviceRequest->created_at,
            'updated_at' => $serviceRequest->updated_at,
            'qr_token' => $serviceRequest->qr_code_token,
        ]);
    }

    /**
     * Get QR code data as JSON for display purposes.
     */
    public function data($qrToken)
    {
        $serviceRequest = ServiceRequest::where('qr_code_token', $qrToken)
            ->with(['citizen', 'service.office', 'service.category'])
            ->firstOrFail();

        return response()->json([
            'id' => $serviceRequest->id,
            'service' => $serviceRequest->service->name,
            'office' => $serviceRequest->service->office->name,
            'status' => $serviceRequest->citizenDisplayStatus(),
            'citizen' => $serviceRequest->citizen->name,
            'email' => $serviceRequest->citizen->email,
            'phone' => $serviceRequest->citizen->phone_number,
            'created_at' => $serviceRequest->created_at->toIso8601String(),
            'updated_at' => $serviceRequest->updated_at->toIso8601String(),
            'qr_token' => $serviceRequest->qr_code_token,
        ]);
    }
}
