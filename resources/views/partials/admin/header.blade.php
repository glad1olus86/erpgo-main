@php
    $users=\Auth::user();
    $profile=\App\Models\Utility::get_file('uploads/avatar/');
    $languages=\App\Models\Utility::languages();

    $lang = isset($users->lang)?$users->lang:'en';
    if ($lang == null) {
        $lang = 'en';
    }
    $LangName = cache()->remember('full_language_data_' . $lang, now()->addHours(24), function () use ($lang) {
    return \App\Models\Language::languageData($lang);
    });

    $setting = \App\Models\Utility::settings();

    $unseenCounter=App\Models\ChMessage::where('to_id', Auth::user()->id)->where('seen', 0)->count();
@endphp
@if (isset($setting['cust_theme_bg']) && $setting['cust_theme_bg'] == 'on')
    <header class="dash-header transprent-bg">
@else
    <header class="dash-header">
@endif
    <div class="header-wrapper">
        <div class="me-auto dash-mob-drp">
            <ul class="list-unstyled">
                <li class="dash-h-item mob-hamburger">
                    <a href="#!" class="dash-head-link" id="mobile-collapse">
                        <div class="hamburger hamburger--arrowturn">
                            <div class="hamburger-box">
                                <div class="hamburger-inner"></div>
                            </div>
                        </div>
                    </a>
                </li>

                <li class="dropdown dash-h-item drp-company">
                    <a class="dash-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                        <span class="theme-avtar">
                             <img src="{{ !empty(\Auth::user()->avatar) ? $profile . \Auth::user()->avatar :  $profile.'avatar.png'}}" class="img-fluid rounded border-2 border border-primary">
                        </span>
                        <span class="hide-mob ms-2">{{__('Hi, ')}}{{\Auth::user()->name }}!</span>
                        <i class="ti ti-chevron-down drp-arrow nocolor hide-mob"></i>
                    </a>
                    <div class="dropdown-menu dash-h-dropdown">

                        <a href="{{route('profile')}}" class="dropdown-item">
                            <i class="ti ti-user text-dark"></i><span>{{__('Profile')}}</span>
                        </a>

                        <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('frm-logout').submit();" class="dropdown-item">
                            <i class="ti ti-power text-dark"></i><span>{{__('Logout')}}</span>
                        </a>

                        <form id="frm-logout" action="{{ route('logout') }}" method="POST" class="d-none">
                            {{ csrf_field() }}
                        </form>

                    </div>
                </li>

            </ul>
        </div>
        <div class="ms-auto">
            <ul class="list-unstyled">
                @if(\Auth::user()->type == 'company' )
                @impersonating($guard = null)
                <li class="dropdown dash-h-item drp-company">
                    <a class="btn btn-danger btn-sm" href="{{ route('exit.company') }}"><i class="ti ti-ban"></i>
                        {{ __('Exit Company Login') }}
                    </a>
                </li>
                @endImpersonating
                @endif

                @if( \Auth::user()->type !='client' && \Auth::user()->type !='super admin' )
                    <li class="dropdown dash-h-item drp-notification">
                        <a class="dash-head-link arrow-none me-0" href="{{ url('chats') }}" aria-haspopup="false"
                           aria-expanded="false">
                            <i class="ti ti-brand-hipchat"></i>
                            <span class="bg-danger dash-h-badge message-toggle-msg  message-counter custom_messanger_counter beep"> {{ $unseenCounter }}<span
                                    class="sr-only"></span>
                            </span>
                        </a>
                    </li>
                @endif

                {{-- System Notifications Bell --}}
                @if( \Auth::user()->type !='client')
                    <li class="dropdown dash-h-item drp-notification" id="notification-dropdown">
                        <a class="dash-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                            <i class="ti ti-bell"></i>
                            <span class="bg-warning dash-h-badge notification-badge" id="notification-badge" style="display: none;">0</span>
                        </a>
                        <div class="dropdown-menu dash-h-dropdown dropdown-menu-end notification-dropdown-menu" style="width: 350px; max-height: 400px; overflow-y: auto;">
                            <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                                <h6 class="mb-0">{{ __('Уведомления') }}</h6>
                                <a href="#" id="mark-all-read" class="text-primary small">{{ __('Прочитать все') }}</a>
                            </div>
                            <div id="notification-list">
                                <div class="text-center py-3 text-muted">
                                    <i class="ti ti-bell-off"></i> {{ __('Нет уведомлений') }}
                                </div>
                            </div>
                            <div class="border-top px-3 py-2 text-center">
                                <a href="{{ route('notifications.index') }}" class="text-primary small">{{ __('Все уведомления') }}</a>
                            </div>
                        </div>
                    </li>
                @endif

                <li class="dropdown dash-h-item drp-language">
                    <a
                        class="dash-head-link dropdown-toggle arrow-none me-0"
                        data-bs-toggle="dropdown"
                        href="#"
                        role="button"
                        aria-haspopup="false"
                        aria-expanded="false"
                    >
                        <i class="ti ti-world nocolor"></i>
                        <span class="drp-text hide-mob">{{ucfirst($LangName->full_name)}}</span>
                        <i class="ti ti-chevron-down drp-arrow nocolor"></i>
                    </a>
                    <div class="dropdown-menu dash-h-dropdown dropdown-menu-end">
                        @foreach ($languages as $code => $language)
                            <a href="{{ route('change.language', $code) }}"
                               class="dropdown-item {{ $lang == $code ? 'text-primary' : '' }}">
                                <span>{{ucFirst($language)}}</span>
                            </a>
                        @endforeach

                        <h></h>
                            @if(\Auth::user()->type=='super admin')
                                <a data-url="{{ route('create.language') }}" class="dropdown-item text-primary" data-ajax-popup="true" data-title="{{__('Create New Language')}}" style="cursor: pointer">
                                    {{ __('Create Language') }}
                                </a>
                                <a class="dropdown-item text-primary" href="{{route('manage.language',[isset($lang)?$lang:'english'])}}">{{ __('Manage Language') }}</a>
                            @endif
                    </div>
                </li>
            </ul>
        </div>
    </div>
    </header>
