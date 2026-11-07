@include('admin.wilayah.kecamatan.index', [
    'editingKecamatan' => $kecamatan ?? $editingKecamatan,
    'search' => $search ?? request('search'),
    'kecamatanList' => $kecamatanList ?? app(\App\Services\WilayahService::class)->getPaginatedKecamatan(request('search')),
])
