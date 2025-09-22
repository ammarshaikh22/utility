<?php

namespace App\Events;

// Import necessary classes
use App\Models\CreditNote;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewCreditNoteEvent
{
    // Use necessary traits for event handling
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $creditNote;  // The new credit note instance

    /**
     * Create a new event instance.
     *
     * @param CreditNote $creditNote
     */
    public function __construct(CreditNote $creditNote)
    {
        // Initialize the creditNote property with the provided value
        $this->creditNote = $creditNote;
    }
}
