<?php
namespace App\Traits;

use App\Models\LogAktivitas;
use App\Enums\JenisAktivitas;
use Illuminate\Support\Facades\Auth;

trait MencatatAktivitas
{
    protected static function bootMencatatAktivitas()
    {
        static::created(function ($model) {
            self::rekamLog(
                $model,
                JenisAktivitas::INSERT,
                'Menambahkan data baru',
                null,
                $model->getAttributes()
            );
        });

        static::updated(function ($model) {
            $changes = $model->getChanges();
            $original = array_intersect_key($model->getOriginal(), $changes);

            self::rekamLog($model, JenisAktivitas::UPDATE, 'Mengubah data', $original, $changes);
        });

        static::deleted(function ($model) {
            self::rekamLog(
                $model,
                JenisAktivitas::DELETE,
                'Menghapus data',
                $model->getOriginal(),
                null
            );
        });
    }

    protected static function rekamLog($model, $jenis, $deskripsi, $dataLama, $dataBaru)
    {
        $userId = Auth::id() ?? 1;

        LogAktivitas::create([
            'user_id' => $userId,
            'jenis_aktivitas' => $jenis,
            'model' => class_basename($model),
            'model_id' => $model->id,
            'deskripsi' => "$deskripsi pada " . class_basename($model),
            'data_lama' => $dataLama,
            'data_baru' => $dataBaru,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}