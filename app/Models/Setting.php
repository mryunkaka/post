<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'group',
        'key',
        'value',
        'autoload',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'autoload' => 'boolean',
        ];
    }
}
