// Entry panel admin (Gentelella v4). Tidak boleh memuat Bootstrap atau jQuery.
// Sidebar/topbar dirender oleh Blade (layouts/admin.blade.php); mountShell hanya
// memasang perilakunya (drawer mobile, rail sidebar, accordion, tema gelap/terang).
import { mountShell } from 'gentelella/v4/shell';

mountShell();

// Tombol mata pada kolom password: klik untuk menampilkan/menyembunyikan isian.
document.addEventListener('click', (event) => {
  const tombol = event.target.closest('[data-toggle-password]');
  if (!tombol) return;

  const input = document.querySelector(tombol.dataset.togglePassword);
  if (!input) return;

  const tampil = input.type === 'password';
  input.type = tampil ? 'text' : 'password';
  tombol.setAttribute('aria-pressed', String(tampil));
  tombol.setAttribute('aria-label', tampil ? 'Sembunyikan password' : 'Tampilkan password');
  tombol.querySelector('[data-icon="show"]').hidden = tampil;
  tombol.querySelector('[data-icon="hide"]').hidden = !tampil;
});
