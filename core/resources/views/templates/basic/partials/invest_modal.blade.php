@php
    $gatewayCurrency = App\Models\GatewayCurrency::whereHas('method', function ($gate) {
        $gate->where('status', Status::ENABLE);
    })
        ->with('method')
        ->orderby('method_code')
        ->get();
@endphp
<div id="investModal" class="modal fade custom--modal invest-modal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 mb-2">
                <div>
                    <h6 class="modal-title">@lang('Invest to ') - <span class="text--base">{{ __($property->title) }}</span>
                    </h6>
                </div>
                <button class="close-btn" type="button" data-bs-dismiss="modal">
                    <i class="las fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('user.invest.store', encrypt(@$property->id)) }}"
                    class="modal-form" id="investForm">
                    @csrf
                    <input type="hidden" name="method" id="paymentMethod" value="gateway">
                    <div class="modal-form__body">
                        <div class="mb-4">
                            <ul class="modal-form__info">
                                @if (@$property->invest_type == Status::INVEST_TYPE_INSTALLMENT)
                                    <li class="modal-form__info-item">
                                        <span class="label">@lang('Down Payment')</span>
                                        <span class="value">{{ getAmount(@$property->down_payment) }}%</span>
                                    </li>
                                    <li class="modal-form__info-item">
                                        <span class="label">@lang('Initial Invest Amount')</span>
                                        <span class="value">
                                            {{ showAmount($initialInvestAmount) }}
                                        </span>
                                    </li>
                                @endif
                                <li class="modal-form__info-item">
                                    <span class="label">@lang('Profit')</span>
                                    <span class="value">
                                        {{ @$property->getProfit }}
                                    </span>
                                </li>
                                <li class="modal-form__info-item">
                                    <span class="label">@lang('Profit Schedule')</span>
                                    <span class="value">
                                        {{ @$property->getProfitSchedule }}
                                    </span>
                                </li>
                                <li class="modal-form__info-item">
                                    <span class="label">@lang('Profit Back')</span>
                                    <span class="value">
                                        @lang(@$property->profit_back . ' days after investment completed')
                                    </span>
                                </li>
                            </ul>
                        </div>
                        <div class="form-group">
                            <div class="d-flex justify-content-between flex-wrap">
                                <label class="form-label">@lang('Invest Amount')</label>
                                @if (@$property->invest_type == Status::INVEST_TYPE_INSTALLMENT)
                                    <div class="form-check mb-0">
                                        <input class="form-check-input" name="invest_full_amount" type="checkbox"
                                            value="true" id="invest_full_amount">
                                        <label class="form-check-label form-label" for="invest_full_amount">
                                            @lang('Invest Full Amount')
                                        </label>
                                    </div>
                                @endif
                            </div>
                            <div class="input-group--custom style-two">
                                @if (@$property->invest_type == Status::INVEST_TYPE_ONETIME)
                                    <input class="form--control" type="number" name="invest_amount"
                                        value="{{ getAmount(@$property->per_share_amount) }}" readonly>
                                @else
                                    <input class="form--control" type="number" name="invest_amount"
                                        value="{{ old('invest_amount', getAmount(@$initialInvestAmount)) }}" readonly>
                                @endif
                                <span class="input-group-text">{{ __(gs('cur_text')) }}</span>

                            </div>
                            <div class="mt-2 preview-details d-none">
                                <span>
                                    <span>@lang('Charge'):</span>
                                    <span class="text--base"><span class="charge ">0</span></span>,
                                </span>
                                <span>
                                    <span>@lang('Total Amount'): </span> <span class="text--base"><span class="payable ">
                                            0</span></span>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-form__footer flex-row   flex-wrap form-group">
                        <button type="button" class="flex-fill btn btn-outline--base active" id="payGatewayButton">
                            <span class="active-badge"> <i class="las la-check"></i> </span>
                            @lang('Pay via Gateway')
                        </button>
                        <button type="button" class="flex-fill btn btn-outline--base" id="payBalanceButton">
                            <span class="active-badge"> <i class="las la-check"></i> </span>
                            @lang('Pay via Balance')
                        </button>
                    </div>
                    <button type="submit" class="flex-fill btn btn--base w-100">
                        @lang('Invest Now')
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('script')
    <script>
        "use strict";
        (function($) {


            $('input[name=invest_full_amount]').on('change', function() {
                if (this.checked) {
                    $('input[name=invest_amount]').val({{ $property->per_share_amount }});
                } else {
                    $('input[name=invest_amount]').val({{ $initialInvestAmount }});
                }
            });

            $('#payGatewayButton').on("click", function() {
                $(this).parent().find("button").removeClass('active');
                $(this).addClass('active');
                $('#paymentMethod').val('gateway');
            });

            $('#payBalanceButton').on("click", function() {
                $(this).parent().find("button").removeClass('active');
                $(this).addClass('active');
                $('#paymentMethod').val('balance');
            });

        })(jQuery);
    </script>
@endpush

@push('style')
    <style>
        .invest-modal .form-check-input:focus {
            border-color: hsl(var(--base));
            box-shadow: none;
        }

        .invest-modal .form-check-input:checked {
            background-color: hsl(var(--base));
            border: 1px solid hsl(var(--base));
        }

        .invest-modal .form-check-input {
            border: 1px solid hsl(var(--base));
        }

        .invest-modal .modal-form__footer button.active {
            border-color: hsl(var(--base));
            position: relative;
        }


        .invest-modal .modal-form__footer button .active-badge {
            display: none;
        }

        .invest-modal .modal-form__footer button.active .active-badge {
            right: 0px;
            top: -1px;
            position: absolute;
            color: #ffffff;
            background: hsl(var(--base));
            text-align: right;
            width: 50px;
            height: 40px;
            padding-right: 4px;
            clip-path: polygon(100% 0, 0 1%, 100% 100%);
            display: block;
        }

        .invest-modal .btn-outline--base:hover,
        .invest-modal .btn-outline--base:focus .invest-modal .btn-outline--base:focus-visible {
            background-color: hsl(var(--base) / 0.05) !important;
            border: 1px solid hsl(var(--base)) !important;
            color: hsl(var(--base)) !important;
        }

        .invest-modal .selected-gateway {
            border-color: hsl(var(--base)/0.32) !important;
        }

        .preview-details {
            font-size: 0.875rem;
        }
    </style>
@endpush
