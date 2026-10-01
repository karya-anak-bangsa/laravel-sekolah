<?php

namespace App\Modules\CompanyProfile\Models;

use App\Modules\CompanyProfile\Policies\PengaturanPolicy;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Model;

#[Table('tb_pengaturan', key: 'id_pengaturan')]
#[Fillable(['kunci', 'nilai'])]
#[UsePolicy(PengaturanPolicy::class)]
class Pengaturan extends Model {}
