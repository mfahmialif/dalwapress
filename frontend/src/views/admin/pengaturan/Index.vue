<template>
  <div class="flex flex-col gap-6">
    <div class="settings-card rounded-xl overflow-hidden">
      <div class="card-header px-6 py-4 flex items-center gap-2">
        <span class="material-symbols-outlined text-accent text-[20px]">tune</span>
        <h3 class="font-bold" style="color: var(--text-heading)">Pengaturan Umum</h3>
      </div>
      <div class="px-6 py-5 space-y-6">
        <div class="setting-row">
          <div class="setting-label">
            <h4 class="text-sm font-bold" style="color: var(--text-heading)">Nama Sistem</h4>
            <p class="text-xs mt-0.5" style="color: var(--text-muted)">Nama yang ditampilkan di halaman publik dan admin</p>
          </div>
          <div class="setting-control">
            <input v-model="systemName" class="setting-input w-full rounded-lg py-2.5 px-4 text-sm focus:outline-none focus:ring-1 focus:ring-accent" />
          </div>
        </div>

        <div class="setting-row">
          <div class="setting-label">
            <h4 class="text-sm font-bold" style="color: var(--text-heading)">Logo Sistem</h4>
            <p class="text-xs mt-0.5" style="color: var(--text-muted)">Identitas visual utama aplikasi</p>
          </div>
          <div class="setting-control">
            <div class="flex items-center gap-4">
              <div class="w-16 h-16 rounded-xl flex items-center justify-center border-2 border-dashed border-accent/40" style="background: var(--bg-input)">
                <span class="material-symbols-outlined text-accent text-3xl">menu_book</span>
              </div>
              <button class="upload-btn flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-bold cursor-pointer transition-all">
                <span class="material-symbols-outlined text-[16px]">cloud_upload</span>
                Upload Logo
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="settings-card rounded-xl overflow-hidden">
      <div class="card-header px-6 py-4 flex items-center gap-2">
        <span class="material-symbols-outlined text-accent text-[20px]">palette</span>
        <h3 class="font-bold" style="color: var(--text-heading)">Warna Tema</h3>
      </div>
      <div class="px-6 py-5 space-y-6">
        <div class="setting-row">
          <div class="setting-label">
            <h4 class="text-sm font-bold" style="color: var(--text-heading)">Warna Aksen</h4>
            <p class="text-xs mt-0.5" style="color: var(--text-muted)">Warna utama untuk tombol, badge, dan highlight</p>
          </div>
          <div class="setting-control">
            <div class="flex items-center gap-3 flex-wrap">
              <button v-for="color in accentColors" :key="color.value"
                      @click="selectedAccent = color.value"
                      :class="['w-10 h-10 rounded-xl cursor-pointer transition-all border-2', selectedAccent === color.value ? 'border-white scale-110 shadow-lg' : 'border-transparent hover:scale-105']"
                      :style="{ background: color.value }"
                      :title="color.name">
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="save-bar rounded-xl p-4 flex flex-col sm:flex-row items-center justify-between gap-4 sticky bottom-0 z-10">
      <p class="text-sm" style="color: var(--text-muted)">
        <span class="material-symbols-outlined text-accent text-[16px] align-text-bottom mr-1">info</span>
        Pengaturan ini disiapkan untuk kebutuhan identitas UII Dalwa Press.
      </p>
      <button class="save-btn flex items-center gap-2 px-6 py-2.5 rounded-lg text-sm font-bold cursor-pointer transition-all active:scale-[0.98]">
        <span class="material-symbols-outlined text-[16px]">save</span>
        Simpan Pengaturan
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const systemName = ref('UII Dalwa Press')
const selectedAccent = ref('#2563eb')
const accentColors = [
  { name: 'Blue', value: '#2563eb' },
  { name: 'Emerald', value: '#047857' },
  { name: 'Slate', value: '#0f172a' },
  { name: 'Rose', value: '#e11d48' },
  { name: 'Cyan', value: '#0891b2' }
]
</script>

<style scoped>
.settings-card { background: var(--bg-card); border: 1px solid var(--border); }
.card-header { border-bottom: 1px solid var(--border); }
.setting-row { display: flex; flex-direction: column; gap: 0.75rem; }
@media (min-width: 768px) {
  .setting-row { flex-direction: row; align-items: flex-start; }
  .setting-label { width: 280px; flex-shrink: 0; padding-top: 0.5rem; }
  .setting-control { flex: 1; }
}
.setting-input {
  background: var(--bg-input);
  border: 1px solid var(--border);
  color: var(--text-heading);
}
.setting-input:focus { border-color: var(--color-accent); box-shadow: 0 0 12px rgba(37, 99, 235, 0.3); }
.upload-btn {
  background: var(--bg-input);
  color: var(--text-heading);
  border: 1px solid var(--border);
}
.upload-btn:hover { border-color: var(--color-accent); color: var(--color-accent); }
.save-bar {
  background: var(--bg-card);
  border: 1px solid var(--border);
  backdrop-filter: blur(12px);
}
.save-btn {
  background: var(--color-accent);
  color: var(--text-btn);
  box-shadow: 0 0 15px rgba(37, 99, 235, 0.3);
}
</style>
