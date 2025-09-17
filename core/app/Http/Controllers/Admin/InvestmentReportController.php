<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Invest;
use App\Models\Profit;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class InvestmentReportController extends Controller
{
    public function dashboard()
    {
        $pageTitle = 'Investment Statistics';

        $widget['total_invest'] = Invest::sum('total_invest_amount');
        $widget['paid_invest']  = Invest::sum('paid_amount');
        $widget['due_invest']   = Invest::sum('due_amount');

        $widget['total_should_pay'] = Invest::sum('should_pay');
        $widget['profit_paid'] = Profit::success()->withoutProfitScheduleLifetime()->sum('amount');

        $profitByProperty = Profit::groupBy('property_id')->selectRaw("SUM(amount) as amount , property_id")->with('property')->orderByDesc('amount')->get();
        $totalProfit = $profitByProperty->sum('amount');
        $profitByProperty = $profitByProperty->mapWithKeys(function ($profit) {
            return [
                $profit->property->title => (float) $profit->amount,
            ];
        });

        $widget['running_investment']   = Invest::running()->sum('total_invest_amount');
        $widget['completed_investment'] = Invest::completed()->sum('total_invest_amount');
        $widget['profit']               = Profit::success()->sum('amount');

        $propertyInvests = Invest::with('property')->groupBy('property_id')->selectRaw("SUM(total_invest_amount) as amount, property_id")->orderByDesc('amount')->get();
        $investAmount = $propertyInvests->mapWithKeys(function ($invest) {
            return [
                $invest->property->title => (float) $invest->amount,
            ];
        });
        $recentInvest = Invest::with('property')->orderByDesc('id')->first();

        return view('admin.invest.statistics', compact('pageTitle', 'widget', 'profitByProperty', 'totalProfit', 'propertyInvests', 'investAmount', 'recentInvest'));
    }

    public function statistics(Request $request)
    {
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $diffInDays = $startDate->diffInDays($endDate);

        $groupBy = $diffInDays > 30 ? 'months' : 'days';
        $format = $diffInDays > 30 ? '%M-%Y'  : '%d-%M-%Y';

        if ($groupBy == 'days') {
            $dates = getAllDates($startDate, $endDate);
        } else {
            $dates = getAllMonths($startDate, $endDate);
        }

        $invests = Invest::whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->selectRaw('SUM(total_invest_amount) AS amount')
            ->selectRaw("DATE_FORMAT(created_at, '{$format}') as created_on")
            ->latest()
            ->groupBy('created_on')
            ->get();

        $totalInvest = $invests->sum('amount');

        $prevTime = Carbon::parse($startDate)->subDays($diffInDays);
        $prevInvest = Invest::where('created_at', '>=', $prevTime)->where('created_at', '<', $startDate)->sum('total_invest_amount');
        $investDiff = ($prevInvest ? $totalInvest / $prevInvest * 100 - 100 : 0);
        if ($investDiff > 0) {
            $upDown = 'up';
        } else {
            $upDown = 'down';
        }

        $data = [];

        foreach ($dates as $date) {
            $data[] = [
                'created_on' => $date,
                'invests' => getAmount($invests->where('created_on', $date)->first()?->amount ?? 0)
            ];
        }

        $data = collect($data);

        $report['created_on']   = $data->pluck('created_on');
        $report['data']     = [
            [
                'data' => $data->pluck('invests')
            ]
        ];
        $report['totalInvest'] = showAmount($totalInvest);
        $report['investDiff'] = round(abs($investDiff), 2);
        $report['upDown'] = $upDown;

        return response()->json($report);
    }

    public function investProfitStatistics(Request $request)
    {
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $diffInDays = $startDate->diffInDays($endDate);

        $groupBy = $diffInDays > 30 ? 'months' : 'days';
        $format = $diffInDays > 30 ? '%M-%Y'  : '%d-%M-%Y';

        if ($groupBy == 'days') {
            $dates = getAllDates($startDate, $endDate);
        } else {
            $dates = getAllMonths($startDate, $endDate);
        }

        $invests   = Invest::whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->selectRaw('SUM(total_invest_amount) AS amount')
            ->selectRaw("DATE_FORMAT(created_at, '{$format}') as created_on")
            ->latest()
            ->groupBy('created_on')
            ->get();

        $profits  = Profit::whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->selectRaw('SUM(amount) AS amount')
            ->selectRaw("DATE_FORMAT(created_at, '{$format}') as created_on")
            ->latest()
            ->groupBy('created_on')
            ->get();

        $data = [];

        foreach ($dates as $date) {
            $data[] = [
                'created_on' => $date,
                'invests' => getAmount($invests->where('created_on', $date)->first()?->amount ?? 0),
                'profits' => getAmount($profits->where('created_on', $date)->first()?->amount ?? 0)
            ];
        }

        $data = collect($data);

        $report['created_on']   = $data->pluck('created_on');
        $report['data']     = [
            [
                'name' => 'Invests',
                'data' => $data->pluck('invests')
            ],
            [
                'name' => 'Profits',
                'data' => $data->pluck('profits')
            ]
        ];

        return response()->json($report);
    }
}
