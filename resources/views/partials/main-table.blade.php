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
            <td class="text-end">
                Rp {{ number_format($recap->Harga_Satuan_Saat_Ini, 0, ',', '.') }}
            </td>
            <td class="text-end text-primary fw-bold">
                Rp {{ number_format($recap->Total_Harga, 0, ',', '.') }}
            </td>
            <td>
                <form action="{{ route('rekap.updateSupplier') }}" method="POST" class="d-inline supplier-form">
                    @csrf
                    {{-- Tambah hidden input untuk ID_Rekap --}}
                    <input type="hidden" name="ID_Rekap" value="{{ $recap->ID_Rekap }}">
                    <div class="input-group input-group-sm">
                        <select name="ID_Supplier" class="form-select form-select-sm"
                                data-recap-id="{{ $recap->ID_Rekap }}"
                                data-bahan-id="{{ $recap->ID_Bahan }}">
                            <option value="">-- Pilih Supplier --</option>
                            @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->ID_Supplier }}"
                                {{ $recap->ID_Supplier == $supplier->ID_Supplier ? 'selected' : '' }}>
                                {{ $supplier->Nama_Supplier }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </td>
        </tr>
        @endforeach

        {{-- Grand Total Row --}}
        @php
            $grandTotal = $recaps->sum('Total_Harga');
        @endphp
        <tr class="table-secondary">
            <td colspan="5" class="text-end fw-bold">Grand Total</td>
            <td class="text-end text-primary fw-bold fs-6">
                Rp {{ number_format($grandTotal, 0, ',', '.') }}
            </td>
            <td></td>
        </tr>
    </tbody>
@endif
