<?php

namespace Tests\Unit;

use App\Support\Http\SafeUserMessage;
use Illuminate\Database\QueryException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SafeUserMessageTest extends TestCase
{
    #[Test]
    public function it_maps_null_constraint_to_friendly_message(): void
    {
        $exception = new QueryException(
            'mysql',
            'insert into master_pegawai (...) values (...)',
            [],
            new \PDOException("SQLSTATE[23000]: Integrity constraint violation: 1048 Column 'email_pegawai' cannot be null")
        );

        $message = SafeUserMessage::fromThrowable($exception, 'menyimpan data');

        $this->assertSame('Data belum lengkap: email pegawai wajib diisi.', $message);
        $this->assertStringNotContainsString('SQLSTATE', $message);
    }

    #[Test]
    public function it_maps_duplicate_entry_to_friendly_message(): void
    {
        $exception = new QueryException(
            'mysql',
            'insert into master_pegawai (...) values (...)',
            [],
            new \PDOException("SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry 'x' for key 'nip_pegawai'")
        );

        $message = SafeUserMessage::fromThrowable($exception, 'menyimpan data');

        $this->assertSame('Data sudah terdaftar. Periksa NIP, email, atau field unik lain yang sama.', $message);
    }

    #[Test]
    public function it_falls_back_to_generic_operation_message_for_other_exceptions(): void
    {
        $message = SafeUserMessage::fromThrowable(new \RuntimeException('internal'), 'menyimpan data');

        $this->assertSame(
            'Terjadi kesalahan saat menyimpan data. Silakan coba lagi atau hubungi administrator.',
            $message
        );
    }
}
