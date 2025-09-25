<?php

namespace App\Observers;

use App\Models\NoticeView;

class NoticeViewObserver
{
    // Before creating a NoticeView, set the company_id
    public function creating(NoticeView $noticeView)
    {
        if (company()) {
            $noticeView->company_id = company()->id;
        }
    }
}
