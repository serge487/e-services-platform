<?php

namespace App\Http\Controllers\Citizen;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeedbackController extends Controller
{
    /**
     * Submit a rating and review after payment is confirmed.
     */
    public function store(Request $request, ServiceRequest $serviceRequest)
    {
        if ($serviceRequest->citizen_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this request.');
        }

        if (! $serviceRequest->canLeaveFeedback()) {
            return redirect()
                ->route('citizen.service-requests.show', $serviceRequest)
                ->with('error', 'Feedback is only available after your payment is confirmed, and only once per request.');
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'citizen_comment' => ['required', 'string', 'min:10', 'max:2000'],
            'is_private' => ['sometimes', 'boolean'],
        ]);

        $serviceRequest->loadMissing('service');

        Feedback::create([
            'service_request_id' => $serviceRequest->id,
            'office_id' => $serviceRequest->service->office_id,
            'service_id' => $serviceRequest->service_id,
            'citizen_id' => Auth::id(),
            'rating' => $validated['rating'],
            'citizen_comment' => $validated['citizen_comment'],
            'is_private' => $request->boolean('is_private'),
        ]);

        return redirect()
            ->route('citizen.service-requests.show', $serviceRequest)
            ->with('success', 'Thank you! Your review has been submitted.');
    }
}
