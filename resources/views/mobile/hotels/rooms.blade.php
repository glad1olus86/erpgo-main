@extends('layouts.mobile')

@section('content')
    {{-- Header --}}
    <div class="mobile-header">
        <div class="mobile-header-row">
            <div class="mobile-header-left">
                <a href="{{ route('mobile.hotels.index') }}" class="mobile-header-btn">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#FF0049" stroke-width="2">
                        <path d="M19 12H5M12 19l-7-7 7-7"/>
                    </svg>
                </a>
            </div>
            <div class="mobile-header-right">
                <div class="mobile-logo">
                    <img src="{{ asset('fromfigma/jobsi_mobile.png') }}" alt="JOBSI">
                </div>
            </div>
        </div>
    </div>

    <div class="mobile-content">
        {{-- Hotel Info --}}
        <div class="mobile-card mb-3">
            <h5 class="mb-1">{{ $hotel->name }}</h5>
            <small class="text-muted">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                    <circle cx="12" cy="10" r="3"></circle>
                </svg>
                {{ $hotel->address }}
            </small>
            
            @php
                $totalCapacity = $hotel->rooms->sum('capacity');
                $totalOccupied = $hotel->rooms->sum(function($room) {
                    return $room->currentAssignments->count();
                });
                $freeSpots = $totalCapacity - $totalOccupied;
            @endphp
            
            <div class="mobile-card-stats mt-3">
                <div class="mobile-stat-item">
                    <span class="mobile-stat-label">{{ __('Rooms') }}</span>
                    <span class="mobile-stat-value">{{ $hotel->rooms->count() }}</span>
                </div>
                <div class="mobile-stat-item">
                    <span class="mobile-stat-label">{{ __('Capacity') }}</span>
                    <span class="mobile-stat-value">{{ $totalCapacity }}</span>
                </div>
                <div class="mobile-stat-item">
                    <span class="mobile-stat-label">{{ __('Free') }}</span>
                    <span class="mobile-stat-value {{ $freeSpots < 5 ? 'text-danger' : '' }}">{{ $freeSpots }}</span>
                </div>
            </div>
        </div>

        {{-- Page Title --}}
        <div class="mobile-section-title">
            <div class="mobile-section-title-left">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#FF0049" stroke-width="2">
                    <path d="M3 7v11m0 -4h18m0 4v-8a2 2 0 0 0 -2 -2h-8v6"></path>
                    <circle cx="7" cy="10" r="1"></circle>
                </svg>
                <span>{{ __('Rooms') }}</span>
            </div>
            @can('create room')
                <a href="#" data-url="{{ route('room.create', ['hotel_id' => $hotel->id, 'redirect_to' => 'mobile']) }}" 
                   data-ajax-popup="true" data-title="{{ __('Add New Room') }}" data-size="md" 
                   class="mobile-add-btn">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#FF0049" stroke-width="2.5">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                </a>
            @endcan
        </div>

        {{-- Rooms List --}}
        @forelse($hotel->rooms as $room)
            @php
                $occupied = $room->currentAssignments->count();
                $free = $room->capacity - $occupied;
                $isFull = $free <= 0;
            @endphp
            <div class="mobile-card mb-3" onclick="window.location='{{ route('mobile.rooms.show', $room->id) }}'">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <h6 class="mb-1">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1">
                                <path d="M3 7v11m0 -4h18m0 4v-8a2 2 0 0 0 -2 -2h-8v6"></path>
                                <circle cx="7" cy="10" r="1"></circle>
                            </svg>
                            {{ __('Room') }} {{ $room->room_number }}
                        </h6>
                        @if($room->floor)
                            <small class="text-muted">{{ __('Floor') }}: {{ $room->floor }}</small>
                        @endif
                    </div>
                    <div>
                        @if($isFull)
                            <span class="mobile-badge mobile-badge-danger">{{ __('Full') }}</span>
                        @elseif($free == 1)
                            <span class="mobile-badge mobile-badge-warning">{{ $free }} {{ __('free') }}</span>
                        @else
                            <span class="mobile-badge mobile-badge-success">{{ $free }} {{ __('free') }}</span>
                        @endif
                    </div>
                </div>

                <div class="mobile-card-meta mb-2">
                    <span>{{ __('Capacity') }}: {{ $room->capacity }}</span>
                    <span class="ms-3">{{ __('Occupied') }}: {{ $occupied }}</span>
                </div>

                {{-- Current Occupants --}}
                @if($room->currentAssignments->count() > 0)
                    <div class="mobile-occupants">
                        @foreach($room->currentAssignments->take(3) as $assignment)
                            <div class="mobile-occupant-item">
                                <div class="mobile-avatar-small">
                                    @if(!empty($assignment->worker->photo))
                                        <img src="{{ asset('uploads/worker_photos/' . $assignment->worker->photo) }}" alt="">
                                    @else
                                        {{ strtoupper(substr($assignment->worker->first_name, 0, 1)) }}
                                    @endif
                                </div>
                                <span class="mobile-occupant-name">{{ $assignment->worker->first_name }}</span>
                            </div>
                        @endforeach
                        @if($room->currentAssignments->count() > 3)
                            <span class="mobile-occupant-more">+{{ $room->currentAssignments->count() - 3 }}</span>
                        @endif
                    </div>
                @else
                    <small class="text-muted">{{ __('No occupants') }}</small>
                @endif
            </div>
        @empty
            <div class="mobile-empty-state">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#ccc" stroke-width="1.5">
                    <path d="M3 7v11m0 -4h18m0 4v-8a2 2 0 0 0 -2 -2h-8v6"></path>
                    <circle cx="7" cy="10" r="1"></circle>
                </svg>
                <p class="mt-2 text-muted">{{ __('No rooms found') }}</p>
                @can('create room')
                    <a href="#" data-url="{{ route('room.create', ['hotel_id' => $hotel->id, 'redirect_to' => 'mobile']) }}" 
                       data-ajax-popup="true" data-title="{{ __('Add New Room') }}" 
                       class="btn btn-sm mobile-btn-primary">
                        {{ __('Add Room') }}
                    </a>
                @endcan
            </div>
        @endforelse
    </div>
@endsection
