@extends($activeTemplate . 'layouts.app')
@php
    $loginContent = getContent('login.content', true);
    $label = 'form--label';
    $formControl = '';
@endphp
@section('main-content')
    <section class="account">
        <div class="account-inner py-60 bg-pattern3">
            <div class="container ">
                <div class="row justify-content-center">
                    <div class="col-md-10 col-lg-8 col-xl-6 col-xxl-5">
                        <form method="POST" action="{{ route('user.login') }}" class="account-form verify-gcaptcha">
                            @csrf
                            <div class="account-form__header text-center">
                                <a class="mb-5" href="{{ route('home') }}"> <img src="{{ siteLogo() }}"></a>
                                <h5 class="account-form__title mb-3">{{ __($loginContent->data_values->heading) }}</h5>

                                @include($activeTemplate . 'partials.social_login')

                            </div>
                            <div class="account-form__body">
                                <div class="form-group">
                                    <label for="usernameOrEmail" class="form--label required">@lang('Username or Email')</label>
                                    <input class="form--control" type="text" name="username"
                                        value="{{ old('username') }}" id="usernameOrEmail" required>
                                </div>
                                <div class="form-group">
                                    <label for="your-password" class="form--label required">@lang('Password')</label>
                                    <div class="position-relative">
                                        <input class="form--control" type="password" name="password" id="your-password">
                                    </div>
                                </div>
                                <x-captcha :label="$label" :formControl="$formControl" />
                                <div class="flex-between">
                                    <div class="form--check">
                                        <input class="form-check-input" type="checkbox" id="remember" name="remember"
                                            {{ old('remember') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="remember">@lang('Remember me')</label>
                                    </div>
                                    <a href="{{ route('user.password.request') }}"
                                        class="account-form__forgot-pass">@lang('Forgot Password')?</a>
                                </div>
                            </div>
                            <div class="account-form__footer">
                                <button type="submit" id="recaptcha" class="w-100 btn btn--base">@lang('Login')</button>
                                <p class="account-form__subtitle mt-3">
                                    @lang('Don\'t have an account')?
                                    <a href="{{ route('user.register') }}">@lang('Register')</a>
                                </p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
