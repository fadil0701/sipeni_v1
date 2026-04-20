<?php

namespace App\Enums;

enum DistribusiStatus: string
{
    case Draft = 'draft';
    case Diproses = 'diproses';
    case Dikirim = 'dikirim';
    case Selesai = 'selesai';
}
