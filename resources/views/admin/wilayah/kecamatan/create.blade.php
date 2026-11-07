@include('admin.wilayah.kecamatan.index', [
    'editingKecamatan' => null,
    'search' => $search ?? request('search'),
    'kecamatanList' => $kecamatanList ?? app(\App\Services\WilayahService::class)->getPaginatedKecamatan(request('search')),
])
