@extends('layouts.app')

@section('title', 'RAB - {{ $project->Nama_Desain }}')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/laporanRAB.css') }}">
    <link rel="stylesheet" href="{{ asset('css/navbar.css') }}">
@endsection

@section('content')
<div class="main-container">

    <div class="header-container">
        <h1>Rencana Anggaran Biaya "{{ $project->Nama_Desain }}"</h1>
        <div>
            <p>Tanggal: {{ \Carbon\Carbon::parse($project->Tanggal_Dibuat)->translatedFormat('d F Y') }}
            File: {{ $project->Nama_File }}</p>
        </div>
    </div>

    <div class="table-utama">
        @if($message)
            {{-- KONDISI JIKA DATA KOSONG --}}
            <div class="alert-empty">
                <h4>Belum Ada Data RAB</h4>
                <p>{{ $message }}</p>
                <p>Silakan hitung kebutuhan bahan terlebih dahulu untuk melihat RAB.</p>
            </div>

            <div class="btn-wrapper mt-4">
                <a href="{{ route('detailProyek.show', $project->ID_Desain_Rumah) }}" class="back-home-btn">
                    <i class="bi bi-arrow-left"></i>
                    <span>Kembali ke Detail Proyek</span>
                </a>
            </div>

        @else
            {{-- KONDISI JIKA DATA ADA --}}

            <div class="btn-wrapper d-flex justify-content-between align-items-center mb-4">

                <div class="container-back">
                    <a href="{{ route('detailProyek.show', $project->ID_Desain_Rumah) }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali ke Detail Proyek
                    </a>
                </div>

                <div class="container-btn d-flex gap-2">
                    <a class="btn-header" onclick="exportToExcel()" style="cursor: pointer;">
                        <i class="bi bi-file-earmark-excel"></i> Export Excel
                    </a>
                    <a class="btn-header" onclick="exportToPDF()" style="cursor: pointer;">
                        <i class="bi bi-file-earmark-pdf"></i> Export PDF
                    </a>
                    <a class="btn-header" onclick="refreshData()" style="cursor: pointer;">
                        <i class="bi bi-arrow-clockwise"></i> Refresh
                    </a>
                </div>
            </div>

            <div class="loadingSpinner" id="loadingSpinner" style="display: none;">
                <div class="loader"></div>
            </div>

            <div class="section info-container">
                <div class="section-header">
                    <h2>Data RAB</h2>
                </div>

                <div class="section-body">
                    <table id="rabTable" class="info-table">
                        <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Bahan</th>
                            <th>Kategori</th>
                            <th>Volume</th>
                            <th>Satuan</th>
                            <th>Harga Satuan</th>
                            <th>Total Harga</th>
                            <th>Supplier</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($recaps as $index => $recap)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $recap->bahan->Nama_Bahan ?? 'Unknown' }}</td>
                            <td>
                                @if($recap->bahan && $recap->bahan->kategori)
                                    {{ $recap->bahan->kategori->Nama_Kelompok_Bahan }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ number_format($recap->Volume_Final, 2) }}</td>
                            <td>{{ $recap->Satuan_Saat_Ini ?? 'Unit' }}</td>
                            <td>Rp {{ number_format($recap->Harga_Satuan_Saat_Ini, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($recap->Total_Harga, 0, ',', '.') }}</td>
                            <td>
                                @if($recap->supplier)
                                    <div>
                                        <span style="font-weight:bold;">{{ $recap->supplier->Nama_Supplier }}</span>
                                        <div style="font-size: 0.85em; color: #555;">
                                            @php
                                                $kontakSupplier = '-';
                                                if ($recap->supplier->kontak) {
                                                    if ($recap->supplier->kontak instanceof \Illuminate\Database\Eloquent\Collection) {
                                                        $kontakSupplier = $recap->supplier->kontak->first()->Kontak_Supplier ?? '-';
                                                    } else {
                                                        $kontakSupplier = $recap->supplier->kontak->Kontak_Supplier ?? '-';
                                                    }
                                                }
                                            @endphp
                                            <i class="bi bi-telephone"></i> {{ $kontakSupplier }}
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="6" style="text-align: right; font-weight: bold;">GRAND TOTAL</td>
                                <td colspan="2" style="font-weight: bold; font-size: 1.1em;">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @endif
    </div>

    @if(!$message)
    <div class="ringkasan-rab">
        <div class="section info-container">
            <div class="section-header">
                <h2>Ringkasan RAB</h2>
            </div>
            <div class="section-body">
                <table class="info-table">
                <tr>
                    <th>Total Item Bahan :</th>
                    <td>{{ $totalItems }}</td>
                    <td>Jenis bahan yang dibutuhkan</td>
                </tr>
                <tr>
                    <th>Supplier Terlibat :</th>
                    <td>{{ $uniqueSuppliers }}</td>
                    <td>Jumlah supplier penyedia</td>
                </tr>
                <tr>
                    <th>Total RAB :</th>
                    <td> </td>
                    <td style="font-weight: bold;">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
                </tr>
                </table>
            </div>
        </div>

        <div class="section info-container">
            <div class="section-header">
                <h2>Status RAB</h2>
            </div>
            <div class="section-body">
                <table class="info-table">
                <tr>
                    <td>
                        @if($totalItems > 0)
                            <span class="badge bg-success">LENGKAP</span>
                        @else
                            <span class="badge bg-danger">BELUM ADA DATA</span>
                        @endif
                    </td>
                    <td>Perhitungan selesai</td>
                </tr>
                </table>
            </div>
        </div>
    </div>
    @endif

    @if($groupedPrices->count() > 0 && !$message)
    <div class="perbandingan-harga">
        <div class="section info-container">
            <div class="section-header">
                <h2>Perbandingan Harga Bahan</h2>
            </div>
            <div class="section-body">
                <div class="table-responsive">
                    <table class="info-table">
                    <tr>
                        <th>Bahan</th>
                        @foreach($suppliers as $supplier)
                        <th>{{ $supplier->Nama_Supplier }}</th>
                        @endforeach
                    </tr>
                    @foreach($groupedPrices as $bahanId => $hargaList)
                    @php
                        $bahan = $hargaList->first()->bahan ?? null;
                    @endphp
                    @if($bahan)
                    <tr>
                        <td>{{ $bahan->Nama_Bahan }}</td>
                        @foreach($suppliers as $supplier)
                        <td>
                            @php
                                $harga = $hargaList->where('ID_Supplier', $supplier->ID_Supplier)->first();
                            @endphp
                            @if($harga)
                                Rp {{ number_format($harga->Harga_Per_Satuan, 0, ',', '.') }}
                                <div style="font-size: 0.8em; color: #666;">
                                    {{ \Carbon\Carbon::parse($harga->Tanggal_Update_Data)->format('d/m/y') }}
                                </div>
                            @else
                                -
                            @endif
                        </td>
                        @endforeach
                    </tr>
                    @endif
                    @endforeach
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    <script>
        function exportToExcel() {
            window.location.href = "{{ route('rab.export-excel', $project->ID_Desain_Rumah) }}";
        }

        function exportToPDF() {
            window.location.href = "{{ route('rab.export-pdf', $project->ID_Desain_Rumah) }}";
        }

        function refreshData() {
            document.getElementById('loadingSpinner').style.display = 'block';
            setTimeout(function() {
                window.location.reload();
            }, 1000);
        }
    </script>
</div>
@endsection
