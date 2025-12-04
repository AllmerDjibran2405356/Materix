<a href="{{ route('viewer', ['id' => $project->ID_Desain_Rumah]) }}">
    Lihat Desain
</a>

<a href="{{ route('dataProyek.index', ['id' => $project->ID_Desain_Rumah]) }}">
    Input Bahan & Produsen
</a>

<a href="{{ route('laporan.index', ['id' => $project->ID_Desain_Rumah]) }}">
    Lihat Laporan Kebutuhan/RAB
</a>
