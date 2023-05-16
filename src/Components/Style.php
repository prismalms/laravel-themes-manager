<?php

namespace Prisma\ThemesManager\Components;

use Illuminate\View\Component;
use Prisma\ThemesManager\Facades\ThemesManager;

class Style extends Component
{
    /**
     * The style source url.
     *
     * @var string
     */
    public $source;

    /**
     * Create the component instance.
     */
    public function __construct(string $src, bool $absolute = true)
    {
        if (!is_null($src)) {
            $this->source = ThemesManager::asset($src, $absolute);
        } else {
            $this->source = null;
        }
    }

    /**
     * Get the view / contents that represents the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('themes-manager::components.style');
    }
}
