<?php

namespace Biin2013\DcatAdminTools\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class Import
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public array $data
    )
    {
    }
}