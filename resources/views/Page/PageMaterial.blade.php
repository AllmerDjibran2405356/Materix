@extends('layouts.app')

@section('title', 'Materials - ' . $project->Nama_Desain)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        Daftar Material - {{ $project->Nama_Desain }}
                    </h3>
                </div>
                <div class="card-body">
                    @include('materials.partials.ActionButtonsMaterial')
                    @include('materials.partials.TableMaterial')
                </div>
            </div>
        </div>
    </div>
</div>

@include('materials.partials.ModalTambahMaterial')
@include('materials.partials.ModalTambahKategori')
@include('materials.partials.ModalTambahSatuan')
@include('materials.partials.ModalTambahBahan')
@include('materials.partials.ModalTambahSupplier')

@include('materials.partials.ScriptMaterial')
@endsection

@section('scripts')
    @vite(['resources/js/material-management.js'])
@endsection