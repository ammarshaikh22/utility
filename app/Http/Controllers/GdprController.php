<?php

namespace App\Http\Controllers;

use App\Helper\Files;
use App\Helper\Reply;
use App\Models\GdprSetting;
use App\Models\PurposeConsent;
use App\Models\PurposeConsentUser;
use App\Models\User;
use App\Models\RemovalRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class GdprController extends AccountBaseController
{
    protected ?GdprSetting $gdprSetting = null;

    public function __construct()
    {
        parent::__construct();

        $this->pageTitle = 'app.menu.gdpr';
        $this->gdprSetting = GdprSetting::first();

        $this->middleware(function ($request, $next) {
            abort_403(!(
                user()->permission('manage_gdpr_setting') === 'all' ||
                in_array('client', user_roles(), true)
            ));
            return $next($request);
        });
    }

    /**
     * Display the GDPR settings dashboard.
     */
    public function index(Request $request): View|JsonResponse
    {
        $this->user = User::findOrFail($this->user->id);

        $this->consents = PurposeConsent::with([
            'user' => fn($query) => $query
                ->where('client_id', $this->user->id)
                ->orderByDesc('created_at')
        ])->get();

        $this->removalRequest = RemovalRequest::where('user_id', $this->user->id)
            ->where('status', 'pending')
            ->first();

        $tab = $request->get('tab', 'right-to-informed');

        $this->view = match ($tab) {
            'right-to-erasure' => 'gdpr.ajax.right-to-erasure',
            'right-to-data-portability' => 'gdpr.ajax.right-to-data-portability',
            'right-to-access' => 'gdpr.ajax.right-to-access',
            'consent' => 'gdpr.ajax.consent',
            default => 'gdpr.ajax.right-to-informed',
        };

        $this->activeTab = $tab;

        if ($request->ajax()) {
            $html = view($this->view, $this->data)->render();

            return Reply::dataOnly([
                'status' => 'success',
                'html' => $html,
                'title' => $this->pageTitle,
                'activeTab' => $this->activeTab,
            ]);
        }

        return view('gdpr.index', $this->data);
    }

    /**
     * Update client consent preferences.
     */
    public function updateClientConsent(Request $request): JsonResponse
    {
        $consents = $request->input('consent_customer', []);

        foreach ($consents as $consentId => $status) {
            PurposeConsentUser::create([
                'client_id' => $this->user->id,
                'updated_by_id' => $this->user->id,
                'purpose_consent_id' => $consentId,
                'status' => $status,
                'ip' => $request->ip(),
            ]);
        }

        return Reply::success(__('messages.gdprUpdated'));
    }

    /**
     * Update user's GDPR data removal request.
     */
    public function updateConsentBlock(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'consent_block' => 'required|string|max:5000',
        ]);

        $removalRequest = RemovalRequest::firstOrNew([
            'company_id' => company()->id,
            'user_id' => $this->user->id,
            'status' => 'pending',
        ]);

        $removalRequest->fill([
            'name' => $this->user->name,
            'description' => $validated['consent_block'],
        ])->save();

        return Reply::success(__('messages.gdprRequestUpdated'));
    }

    /**
     * Download the user's stored data as a JSON file.
     */
    public function downloadJson(): BinaryFileResponse
    {
        $userData = User::with([
            'clientDetails',
            'attendance',
            'employee',
            'employeeDetail',
            'projects',
            'member',
            'group',
        ])->findOrFail(user()->id);

        $filePath = Files::UPLOAD_FOLDER . '/user.json';
        file_put_contents($filePath, $userData->toJson(JSON_PRETTY_PRINT));

        return response()->download($filePath, 'user.json', [
            'Content-Type' => 'application/json',
        ]);
    }
}
