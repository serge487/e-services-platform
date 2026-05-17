@if($appointments->isEmpty())
    <div class="text-center py-5 text-muted">
        <i class="bi bi-calendar2-check fs-1 d-block mb-2"></i>
        @if(request()->filled('booking_status'))
            <p class="mb-0">No bookings with this status.</p>
        @else
            <p class="mb-0">You have no appointments yet.</p>
        @endif
    </div>
@else
    <div class="list-group list-group-flush">
        @foreach($appointments as $appointment)
            <div class="list-group-item py-3">
                <div class="d-flex justify-content-between gap-3">
                    <div>
                        <div class="fw-semibold small">{{ $appointment->officerTimeSlot?->office?->name ?? 'Office' }}</div>
                        <div class="text-muted small">
                            {{ \Illuminate\Support\Carbon::parse($appointment->officerTimeSlot?->slot_date)->format('d M Y') }}
                            · {{ \Illuminate\Support\Carbon::parse($appointment->officerTimeSlot?->start_time)->format('H:i') }} - {{ \Illuminate\Support\Carbon::parse($appointment->officerTimeSlot?->end_time)->format('H:i') }}
                        </div>
                        <div class="text-muted" style="font-size:0.76rem;">
                            Officer: {{ $appointment->officerTimeSlot?->officer?->name ?? 'N/A' }}
                        </div>
                    </div>
                    <div class="text-end">
                        @php
                            $badge = match($appointment->status) {
                                'scheduled' => 'warning text-dark',
                                'confirmed' => 'info text-dark',
                                'completed' => 'success',
                                'cancelled' => 'danger',
                                'no_show' => 'secondary',
                                default => 'secondary',
                            };
                        @endphp
                        <span class="badge bg-{{ $badge }}">{{ str_replace('_', ' ', ucfirst($appointment->status)) }}</span>

                        @if(in_array($appointment->status, ['scheduled', 'confirmed'], true))
                            <form method="POST" action="{{ route('citizen.appointments.cancel', $appointment, absolute: false) }}" class="mt-2">
                                @csrf
                                @method('PATCH')
                                @foreach (['slot_office_id', 'slot_date', 'slot_q', 'booking_status'] as $filterKey)
                                    @if (request()->filled($filterKey))
                                        <input type="hidden" name="{{ $filterKey }}" value="{{ request($filterKey) }}">
                                    @endif
                                @endforeach
                                <button type="submit" class="btn btn-outline-danger btn-sm">Cancel</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    <div class="p-3 border-top">
        {{ $appointments->withQueryString()->links() }}
    </div>
@endif

