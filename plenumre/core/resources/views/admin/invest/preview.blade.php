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
                                    <th>@lang('User')</th>
                                    <th>@lang('Invest Id')</th>
                                    <th>@lang('Profit Amount')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse(@$profits as $profit)
                                    <tr>
                                        <td>
                                            <span class="fw-bold">{{ $profit->user->fullname }}</span><br>
                                            <span class="small">
                                                <a href="{{ route('admin.users.detail', $profit->user->id) }}">
                                                    <span>@</span>{{ $profit->user->username }}
                                                </a>
                                            </span>
                                        </td>
                                        <td><span class="fw-bold">{{ $profit->invest->investment_id }}</span></td>
                                        <td><span class="fw-bold">{{ showAmount($profit->profit_amount) }}</span></td>
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
            </div>
        </div>
    </div>
@endsection

@push('breadcrumb-plugins')
    <x-back route="{{ route('admin.invest.profit.pending') }}" />
@endpush

@push('script')
    <script>
        (function($) {
            "use strict";

        })(jQuery);
    </script>
@endpush
