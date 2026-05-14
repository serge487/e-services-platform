@if($availableSlots->isEmpty())
    <div class="text-center py-5 text-muted">
        <i class="bi bi-calendar-x fs-1 d-block mb-2"></i>
        @if(request()->filled('slot_office_id') || request()->filled('slot_date') || request()->filled('slot_q'))
            <p class="mb-0">No slots match your filters. Try clearing or widening your search.</p>
        @else
            <p class="mb-0">No available slots right now.</p>
        @endif
    </div>
@else
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">Office</th>
                    <th>Officer</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th class="text-end pe-4">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($availableSlots as $slot)
                    <tr>
                        <td class="ps-4 small fw-semibold">{{ $slot->office->name }}</td>
                        <td class="small">{{ $slot->officer->name }}</td>
                        <td class="small text-muted">{{ \Illuminate\Support\Carbon::parse($slot->slot_date)->format('d M Y') }}</td>
                        <td class="small">{{ \Illuminate\Support\Carbon::parse($slot->start_time)->format('H:i') }} - {{ \Illuminate\Support\Carbon::parse($slot->end_time)->format('H:i') }}</td>
                        <td class="text-end pe-4">
                            <form method="POST" action="{{ route('citizen.appointments.store', absolute: false) }}">
                                @csrf
                                <input type="hidden" name="officer_time_slot_id" value="{{ $slot->id }}">
                                @foreach (['slot_office_id', 'slot_date', 'slot_q', 'booking_status'] as $filterKey)
                                    @if (request()->filled($filterKey))
                                        <input type="hidden" name="{{ $filterKey }}" value="{{ request($filterKey) }}">
                                    @endif
                                @endforeach
                                <button type="submit" class="btn btn-sm btn-primary">
                                    <i class="bi bi-calendar-plus me-1"></i> Book
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="p-3 border-top">
        {{ $availableSlots->withQueryString()->links() }}
    </div>
@endif

