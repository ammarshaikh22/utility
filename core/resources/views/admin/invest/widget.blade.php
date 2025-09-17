<div class="card custom--card mb-4">
    <div class="card-body">
        <div class="widget-card-inner">
            <div class="widget-card bg--primary">
                <a href="{{ route('admin.manage.property.invested') }}" class="widget-card-link"></a>
                <div class="widget-card-left">
                    <div class="widget-card-icon">
                        <i class="fas fa-city"></i>
                    </div>
                    <div class="widget-card-content">
                        <h6 class="widget-card-amount">{{ $investStatistics['total_invested_property'] }}</h6>
                        <p class="widget-card-title">@lang('Invested Property')</p>
                    </div>
                </div>
                <span class="widget-card-arrow">
                    <i class="las la-angle-right"></i>
                </span>
            </div>
            <div class="widget-card bg--warning">
                <a href="{{ route('admin.invest.running') }}" class="widget-card-link"></a>
                <div class="widget-card-left">
                    <div class="widget-card-icon">
                        <i class="fas fa-spinner"></i>
                    </div>
                    <div class="widget-card-content">
                        <h6 class="widget-card-amount">{{ showAmount($investStatistics['running_invest_amount']) }}</h6>
                        <p class="widget-card-title">@lang('Running Investment')</p>
                    </div>
                </div>
                <span class="widget-card-arrow">
                    <i class="las la-angle-right"></i>
                </span>
            </div>
            <div class="widget-card bg--success">
                <a href="{{ route('admin.manage.property.invested') }}" class="widget-card-link"></a>
                <div class="widget-card-left">
                    <div class="widget-card-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="widget-card-content">
                        <h6 class="widget-card-amount">{{ showAmount($investStatistics['completed_invest_amount']) }}
                        </h6>
                        <p class="widget-card-title">@lang('Completed Investment')</p>
                    </div>
                </div>
                <span class="widget-card-arrow">
                    <i class="las la-angle-right"></i>
                </span>
            </div>
            <div class="widget-card bg--dark">
                <a href="{{ route('admin.deposit.initiated') }}" class="widget-card-link"></a>
                <div class="widget-card-left">
                    <div class="widget-card-icon">
                        <i class="fas fa-list"></i>
                    </div>
                    <div class="widget-card-content">
                        <h6 class="widget-card-amount">{{ showAmount($investStatistics['all_invest_amount']) }}</h6>
                        <p class="widget-card-title">@lang('Total Investment')</p>
                    </div>
                </div>
                <span class="widget-card-arrow">
                    <i class="las la-angle-right"></i>
                </span>
            </div>
        </div>
    </div>
</div>
