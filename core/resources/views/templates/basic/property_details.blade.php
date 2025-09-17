@extends($activeTemplate . 'layouts.frontend')
@php
    $credentials = gs('socialite_credentials');
    $loginContent = getContent('login.content', true);
    $label = 'form--label';
    $formControl = '';
    $initialInvestAmount = ($property->per_share_amount / 100) * $property->down_payment;
@endphp
@section('content')
    <section class="property-details py-60 bg-pattern">
        <div class="container ">
            <div class="row gy-4 gy-lg-0 row--one">
                <div class="col-lg-7 col-xxl-8">
                    <div class="mb-4">
                        <h4 class="property-details__title mb-0">{{ __(@$property->title) }}</h4>
                        <ul class="property-details-metan">
                            <li class="property-details-meta__item">
                                <span class="icon"><i class="fas fa-map-marker-alt"></i></span>
                                <span class="text">{{ __(@$property->location->name) }}</span>
                            </li>
                        </ul>
                    </div>
                    <div class="property-details__block mb-4">
                        <div class="property-details__slider">
                            @foreach (@$property->propertyGallery as $item)
                                <img class="property-details__slider-img"
                                    src="{{ getImage(getFilePath('propertyGallery') . '/' . @$item->image, getFileSize('propertyGallery')) }}"
                                    alt="property-image">
                            @endforeach
                        </div>
                        <div class="property-details__thumb">
                            @foreach (@$property->propertyGallery as $item)
                                <img class="property-details__thumb-img"
                                    src="{{ getImage(getFilePath('propertyGallery') . '/' . @$item->image, getFileSize('propertyGallery')) }}"
                                    alt="property-image">
                            @endforeach
                        </div>
                    </div>
                    <div class="property-details__block mb-4">
                        <div class="mb-3">
                            <h5 class="title">@lang('Property Description')</h5>
                            <div class="property-details__desc">
                                @php echo $property->details; @endphp
                            </div>
                        </div>
                        <div class="mb-3">
                            <h5 class="title">@lang('Location')</h5>
                            <iframe class="property-details__map" src="{{ $property->map_url }}" style="border:0;"
                                allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade">
                            </iframe>
                        </div>
                    </div>
                    @include($activeTemplate . 'partials.share_now', ['title' => @$property->title])
                </div>
                <div class="col-lg-5 col-xxl-4">
                    <div class="property-details__price mb-4">
                        <h3 class="mb-0">
                            {{ showAmount(@$property->per_share_amount) }}
                        </h3>
                        <span class="text">@lang('Per share amount')</span>
                    </div>
                    <div class="property-details__buttons mb-md-4 mb-0">
                        @guest
                            <a href="{{ route('user.login') }}" type="button" class="btn btn--lg btn--base">
                                @lang('Invest Now')
                            </a>
                        @else
                            <button type="button" class="btn btn--lg btn--base" id="investBtn">
                                @lang('Invest Now')
                            </button>
                        @endguest
                        <button type="button" class="btn btn--lg btn-outline--base d-lg-none" data-toggle="sidebar"
                            data-target="#property-details-sidebar">
                            <i class="fas fa-info-circle"></i>
                            <span>@lang('Details')</span>
                        </button>
                    </div>
                    <div id="property-details-sidebar" class="property-details-sidebar">
                        <button type="button" class="close-btn">
                            <i class="fas fa-times"></i>
                        </button>
                        <div class="property-details-sidebar__block mb-4">
                            <div class="block-heading">
                                <p class="block-heading__subtitle">@lang('Available share'):
                                    {{ $property->total_share - @$property->invests_count }}
                                </p>
                            </div>
                            <div class="card-progress mt-2">
                                <div class="card-progress__bar">
                                    <div class="card-progress__thumb" style="width: {{ @$property->invest_progress }}%;">
                                    </div>
                                </div>
                                <span class="card-progress__label fs-12">
                                    {{ $property->invests_count }} @lang('Investors') |
                                    {{ showAmount(@$property->invested_amount) }}
                                    ({{ getAmount(@$property->invest_progress) }}%)
                                </span>
                            </div>
                            <ul class="property-details-amount-info">
                                <li class="property-details-amount-info__item">
                                    <span class="label">@lang('Investment Type')</span>
                                    <span class="value">
                                        @if (@$property->invest_type == Status::INVEST_TYPE_INSTALLMENT)
                                            @lang('Installment')
                                        @else
                                            @lang('Onetime')
                                        @endif
                                    </span>
                                </li>
                                <li class="property-details-amount-info__item">
                                    <span class="label">@lang('Profit')</span>
                                    <span class="value">
                                        {{ @$property->getProfit }}
                                    </span>

                                </li>
                                @if (@$property->invest_type == Status::INVEST_TYPE_INSTALLMENT)
                                    <li class="property-details-amount-info__item">
                                        <span class="label">@lang('Down Payment')</span>
                                        <span class="value">{{ getAmount($property->down_payment) }}%</span>
                                    </li>
                                    <li class="property-details-amount-info__item">
                                        <span class="label">@lang('Initial Invest Amount')</span>
                                        <span class="value">
                                            {{ showAmount($initialInvestAmount) }}
                                        </span>
                                    </li>
                                    <li class="property-details-amount-info__item">
                                        <span class="label">@lang('Total Installments')</span>
                                        <span class="value">{{ @$property->total_installment }}</span>
                                    </li>
                                    <li class="property-details-amount-info__item">
                                        <span class="label">@lang('Per Installment Amount')</span>
                                        <span class="value">
                                            {{ showAmount(@$property->per_installment_amount) }}
                                        </span>
                                    </li>
                                    <li class="property-details-amount-info__item">
                                        <span class="label">@lang('Installment Schedule')</span>
                                        <span class="value">
                                            {{ __(@$property->installmentDuration->name) }}
                                        </span>
                                    </li>
                                    <li class="property-details-amount-info__item">
                                        <span class="label">@lang('Installment Late Fee')</span>
                                        <span class="value">
                                            {{ showAmount(@$property->installment_late_fee) }}
                                        </span>
                                    </li>
                                @endif
                                <li class="property-details-amount-info__item">
                                    <span class="label">@lang('Profit Schedule')</span>
                                    <span class="value">
                                        {{ @$property->getProfitSchedule }}
                                    </span>
                                </li>

                                @if (@$property->profit_schedule == Status::PROFIT_REPEATED_TIME)
                                    <li class="property-details-amount-info__item">
                                        <span class="label">@lang('Profit Repeat')</span>
                                        <span class="value">
                                            {{ @$property->profit_repeat_time }} @lang('Times')
                                        </span>
                                    </li>
                                @endif
                                <li class="property-details-amount-info__item">
                                    <span class="label">@lang('Profit Back')</span>
                                    <span class="value">@lang(@$property->profit_back . ' days after investment')</span>
                                </li>
                                <li class="property-details-amount-info__item">
                                    <span class="label">@lang('Capital Back')</span>
                                    <span class="value">
                                        {{ @$property->getCapitalBackStatus }}
                                    </span>
                                </li>
                            </ul>
                        </div>
                        @if ($property->video)
                            <div class="investors property-details-sidebar__block mb-4">
                                <div class="block-heading">
                                    <div class="block-heading__wrapper">
                                        <h6 class="block-heading__title mb-3">@lang('Property Video')</h6>
                                    </div>
                                </div>
                                <div class="popup-youtube-inner">
                                    <img src="{{ getImage(getFilePath('propertyThumb') . '/' . @$property->thumb_image, getFileSize('propertyThumb')) }}"
                                        alt="thumb image">
                                    <a class="popup-youtube" href="{{ convertToEmbedUrl($property->video) }}">
                                        <span class="icon">
                                            <i class="las la-play"></i>
                                        </span>
                                    </a>
                                </div>
                            </div>
                        @endif
                        @if ($investors->count() > 0)
                            <div class="property-details-sidebar__block investors mb-4">
                                <div class="block-heading">
                                    <div class="block-heading__wrapper">
                                        <h6 class="block-heading__title">@lang('Real Estate Investor')</h6>
                                        <div class="block-heading__arrows"></div>
                                    </div>
                                </div>
                                <div class="property-details__investors">
                                    @foreach ($investors as $investor)
                                        <div class="property-details-investor">
                                            <div class="property-details-investor__wrapper">
                                                <div class="property-details-investor__thumb">
                                                    <img src="{{ getImage(getFilePath('userProfile') . '/' . @$investor->avatar, isAvatar: true) }}"
                                                        alt="@lang('profile-image')">
                                                </div>
                                                <div class="property-details-investor__content">
                                                    <h6 class="name">{{ __($investor->fullname) }}</h6>
                                                    <span class="designation">
                                                        @lang('Join') {{ diffForHumans($investor->created_at) }}
                                                    </span>
                                                    <ul class="meta-list">
                                                        <li class="meta-list__item">
                                                            <span class="icon"><i class="far fa-building"></i></span>
                                                            <span class="text">{{ $investor->invests_count }}
                                                                @lang('Properties')</span>
                                                        </li>
                                                        <li class="meta-list__item">
                                                            <span class="icon"><i
                                                                    class="fas fa-map-marker-alt"></i></span>
                                                            <span class="text">{{ __($investor->country_name) }}</span>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        @if ($latestProperties->count() > 0)
                            <div class="property-details-sidebar__block">
                                <div class="block-heading">
                                    <h6 class="block-heading__title">@lang('Latest Properties')</h6>
                                </div>
                                <div class="property-details__cards">
                                    @foreach ($latestProperties as $latestProperty)
                                        <div class="property-details-card">
                                            <div class="property-details-card__thumb">
                                                <a href="{{ route('property.details', @$latestProperty->slug) }}">
                                                    <img src="{{ getImage(getFilePath('propertyThumb') . '/thumb_' . @$latestProperty->thumb_image, getFileSize('propertyThumb')) }}"
                                                        alt="Property-image">
                                                </a>
                                            </div>
                                            <div class="property-details-card__content">
                                                <h6 class="title">
                                                    <a href="{{ route('property.details', @$latestProperty->slug) }}">
                                                        {{ __(@$latestProperty->title) }}
                                                    </a>
                                                </h6>
                                                <div class="location">
                                                    <i class="fas fa-map-marker-alt"></i>
                                                    <span>{{ __(@$latestProperty->location->name) }}</span>
                                                </div>
                                                <span class="price">
                                                    {{ showAmount(@$latestProperty->per_share_amount) }}
                                                </span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>

    @include($activeTemplate . 'partials.invest_modal')
@endsection

@if ($property->video)
    @push('style-lib')
        <link href="{{ asset($activeTemplateTrue . 'css/magnific-popup.min.css') }}" rel="stylesheet">
    @endpush

    @push('script-lib')
        <script src="{{ asset($activeTemplateTrue . 'js/jquery.magnific-popup.min.js') }}"></script>
    @endpush
@endif

@push('script')
    <script>
        (function($) {
            "use strict";

            $('#investBtn').on('click', function() {
                let modal = $('#investModal');
                modal.modal('show');
            });


            $('iframe').attr('width', '100%');

            // Property Details Slider Js Start
            $('.property-details__slider').slick({
                slidesToShow: 1,
                slidesToScroll: 1,
                arrows: true,
                asNavFor: '.property-details__thumb',
                prevArrow: '<button type="button" class="slick-prev"><i class="fas fa-angle-left"></i></button>',
                nextArrow: '<button type="button" class="slick-next"><i class="fas fa-angle-right"></i></button>'
            });

            $('.property-details__thumb').slick({
                slidesToShow: 4,
                slidesToScroll: 1,
                asNavFor: '.property-details__slider',
                dots: false,
                arrows: false,
                centerMode: true,
                focusOnSelect: true,
                responsive: [{
                        breakpoint: 600 + 1,
                        settings: {
                            slidesToShow: 3,
                        }
                    },
                    {
                        breakpoint: 424 + 1,
                        settings: {
                            slidesToShow: 2,
                        }
                    }
                ]
            });

            $('.property-details-sidebar__block.investors').each(function(index, element) {
                $(element).find('.property-details__investors').slick({
                    slidesToShow: 1,
                    slidesToScroll: 1,
                    autoplay: true,
                    speed: 1500,
                    arrows: true,
                    appendArrows: $(element).find('.block-heading__arrows'),
                    prevArrow: '<button type="button" class="slick-prev"><i class="fas fa-angle-left"></i></button>',
                    nextArrow: '<button type="button" class="slick-next"><i class="fas fa-angle-right"></i></button>',
                });
            });
            // Property Details Slider Js end


            @if ($property->video)
                $('.popup-youtube').magnificPopup({
                    type: 'iframe',
                    iframe: {
                        patterns: {
                            youtube: {
                                index: 'youtube.com/',
                                id: 'v=',
                                src: `{{ convertToEmbedUrl($property->video) }}`
                            }
                        }
                    },
                    mainClass: 'mfp-fade',
                    removalDelay: 160,
                    preloader: false,
                    fixedContentPos: false
                });
            @endif
        })(jQuery);
    </script>
@endpush

@push('style')
    <style>
        .property-details-sidebar .block-heading__subtitle {
            margin-top: 0px;
        }

        .mfp-iframe-holder .mfp-content {
            max-width: 1200px;
            width: 100%;
        }

        .mfp-close{
            display: none !important;
        }
    </style>
@endpush
