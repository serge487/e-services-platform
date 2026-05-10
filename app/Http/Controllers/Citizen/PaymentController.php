<?php

namespace App\Http\Controllers\Citizen;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\ServiceRequest;
use App\Notifications\ServiceRequestStatusUpdated;
use App\Services\NotificationRealtimeBroadcaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    // Crypto wallet addresses — set these in .env
    private const CRYPTO_WALLETS = [
        'USDT_TRC20' => null, // set via env
        'BTC'        => null, // set via env
    ];

    /**
     * Show the payment selection page for a service request.
     * Only accessible when request is In Review and payment is pending.
     */
    public function show(ServiceRequest $serviceRequest)
    {
        $this->authorizeCitizenAccess($serviceRequest);

        if (! $serviceRequest->needsPayment()) {
            return redirect()->route('citizen.service-requests.show', $serviceRequest)
                ->with('info', 'No payment is required at this time.');
        }

        $serviceRequest->load(['service.office', 'payment']);

        $cryptoWallets = [
            'USDT_TRC20' => config('payment.crypto.usdt_trc20'),
            'BTC'        => config('payment.crypto.btc'),
        ];

        $whishNumber = config('payment.whish.number');

        return view('citizen.payment', compact(
            'serviceRequest',
            'cryptoWallets',
            'whishNumber'
        ));
    }

    /**
     * Citizen selects payment method and submits payment details.
     */
    public function store(Request $request, ServiceRequest $serviceRequest)
    {
        $this->authorizeCitizenAccess($serviceRequest);

        if (! $serviceRequest->needsPayment()) {
            return redirect()->route('citizen.service-requests.show', $serviceRequest);
        }

        $validated = $request->validate([
            'payment_method' => ['required', 'in:whish,crypto,cash'],
            // Whish fields
            'whish_phone'     => ['required_if:payment_method,whish', 'nullable', 'string', 'max:20'],
            'whish_reference' => ['required_if:payment_method,whish', 'nullable', 'string', 'max:100'],
            // Crypto fields
            'crypto_coin'     => ['required_if:payment_method,crypto', 'nullable', 'in:USDT_TRC20,BTC'],
            'crypto_tx_hash'  => ['required_if:payment_method,crypto', 'nullable', 'string', 'max:255'],
        ]);

        $payment = $serviceRequest->payment;

        // Cash on pickup — mark directly as paid, auto-approve request
        if ($validated['payment_method'] === 'cash') {
            $payment->update([
                'payment_method' => 'cash',
                'status'         => 'paid',
                'paid_at'        => now(),
            ]);

            $this->approveRequest($serviceRequest, 'Cash on pickup selected. Payment confirmed.');

            return redirect()->route('citizen.service-requests.show', $serviceRequest)
                ->with('success', 'Cash on pickup confirmed. Your request has been approved. Please visit the office to collect your documents and pay.');
        }

        // Whish — pending manual verification by municipality
        if ($validated['payment_method'] === 'whish') {
            $payment->update([
                'payment_method'  => 'whish',
                'whish_phone'     => $validated['whish_phone'],
                'whish_reference' => $validated['whish_reference'],
                'status'          => 'pending', // municipality must verify
            ]);

            return redirect()->route('citizen.service-requests.show', $serviceRequest)
                ->with('success', 'Whish payment details submitted. The office will verify your payment shortly.');
        }

        // Crypto — pending manual verification by municipality
        if ($validated['payment_method'] === 'crypto') {
            $payment->update([
                'payment_method'        => 'crypto',
                'crypto_coin'           => $validated['crypto_coin'],
                'transaction_reference' => $validated['crypto_tx_hash'],
                'status'                => 'pending', // municipality must verify
            ]);

            return redirect()->route('citizen.service-requests.show', $serviceRequest)
                ->with('success', 'Crypto transaction hash submitted. The office will verify your payment shortly.');
        }
    }

    /**
     * Municipality confirms Whish or Crypto payment manually.
     * Called from municipality side.
     */
    public function confirmPayment(ServiceRequest $serviceRequest)
    {
        // This is called by municipality staff, not citizen
        abort_unless(
            in_array(Auth::user()->role, ['municipality', 'office_staff'], true),
            403
        );

        $payment = $serviceRequest->payment;

        if (! $payment || $payment->isPaid()) {
            return back()->with('error', 'Payment already confirmed or not found.');
        }

        $payment->update([
            'status'  => 'paid',
            'paid_at' => now(),
        ]);

        $this->approveRequest($serviceRequest, 'Payment verified by office staff.');

        return back()->with('success', 'Payment confirmed. Request automatically approved.');
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Abort 403 if the service request does not belong to the authenticated citizen.
     */
    private function authorizeCitizenAccess(ServiceRequest $serviceRequest): void
    {
        if ($serviceRequest->citizen_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this payment.');
        }
    }

    /**
     * Auto-approve the service request after payment is confirmed.
     */
    private function approveRequest(ServiceRequest $serviceRequest, string $notes): void
    {
        $previousStatus = $serviceRequest->status;

        $serviceRequest->update([
            'status'       => 'Approved',
            'office_notes' => $notes,
        ]);

        // Notify citizen
        $serviceRequest->citizen->notify(
            new ServiceRequestStatusUpdated(
                $serviceRequest,
                'Approved',
                $notes
            )
        );

        try {
            NotificationRealtimeBroadcaster::broadcastLatest($serviceRequest->citizen);
        } catch (\Exception $e) {
            // Non-fatal if broadcasting fails
        }
    }
}