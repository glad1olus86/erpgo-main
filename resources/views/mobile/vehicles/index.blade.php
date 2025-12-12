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
                    <circle cx="7" cy="17" r="2"></circle>
                    <circle cx="17" cy="17" r="2"></circle>
                    <path d="M5 17h-2v-6l2 -5h9l4 5h1a2 2 0 0 1 2 2v4h-2m-4 0h-6m-6 -6h15m-6 0v-5"></path>
                </svg>
                <span>{{ __('Vehicles') }}</span>
            </div>
            @can('vehicle_create')
                <a href="{{ route('mobile.vehicles.create') }}" class="mobile-add-btn">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#FF0049" stroke-width="2.5">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                </a>
            @endcan
        </div>

        {{-- Vehicles List --}}
        @forelse($vehicles as $vehicle)
            <div class="mobile-card mb-3" onclick="window.location='{{ route('mobile.vehicles.show', $vehicle->id) }}'">
                <div class="d-flex align-items-start">
                    <div class="mobile-vehicle-photo me-3">
                        @if($vehicle->photo)
                            <img src="{{ asset('uploads/vehicle_photos/' . $vehicle->photo) }}" alt="">
                        @else
                            <div class="mobile-vehicle-placeholder">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2">
                                    <circle cx="7" cy="17" r="2"></circle>
                                    <circle cx="17" cy="17" r="2"></circle>
                                    <path d="M5 17h-2v-6l2 -5h9l4 5h1a2 2 0 0 1 2 2v4h-2m-4 0h-6m-6 -6h15m-6 0v-5"></path>
                                </svg>
                            </div>
                        @endif
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-1">{{ $vehicle->license_plate }}</h6>
                        <div class="mobile-card-meta">
                            <div class="mb-1">{{ $vehicle->brand }} {{ $vehicle->color ? '• ' . $vehicle->color : '' }}</div>
                            @if($vehicle->assigned_name)
                                <div class="text-muted">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="12" cy="7" r="4"></circle>
                                    </svg>
                                    {{ $vehicle->assigned_name }}
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="text-end">
                        <span class="mobile-badge mobile-badge-{{ $vehicle->inspection_status }}">
                            @if($vehicle->inspection_status == 'overdue')
                                {{ __('Overdue') }}
                            @elseif($vehicle->inspection_status == 'soon')
                                {{ __('Soon') }}
                            @elseif($vehicle->inspection_status == 'ok')
                                {{ __('OK') }}
                            @else
                                {{ __('No data') }}
                            @endif
                        </span>
                    </div>
                </div>
                
                <div class="mobile-card-stats mt-2">
                    <div class="mobile-stat-item">
                        <span class="mobile-stat-label">{{ __('Brand') }}</span>
                        <span class="mobile-stat-value">{{ $vehicle->brand }}</span>
                    </div>
                    <div class="mobile-stat-item">
                        <span class="mobile-stat-label">{{ __('Inspection') }}</span>
                        <span class="mobile-stat-value mobile-stat-{{ $vehicle->inspection_status }}">
                            @if($vehicle->latestInspection)
                                {{ \Auth::user()->dateFormat($vehicle->latestInspection->next_inspection_date) }}
                            @else
                                -
                            @endif
                        </span>
                    </div>
                </div>
            </div>
        @empty
            <div class="mobile-empty-state">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#ccc" stroke-width="1.5">
                    <circle cx="7" cy="17" r="2"></circle>
                    <circle cx="17" cy="17" r="2"></circle>
                    <path d="M5 17h-2v-6l2 -5h9l4 5h1a2 2 0 0 1 2 2v4h-2m-4 0h-6m-6 -6h15m-6 0v-5"></path>
                </svg>
                <p class="mt-2 text-muted">{{ __('No vehicles found') }}</p>
                @can('vehicle_create')
                    <a href="{{ route('mobile.vehicles.create') }}" class="btn btn-sm mobile-btn-primary">
                        {{ __('Add Vehicle') }}
                    </a>
                @endcan
            </div>
        @endforelse
    </div>

    <style>
        .mobile-vehicle-photo {
            width: 60px;
            height: 45px;
            border-radius: 8px;
            overflow: hidden;
            flex-shrink: 0;
        }
        .mobile-vehicle-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .mobile-vehicle-placeholder {
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #FF0049, #FF6B6B);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .mobile-badge-overdue {
            background: rgba(220, 53, 69, 0.15);
            color: #dc3545;
        }
        .mobile-badge-soon {
            background: rgba(255, 193, 7, 0.15);
            color: #ffc107;
        }
        .mobile-badge-ok {
            background: rgba(34, 180, 4, 0.15);
            color: #22B404;
        }
        .mobile-badge-none {
            background: rgba(108, 117, 125, 0.15);
            color: #6c757d;
        }
        .mobile-stat-overdue {
            color: #dc3545;
        }
        .mobile-stat-soon {
            color: #ffc107;
        }
        .mobile-stat-ok {
            color: #22B404;
        }
    </style>
@endsection
