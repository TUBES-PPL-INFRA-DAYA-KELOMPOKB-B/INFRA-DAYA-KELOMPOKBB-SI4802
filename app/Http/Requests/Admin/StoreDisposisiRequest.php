<?php

namespace App\Http\Requests\Admin;

use App\Models\LaporanKerusakan;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class StoreDisposisiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'laporan_id' => [
                'required',
                'integer',
                'exists:laporan_kerusakan,id',
                function ($attribute, $value, $fail) {
                    $laporan = LaporanKerusakan::find($value);
                    if ($laporan && $laporan->status !== 'diproses') {
                        $fail('Hanya laporan berstatus disetujui (diproses) yang dapat didisposisikan.');
                    }
                    if ($laporan && $laporan->disposisi()->exists()) {
                        $fail('Laporan ini sudah memiliki disposisi penugasan aktif.');
                    }
                },
            ],
            'petugas_id' => [
                'required',
                'integer',
                'exists:users,id',
                function ($attribute, $value, $fail) {
                    $user = User::find($value);
                    if ($user && (! $user->isPetugas() || ! $user->is_active)) {
                        $fail('Petugas yang dipilih harus berstatus aktif.');
                    }
                },
            ],
            'tanggal_disposisi' => ['nullable', 'date'],
            'tenggat_waktu' => [
                'required',
                'date',
                function ($attribute, $value, $fail) {
                    $tanggalDisposisi = $this->input('tanggal_disposisi', now()->toDateString());
                    if ($value && $tanggalDisposisi && $value < $tanggalDisposisi) {
                        $fail('Tenggat waktu pelaksanaan tidak boleh lebih awal dari tanggal disposisi.');
                    }
                },
            ],
            'catatan_instruksi' => ['required', 'string', 'min:10', 'max:1500'],
        ];
    }

    public function messages(): array
    {
        return [
            'laporan_id.required' => 'Laporan kerusakan yang akan didisposisikan wajib dipilih.',
            'laporan_id.exists' => 'Laporan yang dipilih tidak valid dalam sistem.',
            'petugas_id.required' => 'Petugas lapangan yang ditugaskan wajib dipilih.',
            'petugas_id.exists' => 'Petugas yang dipilih tidak ditemukan.',
            'tenggat_waktu.required' => 'Tenggat waktu pelaksanaan wajib ditentukan.',
            'tenggat_waktu.date' => 'Format tanggal tenggat waktu tidak valid.',
            'catatan_instruksi.required' => 'Instruksi dan catatan teknis pelaksanaan wajib diisi.',
            'catatan_instruksi.min' => 'Instruksi teknis minimal 10 karakter agar jelas dipahami petugas.',
            'catatan_instruksi.max' => 'Instruksi teknis maksimal 1500 karakter.',
        ];
    }
}
