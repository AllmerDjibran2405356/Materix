<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Pendataan Bahan & Produsen</h3>
        <div>
            {{-- Changed to anchor tags for linking, assuming routes exist --}}
            <a href="{{ url('/data-supplier') }}" class="btn btn-outline-primary me-2">
                <i class="bi bi-truck"></i> Data Supplier
            </a>
            <a href="{{ url('/data-bahan') }}" class="btn btn-secondary">
                <i class="bi bi-box-seam"></i> Data Bahan
            </a>
        </div>
    </div>

    {{-- Data Section --}}
    <section>
        @if ($recaps->isEmpty())
            <div class="alert alert-warning text-center">
                <i class="bi bi-exclamation-triangle"></i>
                Belum ada data rekapitulasi. Silakan lakukan perhitungan RAB terlebih dahulu.
            </div>
        @else
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        Desain: <strong>{{ $recaps->first()->desainRumah->Nama_Desain ?? 'ID: '.$recaps->first()->ID_Desain_Rumah }}</strong>
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover mb-0">
                            <thead class="table-dark align-middle">
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal Hitung</th>
                                    <th>Nama Bahan</th>
                                    <th class="text-end">Vol. Teoritis</th>
                                    <th class="text-end">Vol. Final</th>
                                    <th class="text-center">Satuan</th>
                                    <th class="text-end">Harga Satuan</th>
                                    <th class="text-end">Total Harga</th>
                                    <th>Supplier</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($recaps as $index => $recap)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>

                                        {{-- Tanggal --}}
                                        <td>{{ \Carbon\Carbon::parse($recap->Tanggal_Hitung)->format('d M Y') }} <small class="text-muted">{{ \Carbon\Carbon::parse($recap->Tanggal_Hitung)->format('H:i') }}</small></td>

                                        {{-- Nama Bahan --}}
                                        <td>{{ $recap->bahan->Nama_Bahan ?? 'ID: '.$recap->ID_Bahan }}</td>

                                        {{-- Volume --}}
                                        <td class="text-end">{{ number_format($recap->Volume_Teoritis, 2, ',', '.') }}</td>
                                        <td class="text-end fw-bold">{{ number_format($recap->Volume_Final, 2, ',', '.') }}</td>

                                        <td class="text-center">{{ $recap->Satuan_Saat_Ini }}</td>

                                        {{-- Harga --}}
                                        <td class="text-end">Rp {{ number_format($recap->Harga_Satuan_Saat_Ini, 0, ',', '.') }}</td>
                                        <td class="text-end fw-bold text-success">Rp {{ number_format($recap->Total_Harga, 0, ',', '.') }}</td>

                                        {{-- FIX 3: Supplier Name instead of ID Number --}}
                                        <td>
                                            {{-- Assuming relation 'supplier' exists. Fallback to ID if null --}}
                                            {{ $recap->supplier->Nama_Supplier ?? 'ID: ' . $recap->ID_Supplier }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            {{-- Footer untuk Grand Total --}}
                            <tfoot>
                                <tr class="table-secondary fw-bold">
                                    {{-- FIX 2: Colspan should be 7 so the price aligns with column 8 --}}
                                    <td colspan="7" class="text-end text-uppercase">Grand Total Estimasi</td>

                                    {{-- Total Sum aligned with 'Total Harga' column --}}
                                    <td class="text-end text-primary">Rp {{ number_format($recaps->sum('Total_Harga'), 0, ',', '.') }}</td>

                                    {{-- Empty cell for Supplier column --}}
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </section>
</div>
