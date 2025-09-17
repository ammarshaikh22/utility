<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ContractTemplateController extends Controller
{
    public function template()
    {
        $pageTitle = 'Contract Template';
        return view('admin.property.contract.template', compact('pageTitle'));
    }

    public function templateStore(Request $request)
    {
        $request->validate([
            'contract_template' => 'required'
        ]);

        $general = gs();
        $general->contract_template = $request->contract_template;
        $general->save();

        $notify[] = ['success', 'Contract template updated successfully'];
        return back()->withNotify($notify);
    }
}
