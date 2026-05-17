@if($timeSlots->isEmpty())
    <div class="text-center py-5 text-muted">
        <i class="bi bi-calendar-x fs-1 d-block mb-2"></i>
        <p class="mb-0">No slots created yet.</p>
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
                    <th>Status</th>
                    <th class="text-end pe-4">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($timeSlots as $slot)
                    <tr>
                        <td class="ps-4 small">{{ $slot->office->name }}</td>
                        <td class="small">{{ $slot->officer->name }}</td>
                        <td class="small text-muted">{{ \Illuminate\Support\Carbon::parse($slot->slot_date)->format('d M Y') }}</td>
                        <td class="small">{{ \Illuminate\Support\Carbon::parse($slot->start_time)->format('H:i') }} - {{ \Illuminate\Support\Carbon::parse($slot->end_time)->format('H:i') }}</td>
                        <td>
                            <span class="badge bg-{{ $slot->is_booked ? 'warning text-dark' : 'success' }}">
                                {{ $slot->is_booked ? 'Booked' : 'Available' }}
                            </span>
                        </td>
                        <td class="text-end pe-4">
                            @if(!$slot->is_booked)
                                <form method="POST" action="{{ route('municipality.appointments.slots.destroy', $slot, absolute: false) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm">Delete</button>
                                </form>
                            @else
                                <span class="text-muted small">Locked</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="p-3 border-top">
        {{ $timeSlots->withQueryString()->links() }}
    </div>
@endif

