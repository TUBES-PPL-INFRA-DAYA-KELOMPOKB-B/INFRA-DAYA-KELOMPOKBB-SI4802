<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class PortalWargaLayout extends Component
{
    public function __construct(
        public ?string $title = null,
        public ?string $header = null
    ) {}

    public function render(): View
    {
        return view('layouts.portal-warga');
    }
}

