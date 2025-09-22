<?php

namespace App\Events;

// Import necessary classes
use App\Models\File;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FileUploadEvent
{
    // Use necessary traits for event handling
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $file;    // The file that is uploaded
    public $user;    // The user who uploaded the file

    /**
     * Create a new event instance.
     *
     * @param File $file
     * @param mixed $user
     */
    public function __construct(File $file, $user)
    {
        // Initialize the properties with the provided values
        $this->file = $file;
        $this->user = $user;
    }
}
