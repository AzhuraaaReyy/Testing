{{--
    Petunjuk di bawah form login Admin (render hook AUTH_LOGIN_FORM_AFTER), padanan tautan
    "Admin sekolah? Masuk melalui panel Admin" di halaman login portal. Mencegah Guru/Siswa
    mencoba akunnya di pintu yang salah.
--}}
<p class="text-center text-sm text-ink-muted">
    Guru atau Siswa?
    <a href="{{ route('login') }}" class="font-semibold text-primary underline-offset-4 hover:underline">Masuk melalui portal</a>
</p>
