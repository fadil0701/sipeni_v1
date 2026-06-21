<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrintTemplate extends Model
{
    protected $fillable = [
        'key',
        'name',
        'description',
        'header_html',
        'layout_mode',
        'header_preset',
        'paper_size',
        'orientation',
        'print_margin_mm',
        'body',
        'builder_blocks',
        'sample_data',
        'is_active',
    ];

    protected $attributes = [
        'layout_mode' => 'full_page',
        'paper_size' => 'a4',
        'orientation' => 'portrait',
        'print_margin_mm' => 12,
    ];

    protected function casts(): array
    {
        return [
            'sample_data' => 'array',
            'builder_blocks' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Isi dokumen: gabungan blok builder (jika ada) atau kolom body.
     */
    public function compiledBody(): string
    {
        $blocks = $this->builder_blocks;
        if (is_array($blocks) && $blocks !== []) {
            $parts = [];
            foreach ($blocks as $b) {
                if (is_array($b) && isset($b['html']) && trim((string) $b['html']) !== '') {
                    $parts[] = (string) $b['html'];
                }
            }
            if ($parts !== []) {
                return implode("\n\n", $parts);
            }
        }

        return (string) ($this->body ?? '');
    }
}
