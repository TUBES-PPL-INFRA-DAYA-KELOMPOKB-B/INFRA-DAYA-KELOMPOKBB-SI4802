@include('admin.wilayah.kelurahan.index', [
    'editingKelurahan' => null,
    'kecamatanId' => $kecamatanId ?? request('kecamatan_id'),
    'search' => $search ?? request('search'),
    'kecamatanList' => $kecamatanList ?? app(\App\Services\WilayahService::class)->getAllKecamatan(),
    'kelurahanList' => $kelurahanList ?? app(\App\Services\WilayahService::class)->getPaginatedKelurahan(request('kecamatan_id'), request('search')),
])
