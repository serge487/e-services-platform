@if($appointments->isEmpty())
    <div class="text-center py-5 text-muted">
        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
        <p class="mb-0">
            No appointments found{{ $statusFilter ? ' for this status' : '' }}{{ !empty($search) ? ' with this search' : '' }}.
        </p>
    </div>
@else
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">Citizen</th>
                    <th>Office</th>
                    <th>Officer</th>
                    <th>Date & Time</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($appointments as $appointment)
                    <tr>
                        <td class="ps-4 small">
                            <div class="fw-semibold">{{ $appointment->citizen->name }}</div>
                            <div class="text-muted">{{ $appointment->citizen->phone_number ?: 'No phone number' }}</div>
                            <div class="text-muted small">{{ $appointment->citizen->email }}</div>
                        </td>
                        <td class="small">{{ $appointment->officerTimeSlot?->office?->name ?? '-' }}</td>
                        <td class="small">{{ $appointment->officerTimeSlot?->officer?->name ?? '-' }}</td>
                        <td class="small text-muted">
                            {{ \Illuminate\Support\Carbon::parse($appointment->officerTimeSlot?->slot_date)->format('d M Y') }}
                            · {{ \Illuminate\Support\Carbon::parse($appointment->officerTimeSlot?->start_time)->format('H:i') }} - {{ \Illuminate\Support\Carbon::parse($appointment->officerTimeSlot?->end_time)->format('H:i') }}
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
                            <span
                                class="badge bg-{{ $badgeColor }}"
                                data-status-badge
                                data-appointment-id="{{ $appointment->id }}"
                            >
                                {{ str_replace('_', ' ', ucfirst($appointment->status)) }}
                            </span>
                        </td>
                        <td class="text-end pe-4">
                            <div class="d-flex justify-content-end gap-2">
                                <form method="POST" action="{{ route('municipality.appointments.update-status', $appointment, absolute: false) }}" class="d-flex gap-2 align-items-center" data-status-form>
                                    @csrf
                                    @method('PATCH')
                                    <select
                                        name="status"
                                        class="form-select form-select-sm border-primary-subtle"
                                        style="min-width: 135px;"
                                        data-status-select
                                        data-appointment-id="{{ $appointment->id }}"
                                    >
                                        @foreach(['scheduled', 'confirmed', 'completed', 'cancelled', 'no_show'] as $status)
                                            @php
                                                $statusLabel = match($status) {
                                                    'no_show' => 'No Show',
                                                    default => ucfirst($status),
                                                };
                                            @endphp
                                            <option value="{{ $status }}" @selected($appointment->status === $status)>
                                                {{ $statusLabel }}
                                            </option>
                                        @endforeach
                                    </select>
                                </form>
                                <form
                                    method="POST"
                                    action="{{ route('municipality.appointments.remind', $appointment, absolute: false) }}"
                                    data-remind-form
                                    data-appointment-id="{{ $appointment->id }}"
                                >
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-secondary rounded-pill px-3" data-remind-button>
                                        <i class="bi bi-bell me-1"></i>Remind
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('municipality.appointments.destroy', $appointment, absolute: false) }}" onsubmit="return confirm('Delete this appointment?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                                        <i class="bi bi-trash me-1"></i>Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="p-3 border-top">
        {{ $appointments->withQueryString()->links() }}
    </div>
@endif

