<template>
  <div class="flex flex-col gap-6">

    <!-- ═══ PROFILE HEADER CARD ═══ -->
    <div class="profile-header rounded-2xl p-8 relative overflow-hidden">
      <div class="absolute inset-0 opacity-10" style="background: linear-gradient(135deg, var(--color-accent) 0%, transparent 60%)"></div>
      <div class="relative z-10 flex flex-col sm:flex-row items-center sm:items-start gap-6">
        <!-- Avatar -->
        <div class="relative">
          <div class="w-28 h-28 rounded-full border-4 border-accent flex items-center justify-center shadow-[0_0_25px_rgba(37, 99, 235,0.3)]" style="background: var(--bg-input)">
            <span class="material-symbols-outlined text-accent text-6xl">person</span>
          </div>
          <button class="absolute bottom-0 right-0 w-9 h-9 rounded-full bg-accent flex items-center justify-center shadow-lg cursor-pointer hover:scale-110 transition-transform" style="color: var(--text-btn)">
            <span class="material-symbols-outlined text-[18px]">photo_camera</span>
          </button>
        </div>
        <!-- Info -->
        <div class="text-center sm:text-left flex-1">
          <h2 class="text-2xl font-bold" style="color: var(--text-heading)">Administrator</h2>
          <p class="text-sm mt-1" style="color: var(--text-muted)">admin@dalwavision.id</p>
          <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 mt-3">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-accent/20 text-accent border border-accent/30">
              <span class="material-symbols-outlined text-[14px]">shield</span>
              Super Admin
            </span>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold text-green-400 border border-green-500/30 bg-green-900/20">
              <span class="w-1.5 h-1.5 rounded-full bg-green-400 animate-pulse"></span>
              Online
            </span>
          </div>
          <p class="text-xs mt-3" style="color: var(--text-muted)">Bergabung sejak 1 Januari 2024 · Terakhir login: Hari ini, 21:30 WIB</p>
        </div>
        <!-- Edit Button -->
        <button class="btn-edit flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-bold transition-all cursor-pointer shrink-0">
          <span class="material-symbols-outlined text-[18px]">edit</span>
          Edit Profil
        </button>
      </div>
    </div>

    <!-- ═══ INFO GRID ═══ -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

      <!-- Personal Info -->
      <div class="info-card rounded-xl overflow-hidden">
        <div class="card-header px-6 py-4 flex items-center justify-between">
          <h3 class="font-bold flex items-center gap-2" style="color: var(--text-heading)">
            <span class="material-symbols-outlined text-accent text-[20px]">person</span>
            Informasi Pribadi
          </h3>
        </div>
        <div class="px-6 py-4 space-y-4">
          <div v-for="field in personalFields" :key="field.label" class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-0">
            <span class="text-sm font-medium w-40 shrink-0" style="color: var(--text-muted)">{{ field.label }}</span>
            <span class="text-sm font-bold" style="color: var(--text-heading)">{{ field.value }}</span>
          </div>
        </div>
      </div>

      <!-- Account Info -->
      <div class="info-card rounded-xl overflow-hidden">
        <div class="card-header px-6 py-4 flex items-center justify-between">
          <h3 class="font-bold flex items-center gap-2" style="color: var(--text-heading)">
            <span class="material-symbols-outlined text-accent text-[20px]">security</span>
            Informasi Akun
          </h3>
        </div>
        <div class="px-6 py-4 space-y-4">
          <div v-for="field in accountFields" :key="field.label" class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-0">
            <span class="text-sm font-medium w-40 shrink-0" style="color: var(--text-muted)">{{ field.label }}</span>
            <span class="text-sm font-bold" style="color: var(--text-heading)">{{ field.value }}</span>
          </div>
          <div class="pt-2">
            <button class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-bold transition-all cursor-pointer btn-secondary">
              <span class="material-symbols-outlined text-[18px]">lock_reset</span>
              Ubah Password
            </button>
          </div>
        </div>
      </div>

      <!-- Activity Log -->
      <div class="info-card rounded-xl overflow-hidden lg:col-span-2">
        <div class="card-header px-6 py-4 flex items-center justify-between">
          <h3 class="font-bold flex items-center gap-2" style="color: var(--text-heading)">
            <span class="material-symbols-outlined text-accent text-[20px]">history</span>
            Aktivitas Terakhir
          </h3>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="table-head">
                <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wider" style="color: var(--text-muted)">Waktu</th>
                <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wider" style="color: var(--text-muted)">Aktivitas</th>
                <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wider" style="color: var(--text-muted)">IP Address</th>
                <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wider" style="color: var(--text-muted)">Device</th>
              </tr>
            </thead>
            <tbody class="table-body">
              <tr v-for="log in activityLog" :key="log.time" class="table-row-hover">
                <td class="px-6 py-3 text-sm font-mono" style="color: var(--text-muted)">{{ log.time }}</td>
                <td class="px-6 py-3">
                  <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]" :class="log.iconColor">{{ log.icon }}</span>
                    <span class="text-sm font-medium" style="color: var(--text-heading)">{{ log.activity }}</span>
                  </div>
                </td>
                <td class="px-6 py-3 text-sm font-mono" style="color: var(--text-muted)">{{ log.ip }}</td>
                <td class="px-6 py-3 text-sm" style="color: var(--text-muted)">{{ log.device }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
const personalFields = [
  { label: 'Nama Lengkap', value: 'Muhammad Rizki Al-Farisi' },
  { label: 'Jabatan', value: 'IT Administrator' },
  { label: 'Unit', value: 'Divisi Media & Teknologi' },
  { label: 'No. Telepon', value: '+62 812-3456-7890' },
  { label: 'Alamat', value: 'Pondok Pesantren Dalwa, Pasuruan' }
]

const accountFields = [
  { label: 'Username', value: 'admin' },
  { label: 'Email', value: 'admin@dalwavision.id' },
  { label: 'Role', value: 'Super Admin' },
  { label: 'Status', value: 'Active' },
  { label: 'Last Login', value: 'Hari ini, 21:30 WIB' }
]

const activityLog = [
  { time: '21:30', icon: 'login', iconColor: 'text-green-400', activity: 'Login berhasil', ip: '192.168.1.100', device: 'Chrome / Windows' },
  { time: '20:15', icon: 'edit', iconColor: 'text-accent', activity: 'Mengedit konten "Info Terkini"', ip: '192.168.1.100', device: 'Chrome / Windows' },
  { time: '19:45', icon: 'upload', iconColor: 'text-blue-400', activity: 'Upload video "Kajian Pagi"', ip: '192.168.1.100', device: 'Chrome / Windows' },
  { time: '18:00', icon: 'person_add', iconColor: 'text-accent', activity: 'Menambah user baru', ip: '192.168.1.100', device: 'Chrome / Windows' },
  { time: '14:30', icon: 'logout', iconColor: 'text-red-400', activity: 'Logout', ip: '192.168.1.100', device: 'Chrome / Windows' }
]
</script>

<style scoped>
.profile-header { background: var(--bg-card); border: 1px solid var(--border); }
.info-card { background: var(--bg-card); border: 1px solid var(--border); }
.card-header { border-bottom: 1px solid var(--border); }

.btn-edit {
  background: var(--color-accent);
  color: var(--text-btn);
  box-shadow: 0 0 15px rgba(37, 99, 235, 0.2);
}
.btn-edit:hover {
  box-shadow: 0 0 25px rgba(37, 99, 235, 0.4);
  transform: translateY(-1px);
}

.btn-secondary {
  background: var(--bg-input);
  color: var(--text-heading);
  border: 1px solid var(--border);
}
.btn-secondary:hover {
  border-color: var(--color-accent);
  color: var(--color-accent);
}
</style>
