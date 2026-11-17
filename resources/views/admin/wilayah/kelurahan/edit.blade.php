@include('admin.wilayah.kelurahan.index', [
    'editingKelurahan' => $kelurahan ?? $editingKelurahan,
    'kecamatanId' => $kecamatanId ?? ($kelurahan->kecamatan_id ?? request('kecamatan_id')),
    'search' => $search ?? request('search'),
    'kecamatanList' => $kecamatanList ?? app(\App\Services\WilayahService::class)->getAllKecamatan(),
    'kelurahanList' => $kelurahanList ?? app(\App\Services\WilayahService::class)->getPaginatedKelurahan($kelurahan->kecamatan_id ?? request('kecamatan_id'), request('search')),
])
