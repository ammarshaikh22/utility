<?php

namespace App\Observers;

use App\Models\User;
use App\Models\Order;
use App\Models\OrderItems;
use App\Models\Notification;
use App\Events\NewOrderEvent;
use App\Models\CompanyAddress;
use App\Models\OrderItemImage;
use App\Events\OrderUpdatedEvent;
use App\Models\OrderCart;
use App\Scopes\ActiveScope;
use App\Traits\UnitTypeSaveTrait;
use App\Traits\EmployeeActivityTrait;

class OrderObserver
{
    use UnitTypeSaveTrait;
    use EmployeeActivityTrait;

    // Before creating an order, set company, order numbers, and related attributes
    public function creating(Order $order)
    {
        if (company()) {
            $order->added_by = user()->id;
            $order->company_id = company()->id;
        }

        // Auto-generate order number if not valid
        if (is_numeric($order->order_number) || is_null($order->order_number)) {
            $order->order_number = $order->formatOrderNumber();
        }

        $order->custom_order_number = $order->order_number;

        // Extract original order number without prefix/format
        $orderSettings = (company()) ? company()->invoiceSetting : $order->company->invoiceSetting;
        $order->original_order_number = str($order->order_number)->replace(
            $orderSettings->order_prefix . $orderSettings->order_number_separator,
            ''
        );
    }

    // After order is created, log activity, add items, and send notifications
    public function created(Order $order)
    {
        // Log employee activity
        if (user()) {
            self::createEmployeeActivity(user()->id, 'order-created', $order->id, 'order');
        }

        // Skip if running in console or seeding
        if (isRunningInConsoleOrSeeding()) {
            return true;
        }

        // Save order items if provided in request
        if (!empty(request()->item_name)) {
            $itemsId = request()->item_ids;
            $itemsSummary = request()->item_summary;
            $cost_per_item = request()->cost_per_item;
            $hsn_sac_code = request()->hsn_sac_code;
            $quantity = request()->quantity;
            $unitId = request()->unit_id;
            $productId = request()->product_id;
            $amount = request()->amount;
            $tax = request()->taxes;
            $sku = request()->sku;
            $invoice_item_image_url = request()->invoice_item_image_url;

            foreach (request()->item_name as $key => $item) :
                if (!is_null($item)) {
                    $orderItem = OrderItems::create([
                        'order_id' => $order->id,
                        'item_name' => $item,
                        'item_summary' => $itemsSummary[$key] ?: '',
                        'type' => 'item',
                        'unit_id' => (isset($unitId[$key])) ? $unitId[$key] : null,
                        'product_id' => (isset($productId[$key])) ? $productId[$key] : null,
                        'hsn_sac_code' => (isset($hsn_sac_code[$key])) ? $hsn_sac_code[$key] : null,
                        'quantity' => $quantity[$key],
                        'unit_price' => round($cost_per_item[$key], 2),
                        'amount' => round($amount[$key], 2),
                        'taxes' => ($tax ? (array_key_exists($key, $tax) ? json_encode($tax[$key]) : null) : null),
                        'sku' => (isset($sku[$key])) ? $sku[$key] : null,
                        'field_order' => $key + 1
                    ]);

                    // Save order item image if provided
                    if (isset($invoice_item_image_url[$key])) {
                        OrderItemImage::create([
                            'order_item_id' => $orderItem->id,
                            'external_link' => $invoice_item_image_url[$key] ?? ''
                        ]);
                    }
                }

                // Remove from order cart if added by a client
                if (in_array('client', user_roles())) {
                    OrderCart::where('client_id', user()->id)
                        ->where('product_id', $itemsId[$key])
                        ->delete();
                }
            endforeach;
        }

        // Notify client when order is sent
        $notifyUser = User::withoutGlobalScope(ActiveScope::class)->findOrFail($order->client_id);

        if (request()->type && request()->type == 'send') {
            event(new NewOrderEvent($order, $notifyUser));
        }
    }

    // Before saving, ensure company address is set
    public function saving(Order $order)
    {
        if (!isRunningInConsoleOrSeeding()) {
            if (is_null($order->company_address_id)) {
                $defaultCompanyAddress = CompanyAddress::where('is_default', 1)->first();
                $order->company_address_id = $defaultCompanyAddress->id;
            }
        }
    }

    // After updating order, log activity and notify client if important fields changed
    public function updated(Order $order)
    {
        if (user()) {
            self::createEmployeeActivity(user()->id, 'order-updated', $order->id, 'order');
        }

        if (
            ($order->isDirty('order_date')
                || $order->isDirty('sub_total')
                || $order->isDirty('total')
                || $order->isDirty('status')
                || $order->isDirty('currency_id')
                || $order->isDirty('show_shipping_address')
                || $order->isDirty('note')
                || $order->isDirty('last_updated_by'))
            && $order->added_by != null
        ) {
            $clientId = $order->client_id ?: $order->added_by;

            // Notify client
            $notifyUser = User::withoutGlobalScope(ActiveScope::class)->findOrFail($clientId);
            event(new OrderUpdatedEvent($order, $notifyUser));
        }
    }

    // Before deleting, remove related unread notifications
    public function deleting(Order $order)
    {
        $notificationModel = ['App\Notifications\NewOrder'];

        Notification::whereIn('type', $notificationModel)
            ->whereNull('read_at')
            ->where(function ($q) use ($order) {
                $q->where('data', 'like', '{"id":' . $order->id . ',%');
                $q->orWhere('data', 'like', '%,"task_id":' . $order->id . ',%');
            })->delete();
    }

    // After deleting, log employee activity
    public function deleted(Order $order)
    {
        if (user()) {
            self::createEmployeeActivity(user()->id, 'order-deleted');
        }
    }
}
