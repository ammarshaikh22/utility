@extends('admin.layouts.app')
@section('panel')
    <div class="row g-4">
        <div class="col-md-6">
            <div class="row g-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="row g-2 align-items-center">
                                <div class="col-sm-6">
                                    <h5 class="card-title mb-0">@lang('Total Invests')</h5>
                                </div>
                                <div class="col-sm-6 text-sm-end">
                                    <div id="totalInvestDatePicker" class="border p-1 cursor-pointer rounded">
                                        <i class="la la-calendar"></i>&nbsp;
                                        <span></span> <i class="la la-caret-down"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-12">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h3><span class="total_invest"></span></h3>
                                        <p class="up_down"></p>
                                    </div>
                                </div>
                            </div>
                            <div id="totalInvestsChartArea"></div>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-6">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="card-title m-0">@lang('Investments')</h5>
                        </div>
                        <div class="card-body">
                            <div class="card-container">
                                <div class="investments-scheme">
                                    <div class="investments-scheme-item">
                                        <p class="mb-0">@lang('Total Amount')</p>
                                        <h3 class="mb-6">{{ showAmount($widget['total_invest']) }}</h3>
                                    </div>
                                    <div class="investments-scheme-arrow">
                                        <div class="text-end">
                                            <i class="las la-arrow-down text--success"
                                                style="transform: rotate(30deg);"></i>
                                        </div>
                                        <div class="text-start">
                                            <i class="las la-arrow-down text--success"
                                                style="transform: rotate(-30deg);"></i>
                                        </div>
                                    </div>
                                    <div class="investments-scheme-group">
                                        <div class="investments-scheme-content">
                                            <p class="font-12">@lang('Paid Amount')</p>
                                            <h3 class="deposit-amount text--success counter">
                                                {{ showAmount($widget['paid_invest']) }}
                                            </h3>
                                            <p class="mb-0 font-12">
                                                <i class="feather icon-users"></i>
                                                <strong>
                                                    {{ showAmount(($widget['paid_invest'] / $widget['total_invest']) * 100, currencyFormat: false) }}%
                                                </strong>
                                            </p>
                                        </div>
                                        <div class="investments-scheme-content">
                                            <p class="font-12">@lang('Due Amount')</p>
                                            <h3 class="deposit-amount text--danger counter">
                                                {{ showAmount($widget['due_invest']) }}
                                            </h3>
                                            <p class="mb-0 font-12"><i class="feather icon-users"></i>
                                                <strong>
                                                    {{ showAmount(($widget['due_invest'] / $widget['total_invest']) * 100, currencyFormat: false) }}%
                                                </strong>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-6">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="card-title m-0">@lang('Profit To Pay')</h5>
                        </div>
                        <div class="card-body">
                            <div class="card-container">
                                <div class="row align-items-center pb-3 pb-xxl-0">
                                    <div class="col-6">
                                        <p>@lang('Should Pay')</p>
                                        <h3 class="deposit-amount">
                                            {{ showAmount($widget['total_should_pay'] - $widget['profit_paid']) }}</h3>
                                    </div>
                                    <div class="col-6 text-end">
                                        <a href="{{ route('admin.invest.profit') }}" class="btn btn--primary"
                                            target="_blank">
                                            @lang('History')
                                        </a>
                                    </div>
                                </div>
                                <div class="progress-info">
                                    <div class="progress-info-content">
                                        <p>@lang('Paid')
                                            ({{ showAmount(($widget['profit_paid'] / $widget['total_should_pay']) * 100, currencyFormat: false) }}%)
                                        </p>
                                    </div>
                                    <div class="progress-info-content">
                                        <p>
                                            {{ showAmount($widget['profit_paid']) }} /
                                            {{ showAmount($widget['total_should_pay']) }}
                                        </p>
                                    </div>
                                </div>
                                <div class="progress mb-2 my-progressbar">
                                    <div class="progress-bar" role="progressbar"
                                        style="width: {{ showAmount(($widget['profit_paid'] / $widget['total_should_pay']) * 100, currencyFormat: false) }}%;">
                                    </div>
                                </div>

                                <p class="font-12 mb-0">*@lang('This statistics showing data excluding lifetime investment.')</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="card h-100">
                        <div class="card-header">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h5 class="card-title mb-0">@lang('Profit Statistics by Property')</h5>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <div class="chart-info">
                                    <a href="#" class="chart-info-toggle">
                                        <img src="{{ asset('assets/images/collapse.svg') }}" alt="image"
                                            class="chart-info-img">
                                    </a>
                                    <div class="chart-info-content">
                                        <ul class="chart-info-list">
                                            @foreach (@$profitByProperty ?? [] as $key => $profit)
                                                <li class="chart-info-list-item">
                                                    <i class="fas fa-plane planPointInterest me-2"></i>
                                                    {{ __($key) }}
                                                    <strong class="ms-1">
                                                        {{ showAmount(($profit / $totalProfit) * 100, currencyFormat: false) }}%
                                                    </strong>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                                <div class="chart-area">
                                    <canvas id="profit_by_property" height="250" class="chartjs-chart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="row g-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="row align-items-center">
                                <div class="col-12">
                                    <h5 class="card-title mb-0">@lang('Invest & Profit')</h5>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="interest-scheme">
                                <div class="interest-scheme__content">
                                    <p class="mb-0">@lang('Running Invest')</p>
                                    <h5 class="mb-1 text-warning">{{ showAmount($widget['running_investment']) }}</h5>
                                    <p class="mb-0">
                                        <a href="{{ route('admin.invest.running') }}"
                                            class="btn btn-sm btn-warning-rgba font-12 px-2">
                                            @lang('History')
                                        </a>
                                    </p>
                                </div>
                                <div class="interest-scheme__content text-sm-center">
                                    <p class="mb-0 font-12">@lang('Completed Invest')</p>
                                    <h5 class="mb-1 text--success counter">
                                        {{ showAmount($widget['completed_investment']) }}</h5>
                                    <p class="mb-0">
                                        <a href="{{ route('admin.invest.completed') }}"
                                            class="btn btn-sm btn-success-rgba font-12 px-2">
                                            @lang('History')
                                        </a>
                                    </p>
                                </div>
                                <div class="interest-scheme__content text-sm-end">
                                    <p class="mb-0 font-12">@lang('Profit')</p>
                                    <h5 class="mb-1 text--primary interests">{{ showAmount($widget['profit']) }}</h5>
                                    <p class="mb-0">
                                        <a href="{{ route('admin.invest.profit') }}"
                                            class="btn btn-sm btn-primary-rgba font-12 px-2 speedUp">
                                            @lang('History')
                                        </a>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">@lang('Invest Statistics by Property')</h5>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <div class="chart-info">
                                    <a href="#" class="chart-info-toggle">
                                        <img src="{{ asset('assets/images/collapse.svg') }}" alt="image"
                                            class="chart-info-img">
                                    </a>
                                    <div class="chart-info-content">
                                        <ul class="chart-info-list plan-info-data">
                                            @foreach ($propertyInvests as $propertyInvest)
                                                <li class="chart-info-list-item">
                                                    <i class="fas fa-plane planPoint me-2"></i>
                                                    {{ showAmount(($propertyInvest->amount * 100) / $widget['total_invest'], currencyFormat: false) }}%
                                                    -
                                                    {{ __($propertyInvest->property->title) }}
                                                    <a href="{{ route('admin.invest.all') }}?search={{ $propertyInvest->property->title }}"
                                                        target="_blank">
                                                        <i class="las la-info-circle"></i>
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                                <div class="chart-area chart-area--fixed">
                                    <canvas height="250" id="plan_invest_statistics"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="card h-100">
                        <div class="card-header">
                            <div class="d-flex flex-wrap justify-content-between align-items-center">
                                <h5 class="card-title m-0">@lang('Invest & Profit')</h5>
                                <div id="investProfitPicker" class="border p-1 cursor-pointer rounded">
                                    <i class="la la-calendar"></i>&nbsp;
                                    <span></span> <i class="la la-caret-down"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="transactionChartArea"></div>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title mb-0">@lang('Last Investment')</div>
                        </div>
                        <div class="card-body">
                            <div class="plan-list d-flex flex-wrap flex-xxl-column gap-3 gap-xxl-0">
                                <div class="plan-item-two">
                                    <div class="plan-info plan-inner-div">
                                        <div class="plan-name fw-bold">
                                            {{ $recentInvest->property->title }} -
                                            {{ $recentInvest->property->getProfitSchedule }}
                                        </div>
                                        <div class="plan-desc text-end text-xl-start">
                                            @lang('Capital Back'):
                                            @php echo $recentInvest->property->capitalBackStatusBadge @endphp
                                        </div>
                                    </div>
                                    <div class="plan-start plan-inner-div">
                                        <p class="plan-label">@lang('Paid Amount')</p>
                                        <p class="plan-value date">
                                            {{ showAmount($recentInvest->paid_amount) }}
                                        </p>
                                    </div>
                                    <div class="plan-end plan-inner-div">
                                        <p class="plan-label">@lang('Due Amount')</p>
                                        <p class="plan-value date">
                                            {{ showAmount($recentInvest->due_amount) }}
                                        </p>
                                    </div>
                                    <div class="plan-amount plan-inner-div text-end">
                                        <p class="plan-label">@lang('Should Pay')</p>
                                        <p class="plan-value amount">
                                            {{ $recentInvest->should_pay ? showAmount($recentInvest->should_pay) : '---' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script-lib')
    <script src="{{ asset('assets/admin/js/vendor/apexcharts.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/vendor/chart.js.2.8.0.js') }}"></script>
    <script src="{{ asset('assets/admin/js/moment.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/daterangepicker.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/charts.js') }}"></script>
@endpush

@push('style-lib')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/admin/css/daterangepicker.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/admin/css/custom.css') }}">
@endpush

@push('script')
    <script>
        (function($) {
            'use strict';

            let currencyText = `{{ gs('cur_text') }}`;
            let currencySymbol = `{{ gs('cur_sym') }}`;

            // start date-range-picker
            const start = moment().subtract(14, 'days');
            const end = moment();
            const dateRangeOptions = {
                startDate: start,
                endDate: end,
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 15 Days': [moment().subtract(14, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(30, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month')
                        .endOf(
                            'month')
                    ],
                    'Last 6 Months': [moment().subtract(6, 'months').startOf('month'), moment().endOf('month')],
                    'This Year': [moment().startOf('year'), moment().endOf('year')],
                },
                maxDate: moment()
            }

            const changeDatePickerText = (element, startDate, endDate) => {
                $(element).html(startDate.format('MMMM D, YYYY') + ' - ' + endDate.format('MMMM D, YYYY'));
            }
            // end date-range-picker

            // start total invests chart
            let tiChart = barChart(
                document.querySelector("#totalInvestsChartArea"),
                @json(__(gs('cur_text'))),
                [{
                    name: 'Invested',
                    data: []
                }],
                [],
            );

            const totalInvestChart = (startDate, endDate) => {
                const data = {
                    start_date: startDate.format('YYYY-MM-DD'),
                    end_date: endDate.format('YYYY-MM-DD')
                }
                const url = @json(route('admin.invest.report.statistics'));
                $.get(url, data,
                    function(data, status) {
                        if (status == 'success') {
                            $('.total_invest').text(data.totalInvest);
                            let upDown = `<span class="badge badge--dark font-16">0%</span>`;
                            if (data.investDiff != 0) {
                                if (data.upDown == 'up') {
                                    var className = 'success'
                                } else {
                                    var className = 'danger';
                                }
                                upDown =
                                    `<span class="badge badge--${className} font-16">${data.investDiff}%  <i class="las la-arrow-${data.upDown}"></i></span>`;
                            }
                            $('.up_down').html(upDown);
                            tiChart.updateSeries(data.data);
                            tiChart.updateOptions({
                                xaxis: {
                                    categories: data.created_on,
                                }
                            });
                        }
                    }
                );
            }

            $('#totalInvestDatePicker').daterangepicker(dateRangeOptions, (start, end) => changeDatePickerText(
                '#totalInvestDatePicker span', start, end));

            changeDatePickerText('#totalInvestDatePicker span', start, end);

            $('#totalInvestDatePicker').on('apply.daterangepicker', (event, picker) => totalInvestChart(picker
                .startDate, picker.endDate));

            totalInvestChart(start, end);
            // end total invests chart

            // start investment profit chart
            let trxChart = lineChart(
                document.querySelector("#transactionChartArea"),
                [{
                        name: "Investment",
                        data: []
                    },
                    {
                        name: "Profit",
                        data: []
                    }
                ],
                []
            );

            const profitInvestChart = (startDate, endDate) => {
                const data = {
                    start_date: startDate.format('YYYY-MM-DD'),
                    end_date: endDate.format('YYYY-MM-DD')
                }
                const url = @json(route('admin.invest.report.profit.invest'));
                $.get(url, data,
                    function(data, status) {
                        if (status == 'success') {
                            trxChart.updateSeries(data.data);
                            trxChart.updateOptions({
                                xaxis: {
                                    categories: data.created_on,
                                }
                            });
                        }
                    }
                );
            }

            $('#investProfitPicker').daterangepicker(dateRangeOptions, (start, end) => changeDatePickerText(
                '#investProfitPicker span', start, end));
            changeDatePickerText('#investProfitPicker span', start, end);
            $('#investProfitPicker').on('apply.daterangepicker', (event, picker) => profitInvestChart(picker.startDate,
                picker.endDate));
            profitInvestChart(start, end);
            // end investment profit chart

            // start profit statistics by property
            var doughnutChartID = document.getElementById("profit_by_property").getContext('2d');
            var doughnutChart = new Chart(doughnutChartID, {
                type: 'doughnut',
                data: {
                    datasets: [{
                        data: @json($profitByProperty->values()),
                        borderColor: 'transparent',
                        backgroundColor: planColors(),
                    }],
                },
                options: {
                    responsive: true,
                    cutoutPercentage: 75,
                    legend: {
                        position: 'bottom'
                    },
                    title: {
                        display: false,
                        text: 'Chart.js Doughnut Chart'
                    },
                    animation: {
                        animateScale: true,
                        animateRotate: true
                    },
                    tooltips: {
                        callbacks: {
                            label: (tooltipItem, data) =>
                                `${data.datasets[0].data[tooltipItem.index]} ${currencyText}`
                        }
                    }
                }
            });

            var planPointInterests = $('.planPointInterest');
            planPointInterests.each(function(key, planPointInterest) {
                var planPointInterest = $(planPointInterest)
                planPointInterest.css('color', planColors()[key])
            })
            // end profit statistics by property

            // start investment Statistics by property
            var pieChartID = document.getElementById("plan_invest_statistics").getContext('2d');
            var pieChart = new Chart(pieChartID, {
                type: 'pie',
                data: {
                    datasets: [{
                        data: @json($investAmount->values()),
                        borderColor: 'transparent',
                        backgroundColor: planColors()
                    }]
                },
                options: {
                    responsive: true,
                    legend: {
                        display: false
                    },
                    tooltips: {
                        callbacks: {
                            label: (tooltipItem, data) =>
                                `${data.datasets[0].data[tooltipItem.index]} ${currencyText}`
                        }
                    }
                }
            });

            var planPoints = $('.planPoint');
            planPoints.each(function(key, planPoint) {
                var planPoint = $(planPoint)
                planPoint.css('color', planColors()[key])
            })
            // end investment Statistics by property

            function planColors() {
                return [
                    '#ff7675',
                    '#6c5ce7',
                    '#ffa62b',
                    '#ffeaa7',
                    '#D980FA',
                    '#fccbcb',
                    '#45aaf2',
                    '#05dfd7',
                    '#FF00F6',
                    '#1e90ff',
                    '#2ed573',
                    '#eccc68',
                    '#ff5200',
                    '#cd84f1',
                    '#7efff5',
                    '#7158e2',
                    '#fff200',
                    '#ff9ff3',
                    '#08ffc8',
                    '#3742fa',
                    '#1089ff',
                    '#70FF61',
                    '#bf9fee',
                    '#574b90'
                ]
            }

            // start chart toggle
            let chartToggle = $('.chart-info-toggle');
            let chartContent = $(".chart-info-content");
            if (chartToggle || chartContent) {
                chartToggle.each(function() {
                    $(this).on("click", function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        $(this).siblings().toggleClass("is-open");
                    });
                });
                chartContent.each(function() {
                    $(this).on("click", function(e) {
                        e.stopPropagation();
                    });
                });
                $(document).on("click", function() {
                    chartContent.removeClass("is-open");
                });
            }
            // end chart toggle
        })(jQuery)
    </script>
@endpush

@push('style')
    <style>
        .exit-btn {
            padding: 0;
            font-size: 30px;
            line-height: 1;
            color: #5b6e88;
            background: transparent;
            border: none;
            transition: all .3s ease;
        }

        .fa,
        .fa-brands,
        .fa-classic,
        .fa-regular,
        .fa-sharp,
        .fa-solid,
        .fab,
        .far,
        .fas {
            line-height: unset;
        }

        .widget_select {
            padding: 3px 3px;
            font-size: 13px;
        }
    </style>
@endpush
