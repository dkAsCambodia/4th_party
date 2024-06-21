<div class="dlabnav">
    <div class="dlabnav-scroll">
        <ul class="metismenu" id="menu">
            @if (Auth()->user()->role_name !== 'Merchant')
                <li><a class="ai-icon" href="{{ route('home') }}" aria-expanded="false">
                        <i class="flaticon-025-dashboard"></i>
                        <span class="nav-text">{{ __('messages.Dashboard') }}</span>
                    </a>
                </li>
            @endif
            @if (Auth()->user()->role_name === 'Agent')
                <li>
                    <a class=" ai-icon" href="{{ route('Account: View Agent Account') }}" aria-expanded="false">
                        <i class="flaticon-093-account"></i>
                        <span class="nav-text">{{ __('messages.Bank Account') }}</span>
                    </a>
                </li>
                <li>
                    <a class="ai-icon" href="{{ route('view/agent-merchant') }}" aria-expanded="false">
                        <i class="flaticon-093-merchant"></i>
                        <span class="nav-text">{{ __('messages.Merchant List') }}</span>
                    </a>
                </li>
                <li>
                    <a class=" ai-payments" href="{{ route('details-payment/list-agent') }}" aria-expanded="false">
                        <i class="flaticon-072-paymentdetails"></i>
                        <span class="nav-text">{{ __('messages.Payment Details') }}</span>
                    </a>
                </li>
                <li>
                    <a class="has-arrow ai-icon" href="javascript:void()" aria-expanded="false">
                        <i class="flaticon-072-settlements"></i>
                        <span class="nav-text">{{ __('messages.Settlement Management') }}</span>
                    </a>
                    <ul aria-expanded="false">
                        <li><a href="{{ route('view/unsettled-agent') }}">{{ __('messages.Unsettled Request') }}</a>
                        </li>

                        <li><a href="{{ route('view/settleRequest-agent') }}">{{ __('messages.Settled Request') }}</a>
                        </li>
                        <li><a
                                href="{{ route('view/settledHistory-agent') }}">{{ __('messages.Settlement Records') }}</a>
                        </li>
                    </ul>
                </li>
            @endif
            @if (Auth()->user()->role_name === 'Merchant')
                {{-- <li>
                    <a class=" ai-icon" href="{{ route('Account: View Merchant Account') }}" aria-expanded="false">
                        <i class="flaticon-093-account"></i>
                        <span class="nav-text">{{ __('messages.Bank Account') }}</span>
                    </a>
                </li>
                <li>
                    <a class=" ai-icon" href="{{ route('sow/payment-product') }}" aria-expanded="false">
                        <i class="flaticon-093-waving"></i>
                        <span class="nav-text">{{ __('messages.Product Management') }}</span>
                    </a>
                </li> --}}
                <li>
                    <a class=" ai-payments" href="{{ route('details-payment/list-merchant') }}" aria-expanded="false">
                        <i class="flaticon-072-paymentdetails"></i>
                        <span class="nav-text">{{ __('messages.Payment Details') }}</span>
                    </a>
                </li>
                <li>
                    <a class="has-arrow ai-icon" href="javascript:void()" aria-expanded="false">
                        <i class="flaticon-072-settlements"></i>
                        <span class="nav-text">{{ __('messages.Settlement Management') }}</span>
                    </a>
                    <ul aria-expanded="false">
                        <li><a
                                href="{{ route('view/unsettled-merchant') }}">{{ __('messages.Unsettled Request') }}</a>
                        </li>
                        <li><a
                                href="{{ route('view/settleRequest-merchant') }}">{{ __('messages.Settled Request') }}</a>
                        </li>

                        <li><a
                                href="{{ route('view/settledHistory-merchant') }}">{{ __('messages.Settlement Records') }}</a>
                        </li>

                    </ul>
                </li>
                <li>
                    <a class=" ai-payments" href="{{ route('merchant-summary-report') }}" aria-expanded="false">
                        <i class="flaticon-072-logs"></i>
                        <span class="nav-text">{{ __('messages.Summary Report') }}</span>
                    </a>
                </li>
                <li>
                    <a class=" ai-payments" href="{{ route('show/api-documentation') }}" aria-expanded="false">
                        <i class="flaticon-028-download"></i>
                        <span class="nav-text">{{ __('messages.Api Documentation') }}</span>
                    </a>
                </li>
            @endif
            @if (Auth()->user()->role_name === 'Admin')
                {{-- @if (auth()->user()->can('Account: View Account'))
                    <li>
                        <a class=" ai-icon" href="{{ route('Account: View Account') }}" aria-expanded="false">
                            <i class="flaticon-093-account"></i>
                            <span class="nav-text">{{ __('messages.Bank Account') }}</span>
                        </a>
                    </li>
                @endif --}}
                @if (auth()->user()->can('Merchant: View Merchant'))
                    <li
                        class="{{ Route::currentRouteName() == 'Merchant: View PaymentMap Merchant'
                            ? 'mm-active'
                            : (Route::currentRouteName() == 'Merchant: View Billing Merchant'
                                ? 'mm-active'
                                : '') }}
                    ">
                        <a class=" ai-icon" href="{{ route('Merchant: View Merchant') }}" aria-expanded="false">
                            <i class="flaticon-093-merchant"></i>
                            <span class="nav-text">{{ __('messages.Merchant Management') }}</span>
                        </a>
                    </li>
                @endif
                @if (auth()->user()->can('Agent: View Agent'))
                    <li>
                        <a class=" ai-icon" href="{{ route('Agent: View Agent') }}" aria-expanded="false">
                            <i class="flaticon-093-agent"></i>
                            <span class="nav-text">{{ __('messages.Agent Management') }}</span>
                        </a>
                    </li>
                @endif
                @if (auth()->user()->can('Method: View Method') ||
                        auth()->user()->can('Channel: View Channel') ||
                        auth()->user()->can('Source: View Source') ||
                        auth()->user()->can('PaymentUrl: View PaymentUrl') ||
                        auth()->user()->can('GatewayPaymentChannel: View GatewayPaymentChannel'))
                    <li>
                        <a class="has-arrow ai-icon" href="javascript:void()" aria-expanded="false">
                            <i class="flaticon-072-payments"></i>
                            <span class="nav-text">{{ __('messages.Payment Config') }}</span>
                        </a>
                        <ul aria-expanded="false">
                            @if (auth()->user()->can('Channel: View Channel'))
                                <li><a
                                        href="{{ route('Channel: View Channel') }}">{{ __('messages.Payment Gateway') }}</a>
                                </li>
                            @endif
                            @if (auth()->user()->can('GatewayAccount: View Gateway Account'))
                                <li
                                    class="{{ Route::currentRouteName() == 'GatewayAccountMethod: View Method Account' ? 'mm-active' : '' }}">
                                    <a class="{{ Route::currentRouteName() == 'GatewayAccountMethod: View Method Account' ? 'mm-active' : '' }}"
                                        href="{{ route('GatewayAccount: View Gateway Account') }}">{{ __('messages.Gateway Account') }}</a>
                                </li>
                            @endif
                            {{-- @if (auth()->user()->can('PaymentUrl: View PaymentUrl'))
                                <li><a
                                        href="{{ route('PaymentUrl: View PaymentUrl') }}">{{ __('messages.Payment Url') }}</a>
                                </li>
                            @endif --}}
                            @if (auth()->user()->can('GatewayPaymentChannel: View GatewayPaymentChannel'))
                                <li><a
                                        href="{{ route('GatewayPaymentChannel: View GatewayPaymentChannel') }}">{{ __('messages.Payment Channel') }}</a>
                                </li>
                            @endif

                        </ul>
                    </li>
                @endif
                @if (auth()->user()->can('PaymentDetails: View PaymentDetails'))
                    <li>
                        <a class=" ai-payments" href="{{ route('PaymentDetails: View PaymentDetails') }}"
                            aria-expanded="false">
                            <i class="flaticon-072-paymentdetails"></i>
                            <span class="nav-text">{{ __('messages.Payment Details') }}</span>
                        </a>
                    </li>
                @endif
                @if (auth()->user()->can('Settlement: Billing View Settlement') ||
                        auth()->user()->can('Settlement: Settle Request View Settlement') ||
                        auth()->user()->can('Settlement: Settle Approved View Settlement') ||
                        auth()->user()->can('Settlement: Settled View Settlement') ||
                        auth()->user()->can('Settlement: Settled History Settlement'))
                    <li>
                        <a class="has-arrow ai-icon" href="javascript:void()" aria-expanded="false">
                            <i class="flaticon-072-settlements"></i>
                            <span class="nav-text">{{ __('messages.Settlement Management') }}</span>
                        </a>
                        <ul aria-expanded="false">
                            @if (auth()->user()->can('Settlement: Settle Request View Settlement'))
                                <li><a
                                        href="{{ route('Settlement: Settle Request View Settlement') }}">{{ __('messages.Settled Request') }}</a>
                                </li>
                            @endif
                            @if (auth()->user()->can('Settlement: Settle Approved View Settlement'))
                                <li><a
                                        href="{{ route('Settlement: Settle Approved View Settlement') }}">{{ __('messages.Settlement Approved') }}</a>
                                </li>
                            @endif
                            @if (auth()->user()->can('Settlement: Settled History Settlement'))
                                <li><a
                                        href="{{ route('Settlement: Settled History Settlement') }}">{{ __('messages.Settlement Records') }}</a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif
                @if (auth()->user()->can('login/logs') ||
                        auth()->user()->can('audit/list'))
                    <li>
                        <a class="has-arrow ai-icon" href="javascript:void()" aria-expanded="false">
                            <i class="flaticon-072-logs"></i>
                            <span class="nav-text">{{ __('messages.Logs') }} </span>
                        </a>
                        <ul aria-expanded="false">
                            @if (auth()->user()->can('login/logs'))
                                <li><a href="{{ route('login/logs') }}">{{ __('messages.Login Logs') }} </a></li>
                            @endif
                            @if (auth()->user()->can('audit/list'))
                                <li><a href="{{ route('audit/list') }}"> {{ __('messages.Audit List') }} </a></li>
                            @endif
                        </ul>
                    </li>
                @endif
                @if (auth()->user()->can('User: View All User') ||
                        auth()->user()->can('Role: View Role') ||
                        auth()->user()->can('whitelist/list'))
                    <li>
                        <a class="has-arrow ai-icon" href="javascript:void()" aria-expanded="false">
                            <i class="flaticon-025-dashboard"></i>
                            <span class="nav-text">{{ __('messages.User Management') }}</span>
                        </a>
                        <ul aria-expanded="false">
                            @if (auth()->user()->can('User: View All User'))
                                <li><a href="{{ route('User: View All User') }}">{{ __('messages.Users List') }}</a>
                                </li>
                            @endif
                            @if (auth()->user()->can('Role: View Role'))
                                <li><a href="{{ route('Role: View Role') }}">{{ __('messages.Roles List') }}</a></li>
                            @endif
                            @if (auth()->user()->can('whitelist/list'))
                                <li><a href="{{ route('whitelist/list') }}">{{ __('messages.IP Whitelisting') }}</a>
                                </li>
                            @endif
                        </ul>
                    </li>
                    <li>
                        <a class="has-arrow ai-icon" href="{{ route('setting.account.list') }}"
                            aria-expanded="false">
                            <i class="flaticon-025-dashboard"></i>
                            <span class="nav-text">{{ __('messages.System Setting') }}</span>
                        </a>
                        <ul aria-expanded="false">
                            
                            @if (auth()->user()->can('Method: View Method'))
                                <li><a
                                        href="{{ route('Method: View Method') }}">{{ __('messages.Payment Method') }}</a>
                                </li>
                            @endif
                            {{-- <li><a
                                    href="{{ route('setting.account.list') }}">{{ __('messages.Account Setting') }}</a>
                            </li> --}}
                            {{-- @if (auth()->user()->can('Settlement: Billing View Settlement'))
                                <li><a
                                        href="{{ route('Settlement: Billing View Settlement') }}">{{ __('messages.Settlement settings') }}</a>
                                </li>
                            @endif --}}
                            <li><a
                                    href="{{ route('setting.timezone.list') }}">{{ __('messages.Timezone Setting') }}</a>
                            </li>
                        </ul>
                    </li>
                @endif
                <li>
                    <a class=" ai-payments" href="{{ route('admin-summary-report') }}" aria-expanded="false">
                        <i class="flaticon-072-logs"></i>
                        <span class="nav-text">{{ __('messages.Summary Report') }}</span>
                    </a>
                </li>

                <li>
                    <a class=" ai-payments" href="{{ url('admin/api-documentation') }}" aria-expanded="false">
                        <i class="flaticon-028-download"></i>
                        <span class="nav-text">{{ __('messages.Api Documentation') }}</span>
                    </a>
                </li>
            @endif
        </ul>
    </div>
</div>
