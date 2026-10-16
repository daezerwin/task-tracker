<?php

namespace App\Enums;

enum TaskCategory: string
{
    case Cleaning = 'cleaning';
    case Kitchen = 'kitchen';
    case Laundry = 'laundry';
    case Bathroom = 'bathroom';
    case Outdoor = 'outdoor';
    case Errands = 'errands';
    case Maintenance = 'maintenance';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Cleaning => 'Cleaning',
            self::Kitchen => 'Kitchen',
            self::Laundry => 'Laundry',
            self::Bathroom => 'Bathroom',
            self::Outdoor => 'Yard & Garden',
            self::Errands => 'Errands & Bills',
            self::Maintenance => 'Maintenance',
            self::Other => 'Other',
        };
    }

    // Class names are written out in full (never concatenated) so Tailwind's
    // content scan of app/Enums finds them — see tailwind.config.js. The 400
    // shades read well on the dark Material surfaces.
    public function dotClass(): string
    {
        return match ($this) {
            self::Cleaning => 'bg-sky-400',
            self::Kitchen => 'bg-amber-400',
            self::Laundry => 'bg-violet-400',
            self::Bathroom => 'bg-cyan-400',
            self::Outdoor => 'bg-lime-400',
            self::Errands => 'bg-rose-400',
            self::Maintenance => 'bg-orange-400',
            self::Other => 'bg-neutral-400',
        };
    }
}
