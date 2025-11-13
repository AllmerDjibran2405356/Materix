{{-- =================================================== --}}
{{--         SEMUA KODE MODAL (POP-UP) ANDA          --}}
{{-- =================================================== --}}

{{-- 1. MODAL UNTUK UBAH INFORMASI AKUN (SUDAH DIPERBAIKI) --}}
<div class="modal fade" id="ubahAkunModal" tabindex="-1" aria-labelledby="ubahAkunModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header modal-header-custom">
                <h1 class="modal-title fs-5" id="ubahAkunModalLabel">Ubah Informasi Akun</h1>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            {{-- ▼▼▼ FORM SEKARANG MEMBUNGKUS BODY & FOOTER, DENGAN ACTION & ID ▼▼▼ --}}
            <form action="{{ route('pengaturan.updateInfo') }}" method="POST" id="formUbahAkun"> 
                @csrf
                <div class="modal-body modal-body-custom">
                        <div class="mb-3">
                            <label for="namaPengguna" class="form-label">* Nama Pengguna</label>
                            {{-- Tambahkan 'name' --}}
                            <input type="text" class="form-control" id="namaPengguna" name="username" value="{{ auth()->user()->username ?? '' }}">
                        </div>
                        <div class="mb-3">
                            <label for="namaDepan" class="form-label">* Nama Depan</label>
                             {{-- Tambahkan 'name' --}}
                            <input type="text" class="form-control" id="namaDepan" name="first_name" value="{{ auth()->user()->first_name ?? '' }}">
                        </div>
                        <div class="mb-3">
                            <label for="namaBelakang" class="form-label">* Nama Belakang</label>
                             {{-- Tambahkan 'name' --}}
                            <input type="text" class="form-control" id="namaBelakang" name="last_name" value="{{ auth()->user()->last_name ?? '' }}">
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">* Email</label>
                             {{-- Tambahkan 'name' --}}
                            <input type="email" class="form-control" id="email" name="email" value="{{ auth()->user()->email ?? '' }}">
                        </div>
                        <hr>
                        {{-- Tombol ini hanya memicu modal lain, jadi biarkan type="button" --}}
                        <button type="button" class="btn btn-custom-orange w-100" data-bs-toggle="modal" data-bs-target="#cekSandiLamaModal">
                            Ubah Kata Sandi
                        </button>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Batal</button>
                    
                    {{-- ▼▼▼ UBAH MENJADI 'type="submit"' ▼▼▼ --}}
                    <button type="submit" class="btn btn-success">Simpan</button>
                </div>
            </form> {{-- </form> ditutup di sini --}}
        </div>
    </div>
</div>

{{-- =================================================================== --}}

{{-- 2. MODAL UNTUK CEK KATA SANDI LAMA (UPDATE UNTUK AJAX) --}}
<div class="modal fade" id="cekSandiLamaModal" tabindex="-1" aria-labelledby="cekSandiLamaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header modal-header-custom">
                <h1 class="modal-title fs-5" id="cekSandiLamaModalLabel">Ubah Kata Sandi</h1>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body modal-body-custom">
                 <form onsubmit="return false;"> 
                    <div class="mb-3">
                        <label for="sandiLama" class="form-label">* Masukkan Kata Sandi Lama</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="sandiLama">
                            <button class="btn btn-outline-secondary" type="button" id="toggleSandiLama">
                                <i class="bi bi-eye-fill"></i>
                            </button>
                        </div>
                        
                        {{-- ▼▼▼ 1. TAMBAHKAN TEMPAT UNTUK PESAN ERROR ▼▼▼ --}}
                        <div id="sandiLamaError" class="text-danger small mt-2" style="display: none;">
                            </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Batal</button>
                
                {{-- ▼▼▼ 2. HAPUS 'data-bs-toggle' & 'data-bs-target' DARI TOMBOL INI ▼▼▼ --}}
                <button type="button" class="btn btn-success" id="btnLanjutSandi">
                    Lanjut
                </button>
            </div>
        </div>
    </div>
</div>

{{-- =================================================================== --}}

{{-- 3. MODAL UNTUK MASUKKAN KATA SANDI BARU (SUDAH DIPERBAIKI) --}}
<div class="modal fade" id="sandiBaruModal" tabindex="-1" aria-labelledby="sandiBaruModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
             <div class="modal-header modal-header-custom">
                <h1 class="modal-title fs-5" id="sandiBaruModalLabel">Masukkan Kata Sandi Baru</h1>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            {{-- ▼▼▼ FORM SEKARANG MEMBUNGKUS BODY & FOOTER, DENGAN ACTION & ID ▼▼▼ --}}
            <form action="{{ route('pengaturan.updatePassword') }}" method="POST" id="formSandiBaru">
                @csrf
                {{-- JavaScript akan "menyuntikkan" input hidden 'sandi_lama' di sini --}}

                <div class="modal-body modal-body-custom">
                    <div class="mb-3">
                        <label for="sandiBaru" class="form-label">* Masukkan Kata Sandi Baru</label>
                        <div class="input-group">
                            {{-- Tambahkan 'name' --}}
                            <input type="password" class="form-control" id="sandiBaru" name="sandi_baru">
                            <button class="btn btn-outline-secondary" type="button" id="toggleSandiBaru">
                                <i class="bi bi-eye-fill"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="ulangiSandiBaru" class="form-label">* Ulangi Kata Sandi Baru</label>
                        <div class="input-group">
                             {{-- Tambahkan 'name' (wajib '..._confirmation' untuk Laravel) --}}
                            <input type="password" class="form-control" id="ulangiSandiBaru" name="sandi_baru_confirmation">
                            <button class="btn btn-outline-secondary" type="button" id="toggleUlangiSandiBaru">
                                <i class="bi bi-eye-fill"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Batal</button>
                    
                    {{-- ▼▼▼ UBAH MENJADI 'type="submit"' ▼▼▼ --}}
                    <button type="submit" class="btn btn-success">Simpan</button>
                </div>
                
            </form> {{-- </form> ditutup di sini --}}
        </div>
    </div>
</div>

{{-- 4. MODAL KONFIRMASI LOGOUT --}}
<div class="modal fade" id="logoutConfirmModal" tabindex="-1" aria-labelledby="logoutConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header modal-header-custom">
                <h1 class="modal-title fs-5" id="logoutConfirmModalLabel">Konfirmasi Logout</h1>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body modal-body-custom text-center">
                <i class="bi bi-box-arrow-right text-warning" style="font-size: 3rem;"></i>
                <h5 class="mt-3">Apakah Anda yakin ingin logout?</h5>
                <p class="text-muted">Anda harus login kembali untuk mengakses akun Anda.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form id="logoutForm" action="{{ route('logout') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-box-arrow-right me-2 text-white"></i>Ya, Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- MODAL UNTUK UBAH AVATAR --}}
<div class="modal fade" id="ubahAvatarModal" tabindex="-1" aria-labelledby="ubahAvatarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header modal-header-custom">
                <h1 class="modal-title fs-5" id="ubahAvatarModalLabel">Ubah Foto Profil</h1>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            {{-- FORM BIASA TANPA JAVASCRIPT --}}
            <form action="{{ route('pengaturan.updateAvatar') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body modal-body-custom text-center">
                    
                    {{-- Preview Avatar --}}
                    <div class="mb-4">
                        <img src="{{ auth()->user()->getAvatarUrl() }}" 
                             alt="Preview Avatar" 
                             id="avatarPreview"
                             class="rounded-circle border"
                             width="120" 
                             height="120"
                             style="object-fit: cover;">
                    </div>

                    {{-- Input File --}}
                    <div class="mb-3">
                        <label for="avatarInput" class="form-label">Pilih Foto Baru</label>
                        <input type="file" 
                               class="form-control" 
                               id="avatarInput" 
                               name="avatar"
                               accept="image/*"
                               required>
                        <div class="form-text">
                            Format: JPEG, PNG, JPG, GIF, WEBP<br>(Max: 2MB)
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal lainnya tetap sama --}}
<div class="modal fade" id="ubahAkunModal" tabindex="-1" aria-labelledby="ubahAkunModalLabel" aria-hidden="true">
    {{-- ... kode modal ubah akun yang sudah ada ... --}}
</div>