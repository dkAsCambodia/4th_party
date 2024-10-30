<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    {{-- <meta name="keywords" content="admin, dashboard">
    <meta name="author" content="Soeng Souy">
    <meta name="robots" content="index, follow">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Payment Gateway">
    <meta property="og:title" content="Payment Gateway">
    <meta property="og:description" content="Payment Gateway">
    <meta property="og:image" content="{{ URL::to('assets/images/logo.png') }}">
    <meta name="format-detection" content="telephone=no"> --}}
    <title>{{ trans('messages.PAYMENT GATEWAY') }}</title>
    <link rel="shortcut icon" type="image/png" href="{{ URL::to('assets/images/favicon.png') }}">
    <link href="{{ URL::to('assets/css/style.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ URL::to('assets/css/toastr.min.css') }}">
    <script src="{{ URL::to('assets/js/toastr_jquery.min.js') }}"></script>
    <script src="{{ URL::to('assets/js/toastr.min.js') }}"></script>
    <link href="{{ URL::to('assets/css/custom_style.css') }}" rel="stylesheet">
    <link href="{{ URL::to('assets/vendor/datatables/css/responsive.min.css') }}" rel="stylesheet">
    <link href="{{ URL::to('assets/vendor/datatables/css/datatable.min.css') }}" rel="stylesheet">
</head>

<body>
    {{-- <div id="preloader">
        <div class="waviy">
            <span style="--i:1">L</span>
            <span style="--i:2">o</span>
            <span style="--i:3">a</span>
            <span style="--i:4">d</span>
            <span style="--i:5">i</span>
            <span style="--i:6">n</span>
            <span style="--i:7">g</span>
            <span style="--i:8">.</span>
            <span style="--i:9">.</span>
            <span style="--i:10">.</span>
        </div>
    </div> --}}

    <div id="main-wrapper" class="show">
        <div class="nav-header">
            <a href="{{ route('home') }}" class="brand-logo">
				<span class="small">GZ</span>
                {{-- <span class="large">{{ __('messages.PAYMENT GATEWAY') }}</span> --}}
                <span class="large">GTECHZ</span>
            </a>
            <div class="nav-control">
                <div class="hamburger">
                    <span class="line"></span><span class="line"></span><span class="line"></span>
                </div>
            </div>
        </div>

        <div class="header">
            <div class="header-content" style="padding-right: 16px;">
                <nav class="navbar navbar-expand">
                    <div class="collapse navbar-collapse justify-content-between">
                        <div class="header-left">
                            <div class="dashboard_bar">
                                {{ __('messages.Dashboard') }}
                            </div>
                        </div>
                        <ul class="navbar-nav header-right ">
                            {{-- <li class="nav-item dropdown notification_dropdown" style="padding-top:5px;">
                                <div class="langbtn">
                                    <select class="form-control updateTimezone" style="padding-inline: 10px;">
                                        @foreach ($allTimezones as $timez)
                                            <option @selected(auth()->user()->timezone_id == $timez->id)
                                                value="{{ $timez->id }}">{{ $timez->timezone }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </li> --}}

                            <li class="nav-item dropdown notification_dropdown" style="padding-top:5px;">
                                <div class="langbtn">
                                    <i class="fa fa-globe" aria-hidden="true"></i>
                                    <select class="form-control changeLang" style="padding-inline: 19px;">
                                        <option value="en"
                                            {{ session()->get('locale') == 'en' ? 'selected' : '' }}>English</option>
                                        <option value="th"
                                            {{ session()->get('locale') == 'th' ? 'selected' : '' }}>Thai</option>
                                        <option value="ch"
                                            {{ session()->get('locale') == 'ch' ? 'selected' : '' }}>中文</option>
                                    </select>
                                </div>

                                <div class="dropdown-menu dropdown-menu-end">
                                    <div id="dlab_W_TimeLine02"
                                        class="widget-timeline dlab-scroll style-1 ps ps--active-y p-3 height370">
                                    </div>
                                </div>
                            </li>
                             {{-- // for Pusher code START --}}
                                <!-- Hidden audio element -->
                                <audio id="notificationAudio" preload="auto">
                                    <source src="{{ asset('/audio/notifcation.mp3') }}" type="audio/mpeg">
                                    Your browser does not support the audio element.
                                </audio>
                                <button onclick="document.getElementById('notificationAudio').play()"></button>

                                <!-- Include jQuery -->
                                <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
                                <!-- Include Toastr JS -->
                                <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
                                
                                <!-- Your app.js or bootstrap.js script -->
                                <script src="{{ asset('/build/assets/app.56402d86.js') }}"></script>
                                {{-- // for Pusher code END --}}

                            <li class="nav-item dropdown notification_dropdown" id="notiDiv">
                                <a class="nav-link ai-icon" href="javascript:void(0);"
                                    {{ getNotificationTransactions()['NotificationCount']  > 0? 'data-toggle=dropdown aria-haspopup=true aria-expanded=false': '' }}
                                    id="notiBell">
                                    <svg width="28" height="28" viewBox="0 0 28 28" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" clip-rule="evenodd"
                                            d="M12.638 4.9936V2.3C12.638 1.5824 13.2484 1 14.0006 1C14.7513 1 15.3631 1.5824 15.3631 2.3V4.9936C17.3879 5.2718 19.2805 6.1688 20.7438 7.565C22.5329 9.2719 23.5384 11.5872 23.5384 14V18.8932L24.6408 20.9966C25.1681 22.0041 25.1122 23.2001 24.4909 24.1582C23.8709 25.1163 22.774 25.7 21.5941 25.7H15.3631C15.3631 26.4176 14.7513 27 14.0006 27C13.2484 27 12.638 26.4176 12.638 25.7H6.40705C5.22571 25.7 4.12888 25.1163 3.50892 24.1582C2.88759 23.2001 2.83172 22.0041 3.36039 20.9966L4.46268 18.8932V14C4.46268 11.5872 5.46691 9.2719 7.25594 7.565C8.72068 6.1688 10.6119 5.2718 12.638 4.9936ZM14.0006 7.5C12.1924 7.5 10.4607 8.1851 9.18259 9.4045C7.90452 10.6226 7.18779 12.2762 7.18779 14V19.2C7.18779 19.4015 7.13739 19.6004 7.04337 19.7811C7.04337 19.7811 6.43703 20.9381 5.79662 22.1588C5.69171 22.3603 5.70261 22.6008 5.82661 22.7919C5.9506 22.983 6.16996 23.1 6.40705 23.1H21.5941C21.8298 23.1 22.0492 22.983 22.1732 22.7919C22.2972 22.6008 22.3081 22.3603 22.2031 22.1588C21.5627 20.9381 20.9564 19.7811 20.9564 19.7811C20.8624 19.6004 20.8133 19.4015 20.8133 19.2V14C20.8133 12.2762 20.0953 10.6226 18.8172 9.4045C17.5391 8.1851 15.8073 7.5 14.0006 7.5Z"
                                            fill="#4f7086"></path>
                                    </svg>
                                    <span
                                        class="badge light text-white bg-primary rounded-circle {{ getNotificationTransactions()['NotificationCount'] > 0? '': 'd-none' }}"
                                        id="notificationCount">
                                        {{ getNotificationTransactions()['NotificationCount'] ?? '' }}
                                    </span>
                                </a>

                                <div class="dropdown-menu dropdown-menu-end" id="notiBody">
                                    <div id="dlab_W_Notification1" class="widget-media dlab-scroll p-3 ps"
                                        style="height: 200px; overflow-y: auto !important;">
                                        <ul class="timeline" id="notiContent">
                                            @foreach (getNotificationTransactions()['NotificationData'] as $unread)
                                                @php
                                                    $notificationData = json_decode($unread->data, true); 
                                                @endphp
                                            <li>
                                                <div class="timeline-panel">
                                                    <div class="media-body">
                                                        <h5 class="mb-1">{{ $unread->msg ?? '' }}</h5>
                                                        <p class="mb-2 text text-success">{{ trans('messages.Trans ID') }}: {{ $notificationData['transaction_id'] ?? '' }}</p>
                                                        <p class="mb-2 text text-danger">{{ trans('messages.Status') }}: {{ $notificationData['status'] ?? '' }}</p>
                                                        <small class="d-block">{{ $unread->created_at->diffForHumans() }}</small>
                                                    </div>
                                                </div>
                                            </li>
                                            @endforeach
                                            {{-- @foreach (auth()->user()->unreadNotifications as $unread)
                                                <li>
                                                    <div class="timeline-panel">
                                                        <div class="media-body">
                                                            <h5 class="mb-1">{{ trans('messages.Payment Success') }}
                                                                {{ $unread->data['currency'] }}
                                                                {{ $unread->data['amount'] }}</h5>
                                                            <p class="mb-2">{{ trans('messages.Trans ID') }}:
                                                                {{ $unread->data['transaction_id'] }}</p>
                                                            <small
                                                                class="d-block">{{ now()->diffInMinutes($unread->created_at) }}
                                                                {{ trans('messages.minutes ago') }}</small>
                                                        </div>
                                                    </div>
                                                </li>
                                            @endforeach --}}
                                        </ul>
                                    </div>
                                    <a class="all-notification"
                                        href="{{ route('markReadTransaction') }}">{{ trans('messages.mark all as read') }}</a>
                                </div>
                            </li>

                            <li class="nav-item d-none d-md-flex" style="padding-top:5px;">
                            	<span class="font-w600 username-part">{{ trans('messages.hi') }}, <b>{{ ucfirst(Session::get('name')) }}</b></span>
                            </li>

                            <li class="nav-item" style="padding-top:5px;">
                                <a href="{{ route('logout') }}" class="dropdown-item ai-icon">
                                    <svg id="icon-logout" xmlns="http://www.w3.org/2000/svg" class="text-danger"
                                        width="18" height="18" viewbox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                        <polyline points="16 17 21 12 16 7"></polyline>
                                        <line x1="21" y1="12" x2="9" y2="12"></line>
                                    </svg>
                                </a>
                            </li>
                        </ul>
                    </div>
                </nav>
            </div>
        </div>
         
        @include('sidebar.sidebar')

        @yield('content')
    </div>

    <script src="{{ URL::to('assets/vendor/global/global.min.js') }}"></script>
    <script src="{{ URL::to('assets/vendor/datatables/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ URL::to('assets/vendor/datatables/js/responsive.min.js') }}"></script>
    <script src="{{ URL::to('assets/js/custom.min.js') }}"></script>
    <script src="{{ URL::to('assets/js/dlabnav-init.js') }}"></script>
    <script src="{{ URL::to('assets/js/jquery-ui.js') }}"></script>
    @vite('resources/js/app.js')
    <script src="{{ URL::to('assets/js/bootstrap.min.js') }}"></script>

    {{-- JQuery Validation JS --}}
    <script src="{{ asset('/assets/js/jquery-validator.js') }}"></script>
    @if (session()->get('locale') == 'en')
        <script src="{{ asset('/assets/js/localization/messages_en.js') }}"></script>
    @else
        <script src="{{ asset('/assets/js/localization/messages_ch.js') }}"></script>
    @endif

    <script type="text/javascript" src="{{ asset('/assets/js/moment.min.js') }}"></script>
	<script type="text/javascript" src="{{ asset('/assets/js/daterangepicker.js') }}"></script>
	<link rel="stylesheet" type="text/css" href="{{ asset('/assets/css/daterangepicker.css') }}" />

    <script type="text/javascript">
		$(function () {
			$('.daterange').daterangepicker({
				autoUpdateInput: false,
				locale: {
					format: 'YYYY-MM-DD',
					applyLabel: "{{ trans('messages.Apply') }}",
					cancelLabel: "{{ trans('messages.Clear') }}",
					@if(session()->get('locale') == 'ch')
						daysOfWeek: [
							"日",
							"一",
							"二",
							"三",
							"四",
							"五",
							"六"
						],
						monthNames: [
							"一月",
							"二月",
							"三月",
							"四月",
							"五月",
							"六月",
							"七月",
							"八月",
							"九月",
							"十月",
							"十一月",
							"十二月"
						],
					@endif
				},
			});
		})

        $(".changeLang").change(function(){
            window.location.href = "{{ route('changeLang') }}" + "?lang="+ $(this).val();
        });

        $(".updateTimezone").change(function(){
            window.location.href = "{{ route('updateTimezone') }}" + "?tz="+ $(this).val();
        });

        $(function() {
            $('input').attr('maxlength', '20');

            $('input').keyup(function() {
                if ($(this).attr('id') == 'account_number' || $(this).attr('id') == 'editAccountNumber') {
                    $(this).attr('maxlength', '40');
                } else if($(this).attr('id') == 'url' || $(this).attr('id') == 'editUrl' || $(this).attr('id') == 'pre_sign' || $(this).attr('id') == 'editPreSign' || $(this).attr('id') == 'e_comm_website' || $(this).attr('id') == 'editE_comm_website'){
                    $(this).attr('maxlength', '200');
                } else {
                    $('input').attr('maxlength', '20');
                }
            });

             $(".params").keyup(function(){
               $('.params').attr('maxlength', '200');
             });
        })

        $(function() {
            Echo.private('App.Models.User.{{ auth()->id() }}')
                .notification((notification) => {
                    $.ajax({
                        url: "{{ route('get-unread-notification') }}",
                        type: 'GET',
                        success: function(res) {
                            $('#notificationCount').text(res.length);
                            $('#notificationCount').removeClass('d-none');
                            $('#notiBell').attr("data-toggle", "dropdown");
                            $('#notiBell').attr("aria-haspopup", "true");

                            var newContent = '';

                            for (let i = 0; i < res.length; i++) {
                                newContent += `
									<li>
										<div class="timeline-panel">
											<div class="media-body">
												<h5 class="mb-1">{{ trans('messages.Payment Success') }} ${res[i].data.currency} ${res[i].data.amount}</h5>
												<p class="mb-2">{{ trans('messages.Trans ID') }}: ${res[i].data.transaction_id}</p>
												<small class="d-block">
													${Math.abs(Math.round((((new Date().getTime() - new Date(res[i].created_at).getTime()) / 1000) / 60)))} {{ trans('messages.minutes ago') }}
												</small>
											</div>
										</div>
									</li>
								`;
                            }

                            $('#notiContent').html(newContent);
                        }
                    });

                    $('#notiDiv').addClass('show');
                    $('#notiBell').attr("aria-expanded", "true");
                    $('#notiBody').addClass('show');
                    $("#notiBody").effect("shake", {
                        direction: "up",
                        times: 4,
                        distance: 10
                    }, 1000);
                });
        });
    </script>

    @yield('script')
</body>

</html>
