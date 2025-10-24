<?php

namespace App\Http\Controllers;

use App\DataTables\ConsentDataTable;
use App\DataTables\CustomerDataRemovalDataTable;
use App\DataTables\LeadDataRemovalDataTable;
use App\Helper\Reply;
use App\Http\Requests\Gdpr\CreateRequest;
use App\Models\GdprSetting;
use App\Models\PurposeConsent;
use App\Models\RemovalRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class GdprSettingsController extends AccountBaseController
{
    protected ?GdprSetting $gdprSetting = null;

    public function __construct()
    {
        parent::__construct();

        $this->pageTitle = 'app.menu.gdpr';
        $this->activeSettingMenu = 'gdpr_settings';
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
     * Display GDPR settings main page.
     */
    public function index(Request $request): View|JsonResponse
    {
        $tab = $request->get('tab', 'general');

        $this->view = match ($tab) {
            'right-to-erasure' => 'gdpr-settings.ajax.right-to-erasure',
            'right-to-data-portability' => 'gdpr-settings.ajax.right-to-data-portability',
            'right-to-informed' => 'gdpr-settings.ajax.right-to-informed',
            'right-to-access' => 'gdpr-settings.ajax.right-to-access',
            'consent-settings' => 'gdpr-settings.ajax.consent-settings',
            'consent-lists' => null,
            'removal-requests' => null,
            'removal-requests-lead' => null,
            default => 'gdpr-settings.ajax.general',
        };

        $this->activeTab = $tab;

        return match ($tab) {
            'consent-lists' => $this->consentList(),
            'removal-requests' => $this->removalRequest(),
            'removal-requests-lead' => $this->removalRequestLead(),
            default => $this->renderView($request),
        };
    }

    /**
     * Render GDPR tab views.
     */
    protected function renderView(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            $html = view($this->view, $this->data)->render();

            return Reply::dataOnly([
                'status' => 'success',
                'html' => $html,
                'title' => $this->pageTitle,
                'activeTab' => $this->activeTab,
            ]);
        }

        return view('gdpr-settings.index', $this->data);
    }

    /**
     * Update general GDPR settings.
     */
    public function updateGeneral(Request $request): JsonResponse
    {
        $this->gdprSetting->update($request->all());
        $this->clearCache();

        return Reply::success(__('messages.gdprUpdated'));
    }

    /**
     * Store a new consent purpose.
     */
    public function storeConsent(CreateRequest $request): JsonResponse
    {
        PurposeConsent::create($request->validated());
        $this->clearCache();

        return Reply::success(__('messages.gdprUpdated'));
    }

    /**
     * Update a consent purpose.
     */
    public function updateConsent(CreateRequest $request, int $id): JsonResponse
    {
        $consent = PurposeConsent::findOrFail($id);
        $consent->update($request->validated());

        $this->clearCache();

        return Reply::success(__('messages.gdprUpdated'));
    }

    public function addConsent(): View
    {
        return view('gdpr-settings.create-consent-modal', $this->data);
    }

    public function editConsent(int $id): View
    {
        $this->consent = PurposeConsent::findOrFail($id);

        return view('gdpr-settings.edit-consent-modal', $this->data);
    }

    /**
     * Customer data removal requests.
     */
    public function removalRequest(): View
    {
        $dataTable = new CustomerDataRemovalDataTable();
        $this->activeTab = request('tab', 'removal-requests');

        return $dataTable->render('gdpr-settings.index', $this->data);
    }

    /**
     * Lead data removal requests.
     */
    public function removalRequestLead(): View
    {
        $dataTable = new LeadDataRemovalDataTable();
        $this->activeTab = request('tab', 'removal-requests-lead');

        return $dataTable->render('gdpr-settings.index', $this->data);
    }

    /**
     * Display consent list.
     */
    public function consentList(): View
    {
        $dataTable = new ConsentDataTable();
        $this->activeTab = request('tab', 'consent');

        return $dataTable->render('gdpr-settings.index', $this->data);
    }

    /**
     * Handle bulk actions.
     */
    public function applyQuickAction(Request $request): JsonResponse
    {
        return match ($request->action_type) {
            'delete' => $this->deleteRecords($request),
            default => Reply::error(__('messages.selectAction')),
        };
    }

    protected function deleteRecords(Request $request): JsonResponse
    {
        PurposeConsent::whereIn('id', explode(',', $request->row_ids))->delete();

        return Reply::success(__('messages.deleteSuccess'));
    }

    public function purposeDelete(int $id): JsonResponse
    {
        PurposeConsent::destroy($id);
        $this->clearCache();

        return Reply::success(__('messages.deleteSuccess'));
    }

    /**
     * Approve or reject a client data removal request.
     */
    public function approveRejectClient(int $id, string $type): JsonResponse
    {
        $removal = RemovalRequest::findOrFail($id);
        $removal->update(['status' => $type]);

        try {
            if ($type === 'approved' && $removal->user) {
                $removal->user->delete();
            }
        } catch (\Throwable $e) {
            Log::error('GDPR Client Deletion Failed: ' . $e->getMessage());
        }

        $this->clearCache();

        return Reply::success(__('messages.updateSuccess'));
    }

    /**
     * Approve or reject a lead data removal request.
     */
    public function approveRejectLead(int $id, string $type): JsonResponse
    {
        $removal = \App\Models\RemovalRequestLead::findOrFail($id);
        $removal->update(['status' => $type]);

        try {
            if ($type === 'approved' && $removal->lead) {
                $removal->lead->delete();
            }
        } catch (\Throwable $e) {
            Log::error('GDPR Lead Deletion Failed: ' . $e->getMessage());
        }

        $this->clearCache();

        return Reply::success(__('messages.updateSuccess'));
    }

    /**
     * Clear relevant cache/session keys.
     */
    protected function clearCache(): void
    {
        session()->forget('gdpr_setting');
        cache()->forget('global-setting');
    }
}
