@extends('layouts.mobile')

@section('content')
    {{-- Header --}}
    <div class="mobile-header">
        <div class="mobile-header-row">
            <div class="mobile-header-left">
                <button type="button" class="mobile-header-btn" onclick="openSidebar()">
                    <img src="{{ asset('fromfigma/menu_mobile.svg') }}" alt=""
                        onerror="this.outerHTML='<svg width=24 height=24 viewBox=\'0 0 24 24\' fill=none stroke=#FF0049 stroke-width=2><line x1=3 y1=6 x2=21 y2=6></line><line x1=3 y1=12 x2=21 y2=12></line><line x1=3 y1=18 x2=21 y2=18></line></svg>'">
                </button>
                <a href="{{ route('mobile.notifications.index') }}" class="mobile-header-btn">
                    <img src="{{ asset('fromfigma/bell_mobile.svg') }}" alt=""
                        onerror="this.outerHTML='<svg width=24 height=24 viewBox=\'0 0 24 24\' fill=none stroke=#FF0049 stroke-width=2><path d=\'M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9\'></path><path d=\'M13.73 21a2 2 0 0 1-3.46 0\'></path></svg>'">
                </a>
            </div>
            <div class="mobile-header-right">
                <div class="dropdown">
                    <button class="mobile-lang-btn" data-bs-toggle="dropdown">
                        @php $lang = app()->getLocale(); @endphp
                        @if ($lang == 'cs')
                            <img src="{{ asset('fromfigma/czech_flag.svg') }}" alt="CS" class="mobile-flag">
                        @elseif ($lang == 'uk')
                            <img src="{{ asset('fromfigma/ukraine_flag.png') }}" alt="UK" class="mobile-flag">
                        @elseif ($lang == 'ru')
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#666" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="2" y1="12" x2="22" y2="12"></line>
                                <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                            </svg>
                        @else
                            <img src="{{ asset('fromfigma/uk_flag.png') }}" alt="EN" class="mobile-flag">
                        @endif
                        <span>{{ strtoupper($lang) }}</span>
                        <svg width="10" height="10" viewBox="0 0 24 24" fill="#000">
                            <path d="M7 10l5 5 5-5z" />
                        </svg>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        @foreach (['ru' => 'Русский', 'en' => 'English', 'cs' => 'Čeština', 'uk' => 'Українська'] as $code => $language)
                            <a href="{{ route('change.language', $code) }}" class="dropdown-item {{ $lang == $code ? 'text-primary' : '' }}">{{ $language }}</a>
                        @endforeach
                    </div>
                </div>
                <div class="mobile-logo">
                    <img src="{{ asset('fromfigma/jobsi_mobile.png') }}" alt="JOBSI">
                </div>
            </div>
        </div>
    </div>

    <div class="mobile-content">
        {{-- Page Title --}}
        <div class="mobile-section-title">
            <div class="mobile-section-title-left">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#FF0049" stroke-width="2">
                    <path d="M3 21h18"></path>
                    <path d="M5 21v-14l8 -4v18"></path>
                    <path d="M19 21v-10l-6 -4"></path>
                    <path d="M9 9v.01"></path>
                    <path d="M9 12v.01"></path>
                    <path d="M9 15v.01"></path>
                    <path d="M9 18v.01"></path>
                </svg>
                <span>{{ __('Hotels') }}</span>
            </div>
            @can('create hotel')
                <a href="#" data-url="{{ route('hotel.create', ['redirect_to' => 'mobile']) }}" data-ajax-popup="true" 
                   data-title="{{ __('Create New Hotel') }}" data-size="md" class="mobile-add-btn">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#FF0049" stroke-width="2.5">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                </a>
            @endcan
        </div>

        {{-- Hotels List --}}
        @forelse($hotels as $hotel)
            @php
                $totalCapacity = $hotel->rooms->sum('capacity');
                $totalOccupied = $hotel->rooms->sum(function($room) {
                    return $room->currentAssignments->count();
                });
                $freeSpots = $totalCapacity - $totalOccupied;
                $percentage = $totalCapacity > 0 ? ($totalOccupied / $totalCapacity) * 100 : 0;
            @endphp
            <div class="mobile-card mb-3" onclick="window.location='{{ route('mobile.hotels.rooms', $hotel->id) }}'">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <h6 class="mb-1">{{ $hotel->name }}</h6>
                        <small class="text-muted">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                <circle cx="12" cy="10" r="3"></circle>
                            </svg>
                            {{ $hotel->address }}
                        </small>
                    </div>
                    <div class="text-end">
                        @if($freeSpots < 5)
                            <span class="mobile-badge mobile-badge-danger">{{ $freeSpots }} {{ __('free') }}</span>
                        @elseif($freeSpots < 10)
                            <span class="mobile-badge mobile-badge-warning">{{ $freeSpots }} {{ __('free') }}</span>
                        @else
                            <span class="mobile-badge mobile-badge-success">{{ $freeSpots }} {{ __('free') }}</span>
                        @endif
                    </div>
                </div>
                
                <div class="mobile-card-stats">
                    <div class="mobile-stat-item">
                        <span class="mobile-stat-label">{{ __('Rooms') }}</span>
                        <span class="mobile-stat-value">{{ $hotel->rooms->count() }}</span>
                    </div>
                    <div class="mobile-stat-item">
                        <span class="mobile-stat-label">{{ __('Capacity') }}</span>
                        <span class="mobile-stat-value">{{ $totalCapacity }}</span>
                    </div>
                    <div class="mobile-stat-item">
                        <span class="mobile-stat-label">{{ __('Occupied') }}</span>
                        <span class="mobile-stat-value">{{ $totalOccupied }}</span>
                    </div>
                </div>

                <div class="progress mt-2" style="height: 6px;">
                    <div class="progress-bar {{ $percentage >= 90 ? 'bg-success' : ($percentage >= 50 ? 'bg-warning' : 'bg-danger') }}" 
                         style="width: {{ $percentage }}%"></div>
                </div>
            </div>
        @empty
            <div class="mobile-empty-state">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#ccc" stroke-width="1.5">
                    <path d="M3 21h18"></path>
                    <path d="M5 21v-14l8 -4v18"></path>
                    <path d="M19 21v-10l-6 -4"></path>
                </svg>
                <p class="mt-2 text-muted">{{ __('No hotels found') }}</p>
                @can('create hotel')
                    <a href="#" data-url="{{ route('hotel.create', ['redirect_to' => 'mobile']) }}" data-ajax-popup="true" 
                       data-title="{{ __('Create New Hotel') }}" class="btn btn-sm mobile-btn-primary">
                        {{ __('Add Hotel') }}
                    </a>
                @endcan
            </div>
        @endforelse
    </div>
@endsection
