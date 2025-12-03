<div class="container mt-4">
    <h3>Pendataan Bahan & Produsen</h3>

    <div class="mb-3">
        <button class="btn btn-primary">Data Supplier</button>
        <button class="btn btn-secondary">Data Bahan</button>
    </div>

    {{-- Data Section --}}
    <section>
        @if ($recaps->isEmpty())
            <div class="alert alert-warning text-center">
                Belum ada data rekapitulasi. Silakan lakukan perhitungan RAB terlebih dahulu.
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>Tanggal Hitung</th>
                            <th>Desain Rumah</th>
                            <th>Nama Bahan</th>
                            <th class="text-end">Vol. Teoritis</th>
                            <th class="text-end">Vol. Final</th>
                            <th class="text-center">Satuan</th>
                            <th class="text-end">Harga Satuan</th>
                            <th class="text-end">Total Harga</th>
                            <th>Dihitung Oleh</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recaps as $index => $recap)
                            <tr>
                                <td>{{ $index + 1 }}</td>

                                {{-- Format Tanggal --}}
                                <td>{{ \Carbon\Carbon::parse($recap->Tanggal_Hitung)->format('d M Y H:i') }}</td>

                                {{-- Mengambil Nama Desain dari Relasi (bukan ID) --}}
                                <td>{{ $recap->desainRumah->Nama_Desain ?? 'ID: '.$recap->ID_Desain_Rumah }}</td>

                                {{-- Mengambil Nama Bahan dari Relasi --}}
                                <td>{{ $recap->bahan->Nama_Bahan ?? 'ID: '.$recap->ID_Bahan }}</td>

                                {{-- Format Angka Desimal --}}
                                <td class="text-end">{{ number_format($recap->Volume_Teoritis, 2, ',', '.') }}</td>
                                <td class="text-end fw-bold">{{ number_format($recap->Volume_Final, 2, ',', '.') }}</td>

                                <td class="text-center">{{ $recap->Satuan_Saat_Ini }}</td>

                                {{-- Format Rupiah --}}
                                <td class="text-end">Rp {{ number_format($recap->Harga_Satuan_Saat_Ini, 0, ',', '.') }}</td>
                                <td class="text-end fw-bold text-success">Rp {{ number_format($recap->Total_Harga, 0, ',', '.') }}</td>

                                {{-- Nama User --}}
                                <td>{{ $recap->user->name ?? 'User ID: '.$recap->ID_User }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    {{-- Footer untuk Grand Total --}}
                    <tfoot>
                        <tr class="table-secondary fw-bold">
                            <td colspan="8" class="text-end">GRAND TOTAL ESTIMASI</td>
                            <td class="text-end">Rp {{ number_format($recaps->sum('Total_Harga'), 0, ',', '.') }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
    </section>
</div>
