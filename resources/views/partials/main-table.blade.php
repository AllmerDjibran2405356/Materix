{{-- File: resources/views/partials/main-table.blade.php --}}
@if($recaps->isEmpty())
    <tbody>
        <tr>
            <td colspan="7" class="text-center text-muted py-4">
                <i class="bi bi-exclamation-triangle"></i>
                Belum ada data rekapitulasi.
            </td>
        </tr>
    </tbody>
@else
    <tbody>
        @foreach ($recaps as $index => $recap)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $recap->bahan->Nama_Bahan ?? 'Unknown' }}</td>
            <td class="text-end">{{ number_format($recap->Volume_Final, 2, ',', '.') }}</td>
            <td class="text-center">{{ $recap->bahan->satuan->Nama_Satuan ?? 'Unit' }}</td>

            {{-- KOLOM HARGA SATUAN: Tambah class 'cell-harga-satuan' --}}
            <td class="text-end cell-harga-satuan">
                Rp {{ number_format($recap->Harga_Satuan_Saat_Ini, 0, ',', '.') }}
            </td>

            {{-- KOLOM TOTAL HARGA: Tambah class 'cell-total-harga' --}}
            <td class="text-end text-primary fw-bold cell-total-harga">
                Rp {{ number_format($recap->Total_Harga, 0, ',', '.') }}
            </td>

            <td>
                {{-- Form tidak wajib submit, kita pakai AJAX via Select --}}
                <div class="input-group input-group-sm">
                    {{--
                        PENTING:
                        1. Class 'select-supplier-main' untuk trigger JS
                        2. data-url untuk tujuan AJAX
                        3. data-id untuk ID Rekap yang mau diupdate
                    --}}
                    <select name="ID_Supplier" class="form-select form-select-sm select-supplier-main"
                            data-id="{{ $recap->ID_Rekap }}"
                            data-url="{{ route('rekap.updateSupplier') }}">

                        <option value="" disabled {{ !$recap->ID_Supplier ? 'selected' : '' }}>-- Pilih Supplier --</option>

                        @foreach ($suppliers as $supplier)
                        <option value="{{ $supplier->ID_Supplier }}"
                            {{ $recap->ID_Supplier == $supplier->ID_Supplier ? 'selected' : '' }}>
                            {{ $supplier->Nama_Supplier }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </td>
        </tr>
        @endforeach

        {{-- Grand Total Row --}}
        @php
            $grandTotal = $recaps->sum('Total_Harga');
        @endphp
        <tr class="table-secondary">
            <td colspan="5" class="text-end fw-bold">Grand Total</td>
            {{-- Tambah ID 'grand-total-display' agar bisa diupdate JS --}}
            <td class="text-end text-primary fw-bold fs-6" id="grand-total-display">
                Rp {{ number_format($grandTotal, 0, ',', '.') }}
            </td>
            <td></td>
        </tr>
    </tbody>
@endif
