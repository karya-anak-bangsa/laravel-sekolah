// Entry panel admin (Gentelella v4). Tidak boleh memuat Bootstrap atau jQuery.
// Sidebar/topbar dirender oleh Blade (layouts/admin.blade.php); mountShell hanya
// memasang perilakunya (drawer mobile, rail sidebar, accordion, tema gelap/terang).
import { mountShell } from 'gentelella/v4/shell';

mountShell();
