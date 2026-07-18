<?php

namespace Tests\Unit;

use App\Enums\DistribusiStatus;
use App\Enums\PermintaanBarangStatus;
use App\Support\UiColor;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class UiColorTest extends TestCase
{
    #[DataProvider('statusToneProvider')]
    public function test_tone_for_status_is_unambiguous(string $status, string $expectedTone): void
    {
        $this->assertSame($expectedTone, UiColor::toneForStatus($status));
    }

    public static function statusToneProvider(): array
    {
        return [
            'draft' => ['draft', UiColor::NEUTRAL],
            'diajukan' => ['diajukan', UiColor::WARNING],
            'menunggu' => ['MENUNGGU', UiColor::WARNING],
            'menunggu_verifikasi' => ['MENUNGGU_VERIFIKASI', UiColor::WARNING],
            'menunggu_bukti' => ['MENUNGGU_BUKTI_SAMPAI', UiColor::WARNING],
            'diproses' => ['diproses', UiColor::INFO],
            'dikirim' => ['dikirim', UiColor::INFO],
            'diverifikasi' => ['diverifikasi', UiColor::SUCCESS],
            'disetujui' => ['DISETUJUI', UiColor::SUCCESS],
            'diterima' => ['diterima', UiColor::SUCCESS],
            'selesai' => ['selesai', UiColor::SUCCESS],
            'ditolak' => ['ditolak', UiColor::DANGER],
            'dibatalkan' => ['DIBATALKAN', UiColor::DANGER],
        ];
    }

    public function test_badge_classes_use_high_contrast_text(): void
    {
        foreach (['success', 'warning', 'danger', 'info', 'neutral'] as $tone) {
            $classes = UiColor::badge($tone);
            $this->assertStringContainsString('bg-', $classes);
            $this->assertMatchesRegularExpression('/text-(green|amber|red|blue|gray)-9/', $classes);
        }
    }

    public function test_enums_delegate_to_uicolor(): void
    {
        $this->assertSame(
            UiColor::badgeForStatus('selesai'),
            PermintaanBarangStatus::Selesai->badgeClasses()
        );
        $this->assertSame(
            UiColor::badgeForStatus('diproses'),
            DistribusiStatus::Diproses->badgeClasses()
        );
    }

    public function test_same_status_same_color_everywhere(): void
    {
        $a = UiColor::badgeForStatus('DIPROSES');
        $b = UiColor::badgeForStatus('diproses');
        $this->assertSame($a, $b);
        $this->assertSame(UiColor::INFO, UiColor::toneForStatus('DIPROSES'));
    }
}
