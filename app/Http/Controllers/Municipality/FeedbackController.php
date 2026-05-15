<?php

namespace App\Http\Controllers\Municipality;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Models\Office;
use App\Services\FeedbackReplyNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeedbackController extends Controller
{
    public function index(Request $request)
    {
        $officeIds = $this->accessibleOfficeIds();

        $officeFilter = $request->query('office_id');
        if ($officeFilter && ! in_array((int) $officeFilter, $officeIds, true)) {
            abort(403);
        }

        $query = Feedback::query()
            ->with(['citizen', 'service', 'office', 'serviceRequest'])
            ->whereIn('office_id', $officeIds)
            ->latest();

        if ($officeFilter) {
            $query->where('office_id', $officeFilter);
        }

        $replyFilter = $request->query('reply', 'all');
        if ($replyFilter === 'awaiting') {
            $query->whereNull('office_response');
        } elseif ($replyFilter === 'replied') {
            $query->whereNotNull('office_response');
        }

        $feedbacks = $query->paginate(12)->withQueryString();

        $offices = Office::query()
            ->whereIn('id', $officeIds)
            ->orderBy('name')
            ->get();

        $statsQuery = Feedback::whereIn('office_id', $officeIds);

        $stats = [
            'total' => (clone $statsQuery)->count(),
            'average' => round((float) (clone $statsQuery)->avg('rating'), 1),
            'this_month' => (clone $statsQuery)
                ->where('created_at', '>=', now()->startOfMonth())
                ->count(),
            'awaiting_response' => (clone $statsQuery)->whereNull('office_response')->count(),
        ];

        return view('municipality.feedback', [
            'feedbacks' => $feedbacks,
            'offices' => $offices,
            'officeFilter' => $officeFilter,
            'replyFilter' => $replyFilter,
            'stats' => $stats,
            'canRespond' => Auth::user()->isMunicipalityAdmin(),
        ]);
    }

    /**
     * Municipality administrators may reply to citizen reviews (public or private reply).
     */
    public function respond(Request $request, Feedback $feedback)
    {
        $this->authorizeFeedback($feedback);

        $validated = $request->validate([
            'office_response' => ['required', 'string', 'min:5', 'max:2000'],
            'office_response_is_private' => ['sometimes', 'boolean'],
        ]);

        $feedback->update([
            'office_response' => $validated['office_response'],
            'office_response_is_private' => $request->boolean('office_response_is_private'),
        ]);

        FeedbackReplyNotifier::notifyCitizen($feedback);

        return redirect()
            ->route('municipality.feedback', $request->only(['office_id', 'reply']))
            ->with('success', 'Your reply has been saved and sent to the citizen.');
    }

    private function accessibleOfficeIds(): array
    {
        $officeIds = Auth::user()->accessibleOfficeIds();

        if ($officeIds === []) {
            abort(403, 'No offices assigned to your account.');
        }

        return $officeIds;
    }

    private function authorizeFeedback(Feedback $feedback): void
    {
        if (! in_array($feedback->office_id, $this->accessibleOfficeIds(), true)) {
            abort(403);
        }
    }
}
