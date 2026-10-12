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
    // content scan of app/Enums finds them — see tailwind.config.js.
    public function badgeClasses(): string
    {
        return match ($this) {
            self::Cleaning => 'bg-sky-50 text-sky-700',
            self::Kitchen => 'bg-amber-50 text-amber-700',
            self::Laundry => 'bg-violet-50 text-violet-700',
            self::Bathroom => 'bg-cyan-50 text-cyan-700',
            self::Outdoor => 'bg-emerald-50 text-emerald-700',
            self::Errands => 'bg-rose-50 text-rose-700',
            self::Maintenance => 'bg-orange-50 text-orange-700',
            self::Other => 'bg-gray-100 text-gray-600',
        };
    }

    public function dotClass(): string
    {
        return match ($this) {
            self::Cleaning => 'bg-sky-500',
            self::Kitchen => 'bg-amber-500',
            self::Laundry => 'bg-violet-500',
            self::Bathroom => 'bg-cyan-500',
            self::Outdoor => 'bg-emerald-500',
            self::Errands => 'bg-rose-500',
            self::Maintenance => 'bg-orange-500',
            self::Other => 'bg-gray-400',
        };
    }
}
