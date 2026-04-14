@if($todayAppointments->isEmpty())
    <div class="text-center py-5 text-muted">
        <i class="bi bi-calendar2 fs-1 d-block mb-2"></i>
        <p class="mb-0">No appointments scheduled for today.</p>
    </div>
@else
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">Citizen</th>
                    <th>Office</th>
                    <th>Officer</th>
                    <th>Time</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($todayAppointments as $appointment)
                    <tr>
                        <td class="ps-4 small">
                            <div class="fw-semibold">{{ $appointment->citizen->name }}</div>
                            <div class="text-muted">{{ $appointment->citizen->email }}</div>
                        </td>
                        <td class="small">{{ $appointment->officerTimeSlot?->office?->name ?? '-' }}</td>
                        <td class="small">{{ $appointment->officerTimeSlot?->officer?->name ?? '-' }}</td>
                        <td class="small">
                            {{ \Illuminate\Support\Carbon::parse($appointment->officerTimeSlot?->start_time)->format('H:i') }}
                            - {{ \Illuminate\Support\Carbon::parse($appointment->officerTimeSlot?->end_time)->format('H:i') }}
                        </td>
                        <td>
                            @php
                                $badgeColor = match($appointment->status) {
                                    'scheduled' => 'warning text-dark',
                                    'confirmed' => 'info text-dark',
                                    'completed' => 'success',
                                    'cancelled' => 'danger',
                                    'no_show'   => 'secondary',
                                    default     => 'secondary',
                                };
                            @endphp
                            <span class="badge bg-{{ $badgeColor }}">{{ str_replace('_', ' ', ucfirst($appointment->status)) }}</span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
