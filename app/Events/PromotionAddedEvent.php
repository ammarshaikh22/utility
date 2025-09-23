<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PromotionAddedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $promotion; // Promotion object/DTO
    public $user;      // Employee (resolved from promotion->employee_id)

    /**
     * @param mixed $promotion Newly created promotion entity
     * @param mixed $type      Optional flag (unused)
     */
    public function __construct($promotion, $type = null)
    {
        $this->promotion = $promotion;
        // Resolve employee once so listeners don’t repeat the lookup
        $this->user = User::where('id', $promotion->employee_id)->first();
    }
}
