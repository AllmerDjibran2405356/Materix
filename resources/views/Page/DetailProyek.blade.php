<div class="position-fixed" style="top: 20px; left: 20px; z-index: 1000;">
    <button onclick="window.location.href='{{ route('DaftarProyek.index') }}'"
            class="btn btn-light shadow-sm rounded-circle"
            style="width: 50px; height: 50px;"
            title="Kembali ke Detail Proyek">
        <i class="bi bi-arrow-left fs-5"></i>
    </button>
</div>

<a href="{{ route('viewer', ['id' => $project->ID_Desain_Rumah]) }}">
    Lihat Desain
</a>

<a href="{{ route('dataProyek.index', ['id' => $project->ID_Desain_Rumah]) }}">
    Input Bahan & Produsen
</a>

<a href="{{ route('laporan.index', ['id' => $project->ID_Desain_Rumah]) }}">
    Lihat Laporan Kebutuhan/RAB
</a>
