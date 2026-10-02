<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'icon',
    ];

    public static function record(string $title, ?string $description = null, string $icon = 'info-circle'): self
    {
        return self::create([
            'title' => $title,
            'description' => $description,
            'icon' => $icon,
        ]);
    }
}
