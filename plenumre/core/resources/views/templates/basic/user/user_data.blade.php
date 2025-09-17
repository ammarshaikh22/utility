@extends($activeTemplate . 'layouts.frontend')
@section('content')
    <section class="account">
        <div class="account-inner py-120 bg-pattern3">
            <div class="container ">
                <div class="row justify-content-center">
                    <div class="col-lg-7">
                        <form method="POST" action="{{ route('user.data.submit') }}" class="account-form">
                            @csrf
                            <div class="account-form__body">
                                <div class="alert alert-primary mb-4" role="alert">
                                    <strong>
                                        @lang('Complete your profile')
                                    </strong>
                                    <p>@lang('You need to complete your profile by providing below information.')</p>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <label class="form-label">@lang('Username')</label>
                                            <input type="text" class="form-control form--control checkUser"
                                                name="username" value="{{ old('username') }}">
                                            <small class="text--danger usernameExist"></small>
                                        </div>
                                    </div>
                                </div>
                                <div class="row gx-3">
                                    <div class="col-xsm-6 col-sm-6">
                                        <div class="form-group">
                                            <label class="form-label">@lang('Country')</label>
                                            <select name="country" class="form--control select2" required>
                                                @foreach ($countries as $key => $country)
                                                    <option data-mobile_code="{{ $country->dial_code }}"
                                                        value="{{ $country->country }}" data-code="{{ $key }}">
                                                        {{ __($country->country) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-xsm-6 col-sm-6">
                                        <div class="form-group">
                                            <label class="form--label">@lang('Mobile')</label>
                                            <div class="input-group input-with-text">
                                                <input type="hidden" name="mobile_code">
                                                <input type="hidden" name="country_code">
                                                <span class="input-group-text mobile-code"></span>
                                                <input class="form--control checkUser" type="number" name="mobile"
                                                    value="{{ old('mobile') }}" required>
                                            </div>
                                            <small class="text-danger mobileExist"></small>
                                        </div>
                                    </div>
                                </div>
                                <div class="row gx-3">
                                    <div class="col-xsm-6 col-sm-6">
                                        <div class="form-group">
                                            <label class="form--label">@lang('Address')</label>
                                            <input type="text" class="form--control" name="address"
                                                value="{{ old('address') }}">
                                        </div>
                                    </div>
                                    <div class="col-xsm-6 col-sm-6">
                                        <div class="form-group">
                                            <label class="form--label">@lang('State')</label>
                                            <input type="text" class="form--control" name="state"
                                                value="{{ old('state') }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="row gx-3">
                                    <div class="col-xsm-6 col-sm-6">
                                        <div class="form-group">
                                            <label for="your-password" class="form--label">@lang('Zip Code')</label>
                                            <input class="form--control" type="text" name="zip"
                                                value="{{ old('zip') }}">
                                        </div>
                                    </div>
                                    <div class="col-xsm-6 col-sm-6">
                                        <div class="form-group">
                                            <label class="form--label">@lang('City')</label>
                                            <input class="form--control" type="text" name="city"
                                                value="{{ old('city') }}">
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="w-100 btn btn--base">@lang('Submit')</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('style-lib')
    <link rel="stylesheet" href="{{ asset('assets/global/css/select2.min.css') }}">
@endpush

@push('script-lib')
    <script src="{{ asset('assets/global/js/select2.min.js') }}"></script>
@endpush


@push('script')
    <script>
        (function($) {
            "use strict";

            @if($mobileCode)
            $(`option[data-code={{ $mobileCode }}]`).attr('selected','');
            @endif
            $('.select2').select2();

            $('select[name=country]').on('change', function() {
                $('input[name=mobile_code]').val($('select[name=country] :selected').data('mobile_code'));
                $('input[name=country_code]').val($('select[name=country] :selected').data('code'));
                $('.mobile-code').text('+' + $('select[name=country] :selected').data('mobile_code'));
                var value = $('[name=mobile]').val();
                var name = 'mobile';
                checkUser(value, name);
            });

            $('input[name=mobile_code]').val($('select[name=country] :selected').data('mobile_code'));
            $('input[name=country_code]').val($('select[name=country] :selected').data('code'));
            $('.mobile-code').text('+' + $('select[name=country] :selected').data('mobile_code'));


            $('.checkUser').on('focusout', function(e) {
                var value = $(this).val();
                var name = $(this).attr('name')
                checkUser(value, name);
            });

            function checkUser(value, name) {
                var url = '{{ route('user.checkUser') }}';
                var token = '{{ csrf_token() }}';

                if (name == 'mobile') {
                    var mobile = `${value}`;
                    var data = {
                        mobile: mobile,
                        mobile_code: $('.mobile-code').text().substr(1),
                        _token: token
                    }
                }
                if (name == 'username') {
                    var data = {
                        username: value,
                        _token: token
                    }
                }
                $.post(url, data, function(response) {
                    if (response.data != false) {
                        $(`.${response.type}Exist`).text(`${response.field} already exist`);
                    } else {
                        $(`.${response.type}Exist`).text('');
                    }
                });
            }
        })(jQuery);
    </script>
@endpush

@push('style')
    <style>
        .selection {
            display: block;
        }

        .input-group-text {
            border-top-left-radius: 8px !important;
            border-bottom-left-radius: 8px !important;
        }
    </style>
@endpush
