<?php

namespace App\View\Components;

use Illuminate\View\Component;
use App\Models\User;
use App\Enums\UserType;

class InitialsAvatar extends Component
{
    public $user;
    public $size;
    public $role;
    public $shape;
    public $initials;
    public $bgColor;
    public $textColorClass;
    public $shapeClass;

    /**
     * Create a new component instance.
     *
     * @param  \App\Models\User  $user
     * @param  string  $size    Avatar size: 'xs', 'sm', 'md', 'lg', 'xl'
     * @param  string|null $role Force a specific role (optional)
     * @param  string $shape    'circle', 'square', 'rounded'
     */
    public function __construct($user, $size = 'md', $role = null, $shape = 'circle')
    {
        $this->user = $user;
        $this->size = $size;
        $this->role = $role ?: ($user->currentRole?->name ?? null);
        $this->shape = $shape;

        $this->initials = $this->generateInitials($user->name);
        $this->bgColor = $this->getRoleColor($this->role);
        $this->textColorClass = $this->getTextColorClass($this->bgColor);
        $this->shapeClass = $this->getShapeClass($shape);
    }

    /**
     * Generate initials: first letter of first word + first letter of last word.
     * Fallback to first two letters for single‑word names.
     */
    private function generateInitials($name)
    {
        $name = trim($name);
        $parts = explode(' ', $name);

        if (count($parts) >= 2) {
            return strtoupper(substr($parts[0], 0, 1) . substr($parts[count($parts)-1], 0, 1));
        }
        return strtoupper(substr($name, 0, 2));
    }

    /**
     * Get role‑specific background color.
     * Customize these hex values to match your brand.
     */
    private function getRoleColor($role)
    {
        $colors = [
            UserType::ADMIN->value              => '#696cff', // Sneat primary
            UserType::DEAN->value                => '#28c76f', // lighter blue
            UserType::TASK_FORCE->value          => '#ffb258', // warm orange
            UserType::INTERNAL_ASSESSOR->value   => '#28c76f', // green
            UserType::ACCREDITOR->value          => '#ea5455', // red
        ];

        return $colors[$role] ?? '#6c757d'; // neutral gray fallback
    }

    /**
     * Determine if a background color is dark or light and return appropriate text color class.
     */
    private function getTextColorClass($hex)
    {
        list($r, $g, $b) = $this->hexToRgb($hex);
        $brightness = ($r * 0.299 + $g * 0.587 + $b * 0.114);
        return $brightness < 128 ? 'text-white' : 'text-dark';
    }

    /**
     * Map shape prop to Bootstrap class.
     */
    private function getShapeClass($shape)
    {
        return match($shape) {
            'circle' => 'rounded-circle',
            'square' => 'rounded-0',
            'rounded' => 'rounded',
            default => 'rounded-circle',
        };
    }

    /**
     * Convert hex to RGB array.
     */
    private function hexToRgb($hex)
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 8) $hex = substr($hex, 0, 6);
        if (strlen($hex) == 3) {
            $r = hexdec(str_repeat(substr($hex,0,1), 2));
            $g = hexdec(str_repeat(substr($hex,1,1), 2));
            $b = hexdec(str_repeat(substr($hex,2,1), 2));
        } else {
            $r = hexdec(substr($hex,0,2));
            $g = hexdec(substr($hex,2,2));
            $b = hexdec(substr($hex,4,2));
        }
        return [$r, $g, $b];
    }

    public function render()
    {
        return view('components.initials-avatar');
    }
}