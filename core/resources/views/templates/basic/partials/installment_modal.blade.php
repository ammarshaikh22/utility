@php
    $gatewayCurrency = App\Models\GatewayCurrency::whereHas('method', function ($gate) {
        $gate->where('status', Status::ENABLE);
    })
        ->with('method')
        ->orderby('method_code')
        ->get();
@endphp

<div id="installmentModal" class="modal fade custom--modal installment-modal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header  mb-2">
                <div>
                    <h6 class="modal-title">@lang('Installment to ') - <span
                            class="text--base">{{ __(@$nextInstallment->invest->property->title) }}</span>
                        @lang('property')
                    </h6>
                </div>
                <button class="close-btn" type="button" data-bs-dismiss="modal">
                    <i class="las fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form method="POST"
                    action="{{ route('user.invest.installment.pay', [encrypt(@$nextInstallment->invest->id), encrypt(@$nextInstallment->id)]) }}"
                    class="modal-form" id="installmentForm">
                    @csrf
                    <input type="hidden" name="method" id="paymentMethod" value="gateway">
                    <input name="currency" type="hidden">

                    <div class="modal-form__body">
                        <div class="form-group">
                            <label class="form--label">@lang('Installment Amount')</label>
                            <div class="input-group--custom">
                                <input class="form--control" type="number" name="installment_amount"
                                    value="{{ old('installment_amount', getAmount(@$nextInstallment->invest->per_installment_amount)) }}"
                                    readonly required>
                                <span class="input-group-text p-0 border-0">
                                    <small class="px-3">{{ __(gs('cur_text')) }}</small>
                                </span>
                            </div>
                            <div class="mt-2 preview-details d-none">
                                <span>
                                    <span>@lang('Charge'):</span>
                                    <span class="text--base">{{ gs('cur_sym') }}<span class="charge ">0</span></span>,
                                </span>
                                <span>
                                    <span>@lang('Total Amount'): </span> <span class="text--base">{{ gs('cur_sym') }}<span
                                            class="payable ">
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
                        @lang('Paid Now')
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
            $("body").on('click', '#installmentBtn', function() {
                let action = $(this).data().action;
                let invest = $(this).data().invest;
                var modal = $('#installmentModal');
                modal.find('input[name="installment_amount"]').val(
                    {{ getAmount(@$nextInstallment->invest->per_installment_amount) }});
                modal.find('form').attr('action', action);
                modal.modal('show');
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
        .installment-modal .form-check-input:focus {
            border-color: hsl(var(--base));
            box-shadow: none;
        }

        .installment-modal .form-check-input:checked {
            background-color: hsl(var(--base));
            border: 1px solid hsl(var(--base));
        }

        .installment-modal .form-check-input {
            border: 1px solid hsl(var(--base));
        }

        .installment-modal .modal-form__footer button.active {
            border-color: hsl(var(--base));
            position: relative;
        }


        .installment-modal .modal-form__footer button .active-badge {
            display: none;
        }

        .installment-modal .modal-form__footer button.active .active-badge {
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

        .installment-modal .btn-outline--base:hover,
        .installment-modal .btn-outline--base:focus .installment-modal .btn-outline--base:focus-visible {
            background-color: hsl(var(--base) / 0.05) !important;
            border: 1px solid hsl(var(--base)) !important;
            color: hsl(var(--base)) !important;
        }

        .installment-modal .selected-gateway {
            border-color: hsl(var(--base)/0.32) !important;
        }

        .preview-details {
            font-size: 0.875rem;
        }
    </style>
@endpush
