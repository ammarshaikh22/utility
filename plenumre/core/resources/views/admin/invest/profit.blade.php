@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive--md table-responsive">
                        <table class="table table--light style--two">
                            <thead>
                                <tr>
                                    @if (request()->routeIs('admin.invest.profit'))
                                        <th>@lang('User')</th>
                                    @endif
                                    <th>@lang('Property')</th>
                                    @if (request()->routeIs('admin.invest.profit'))
                                        <th>@lang('Invest Id')</th>
                                        <th>@lang('Invest Amount')</th>
                                        <th>@lang('TRX')</th>
                                    @endif
                                    <th>@lang('Profit Amount')</th>
                                    <th>@lang('Paid Date')</th>
                                    @if (!request()->routeIs('admin.invest.profit'))
                                        <th>@lang('Total Investor')</th>
                                        <th>@lang('Action')</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse(@$profitList as $profit)
                                    <tr>
                                        @if (request()->routeIs('admin.invest.profit'))
                                            <td>
                                                <span class="fw-bold">{{ $profit->user->fullname }}</span><br>
                                                <span class="small">
                                                    <a href="{{ route('admin.users.detail', $profit->user->id) }}">
                                                        <span>@</span>{{ $profit->user->username }}
                                                    </a>
                                                </span>
                                            </td>
                                        @endif
                                        <td><span class="fw-bold">{{ $profit->invest->property->title }}</span></td>
                                        @if (request()->routeIs('admin.invest.profit'))
                                            <td><span class="fw-bold">{{ $profit->invest->investment_id }}</span></td>
                                            <td>
                                                <span class="fw-bold">
                                                    {{ showAmount($profit->invest->total_invest_amount) }}
                                                </span>
                                            </td>
                                            <td>{{ $profit->transaction->trx }}</td>
                                        @endif
                                        <td>
                                            @if ($profit->amount > 0)
                                                {{ showAmount($profit->amount) }}
                                            @else
                                                {{ $profit->property->getProfit }}
                                            @endif
                                        </td>
                                        <td>{{ showDateTime($profit->updated_at) }}</td>
                                        @if (!request()->routeIs('admin.invest.profit'))
                                            <td>{{ $profit->total_investor }}</td>
                                            <td>
                                                <button class="btn btn-outline--primary btn--sm dischargeBtn"
                                                    data-action="{{ route('admin.invest.profit.discharge.preview', $profit->property->id) }}"
                                                    {{-- data-action="{{ route('admin.invest.profit.discharge', $profit->property->id) }}" --}} data-profit="{{ $profit }}">
                                                    <i class="las la-money-bill-wave"></i> @lang('Discharge')
                                                </button>
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-muted text-center" colspan="100%">{{ __($emptyMessage) }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if (@$profitList->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($profitList) }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if (!request()->routeIs('admin.invest.profit'))
        <div class="modal fade" id="dischargeModal">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">@lang('Discharge Investor Profit')</h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <i class="las la-times"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap ps-0">
                                @lang('Property Name')
                                <span class="fw-bold property_name"></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap ps-0">
                                @lang('Profit Type')
                                <span class="fw-bold profit_type"></span>
                            </li>
                            <li
                                class="list-group-item d-flex justify-content-between align-items-center flex-wrap ps-0 profitTypeFixed">
                                @lang('Profit Amount')
                                <span class="fw-bold profit_amount"></span>
                            </li>
                            <li
                                class="list-group-item d-flex justify-content-between align-items-center flex-wrap ps-0 profitTypeRange">
                                @lang('Minimum Profit Amount')
                                <span class="fw-bold minimum_profit_amount"></span>
                            </li>
                            <li
                                class="list-group-item d-flex justify-content-between align-items-center flex-wrap ps-0 profitTypeRange">
                                @lang('Maximum Profit Amount')
                                <span class="fw-bold maximum_profit_amount"></span>
                            </li>
                            <li
                                class="list-group-item d-flex justify-content-between align-items-center flex-wrap ps-0 totalProfitAmount">
                                @lang('Total Profit Amount')
                                <span class="fw-bold total_profit_amount"></span>
                            </li>
                        </ul>
                        <div class="row mt-3">
                            <input type="hidden" name="url" value="">
                            <div class="col-12">
                                <div class="form-group">
                                    <label>@lang('New Profit Amount')</label>
                                    <div class="input-group">
                                        <input type="number" step="any" class="form-control" name="new_profit_amount"
                                            required>
                                        <span class="input-group-text profitAmountType"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn--primary w-100 h-45 previewBtn">@lang('Preview')</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="previewModal">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">@lang('Profit Discharge Preview')</h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <i class="las la-times"></i>
                        </button>
                    </div>
                    <form method="POST">
                        @csrf
                        <div class="modal-body">
                            <ul class="list-group list-group-flush">
                                <li
                                    class="list-group-item d-flex justify-content-between align-items-center flex-wrap ps-0">
                                    @lang('Property Name')
                                    <span class="fw-bold property_name"></span>
                                </li>
                                <li
                                    class="list-group-item d-flex justify-content-between align-items-center flex-wrap ps-0">
                                    @lang('Profit Type')
                                    <span class="fw-bold profit_type"></span>
                                </li>
                                <li
                                    class="list-group-item d-flex justify-content-between align-items-center flex-wrap ps-0 profitTypeFixed">
                                    @lang('Profit Amount')
                                    <span class="fw-bold profit_amount"></span>
                                </li>
                                <li
                                    class="list-group-item d-flex justify-content-between align-items-center flex-wrap ps-0 profitTypeRange">
                                    @lang('Minimum Profit Amount')
                                    <span class="fw-bold minimum_profit_amount"></span>
                                </li>
                                <li
                                    class="list-group-item d-flex justify-content-between align-items-center flex-wrap ps-0 profitTypeRange">
                                    @lang('Maximum Profit Amount')
                                    <span class="fw-bold maximum_profit_amount"></span>
                                </li>
                                <li
                                    class="list-group-item d-flex justify-content-between align-items-center flex-wrap ps-0">
                                    @lang('New Profit')
                                    <span class="fw-bold new_profit"></span>
                                </li>
                                <li
                                    class="list-group-item d-flex justify-content-between align-items-center flex-wrap ps-0">
                                    @lang('Per Share Amount')
                                    <span class="fw-bold per_share_amount"></span>
                                </li>
                                <li
                                    class="list-group-item d-flex justify-content-between align-items-center flex-wrap ps-0">
                                    @lang('Profit Amount')
                                    <span class="fw-bold new_profit_amount"></span>
                                </li>
                                <li
                                    class="list-group-item d-flex justify-content-between align-items-center flex-wrap ps-0">
                                    @lang('Number of pending profits')
                                    <span class="fw-bold pending_profit_count"></span>
                                </li>
                                <li
                                    class="list-group-item d-flex justify-content-between align-items-center flex-wrap ps-0">
                                    @lang('Total Discharge Amount')
                                    <span class="fw-bold discharge_amount"></span>
                                </li>
                            </ul>
                            <div class="row mt-3">
                                <input type="hidden" name="new_profit_amount" value="">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn--primary w-100 h-45">@lang('Discharge')</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('breadcrumb-plugins')
    <x-search-form placeholder="Search" />
@endpush

@push('script')
    <script>
        (function($) {
            "use strict";

            let modal = $('#dischargeModal');
            let previewModal = $('#previewModal');
            let property = '';
            let data = '';
            let profit = '';
            let profitAmountType = '%';
            let currencyText = `{{ __(gs('cur_text')) }}`;

            $('.dischargeBtn').on('click', function() {
                data = $(this).data();
                property = data.profit.property;
                profit = data.profit;

                modal.find('[name=url]').val(data.action);
                modal.find('.property_name').text(property.title);

                if (property.profit_amount_type == {{ Status::PROFIT_AMOUNT_TYPE_FIXED }}) {
                    profitAmountType = '{{ __(gs('cur_text')) }}';
                }else{
                    profitAmountType = '%';
                }
                modal.find('.profitAmountType').text(profitAmountType);

                if (property.profit_type == {{ Status::PROFIT_TYPE_FIXED }}) {
                    modal.find('.profit_type').text('Fixed');
                    $('.profitTypeFixed').removeClass('d-none');
                    $('.profitTypeRange').addClass('d-none');
                    modal.find('.profit_amount').html(Number(property.profit_amount) + ' ' + profitAmountType);
                    $('input[name=new_profit_amount]').val('');
                    $('input[name=new_profit_amount]').val(Number(property.profit_amount))
                    $('.total_profit_amount').text(Number(property.profit_amount) * Number(profit
                        .total_investor) + ' ' + profitAmountType)
                } else {
                    modal.find('.profit_type').text('Range');
                    $('.profitTypeFixed').addClass('d-none');
                    $('.profitTypeRange').removeClass('d-none');
                    $('input[name=new_profit_amount]').val('');
                    modal.find('.maximum_profit_amount').html(Number(property.maximum_profit_amount) + ' ' +
                        profitAmountType);
                    modal.find('.minimum_profit_amount').html(Number(property.minimum_profit_amount) + ' ' +
                        profitAmountType);
                    $('.totalProfitAmount').addClass('d-none');
                }
                modal.modal('show');
            });

            modal.find('.previewBtn').on('click', function(e) {
                e.preventDefault();
                let url = modal.find('[name=url]').val();
                let newProfitAmount = modal.find('[name=new_profit_amount]').val();
                let formData = {
                    new_profit_amount: newProfitAmount,
                    _token: "{{ csrf_token() }}"
                };
                $.ajax({
                    url: url,
                    type: "POST",
                    data: formData,
                    success: function(response) {
                        modal.modal('hide');
                        previewModal.find('form').attr('action', response.submitUrl);
                        previewModal.find('.profitAmountType').text(profitAmountType);
                        previewModal.find('.property_name').text(property.title);
                        if (property.profit_type == {{ Status::PROFIT_TYPE_FIXED }}) {
                            previewModal.find('.profit_type').text('Fixed');
                            $('.profitTypeFixed').removeClass('d-none');
                            $('.profitTypeRange').addClass('d-none');
                            previewModal.find('.profit_amount').html(Number(property.profit_amount) + ' ' +
                                profitAmountType);
                            $('input[name=new_profit_amount]').val('');
                            $('input[name=new_profit_amount]').val(Number(property.profit_amount))
                            $('.total_profit_amount').text(Number(property.profit_amount) * Number(
                                profit
                                .total_investor) + ' ' + profitAmountType)
                        } else {
                            previewModal.find('.profit_type').text('Range');
                            $('.profitTypeFixed').addClass('d-none');
                            $('.profitTypeRange').removeClass('d-none');
                            $('input[name=new_profit_amount]').val('');
                            previewModal.find('.maximum_profit_amount').html(Number(property
                                    .maximum_profit_amount) + ' ' +
                                profitAmountType);
                            previewModal.find('.minimum_profit_amount').html(Number(property
                                    .minimum_profit_amount) + ' ' +
                                profitAmountType);
                            $('.totalProfitAmount').addClass('d-none');
                        }
                        previewModal.find('[name=new_profit_amount]').val(newProfitAmount);
                        previewModal.find('.new_profit').text(`${newProfitAmount} ${profitAmountType}`);
                        previewModal.find('.per_share_amount').text(`${Number(property.per_share_amount).toFixed(2)} ${currencyText}`);
                        previewModal.find('.new_profit_amount').text(response.profitsAmount);
                        previewModal.find('.pending_profit_count').text(response.totalProfitCount);
                        previewModal.find('.discharge_amount').text(response.totalProfitAmount);
                        previewModal.modal('show');
                    }
                });
            });
        })(jQuery);
    </script>
@endpush
