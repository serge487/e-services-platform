@extends('layouts.public')
@section('title', 'Complete Payment')
@section('page-title', 'Complete Payment')

@push('styles')
<style>
:root {
    --g:       #0a5c4a;
    --g-dark:  #064e3b;
    --g-soft:  #ecfdf5;
    --g-border:#d9eee7;
}

.pay-page .method-card {
    border: 2px solid #e2e8f0;
    border-radius: 14px;
    cursor: pointer;
    padding: 1.1rem 1.25rem;
    background: #fff;
    position: relative;
    transition: border-color 0.15s, box-shadow 0.15s, transform 0.1s;
    display: flex;
    align-items: center;
    gap: 1rem;
}
.pay-page .method-card:hover {
    border-color: var(--g);
    box-shadow: 0 3px 14px rgba(10,92,74,0.1);
    transform: translateY(-1px);
}
.pay-page .method-card.selected {
    border-color: var(--g);
    background: var(--g-soft);
    box-shadow: 0 3px 14px rgba(10,92,74,0.14);
}
.pay-page .method-logo {
    width: 48px; height: 48px;
    flex-shrink: 0;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    overflow: hidden;
}
.pay-page .method-logo img { width: 100%; height: 100%; object-fit: contain; }
.pay-page .method-logo.cash-logo {
    background: var(--g-soft);
    border: 1px solid var(--g-border);
    font-size: 1.4rem;
    color: var(--g);
}
.pay-page .method-info { flex: 1; min-width: 0; }
.pay-page .method-title {
    font-weight: 700;
    font-size: 0.95rem;
    color: #1e293b;
    margin-bottom: 0.15rem;
}
.pay-page .method-desc {
    font-size: 0.78rem;
    color: #64748b;
    line-height: 1.4;
}
.pay-page .method-check {
    width: 22px; height: 22px;
    border: 2px solid #cbd5e1;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    transition: all 0.15s;
    font-size: 0.75rem;
    color: transparent;
}
.pay-page .method-card.selected .method-check {
    background: var(--g);
    border-color: var(--g);
    color: #fff;
}

/* Amount header */
.pay-page .amount-header {
    background: linear-gradient(135deg, #0a5c4a 0%, #0d6e5a 100%);
    border-radius: 14px;
    color: #fff;
    padding: 1.25rem 1.5rem;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 0.5rem;
}
.pay-page .amount-header .amt { font-size: 2rem; font-weight: 900; line-height: 1; }
.pay-page .amount-header .amt-label { font-size: 0.72rem; opacity: 0.7; text-transform: uppercase; letter-spacing: 0.5px; }
.pay-page .amount-header .amt-service { font-size: 0.82rem; opacity: 0.8; margin-top: 0.2rem; }

/* Form panel */
.pay-page .form-panel {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 1.5rem;
    box-shadow: 0 1px 6px rgba(0,0,0,0.05);
}

/* Steps */
.pay-page .step-row {
    display: flex;
    gap: 0.85rem;
    padding: 0.6rem 0;
    border-bottom: 1px solid #f1f5f9;
    align-items: flex-start;
}
.pay-page .step-row:last-child { border-bottom: none; }
.pay-page .step-dot {
    width: 24px; height: 24px;
    background: var(--g);
    color: #fff;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.68rem;
    font-weight: 800;
    flex-shrink: 0;
    margin-top: 1px;
}
.pay-page .step-txt { font-size: 0.84rem; color: #374151; line-height: 1.45; }

/* Wallet address */
.pay-page .wallet-addr {
    background: #f8fafc;
    border: 1px solid var(--g-border);
    border-radius: 8px;
    font-family: monospace;
    font-size: 0.8rem;
    color: var(--g);
    padding: 0.6rem 0.85rem;
    word-break: break-all;
    cursor: pointer;
    transition: background 0.12s;
}
.pay-page .wallet-addr:hover { background: var(--g-soft); }

/* Buttons */
.pay-page .btn-submit {
    background: var(--g);
    border: none;
    border-radius: 10px;
    color: #fff;
    font-size: 0.9rem;
    font-weight: 700;
    padding: 0.7rem 1.5rem;
    width: 100%;
    transition: background 0.15s;
}
.pay-page .btn-submit:hover { background: var(--g-dark); color: #fff; }

/* Notes */
.pay-page .note-warn {
    background: #fffbeb;
    border: 1px solid #fde68a;
    border-radius: 8px;
    padding: 0.65rem 0.9rem;
    font-size: 0.78rem;
    color: #92400e;
}
.pay-page .note-ok {
    background: var(--g-soft);
    border: 1px solid var(--g-border);
    border-radius: 8px;
    padding: 0.65rem 0.9rem;
    font-size: 0.78rem;
    color: var(--g);
}

/* Form controls */
.pay-page .form-control:focus, .pay-page .form-select:focus {
    border-color: var(--g);
    box-shadow: 0 0 0 3px rgba(10,92,74,0.1);
}
.pay-page .form-label { font-weight: 600; font-size: 0.82rem; color: #374151; }

/* Summary card */
.pay-page .summary-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 1.5rem;
    box-shadow: 0 1px 6px rgba(0,0,0,0.05);
    position: sticky;
    top: 80px;
}
</style>
@endpush

@section('content')
<div class="pay-page">

    <a href="{{ route('citizen.service-requests.show', $serviceRequest, absolute: false) }}"
       class="text-decoration-none text-muted small d-inline-flex align-items-center gap-1 mb-3">
        <i class="bi bi-arrow-left"></i> Back to Request
    </a>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-3">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">

        {{-- Left --}}
        <div class="col-lg-7">

            {{-- Amount header --}}
            <div class="amount-header">
                <div>
                    <div class="amt-label">Amount Due</div>
                    <div class="amt">${{ number_format($serviceRequest->payment->amount, 2) }}</div>
                    <div class="amt-service">
                        {{ $serviceRequest->service->name }} — {{ $serviceRequest->service->office->name }}
                    </div>
                </div>
                <div style="text-align:right;">
                    <div class="amt-label">Request</div>
                    <div style="font-size:1.1rem;font-weight:700;">#{{ $serviceRequest->id }}</div>
                </div>
            </div>

            <p class="fw-bold small mb-3" style="color:#374151;">Select Payment Method</p>

            <form method="POST"
                  action="{{ route('citizen.service-requests.payment.store', $serviceRequest, absolute: false) }}"
                  id="pay-form">
                @csrf
                <input type="hidden" name="payment_method" id="selected_method" value="">

                {{-- Method cards --}}
                <div class="d-flex flex-column gap-2 mb-4">

                    {{-- Whish --}}
                    <div class="method-card" id="card-whish" onclick="pickMethod('whish')">
                        <div class="method-logo">
                            {{-- Real Whish logo from their CDN --}}
                            <img src="https://cdn.brandfetch.io/id2y9QbeKJ/theme/dark/logo.svg?c=1bxid64Mup7aczewSAYMX&t=1772812728722"
"
                                 alt="Whish Money"
                                 onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                            <div style="display:none;width:100%;height:100%;align-items:center;justify-content:center;background:#fff7ed;border-radius:10px;color:#d97706;font-size:1.2rem;">
                                <i class="bi bi-phone-fill"></i>
                            </div>
                        </div>
                        <div class="method-info">
                            <div class="method-title">Whish Money</div>
                            <div class="method-desc">Transfer from your Whish wallet and submit reference code</div>
                        </div>
                        <div class="method-check"><i class="bi bi-check2"></i></div>
                    </div>

                    {{-- Crypto --}}
                    <div class="method-card" id="card-crypto" onclick="pickMethod('crypto')">
                        <div class="method-logo" style="background:#f5f3ff;border:1px solid #e9d5ff;border-radius:10px;padding:6px;">
                            {{-- Real crypto (Bitcoin) logo SVG --}}
                            <svg viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg" width="36" height="36">
                                <g fill="none" fill-rule="evenodd">
                                    <circle cx="16" cy="16" r="16" fill="#F7931A"/>
                                    <path d="M22.56 14.17c.3-2.02-1.24-3.1-3.34-3.83l.68-2.73-1.67-.42-.67 2.67c-.44-.11-.89-.21-1.34-.32l.67-2.69-1.67-.42-.68 2.73c-.36-.08-.72-.17-1.07-.25l.002-.008-2.3-.57-.44 1.78s1.24.28 1.22.3c.68.17.8.62.78 .97l-.79 3.15c.05.01.11.03.17.06l-.17-.04-1.1 4.42c-.08.21-.3.52-.77.4.02.02-1.22-.3-1.22-.3l-.84 1.92 2.18.54c.4.1.8.21 1.18.31l-.69 2.76 1.66.42.68-2.73c.46.12.9.24 1.34.34l-.68 2.71 1.67.42.69-2.75c2.83.54 4.96.32 5.86-2.24.72-2.06-.04-3.25-1.53-4.02 1.08-.25 1.9-.97 2.12-2.44zm-3.8 5.33c-.51 2.06-3.97.95-5.09.67l.91-3.63c1.12.28 4.72.83 4.18 2.96zm.52-5.37c-.47 1.88-3.36.92-4.3.69l.82-3.3c.94.24 3.97.67 3.48 2.61z" fill="#FFF"/>
                                </g>
                            </svg>
                        </div>
                        <div class="method-info">
                            <div class="method-title">Cryptocurrency</div>
                            <div class="method-desc">Pay with USDT (TRC20) or Bitcoin — submit TX hash</div>
                        </div>
                        <div class="method-check"><i class="bi bi-check2"></i></div>
                    </div>

                    {{-- Cash --}}
                    <div class="method-card" id="card-cash" onclick="pickMethod('cash')">
                        <div class="method-logo cash-logo">
                            <i class="bi bi-cash-coin"></i>
                        </div>
                        <div class="method-info">
                            <div class="method-title">Cash on Pickup</div>
                            <div class="method-desc">Pay in cash when you visit the office — approved immediately</div>
                        </div>
                        <div class="method-check"><i class="bi bi-check2"></i></div>
                    </div>

                </div>

                {{-- Whish form --}}
                <div id="form-whish" class="form-panel mb-3 d-none">
                    <h6 class="fw-bold mb-3" style="color:var(--g);">
                        <i class="bi bi-phone me-2"></i>Whish Payment Details
                    </h6>
                    <div class="step-row">
                        <div class="step-dot">1</div>
                        <div class="step-txt">
                            Open your <strong>Whish Money app</strong> → Send
                            <strong>${{ number_format($serviceRequest->payment->amount, 2) }}</strong>
                            to <strong>{{ $whishNumber }}</strong>
                        </div>
                    </div>
                    <div class="step-row">
                        <div class="step-dot">2</div>
                        <div class="step-txt">
                            In the note field write: <strong>Request #{{ $serviceRequest->id }}</strong>
                        </div>
                    </div>
                    <div class="step-row mb-3">
                        <div class="step-dot">3</div>
                        <div class="step-txt">Fill in the details below and submit.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Your Whish Phone Number</label>
                        <input type="tel" name="whish_phone"
                               class="form-control @error('whish_phone') is-invalid @enderror"
                               placeholder="+961 XX XXX XXX"
                               value="{{ old('whish_phone') }}">
                        @error('whish_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Transaction Reference Code</label>
                        <input type="text" name="whish_reference"
                               class="form-control @error('whish_reference') is-invalid @enderror"
                               placeholder="e.g. WH-123456789"
                               value="{{ old('whish_reference') }}">
                        @error('whish_reference')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="note-warn mb-3">
                        <i class="bi bi-clock me-1"></i>
                        Office will verify within 1–2 business days then auto-approve your request.
                    </div>
                    <button type="submit" class="btn-submit">
                        <i class="bi bi-send me-2"></i>Submit Whish Payment
                    </button>
                </div>

                {{-- Crypto form --}}
                <div id="form-crypto" class="form-panel mb-3 d-none">
                    <h6 class="fw-bold mb-3" style="color:#6d28d9;">
                        <i class="bi bi-currency-bitcoin me-2"></i>Cryptocurrency Payment
                    </h6>
                    <div class="mb-3">
                        <label class="form-label">Select Coin</label>
                        <select name="crypto_coin" id="crypto_coin_select"
                                class="form-select @error('crypto_coin') is-invalid @enderror"
                                onchange="showWallet()">
                            <option value="">— Choose coin —</option>
                            @if($cryptoWallets['USDT_TRC20'])
                                <option value="USDT_TRC20" {{ old('crypto_coin') === 'USDT_TRC20' ? 'selected':'' }}>
                                    USDT (TRC20 Network)
                                </option>
                            @endif
                            @if($cryptoWallets['BTC'])
                                <option value="BTC" {{ old('crypto_coin') === 'BTC' ? 'selected':'' }}>
                                    Bitcoin (BTC)
                                </option>
                            @endif
                        </select>
                        @error('crypto_coin')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div id="wallet-panel" class="d-none mb-3">
                        <label class="form-label">
                            Send <strong>${{ number_format($serviceRequest->payment->amount, 2) }} USD</strong>
                            worth of <span id="coin-label" class="badge" style="background:#f5f3ff;color:#6d28d9;"></span>
                            to:
                        </label>
                        <div class="wallet-addr mb-1" id="wallet-display" onclick="copyAddr()" title="Click to copy"></div>
                        <small class="text-muted"><i class="bi bi-clipboard me-1"></i>Click to copy address</small>
                    </div>
                    <div class="step-row">
                        <div class="step-dot">1</div>
                        <div class="step-txt">Select coin and send the exact USD equivalent to the wallet address.</div>
                    </div>
                    <div class="step-row mb-3">
                        <div class="step-dot">2</div>
                        <div class="step-txt">Copy your <strong>transaction hash / TX ID</strong> and paste below.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Transaction Hash / TX ID</label>
                        <input type="text" name="crypto_tx_hash"
                               class="form-control @error('crypto_tx_hash') is-invalid @enderror"
                               placeholder="0x... or blockchain TX ID"
                               value="{{ old('crypto_tx_hash') }}">
                        @error('crypto_tx_hash')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="note-warn mb-3">
                        <i class="bi bi-clock me-1"></i>
                        Transaction verified on-chain by office. May take 1–24 hours.
                    </div>
                    <button type="submit" class="btn-submit">
                        <i class="bi bi-send me-2"></i>Submit Transaction Hash
                    </button>
                </div>

                {{-- Cash form --}}
                <div id="form-cash" class="form-panel mb-3 d-none">
                    <h6 class="fw-bold mb-3" style="color:var(--g);">
                        <i class="bi bi-cash-coin me-2"></i>Cash on Pickup
                    </h6>
                    <div class="note-ok mb-3">
                        <i class="bi bi-check-circle me-1"></i>
                        <strong>No upfront payment.</strong> Your request is approved immediately.
                        Bring <strong>${{ number_format($serviceRequest->payment->amount, 2) }}</strong> cash to the office.
                    </div>
                    <div class="step-row">
                        <div class="step-dot">1</div>
                        <div class="step-txt">Click confirm — status changes to <strong>Approved</strong> instantly.</div>
                    </div>
                    <div class="step-row mb-3">
                        <div class="step-dot">2</div>
                        <div class="step-txt">
                            Visit <strong>{{ $serviceRequest->service->office->name }}</strong> with
                            <strong>${{ number_format($serviceRequest->payment->amount, 2) }}</strong> cash.<br>
                            <span class="text-muted" style="font-size:0.78rem;">
                                {{ $serviceRequest->service->office->address }} ·
                                {{ $serviceRequest->service->office->contact_info }}
                            </span>
                        </div>
                    </div>
                    <button type="submit" class="btn-submit">
                        <i class="bi bi-check-circle me-2"></i>Confirm Cash on Pickup
                    </button>
                </div>

            </form>
        </div>

        {{-- Right: summary --}}
        <div class="col-lg-5">
            <div class="summary-card">
                <h6 class="fw-bold mb-3" style="color:var(--g);">
                    <i class="bi bi-receipt me-2"></i>Payment Summary
                </h6>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="small text-muted">Service</span>
                    <span class="small fw-semibold text-end" style="max-width:60%;">
                        {{ $serviceRequest->service->name }}
                    </span>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="small text-muted">Office</span>
                    <span class="small fw-semibold text-end" style="max-width:60%;">
                        {{ $serviceRequest->service->office->name }}
                    </span>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="small text-muted">Request ID</span>
                    <code class="small" style="color:var(--g);">#{{ $serviceRequest->id }}</code>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="small text-muted">Currency</span>
                    <span class="small fw-semibold">USD</span>
                </div>
                <div class="d-flex justify-content-between align-items-center pt-3">
                    <span class="fw-bold">Total Due</span>
                    <span class="fw-bold fs-4" style="color:var(--g);">
                        ${{ number_format($serviceRequest->payment->amount, 2) }}
                    </span>
                </div>
                <hr>
                <div class="small text-muted">
                    <i class="bi bi-shield-check me-1"></i>
                    After submission the office verifies and approves your request.
                </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const wallets = {
    'USDT_TRC20': '{{ config("payment.crypto.usdt_trc20") }}',
    'BTC':        '{{ config("payment.crypto.btc") }}',
};
const coinLabels = { 'USDT_TRC20': 'USDT TRC20', 'BTC': 'Bitcoin (BTC)' };

function pickMethod(m) {
    ['whish','crypto','cash'].forEach(x => {
        document.getElementById('card-' + x).classList.remove('selected');
        document.getElementById('form-' + x).classList.add('d-none');
    });
    document.getElementById('card-' + m).classList.add('selected');
    document.getElementById('form-' + m).classList.remove('d-none');
    document.getElementById('selected_method').value = m;
    document.getElementById('form-' + m).scrollIntoView({ behavior:'smooth', block:'nearest' });
}

function showWallet() {
    const coin = document.getElementById('crypto_coin_select').value;
    const panel = document.getElementById('wallet-panel');
    const display = document.getElementById('wallet-display');
    const label = document.getElementById('coin-label');
    if (coin && wallets[coin]) {
        panel.classList.remove('d-none');
        display.textContent = wallets[coin];
        label.textContent = coinLabels[coin] || coin;
    } else {
        panel.classList.add('d-none');
    }
}

function copyAddr() {
    const el = document.getElementById('wallet-display');
    navigator.clipboard.writeText(el.textContent).then(() => {
        const orig = el.textContent;
        el.textContent = '✓ Copied!';
        setTimeout(() => el.textContent = orig, 1500);
    });
}

const oldMethod = '{{ old("payment_method") }}';
if (oldMethod) pickMethod(oldMethod);

if ('{{ old("crypto_coin") }}') {
    document.getElementById('crypto_coin_select').value = '{{ old("crypto_coin") }}';
    showWallet();
}
</script>
@endpush