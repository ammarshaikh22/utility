<?php

namespace App\Lib;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Invest;
use App\Models\Referral;
use App\Constants\Status;
use App\Models\Installment;
use App\Models\Transaction;
use App\Models\AdminNotification;

class PropertyInvest
{
    protected $user;
    protected $property;
    protected $invest;
    protected $installment;
    protected $trx;
    protected $notificationTemplate;
    protected $paymentType;

    public function __construct($property = null, $invest = null, $installment = null, $user = null, $paymentType = null)
    {
        if (!$user) {
            $this->user = auth()->user();
        } else {
            $this->user = $user;
        }
        $this->property    = $property;
        $this->installment = $installment;
        $this->invest      = $invest;
        $this->paymentType = $paymentType;
    }

    public function invest($amount, $lateFee = 0)
    {
        if (!$this->installment) {
            $this->createInvest($amount);
            if (!$this->paymentType && $this->property->invest_type == Status::INVEST_TYPE_INSTALLMENT) {
                $this->createInvestInstallment();
            }
        } else {
            $this->invest->paid_amount += $this->invest->per_installment_amount;
            $this->invest->due_amount  -= $this->invest->per_installment_amount;
            $this->invest->save();

            $this->installment->status    = Status::INSTALLMENT_SUCCESS;
            $this->installment->paid_time = now();
            $this->installment->save();
        }

        $totalInvestedAmount           = $this->property->invested_amount + $amount;
        $this->property->invest_status = Status::PROPERTY_RUNNING;

        if (($this->property->goal_amount - $totalInvestedAmount) <= 0) {
            $this->property->invest_status = Status::PROPERTY_COMPLETED;
        }

        $this->property->invested_amount += $amount;
        $this->property->save();

        if ($this->invest->total_invest_amount <= $this->invest->paid_amount) {
            $this->invest->invest_status    = Status::COMPLETED;
            $this->invest->due_amount       = 0;
            $this->invest->profit_status    = Status::RUNNING;
            $this->invest->next_profit_date = now()->addDays(intval($this->property->profit_back));
        }

        $this->invest->save();
        $this->trx = getTrx();

        $this->createLateFee($lateFee);

        if ($this->installment) {
            $this->installment->save();
            $transactionDetails         = 'Installment payment on ';
            $remark                     = 'installment';
            $this->notificationTemplate = 'INSTALLMENT';
        } else {
            $this->invest->save();
            $transactionDetails         = 'Investment payment on ';
            $remark                     = 'down_payment';
            $this->notificationTemplate = 'DOWN_PAYMENT';
        }

        $this->user->balance -= $amount;
        $this->user->save();

        $transaction               = new Transaction();
        $transaction->user_id      = $this->user->id;
        $transaction->invest_id    = $this->invest->id;
        $transaction->amount       = $amount;
        $transaction->post_balance = $this->user->balance;
        $transaction->charge       = 0;
        $transaction->trx_type     = '-';
        $transaction->details      = $transactionDetails . $this->property->title . ' property';
        $transaction->trx          = $this->trx;
        $transaction->remark       = $remark;
        $transaction->save();

        notify($this->user, $this->notificationTemplate, [
            'trx'             => $this->trx,
            'amount'          => showAmount($amount, currencyFormat: false),
            'property_name'   => $this->property->title,
            'post_balance'    => showAmount($this->user->balance, currencyFormat: false),
            'paid_amount'     => showAmount($this->invest->paid_amount, currencyFormat: false),
            'due_amount'      => showAmount($this->invest->due_amount, currencyFormat: false),
            'invested_amount' => showAmount($this->invest->total_invest_amount, currencyFormat: false),
        ]);

        if ($this->invest->invest_status == Status::COMPLETED) {
            notify($this->user, 'INVESTMENT', [
                'amount'          => showAmount($this->invest->total_invest_amount, currencyFormat: false),
                'property_name'   => $this->property->title,
                'post_balance'    => showAmount($this->user->balance, currencyFormat: false),
                'paid_amount'     => showAmount($this->invest->paid_amount, currencyFormat: false),
                'due_amount'      => showAmount($this->invest->due_amount, currencyFormat: false),
            ]);
        }

        if (gs('invest_commission')) {
            if ($this->user->ref_by) {
                $this->referralCommission('invest_commission', $amount);
            }
        }

        $adminNotification            = new AdminNotification();
        $adminNotification->user_id   = $this->user->id;
        $adminNotification->title     = showAmount($amount) . ' invested to ' . $this->property->title;
        $adminNotification->click_url = '#';
        $adminNotification->save();

        return $this->invest;
    }

    protected function createInvest($amount)
    {
        $perInstallmentAmount = 0;

        if (!$this->paymentType && $this->property->invest_type == Status::INVEST_TYPE_INSTALLMENT) {
            $perInstallmentAmount = $this->property->per_installment_amount;
        }

        $invest                         = new Invest();
        $invest->user_id                = $this->user->id;
        $invest->property_id            = $this->property->id;
        $invest->investment_id          = getTrx(10);
        $invest->total_invest_amount    = $this->property->per_share_amount;
        $invest->initial_invest_amount  = $amount;
        $invest->paid_amount            = $amount;
        $invest->due_amount             = $this->property->per_share_amount - $amount;
        $invest->per_installment_amount = $perInstallmentAmount;
        $invest->should_pay             = $this->shouldPayCalculate() ?? 0;
        $invest->profit_status          = Status::INVESTMENT_RUNNING;
        $invest->save();

        $this->invest = $invest;
    }

    protected function createInvestInstallment()
    {
        $prevInstallment = null;

        for ($i = 0; $i < $this->property->total_installment; $i++) {
            $installment            = new Installment();
            $installment->invest_id = $this->invest->id;

            if ($prevInstallment) {
                $time            = $prevInstallment->next_time;
                $nextInstallment = Carbon::parse($time)->addHours(intval($this->property->installmentDuration->time));
            } else {
                $nextInstallment = Carbon::now()->addHours(intval($this->property->installmentDuration->time));
            }

            $installment->next_time = $nextInstallment;
            $installment->status    = Status::INSTALLMENT_PENDING;
            $installment->save();

            $prevInstallment = $installment;
        }
    }

    public function calProfit($profit, $amount = 0, $dischargePreview = false)
    {
        if ($amount) {
            $profitAmount = $this->getProfitAmount($amount);
        } else {
            $profitAmount = $this->getProfitAmount();
        }

        if ($dischargePreview) {
            return $profitAmount;
        }

        if ($profitAmount) {
            if ($this->property->is_capital_back == Status::CAPITAL_BACK_YES && $this->invest->get_profit_count == 0) {
                $this->user->balance += $this->invest->total_invest_amount;
                $this->user->save();

                $trx = getTrx();

                $transaction               = new Transaction();
                $transaction->user_id      = $this->user->id;
                $transaction->invest_id    = $this->invest->id;
                $transaction->amount       = $this->invest->total_invest_amount;
                $transaction->charge       = 0;
                $transaction->post_balance = $this->user->balance;
                $transaction->trx_type     = '+';
                $transaction->trx          = $trx;
                $transaction->remark       = 'capital_back';
                $transaction->details      = 'Capital back from ' . @$this->invest->property->title . " property investment";
                $transaction->save();

                notify($this->user, 'CAPITAL_BACK', [
                    'trx'          => $transaction->trx,
                    'amount'       => showAmount($this->invest->total_invest_amount, currencyFormat: false),
                    'property_name'    => @$this->invest->property->title,
                    'post_balance' => showAmount($this->user->balance, currencyFormat: false),
                ]);
            }

            $this->invest->get_profit_count += 1;
            $this->invest->total_profit     += $profitAmount;
            $this->invest->save();

            if ($this->property->profit_schedule == Status::PROFIT_ONETIME) {
                $this->invest->profit_status = Status::COMPLETED;
                $this->invest->save();
            } elseif ($this->property->profit_schedule == Status::PROFIT_REPEATED_TIME) {
                if ($this->invest->get_profit_count == $this->property->profit_repeat_time) {
                    $this->invest->profit_status = Status::COMPLETED;
                    $this->invest->save();
                }
            }

            if (($this->property->profit_schedule == Status::PROFIT_REPEATED_TIME || $this->property->profit_schedule == Status::PROFIT_LIFETIME) && $this->invest->profit_status != Status::COMPLETED) {
                $this->invest->next_profit_date = now()->addHours(intval($this->property->profitScheduleTime->time));
                $this->invest->save();
            }

            $this->user->balance += $profitAmount;
            $this->user->save();

            $trx = getTrx();

            $transaction               = new Transaction();
            $transaction->user_id      = $this->user->id;
            $transaction->invest_id    = $this->invest->id;
            $transaction->profit_id    = $profit->id;
            $transaction->amount       = $profitAmount;
            $transaction->charge       = 0;
            $transaction->post_balance = $this->user->balance;
            $transaction->trx_type     = '+';
            $transaction->trx          = $trx;
            $transaction->remark       = 'profit';
            $transaction->details      = showAmount($profitAmount) . ' profit from ' . @$this->invest->property->title . " property investment";
            $transaction->save();

            if (gs('profit_commission') == Status::YES) {
                $commissionType = 'profit_commission';
                $this->referralCommission($commissionType, $profitAmount, $trx);
            }

            notify($this->user, 'PROFIT', [
                'trx'          => $transaction->trx,
                'amount'       => showAmount($profitAmount, currencyFormat: false),
                'property_name'    => @$this->invest->property->title,
                'post_balance' => showAmount($this->user->balance, currencyFormat: false),
            ]);
        }

        return $profitAmount;
    }

    protected function getProfitAmount($amount = 0)
    {
        if ($this->checkProfitType()) {
            if ($this->checkProfitAmountType()) {
                return $amount ? $amount : $this->property->auto_profit_distribution_amount;
            } else {
                return ($this->invest->total_invest_amount / 100) * ($amount ? $amount : $this->property->auto_profit_distribution_amount);
            }
        } else {
            if ($this->checkProfitAmountType()) {
                return $amount ? $amount : $this->property->profit_amount;
            } else {
                return ($this->invest->total_invest_amount / 100) * ($amount ? $amount : $this->property->profit_amount);
            }
        }
    }

    public function shouldPayCalculate()
    {
        $property = $this->property;
        if ($property->profit_schedule == Status::PROFIT_ONETIME) {
            if ($this->checkProfitDistributionType()) {
                $calculateAmount = $this->checkProfitType() ? $property->auto_profit_distribution_amount : $property->profit_amount;
                return $this->getShouldPayAmount($calculateAmount);
            } else {
                $calculateAmount = $this->checkProfitType() ? $property->minimum_profit_amount : $property->profit_amount;
                return $this->getShouldPayAmount($calculateAmount);
            }
        } elseif ($property->profit_schedule == Status::PROFIT_REPEATED_TIME) {
            if ($this->checkProfitDistributionType()) {
                $calculateAmount = $this->checkProfitType() ? $property->auto_profit_distribution_amount : $property->profit_amount;
                return $this->getShouldPayAmount($calculateAmount, $property->profit_repeat_time);
            } else {
                $calculateAmount = $this->checkProfitType() ? $property->minimum_profit_amount : $property->profit_amount;
                return $this->getShouldPayAmount($calculateAmount, $property->profit_repeat_time);
            }
        }

        return 0;
    }

    protected function getShouldPayAmount($calculateAmount, $profitRepeatTime = 1)
    {
        if ($this->checkProfitAmountType()) {
            return $calculateAmount * $profitRepeatTime;
        } else {
            return ($this->property->per_share_amount * $calculateAmount / 100) * $profitRepeatTime;
        }
    }

    protected function checkProfitDistributionType()
    {
        return $this->property->profit_distribution == Status::PROFIT_DISTRIBUTION_AUTO ? true : false;
    }

    protected function checkProfitType()
    {
        return $this->property->profit_type == Status::PROFIT_TYPE_RANGE ? true : false;
    }

    protected function checkProfitAmountType()
    {
        return $this->property->profit_amount_type == Status::PROFIT_AMOUNT_TYPE_FIXED ? true : false;
    }

    protected function createLateFee($lateFee = 0)
    {
        if ($lateFee > 0) {

            $this->user->balance -= $lateFee;
            $this->user->save();

            $trx                       = getTrx();
            $transaction               = new Transaction();
            $transaction->user_id      = $this->user->id;
            $transaction->invest_id    = $this->invest->id;
            $transaction->amount       = $lateFee;
            $transaction->post_balance = $this->user->balance;
            $transaction->charge       = 0;
            $transaction->trx_type     = '-';
            $transaction->details      = 'Installment late fee on ' . $this->property->title . ' property investment';
            $transaction->trx          = $trx;
            $transaction->remark       = 'installment_late_fee';
            $transaction->save();
        }
    }

    public function referralCommission($commissionType, $amount, $trx = null)
    {
        $user      = $this->user;
        $levelInfo = Referral::where('commission_type', $commissionType)->get();
        $level     = 0;

        while (@$user->ref_by && $level < $levelInfo->count()) {
            $user = User::find($user->ref_by);
            $commission = ($levelInfo[$level]->percent / 100) * $amount;
            $user->balance += $commission;
            $user->save();
            $level++;

            $transaction               = new Transaction();
            $transaction->user_id      = $user->id;
            $transaction->amount       = $commission;
            $transaction->post_balance = $user->balance;
            $transaction->charge       = 0;
            $transaction->trx_type     = '+';
            $transaction->details      = 'Level ' . $level . ' Referral Commission From ' . $this->user->username;
            $transaction->trx          =  $trx ?? $this->trx;
            $transaction->remark       = $commissionType;
            $transaction->save();

            if ($commissionType == 'deposit_commission') {
                $comType = 'Deposit';
            } elseif ($commissionType == 'profit_commission') {
                $comType = 'Profit ';
            } else {
                $comType = 'Invest';
            }

            notify($user, 'REFERRAL_COMMISSION', [
                'amount'       => showAmount($commission, currencyFormat: false),
                'post_balance' => showAmount($user->balance, currencyFormat: false),
                'trx'          => $this->trx,
                'level'        => ordinal($level),
                'type'         => $comType,
            ]);
        }
    }
}
