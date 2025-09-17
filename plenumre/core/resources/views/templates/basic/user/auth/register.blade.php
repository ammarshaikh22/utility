@extends($activeTemplate . 'layouts.app')

@section('main-content')
    <section class="account">
        <div class="account-inner py-60 bg-pattern3">
            @if (gs('registration'))

                @php
                    $registerContent = getContent('register.content', true);
                    $credentials = gs('socialite_credentials');
                    $label = 'form--label';
                    $formControl = '';
                @endphp


                <div class="container ">
                    <div class="row justify-content-center">
                        <div class="col-md-10 col-lg-8 col-xl-6">
                            <form method="POST" action="{{ route('user.register') }}" class="account-form verify-gcaptcha">
                                @csrf
                                <div class="account-form__header text-center">
                                    <a class="mb-5" href="{{ route('home') }}"> <img src="{{ siteLogo() }}"></a>
                                    <h5 class="account-form__title mb-3">{{ __($registerContent->data_values->heading) }}
                                    </h5>

                                    @include($activeTemplate . 'partials.social_login', [
                                        'register' => true,
                                    ])

                                </div>
                                <div class="account-form__body">
                                    <div class="row">
                                        @if (session()->get('reference') != null)
                                            <div class="col-xsm-12 col-sm-12">
                                                <div class="form-group">
                                                    <label for="referenceBy"
                                                        class="form--label required">@lang('Reference by')</label>
                                                    <input class="form--control" type="text" name="referBy"
                                                        value="{{ session()->get('reference') }}" id="referenceBy" readonly>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="row gx-3">
                                        <div class="col-xsm-6 col-sm-6">
                                            <div class="form-group">
                                                <label class="form--label">@lang('First Name')</label>
                                                <input class="form--control" type="text" name="firstname"
                                                    value="{{ old('firstname') }}" required>
                                            </div>
                                        </div>
                                        <div class="col-xsm-6 col-sm-6">
                                            <div class="form-group">
                                                <label class="form--label">@lang('Last Name')</label>
                                                <input class="form--control" type="text" name="lastname"
                                                    value="{{ old('lastname') }}" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row gx-3">
                                        <div class="col-sm-12">
                                            <div class="form-group">
                                                <label for="email" class="form--label">@lang('Email')</label>
                                                <input class="form--control checkUser" type="email" id="email"
                                                    name="email" value="{{ old('email') }}" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row gx-3">
                                        <div class="col-xsm-6 col-sm-6">
                                            <div class="form-group">
                                                <label for="your-password" class="form--label">@lang('Password')</label>
                                                <div class="position-relative">
                                                    <input
                                                        class="form--control @if (gs('secure_password')) secure-password @endif"
                                                        type="password" name="password" id="your-password" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xsm-6 col-sm-6">
                                            <div class="form-group">
                                                <label for="your-password" class="form--label">@lang('Confirm Password')</label>
                                                <div class="position-relative">
                                                    <input class="form--control" type="password" id="confirm-password"
                                                        name="password_confirmation" required>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <x-captcha :label="$label" :formControl="$formControl" />
                                    <div class="row gx-3">
                                        <div class="col">
                                            @if (gs('agree'))
                                                @php
                                                    $policyPages = getContent(
                                                        'policy_pages.element',
                                                        false,
                                                        null,
                                                        true,
                                                    );
                                                @endphp
                                                <div class="form--check">
                                                    <input class="form-check-input" type="checkbox" id="agree"
                                                        @checked(old('agree')) name="agree" required>
                                                    <label class="form-check-label" for="agree">@lang('I agree with ')
                                                        @foreach ($policyPages as $policy)
                                                            <a href="{{ route('policy.pages', slug($policy->data_values->title)) }}"
                                                                target="_blank">{{ __($policy->data_values->title) }}</a>
                                                            @if (!$loop->last)
                                                                ,
                                                            @endif
                                                        @endforeach
                                                    </label>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="account-form__footer">
                                    <button type="submit" id="recaptcha"
                                        class="w-100 btn btn--base">@lang('Register')</button>
                                    <p class="account-form__subtitle mt-3">@lang('Already have an account')?
                                        <a href="{{ route('user.login') }}">@lang('Login')</a>
                                    </p>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @else
                @include($activeTemplate . 'partials.registration_disabled')
            @endif
        </div>
    </section>

    <div class="modal fade custom--modal" id="existModalCenter">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('You are with us')</h5>
                    <button class="close-btn" type="button" data-bs-dismiss="modal">
                        <i class="las fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="modal-form__header">
                        <h6 class="modal-form__title">@lang('You already have an account please Login ')</h6>
                    </div>
                    <div class="d-flex justify-content-end">
                        <a href="{{ route('user.login') }}" class="btn btn--base btn--sm">@lang('Login')</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('style')
    <style>
        .country-code .input-group-text {
            background: #fff !important;
        }

        .country-code select {
            border: none;
        }

        .country-code select:focus {
            border: none;
            outline: none;
        }
    </style>
@endpush

@if (gs('secure_password'))
    @push('script-lib')
        <script src="{{ asset('assets/global/js/secure_password.js') }}"></script>
    @endpush
@endif

@push('script')
    <script>
        "use strict";
        (function($) {

            $('.checkUser').on('focusout', function(e) {
                var url = '{{ route('user.checkUser') }}';
                var value = $(this).val();
                var token = '{{ csrf_token() }}';
                if ($(this).attr('name') == 'email') {
                    var data = {
                        email: value,
                        _token: token
                    }
                }

                $.post(url, data, function(response) {
                    if (response.data != false && response.type == 'email') {
                        $('#existModalCenter').modal('show');
                    }
                });
            });
        })(jQuery);
    </script>
@endpush
