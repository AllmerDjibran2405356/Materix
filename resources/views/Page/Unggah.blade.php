<meta name="csrf-token" content="{{ csrf_token() }}">

<div id="drop-zone"
     style="width: 100%; height: 200px; background:#007bff;
            border-radius:10px; display:flex; align-items:center;
            justify-content:center; flex-direction:column; color:#fff;
            cursor:pointer;">

    <h5>Seret file IFC ke sini</h5>
    <p style="opacity:0.8;">atau klik tombol di bawah</p>

<script>
    const uploadRoute = "{{ route('unggah.upload') }}";
    const csrfToken = "{{ csrf_token() }}";
</script>

<script src="{{ asset('js/Unggah.js') }}"></script>


    <button id="triggerInput" class="btn btn-light mt-2">Pilih File</button>

    <input type="file" id="fileInput" accept=".ifc" style="display:none">
</div>
