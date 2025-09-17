<?php

namespace App\Http\Controllers\User;

use App\Models\Invest;
use App\Models\Deposit;
use App\Models\Property;
use App\Constants\Status;
use App\Lib\PropertyInvest;
use App\Models\Installment;
use Illuminate\Http\Request;
use App\Models\GatewayCurrency;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Controller;

class InvestController extends Controller
{
    public function store(Request $request, $id)
    {
        $request->validate([
            'invest_amount' => 'required|numeric|gt:0',
            'method'        => 'required|in:gateway,balance',
        ]);

        try {
            $id = decrypt($id);
        } catch (\Exception $exp) {
            $notify[] = ['error', 'Invalid request'];
            return back()->withNotify($notify);
        }

        $property = Property::where('id', $id)->withCount('invests')->first();

        if (!$property) {
            $notify[] = ['error', 'Invalid Property'];
            return back()->withNotify($notify);
        }

        if ($property->invests_count == $property->total_share) {
            $notify[] = ['error', 'Property is not available for investment'];
            return back()->withNotify($notify);
        }

        $user   = auth()->user();
        $amount = $property->per_share_amount;

        if (
            $property->invest_type == Status::INVEST_TYPE_INSTALLMENT &&
            $request->invest_full_amount != 'true'
        ) {
            $amount = ($property->per_share_amount / 100) * $property->down_payment;
        }

        $isFullAmount = $request->invest_full_amount == 'true' ||
            $property->invest_type == Status::INVEST_TYPE_ONETIME;

        if ($request->method == 'gateway') {
            $investData = [
                'invest_amount'  => $amount,
                'is_full_amount' => $isFullAmount,
                'property_id'    => $property->id,
            ];
            session()->put('invest_data', $investData);
            return to_route('user.invest.gateway.payment');
        }

        if ($amount > $user->balance) {
            $notify[] = ['error', 'You don\'t have sufficient balance'];
            return back()->withNotify($notify);
        }

        if ($isFullAmount) {
            $paymentType = Status::INVEST_TYPE_ONETIME;
        }

        $propertyInvest = new PropertyInvest($property, paymentType: @$paymentType ?? null);
        $invest         = $propertyInvest->invest($amount);

        $notify[] = ['success', 'Property invested successfully'];

        if (isset($paymentType)) {
            return to_route('user.invest.history')->withNotify($notify);
        }

        return to_route('user.invest.installment.details', encrypt($invest->id))->withNotify($notify);
    }

    public function installmentPay(Request $request, $id, $installmentId)
    {
        $request->validate([
            'installment_amount' => 'required|numeric|gt:0',
            'method'             => 'required|in:gateway,balance',
        ]);

        try {
            $id            = decrypt($id);
            $installmentId = decrypt($installmentId);
        } catch (\Exception $exp) {
            $notify[] = ['error', 'Invalid request'];
            return back()->withNotify($notify);
        }

        $user = auth()->user();

        $invest = Invest::where('id', $id)
            ->where('user_id', $user->id)
            ->with([
                'property',
                'installments' => function ($q) use ($installmentId) {
                    $q->where('id', $installmentId)
                        ->where('status', Status::INSTALLMENT_PENDING);
                },
            ])
            ->firstOrFail();

        $nextInstallment = Installment::where('invest_id', $id)
            ->where('status', Status::INSTALLMENT_PENDING)
            ->first();

        if ($invest->installments[0]->id != $nextInstallment->id) {
            $notify[] = ['error', 'Pay previous installment first'];
            return back()->withNotify($notify);
        }

        $installmentAmount = $invest->per_installment_amount + $invest->installments[0]->late_fee;

        if ($installmentAmount != $request->installment_amount) {
            $notify[] = ['error', 'Invalid amount'];
            return back()->withNotify($notify);
        }

        if ($request->method == 'gateway') {
            $investData = [
                'invest_amount'  => $installmentAmount,
                'installment_id' => $installmentId,
                'invest_id'      => $id,
            ];
            session()->put('invest_data', $investData);
            return to_route('user.invest.installment.gateway.payment');
        }

        if ($installmentAmount > $user->balance) {
            $notify[] = ['error', 'You don\'t have sufficient balance'];
            return back()->withNotify($notify);
        }

        $propertyInvest = new PropertyInvest(
            $invest->property,
            $invest,
            $invest->installments[0]
        );
        $invest = $propertyInvest->invest(
            $invest->per_installment_amount,
            $invest->installments[0]->late_fee
        );

        $notify[] = ['success', 'Installment paid successfully'];
        return to_route('user.invest.installment.details', encrypt($id))->withNotify($notify);
    }

    public function installmentDetails($id)
    {
        $pageTitle = 'Installment Details';

        try {
            $id = decrypt($id);
        } catch (\Exception $exp) {
            $notify[] = ['error', 'Invalid request'];
            return back()->withNotify($notify);
        }

        $invest = Invest::where('id', $id)
            ->where('user_id', auth()->id())
            ->with([
                'installments' => function ($query) {
                    $query->latest();
                },
                'property'
            ])
            ->firstOrFail();


        return view('Template::user.invest.installment', compact('pageTitle', 'invest'));
    }

    public function investHistory()
    {
        $pageTitle = 'Invest History';

        $invests = Invest::where('user_id', auth()->id())
            ->with([
                'property',
                'property.profitScheduleTime',
                'installments' => function ($installments) {
                    $installments->where('status', Status::INSTALLMENT_PENDING);
                },
            ])
            ->searchable(['property:title'])
            ->orderByDesc('id')
            ->paginate(getPaginate());

        return view('Template::user.invest.history', compact('pageTitle', 'invests'));
    }

    public function gatewayPaymentInsert(Request $request)
    {

        $request->validate([
            'gateway'  => 'required',
            'currency' => 'required',
        ]);

        $investData    = session()->get('invest_data');
        session()->forget('invest_data');
        $amount        = $investData['invest_amount'];
        $isFullAmount  = $investData['is_full_amount'] ?? 0;
        $propertyId    = $investData['property_id'] ?? 0;
        $investId      = $investData['invest_id'] ?? 0;
        $installmentId = $investData['installment_id'] ?? 0;

        $user = auth()->user();

        $gate = GatewayCurrency::whereHas('method', function ($gate) {
            $gate->where('status', Status::ENABLE);
        })
            ->where('method_code', $request->gateway)
            ->where('currency', $request->currency)
            ->first();

        if (!$gate) {
            $notify[] = ['error', 'Invalid gateway'];
            return back()->withNotify($notify);
        }


        $charge       = $gate->fixed_charge + ($amount * $gate->percent_charge / 100);
        $payable      = $amount + $charge;
        $final_amount = $payable * $gate->rate;

        $data                      = new Deposit();
        $data->user_id             = $user->id;
        $data->property_id         = $propertyId;
        $data->invest_id           = $investId;
        $data->installment_id      = $installmentId;
        $data->payment_type        = $isFullAmount;
        $data->method_code         = $gate->method_code;
        $data->method_currency     = strtoupper($gate->currency);
        $data->amount              = $amount;
        $data->invest_amount       = $amount;
        $data->charge              = $charge;
        $data->rate                = $gate->rate;
        $data->final_amount        = $final_amount;
        $data->btc_amount          = 0;
        $data->btc_wallet          = "";
        $data->trx                 = getTrx();
        $data->success_url         = urlPath('user.invest.history');
        $data->failed_url          = urlPath('user.invest.history');
        $data->save();

        session()->put('Track', $data->trx);
        return to_route('user.deposit.confirm');
    }

    public function gatewayPayment()
    {
        $gatewayCurrency = GatewayCurrency::whereHas('method', function ($gate) {
            $gate->where('status', Status::ENABLE);
        })
            ->with('method')
            ->orderBy('name')
            ->get();

        $pageTitle = 'Payment Method';
        return view('Template::user.payment.deposit', compact('pageTitle', 'gatewayCurrency'));
    }

    public function installmentGatewayPayment()
    {
        $gatewayCurrency = GatewayCurrency::whereHas('method', function ($gate) {
            $gate->where('status', Status::ENABLE);
        })
            ->with('method')
            ->orderBy('name')
            ->get();

        $pageTitle = 'Payment Method';
        return view('Template::user.payment.deposit', compact('pageTitle', 'gatewayCurrency'));
    }

    public function downloadContract($id)
    {
        $user = auth()->user();

        $invest = Invest::where('user_id', $user->id)
            ->with(['property'])
            ->findOrFail($id);

        $signatureData = base64_encode(
            file_get_contents(
                getImage(getFilePath('userSignature') . '/' . ($user->signature ?? ''))
            )
        );
        $signature = 'data:image/png;base64,' . $signatureData;

        $logoData = base64_encode(file_get_contents(siteLogo()));
        $logo     = 'data:image/png;base64,' . $logoData;

        $address = implode(', ', [
            $user->address,
            $user->city,
            $user->state,
            $user->zip,
            $user->country_name,
        ]);

        $contractTemplate = gs('contract_template');
        $contractTemplate = str_replace("{{buyer_name}}", $user->firstname . ' ' . $user->lastname, $contractTemplate);
        $contractTemplate = str_replace("{{buyer_address}}", $address, $contractTemplate);
        $contractTemplate = str_replace("{{buyer_contact_number}}", $user->mobileNumber, $contractTemplate);
        $contractTemplate = str_replace("{{buyer_email}}", $user->email, $contractTemplate);
        $contractTemplate = str_replace("{{property_title}}", $invest->property->title, $contractTemplate);
        $contractTemplate = str_replace("{{profit_schedule}}", $invest->property->getProfitSchedule, $contractTemplate);
        $contractTemplate = str_replace("{{profit_back}}", $invest->property->profit_back . ' days after investment', $contractTemplate);
        $contractTemplate = str_replace("{{total_invest_amount}}", showAmount($invest->total_invest_amount, currencyFormat: false), $contractTemplate);
        $contractTemplate = str_replace("{{investment_id}}", $invest->investment_id, $contractTemplate);
        $contractTemplate = str_replace("{{investment_date}}", showDateTime($invest->created_at, 'Y-m-d'), $contractTemplate);
        $contractTemplate = str_replace("{{signature}}", $signature ?? 'Update your signature', $contractTemplate);
        $contractTemplate = str_replace("{{logo}}", $logo, $contractTemplate);
        $contractTemplate = str_replace("{{site_name}}", gs('site_name'), $contractTemplate);
        $contractTemplate = str_replace("{{site_currency}}", gs('cur_text'), $contractTemplate);
        $contractTemplate = str_replace("{{currency_symbol}}", gs('cur_sym'), $contractTemplate);

        $data = [
            'contractTemplate' => $contractTemplate,
        ];

        $pdf = Pdf::loadView('Template::user.invest.contract_template', $data);
        return $pdf->download($invest->investment_id . '_contract.pdf');
    }
}
