@extends('layouts.mobile')

@section('content')
    {{-- Header --}}
    <div class="mobile-header">
        <div class="mobile-header-row">
            {{-- Left side: Menu + Notifications --}}
            <div class="mobile-header-left">
                <button type="button" class="mobile-header-btn" onclick="openSidebar()">
                    <img src="{{ asset('fromfigma/menu_mobile.svg') }}" alt=""
                        onerror="this.outerHTML='<svg width=24 height=24 viewBox=\'0 0 24 24\' fill=none stroke=#FF0049 stroke-width=2><line x1=3 y1=6 x2=21 y2=6></line><line x1=3 y1=12 x2=21 y2=12></line><line x1=3 y1=18 x2=21 y2=18></line></svg>'">
                </button>
                <a href="{{ route('notifications.index') }}" class="mobile-header-btn">
                    <img src="{{ asset('fromfigma/bell_mobile.svg') }}" alt=""
                        onerror="this.outerHTML='<svg width=24 height=24 viewBox=\'0 0 24 24\' fill=none stroke=#FF0049 stroke-width=2><path d=\'M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9\'></path><path d=\'M13.73 21a2 2 0 0 1-3.46 0\'></path></svg>'">
                </a>
            </div>

            {{-- Right side: Language + Logo --}}
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
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0"><i class="ti ti-users me-2"></i>{{ __('Workers') }}</h5>
            @can('create worker')
                <a href="#" data-url="{{ route('worker.create') }}" data-ajax-popup="true" 
                   data-title="{{ __('Add New Worker') }}" data-size="lg" class="btn btn-sm mobile-btn-primary">
                    <i class="ti ti-plus"></i>
                </a>
            @endcan
        </div>

        {{-- Search --}}
        <div class="mb-3">
            <form action="{{ route('mobile.workers.index') }}" method="GET">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" 
                           placeholder="{{ __('Search workers') }}..." 
                           value="{{ request('search') }}">
                    <button type="submit" class="btn mobile-btn-primary">
                        <i class="ti ti-search"></i>
                    </button>
                </div>
            </form>
        </div>

        {{-- Workers List --}}
        @forelse($workers as $worker)
            <div class="mobile-card mb-3" onclick="window.location='{{ route('mobile.workers.show', $worker->id) }}'">
                <div class="d-flex align-items-center">
                    <div class="mobile-avatar me-3">
                        @if(!empty($worker->photo))
                            <img src="{{ asset('uploads/worker_photos/' . $worker->photo) }}" alt="">
                        @else
                            <div class="mobile-avatar-placeholder">
                                {{ strtoupper(substr($worker->first_name, 0, 1)) }}{{ strtoupper(substr($worker->last_name, 0, 1)) }}
                            </div>
                        @endif
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-1">{{ $worker->first_name }} {{ $worker->last_name }}</h6>
                        <div class="mobile-card-meta">
                            <span><i class="ti ti-flag me-1"></i>{{ $worker->nationality }}</span>
                            @if($worker->currentWorkAssignment)
                                <span class="mobile-badge mobile-badge-working ms-2">{{ __('Working') }}</span>
                            @endif
                            @if($worker->currentAssignment)
                                <span class="mobile-badge mobile-badge-housed ms-1">{{ __('Housed') }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="mobile-card-arrow">
                        <i class="ti ti-chevron-right"></i>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-5">
                <i class="ti ti-users-off" style="font-size: 48px; opacity: 0.3;"></i>
                <p class="mt-3 text-muted">{{ __('No workers found') }}</p>
            </div>
        @endforelse

        {{-- Pagination --}}
        @if($workers instanceof \Illuminate\Pagination\LengthAwarePaginator && $workers->hasPages())
            <div class="d-flex justify-content-center mt-3">
                {{ $workers->links('pagination::bootstrap-4') }}
            </div>
        @endif
    </div>

    <style>
        .mobile-btn-primary {
            background: #FF0049 !important;
            border-color: #FF0049 !important;
            color: #fff !important;
        }
        .mobile-btn-primary:hover, .mobile-btn-primary:focus {
            background: #e00040 !important;
            border-color: #e00040 !important;
        }
        .mobile-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .mobile-badge-working {
            background: #22B404;
            color: #fff;
        }
        .mobile-badge-housed {
            background: #FF0049;
            color: #fff;
        }
        .mobile-card {
            background: #fff;
            border-radius: 10px;
            padding: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            cursor: pointer;
            transition: transform 0.2s;
        }
        .mobile-card:active {
            transform: scale(0.98);
        }
        .mobile-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            overflow: hidden;
        }
        .mobile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .mobile-avatar-placeholder {
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #FF0049, #FF6B6B);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 16px;
        }
        .mobile-card-meta {
            font-size: 12px;
            color: #666;
        }
        .mobile-card-arrow {
            color: #ccc;
        }
    </style>
@endsection
