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
            <h4 class="text-sm font-bold" style="color: var(--text-heading)">Favicon</h4>
            <p class="text-xs mt-0.5" style="color: var(--text-muted)">Ikon kecil yang tampil di tab browser. Format: ico, png, jpg, svg, atau webp</p>
          </div>
          <div class="setting-control">
            <div class="flex items-center gap-4">
              <div class="w-16 h-16 rounded-xl flex items-center justify-center overflow-hidden border-2 border-dashed border-accent/40" style="background: var(--bg-input)">
                <img v-if="faviconPreview" :src="faviconPreview" alt="Favicon preview" class="h-10 w-10 object-contain" />
                <span v-else class="material-symbols-outlined text-accent text-3xl">web_asset</span>
              </div>
              <label class="upload-btn flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-bold cursor-pointer transition-all">
                <span class="material-symbols-outlined text-[16px]">cloud_upload</span>
                Upload Favicon
                <input class="sr-only" type="file" accept=".ico,.png,.jpg,.jpeg,.svg,.webp,image/*" @change="handleFaviconChange" />
              </label>
            </div>
          </div>
        </div>

        <div class="setting-row">
          <div class="setting-label">
            <h4 class="text-sm font-bold" style="color: var(--text-heading)">Brand Navbar Publik</h4>
            <p class="text-xs mt-0.5" style="color: var(--text-muted)">Gunakan ikon dan teks seperti tampilan saat ini, atau ganti seluruh brand dengan logo gambar</p>
          </div>
          <div class="setting-control">
            <div class="brand-settings-panel">
              <div class="brand-mode-switch">
                <button type="button" :class="{ active: navbarBrandForm.mode === 'text' }" @click="navbarBrandForm.mode = 'text'">
                  <span class="material-symbols-outlined">title</span>
                  Ikon + Teks
                </button>
                <button type="button" :class="{ active: navbarBrandForm.mode === 'logo' }" @click="navbarBrandForm.mode = 'logo'">
                  <span class="material-symbols-outlined">image</span>
                  Logo
                </button>
              </div>

              <div v-if="navbarBrandForm.mode === 'text'" class="brand-fields">
                <div class="brand-field">
                  <span>Icon Navbar</span>
                  <div class="brand-icon-select">
                    <span class="material-symbols-outlined">{{ navbarBrandForm.icon }}</span>
                    <select v-model="navbarBrandForm.icon" class="setting-input">
                      <option v-for="icon in navbarIconOptions" :key="icon.value" :value="icon.value">{{ icon.label }}</option>
                    </select>
                  </div>
                </div>
                <label class="brand-field">
                  <span>Judul Navbar</span>
                  <input v-model="navbarBrandForm.title" class="setting-input" maxlength="80" />
                </label>
                <label class="brand-field">
                  <span>Subtitle Navbar</span>
                  <input v-model="navbarBrandForm.subtitle" class="setting-input" maxlength="80" />
                </label>

                <div class="brand-text-preview">
                  <span class="brand-text-preview-icon material-symbols-outlined">{{ navbarBrandForm.icon }}</span>
                  <span>
                    <strong>{{ navbarBrandForm.title || 'Judul Navbar' }}</strong>
                    <small v-if="navbarBrandForm.subtitle">{{ navbarBrandForm.subtitle }}</small>
                  </span>
                </div>
              </div>

              <div v-else class="brand-logo-editor">
                <div class="brand-logo-preview">
                  <img v-if="navbarBrandLogoPreview" :src="navbarBrandLogoPreview" alt="Preview logo navbar" />
                  <span v-else class="material-symbols-outlined">image</span>
                </div>
                <label class="upload-btn flex w-fit items-center gap-2 px-4 py-2 rounded-lg text-sm font-bold cursor-pointer transition-all">
                  <span class="material-symbols-outlined text-[16px]">cloud_upload</span>
                  Upload Logo Navbar
                  <input class="sr-only" type="file" accept=".png,.jpg,.jpeg,.webp,image/png,image/jpeg,image/webp" @change="handleNavbarBrandLogoChange" />
                </label>
                <p class="text-xs" style="color: var(--text-muted)">Gunakan logo horizontal dengan background transparan. Maksimal 2 MB.</p>
              </div>
            </div>
          </div>
        </div>

        <div class="setting-row">
          <div class="setting-label">
            <h4 class="text-sm font-bold" style="color: var(--text-heading)">Kontak Footer Publik</h4>
            <p class="text-xs mt-0.5" style="color: var(--text-muted)">Informasi email, WhatsApp, dan lokasi yang tampil pada bagian Kontak di footer</p>
          </div>
          <div class="setting-control">
            <div class="footer-contact-settings">
              <label class="brand-field">
                <span>Email</span>
                <div class="contact-setting-field">
                  <span class="material-symbols-outlined">mail</span>
                  <input v-model="footerContactForm.email" type="email" class="setting-input" maxlength="255" />
                </div>
              </label>
              <label class="brand-field">
                <span>Nomor Telepon / WhatsApp</span>
                <div class="contact-setting-field">
                  <span class="material-symbols-outlined">chat</span>
                  <input v-model="footerContactForm.phone" class="setting-input" maxlength="40" />
                </div>
              </label>
              <label class="brand-field">
                <span>Lokasi</span>
                <div class="contact-setting-field">
                  <span class="material-symbols-outlined">location_on</span>
                  <input v-model="footerContactForm.location" class="setting-input" maxlength="160" />
                </div>
              </label>
              <label class="brand-field">
                <span>URL Google Maps</span>
                <div class="contact-setting-field">
                  <span class="material-symbols-outlined">map</span>
                  <input v-model="footerContactForm.mapsUrl" type="url" class="setting-input" maxlength="500" placeholder="https://maps.app.goo.gl/..." />
                </div>
              </label>
            </div>
          </div>
        </div>

        <div class="setting-row">
          <div class="setting-label">
            <h4 class="text-sm font-bold" style="color: var(--text-heading)">Gambar Hero Landing Page</h4>
            <p class="text-xs mt-0.5" style="color: var(--text-muted)">Gambar pada kartu utama landing page. Format: png, jpg, atau webp, maksimal 5 MB</p>
          </div>
          <div class="setting-control">
            <div class="image-upload-control">
              <div class="image-preview-shell">
                <img :src="landingHeroImagePreview" alt="Preview hero landing page" />
              </div>
              <label class="upload-btn flex w-fit items-center gap-2 px-4 py-2 rounded-lg text-sm font-bold cursor-pointer transition-all">
                <span class="material-symbols-outlined text-[16px]">add_photo_alternate</span>
                Pilih Gambar Hero
                <input class="sr-only" type="file" accept=".png,.jpg,.jpeg,.webp,image/png,image/jpeg,image/webp" @change="handleLandingHeroImageChange" />
              </label>
            </div>
          </div>
        </div>

        <div class="setting-row">
          <div class="setting-label">
            <h4 class="text-sm font-bold" style="color: var(--text-heading)">Gambar Halaman Login</h4>
            <p class="text-xs mt-0.5" style="color: var(--text-muted)">Gambar latar panel kiri pada halaman login. Format: png, jpg, atau webp, maksimal 5 MB</p>
          </div>
          <div class="setting-control">
            <div class="image-upload-control">
              <div class="image-preview-shell">
                <img :src="loginBackgroundImagePreview" alt="Preview gambar halaman login" />
              </div>
              <label class="upload-btn flex w-fit items-center gap-2 px-4 py-2 rounded-lg text-sm font-bold cursor-pointer transition-all">
                <span class="material-symbols-outlined text-[16px]">add_photo_alternate</span>
                Pilih Gambar Login
                <input class="sr-only" type="file" accept=".png,.jpg,.jpeg,.webp,image/png,image/jpeg,image/webp" @change="handleLoginBackgroundImageChange" />
              </label>
            </div>
          </div>
        </div>

        <Transition name="fade">
          <div v-if="generalMessage.text" class="message-box" :class="generalMessage.type">
            <span class="material-symbols-outlined text-[18px]">{{ generalMessage.type === 'success' ? 'check_circle' : 'error' }}</span>
            <span>{{ generalMessage.text }}</span>
          </div>
        </Transition>
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
            <div class="color-control">
              <label class="color-picker-shell" :style="{ '--picked-color': selectedAccent }">
                <input v-model="selectedAccent" type="color" @input="applyAccentColor(selectedAccent)" />
              </label>
              <input
                v-model="selectedAccent"
                class="setting-input color-code rounded-lg py-2.5 px-4 text-sm font-bold uppercase focus:outline-none focus:ring-1 focus:ring-accent"
                maxlength="7"
                @input="handleAccentInput"
              />
            </div>
            <div class="mt-4 flex items-center gap-3 flex-wrap">
              <button v-for="color in accentColors" :key="color.value"
                      @click="chooseAccent(color.value)"
                      :class="['w-10 h-10 rounded-xl cursor-pointer transition-all border-2', selectedAccent === color.value ? 'border-white scale-110 shadow-lg' : 'border-transparent hover:scale-105']"
                      :style="{ background: color.value }"
                      :title="color.name">
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="settings-card rounded-xl overflow-hidden">
      <div class="card-header px-6 py-4 flex items-center justify-between gap-4">
        <div class="flex items-center gap-2">
          <span class="material-symbols-outlined text-accent text-[20px]">outgoing_mail</span>
          <h3 class="font-bold" style="color: var(--text-heading)">Pengaturan Email SMTP</h3>
        </div>
        <button type="button" class="toggle-switch" :class="{ active: emailForm.smtp_enabled }" @click="emailForm.smtp_enabled = !emailForm.smtp_enabled">
          <span></span>
        </button>
      </div>
      <div class="px-6 py-5 space-y-6">
        <div class="guide-panel">
          <div class="guide-head">
            <span class="material-symbols-outlined text-[20px]">mail_lock</span>
            <div>
              <h4>Panduan Menyiapkan SMTP</h4>
              <p>SMTP dipakai untuk mengirim kode verifikasi saat user daftar dengan email.</p>
            </div>
          </div>

          <div class="guide-grid">
            <div v-for="step in emailGuideSteps" :key="step.title" class="guide-step">
              <span class="guide-step-icon material-symbols-outlined">{{ step.icon }}</span>
              <div>
                <h5>{{ step.title }}</h5>
                <p>{{ step.description }}</p>
              </div>
            </div>
          </div>

          <div class="guide-notes">
            <div>
              <h5>Gmail / Google Workspace</h5>
              <p>Gunakan App Password, bukan password akun utama. Host: <code>smtp.gmail.com</code>, port <code>587</code>, encryption <code>tls</code>.</p>
            </div>
            <div>
              <h5>Mailtrap / Sandbox</h5>
              <p>Salin host, port, username, dan password dari inbox testing. Cocok untuk development sebelum memakai email production.</p>
            </div>
            <div>
              <h5>From Address</h5>
              <p>Gunakan email yang diizinkan provider SMTP. Beberapa provider menolak pengiriman jika From Address berbeda dari akun SMTP.</p>
            </div>
          </div>
        </div>

        <div class="setting-row">
          <div class="setting-label">
            <h4 class="text-sm font-bold" style="color: var(--text-heading)">Status SMTP</h4>
            <p class="text-xs mt-0.5" style="color: var(--text-muted)">Aktifkan agar sistem bisa mengirim kode verifikasi email</p>
          </div>
          <div class="setting-control">
            <label class="inline-flex items-center gap-3 cursor-pointer">
              <input v-model="emailForm.smtp_enabled" type="checkbox" class="sr-only" />
              <span class="checkbox-box">
                <span class="material-symbols-outlined text-[15px]">check</span>
              </span>
              <span class="text-sm font-bold" style="color: var(--text-heading)">
                {{ emailForm.smtp_enabled ? 'Aktif' : 'Nonaktif' }}
              </span>
            </label>
          </div>
        </div>

        <div class="setting-row">
          <div class="setting-label">
            <h4 class="text-sm font-bold" style="color: var(--text-heading)">Host SMTP</h4>
            <p class="text-xs mt-0.5" style="color: var(--text-muted)">Alamat server SMTP provider email</p>
          </div>
          <div class="setting-control">
            <input v-model="emailForm.smtp_host" class="setting-input w-full rounded-lg py-2.5 px-4 text-sm focus:outline-none focus:ring-1 focus:ring-accent" placeholder="smtp.gmail.com" />
          </div>
        </div>

        <div class="setting-row">
          <div class="setting-label">
            <h4 class="text-sm font-bold" style="color: var(--text-heading)">Port & Encryption</h4>
            <p class="text-xs mt-0.5" style="color: var(--text-muted)">Umumnya 587/tls atau 465/ssl</p>
          </div>
          <div class="setting-control grid gap-3 sm:grid-cols-[1fr_1fr]">
            <input v-model.number="emailForm.smtp_port" class="setting-input rounded-lg py-2.5 px-4 text-sm focus:outline-none focus:ring-1 focus:ring-accent" type="number" min="1" max="65535" placeholder="587" />
            <select v-model="emailForm.smtp_encryption" class="setting-input rounded-lg py-2.5 px-4 text-sm focus:outline-none focus:ring-1 focus:ring-accent">
              <option value="tls">TLS</option>
              <option value="ssl">SSL</option>
              <option value="">Tanpa encryption</option>
            </select>
          </div>
        </div>

        <div class="setting-row">
          <div class="setting-label">
            <h4 class="text-sm font-bold" style="color: var(--text-heading)">Username SMTP</h4>
            <p class="text-xs mt-0.5" style="color: var(--text-muted)">Biasanya alamat email atau username dari provider</p>
          </div>
          <div class="setting-control">
            <input v-model="emailForm.smtp_username" class="setting-input w-full rounded-lg py-2.5 px-4 text-sm focus:outline-none focus:ring-1 focus:ring-accent" placeholder="no-reply@domain.com" />
          </div>
        </div>

        <div class="setting-row">
          <div class="setting-label">
            <h4 class="text-sm font-bold" style="color: var(--text-heading)">Password SMTP</h4>
            <p class="text-xs mt-0.5" style="color: var(--text-muted)">Untuk Gmail gunakan App Password</p>
          </div>
          <div class="setting-control">
            <input v-model="emailForm.smtp_password" class="setting-input w-full rounded-lg py-2.5 px-4 text-sm focus:outline-none focus:ring-1 focus:ring-accent" type="password" autocomplete="new-password" placeholder="••••••••" />
          </div>
        </div>

        <div class="setting-row">
          <div class="setting-label">
            <h4 class="text-sm font-bold" style="color: var(--text-heading)">From Email & Nama</h4>
            <p class="text-xs mt-0.5" style="color: var(--text-muted)">Identitas pengirim email verifikasi</p>
          </div>
          <div class="setting-control grid gap-3 sm:grid-cols-[1fr_1fr]">
            <input v-model="emailForm.smtp_from_address" class="setting-input rounded-lg py-2.5 px-4 text-sm focus:outline-none focus:ring-1 focus:ring-accent" type="email" placeholder="no-reply@domain.com" />
            <input v-model="emailForm.smtp_from_name" class="setting-input rounded-lg py-2.5 px-4 text-sm focus:outline-none focus:ring-1 focus:ring-accent" placeholder="UII Dalwa Press" />
          </div>
        </div>

        <div class="setting-row">
          <div class="setting-label">
            <h4 class="text-sm font-bold" style="color: var(--text-heading)">Role Default Pendaftar Email</h4>
            <p class="text-xs mt-0.5" style="color: var(--text-muted)">Role user baru setelah kode email berhasil diverifikasi</p>
          </div>
          <div class="setting-control">
            <select v-model="emailForm.email_registration_default_role_id" class="setting-input w-full rounded-lg py-2.5 px-4 text-sm focus:outline-none focus:ring-1 focus:ring-accent">
              <option :value="null">Pilih role default</option>
              <option v-for="role in emailRoles" :key="role.id" :value="role.id">{{ role.name }}</option>
            </select>
          </div>
        </div>

        <Transition name="fade">
          <div v-if="emailMessage.text" class="message-box" :class="emailMessage.type">
            <span class="material-symbols-outlined text-[18px]">{{ emailMessage.type === 'success' ? 'check_circle' : 'error' }}</span>
            <span>{{ emailMessage.text }}</span>
          </div>
        </Transition>
      </div>
    </div>

    <div class="settings-card rounded-xl overflow-hidden">
      <div class="card-header px-6 py-4 flex items-center justify-between gap-4">
        <div class="flex items-center gap-2">
          <span class="material-symbols-outlined text-accent text-[20px]">passkey</span>
          <h3 class="font-bold" style="color: var(--text-heading)">Login Google</h3>
        </div>
        <button
          type="button"
          class="toggle-switch"
          :class="{ active: googleForm.google_login_enabled }"
          @click="googleForm.google_login_enabled = !googleForm.google_login_enabled"
        >
          <span></span>
        </button>
      </div>
      <div class="px-6 py-5 space-y-6">
        <div class="guide-panel">
          <div class="guide-head">
            <span class="material-symbols-outlined text-[20px]">school</span>
            <div>
              <h4>Panduan Mendapatkan Konfigurasi Google</h4>
              <p>Ikuti urutan ini sebelum mengaktifkan tombol Login Google.</p>
            </div>
          </div>

          <div class="guide-grid">
            <div v-for="step in googleGuideSteps" :key="step.title" class="guide-step">
              <span class="guide-step-icon material-symbols-outlined">{{ step.icon }}</span>
              <div>
                <h5>{{ step.title }}</h5>
                <p>{{ step.description }}</p>
              </div>
            </div>
          </div>

          <div class="guide-notes">
            <div>
              <h5>Authorized JavaScript origins</h5>
              <p>Masukkan origin frontend, tanpa path. Contoh lokal: <code>http://localhost:5173</code>. Contoh production: <code>https://domain-anda.com</code>.</p>
            </div>
            <div>
              <h5>Field yang dipakai sistem</h5>
              <p><strong>Client ID</strong> wajib untuk tombol login dan verifikasi token. <strong>Client Secret</strong> disediakan untuk kebutuhan OAuth lanjutan, tetapi flow saat ini memakai ID token.</p>
            </div>
            <div>
              <h5>Domain dan user baru</h5>
              <p>Isi pembatas domain jika hanya email institusi yang boleh login. Jika auto buat user aktif, pilih role default agar akun Google baru langsung punya akses dashboard.</p>
            </div>
          </div>

          <a
            class="guide-link"
            href="https://developers.google.com/identity/gsi/web/guides/get-google-api-clientid"
            target="_blank"
            rel="noopener noreferrer"
          >
            <span class="material-symbols-outlined text-[16px]">open_in_new</span>
            Dokumentasi resmi Google Identity Services
          </a>
        </div>

        <div class="setting-row">
          <div class="setting-label">
            <h4 class="text-sm font-bold" style="color: var(--text-heading)">Status Login Google</h4>
            <p class="text-xs mt-0.5" style="color: var(--text-muted)">Aktifkan tombol login Google di halaman login publik</p>
          </div>
          <div class="setting-control">
            <label class="inline-flex items-center gap-3 cursor-pointer">
              <input v-model="googleForm.google_login_enabled" type="checkbox" class="sr-only" />
              <span class="checkbox-box">
                <span class="material-symbols-outlined text-[15px]">check</span>
              </span>
              <span class="text-sm font-bold" style="color: var(--text-heading)">
                {{ googleForm.google_login_enabled ? 'Aktif' : 'Nonaktif' }}
              </span>
            </label>
          </div>
        </div>

        <div class="setting-row">
          <div class="setting-label">
            <h4 class="text-sm font-bold" style="color: var(--text-heading)">Google Client ID</h4>
            <p class="text-xs mt-0.5" style="color: var(--text-muted)">OAuth Web Client ID dari Google Cloud Console</p>
          </div>
          <div class="setting-control">
            <input
              v-model="googleForm.google_client_id"
              class="setting-input w-full rounded-lg py-2.5 px-4 text-sm focus:outline-none focus:ring-1 focus:ring-accent"
              placeholder="xxxxxxxxxxxx-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx.apps.googleusercontent.com"
            />
          </div>
        </div>

        <div class="setting-row">
          <div class="setting-label">
            <h4 class="text-sm font-bold" style="color: var(--text-heading)">Google Client Secret</h4>
            <p class="text-xs mt-0.5" style="color: var(--text-muted)">Disimpan untuk kebutuhan OAuth lanjutan jika nanti diperlukan</p>
          </div>
          <div class="setting-control">
            <input
              v-model="googleForm.google_client_secret"
              class="setting-input w-full rounded-lg py-2.5 px-4 text-sm focus:outline-none focus:ring-1 focus:ring-accent"
              placeholder="GOCSPX-..."
              type="password"
              autocomplete="new-password"
            />
          </div>
        </div>

        <div class="setting-row">
          <div class="setting-label">
            <h4 class="text-sm font-bold" style="color: var(--text-heading)">Batasi Domain Email</h4>
            <p class="text-xs mt-0.5" style="color: var(--text-muted)">Opsional, contoh: uiidalwa.ac.id. Kosongkan untuk semua domain Google</p>
          </div>
          <div class="setting-control">
            <input
              v-model="googleForm.google_hosted_domain"
              class="setting-input w-full rounded-lg py-2.5 px-4 text-sm focus:outline-none focus:ring-1 focus:ring-accent"
              placeholder="uiidalwa.ac.id"
            />
          </div>
        </div>

        <div class="setting-row">
          <div class="setting-label">
            <h4 class="text-sm font-bold" style="color: var(--text-heading)">Auto Buat User</h4>
            <p class="text-xs mt-0.5" style="color: var(--text-muted)">Jika aktif, akun Google baru akan dibuat otomatis dengan role default</p>
          </div>
          <div class="setting-control">
            <label class="inline-flex items-center gap-3 cursor-pointer">
              <input v-model="googleForm.google_auto_create_users" type="checkbox" class="sr-only" />
              <span class="checkbox-box">
                <span class="material-symbols-outlined text-[15px]">check</span>
              </span>
              <span class="text-sm font-bold" style="color: var(--text-heading)">
                {{ googleForm.google_auto_create_users ? 'Aktif' : 'Nonaktif' }}
              </span>
            </label>
          </div>
        </div>

        <div class="setting-row">
          <div class="setting-label">
            <h4 class="text-sm font-bold" style="color: var(--text-heading)">Role Default</h4>
            <p class="text-xs mt-0.5" style="color: var(--text-muted)">Role untuk user yang dibuat otomatis dari Google</p>
          </div>
          <div class="setting-control">
            <select
              v-model="googleForm.google_default_role_id"
              class="setting-input w-full rounded-lg py-2.5 px-4 text-sm focus:outline-none focus:ring-1 focus:ring-accent"
            >
              <option :value="null">Pilih role default</option>
              <option v-for="role in googleRoles" :key="role.id" :value="role.id">{{ role.name }}</option>
            </select>
          </div>
        </div>

        <Transition name="fade">
          <div v-if="googleMessage.text" class="message-box" :class="googleMessage.type">
            <span class="material-symbols-outlined text-[18px]">{{ googleMessage.type === 'success' ? 'check_circle' : 'error' }}</span>
            <span>{{ googleMessage.text }}</span>
          </div>
        </Transition>
      </div>
    </div>

    <div class="save-bar rounded-xl p-4 flex flex-col sm:flex-row items-center justify-between gap-4 sticky bottom-0 z-10">
      <p class="text-sm" style="color: var(--text-muted)">
        <span class="material-symbols-outlined text-accent text-[16px] align-text-bottom mr-1">info</span>
        Pengaturan ini disiapkan untuk kebutuhan identitas UII Dalwa Press.
      </p>
      <button
        class="save-btn flex items-center gap-2 px-6 py-2.5 rounded-lg text-sm font-bold cursor-pointer transition-all active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-60"
        :disabled="isSaving"
        @click="saveAllSettings"
      >
        <span v-if="!isSaving" class="material-symbols-outlined text-[16px]">save</span>
        {{ isSaving ? 'Processing...' : 'Simpan Pengaturan' }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import api from '../../../axios'
import { syncPublicSettings } from '../../../services/publicSettings'
import { assetUrl } from '../../../utils/asset'

const systemName = ref('UII Dalwa Press')
const selectedAccent = ref('#2563eb')
const faviconFile = ref(null)
const faviconPreview = ref('')
const navbarBrandLogoFile = ref(null)
const navbarBrandLogoPreview = ref('')
const landingHeroImageFile = ref(null)
const landingHeroImagePreview = ref('/img/hero-bg.jpg')
const loginBackgroundImageFile = ref(null)
const loginBackgroundImagePreview = ref('/img/hero-bg.jpg')
const savingGeneral = ref(false)
const savingEmail = ref(false)
const savingGoogle = ref(false)
const googleRoles = ref([])
const emailRoles = ref([])
const isSaving = computed(() => savingGeneral.value || savingEmail.value || savingGoogle.value)
const generalMessage = reactive({
  type: '',
  text: '',
})
const navbarBrandForm = reactive({
  mode: 'text',
  icon: 'auto_stories',
  title: 'UII Dalwa',
  subtitle: 'Press Publisher',
})
const footerContactForm = reactive({
  email: 'press@uiidalwa.ac.id',
  phone: '+62 812 0000 0000',
  location: 'Pasuruan, Jawa Timur',
  mapsUrl: 'https://maps.app.goo.gl/pNNU4dnCtuu7y4Qk6',
})
const navbarIconOptions = [
  { value: 'auto_stories', label: 'Auto Stories' },
  { value: 'menu_book', label: 'Menu Book' },
  { value: 'school', label: 'School' },
  { value: 'local_library', label: 'Local Library' },
  { value: 'import_contacts', label: 'Import Contacts' },
  { value: 'history_edu', label: 'History Edu' },
  { value: 'edit_note', label: 'Edit Note' },
  { value: 'collections_bookmark', label: 'Collections Bookmark' },
]
const emailMessage = reactive({
  type: '',
  text: '',
})
const googleMessage = reactive({
  type: '',
  text: '',
})
const googleForm = reactive({
  google_login_enabled: false,
  google_client_id: '',
  google_client_secret: '',
  google_hosted_domain: '',
  google_auto_create_users: false,
  google_default_role_id: null,
})
const emailForm = reactive({
  smtp_enabled: false,
  smtp_host: '',
  smtp_port: 587,
  smtp_username: '',
  smtp_password: '',
  smtp_encryption: 'tls',
  smtp_from_address: '',
  smtp_from_name: 'UII Dalwa Press',
  email_registration_default_role_id: null,
})
const emailGuideSteps = [
  {
    icon: 'alternate_email',
    title: 'Siapkan Akun Pengirim',
    description: 'Gunakan email resmi atau email khusus no-reply agar kode verifikasi tidak tercampur dengan akun pribadi.',
  },
  {
    icon: 'vpn_key',
    title: 'Ambil Kredensial SMTP',
    description: 'Salin host, port, username, password atau app password dari provider email yang digunakan.',
  },
  {
    icon: 'encrypted',
    title: 'Pilih Encryption',
    description: 'Pilih TLS untuk port 587, SSL untuk port 465, atau kosongkan hanya jika provider meminta tanpa enkripsi.',
  },
  {
    icon: 'outgoing_mail',
    title: 'Isi From Email',
    description: 'Pastikan From Address diizinkan oleh provider SMTP supaya email tidak ditolak.',
  },
  {
    icon: 'group_add',
    title: 'Pilih Role Default',
    description: 'Role ini diberikan ke user baru setelah berhasil memasukkan kode verifikasi email.',
  },
  {
    icon: 'save',
    title: 'Simpan dan Coba Daftar',
    description: 'Setelah disimpan, coba daftar akun dari halaman login untuk memastikan email kode terkirim.',
  },
]
const googleGuideSteps = [
  {
    icon: 'cloud',
    title: 'Buka Google Cloud Console',
    description: 'Buat project baru atau pilih project yang sudah dipakai untuk UII Dalwa Press.',
  },
  {
    icon: 'branding_watermark',
    title: 'Lengkapi OAuth Branding',
    description: 'Isi nama aplikasi, support email, authorized domain, homepage, privacy policy, dan terms jika tersedia.',
  },
  {
    icon: 'key',
    title: 'Buat OAuth Client',
    description: 'Masuk ke Clients atau Credentials, pilih Create client, lalu pilih Application type: Web application.',
  },
  {
    icon: 'language',
    title: 'Tambahkan Origin Frontend',
    description: 'Masukkan origin website ke Authorized JavaScript origins, termasuk origin lokal saat development.',
  },
  {
    icon: 'content_copy',
    title: 'Salin Client ID',
    description: 'Tempel Client ID ke form di bawah. Client ID biasanya berakhiran .apps.googleusercontent.com.',
  },
  {
    icon: 'toggle_on',
    title: 'Aktifkan dan Simpan',
    description: 'Aktifkan Login Google, atur domain atau auto-create jika perlu, lalu simpan pengaturan.',
  },
]
const accentColors = [
  { name: 'Blue', value: '#2563eb' },
  { name: 'Emerald', value: '#047857' },
  { name: 'Slate', value: '#0f172a' },
  { name: 'Rose', value: '#e11d48' },
  { name: 'Cyan', value: '#0891b2' }
]

function isHexColor(value) {
  return /^#[0-9a-fA-F]{6}$/.test(value)
}

function applyAccentColor(value) {
  if (!isHexColor(value)) return

  const root = document.querySelector('.admin-root, .author-root, .editor-root, .portal-root')
  root?.style.setProperty('--color-accent', value)
}

function applyFavicon(url) {
  if (!url) return

  let favicon = document.querySelector("link[rel='icon']")
  if (!favicon) {
    favicon = document.createElement('link')
    favicon.rel = 'icon'
    document.head.appendChild(favicon)
  }
  favicon.href = url
}

function fillGeneralForm(data) {
  const publicSettings = syncPublicSettings(data)
  systemName.value = data.system_name || 'UII Dalwa Press'
  selectedAccent.value = data.accent_color || '#2563eb'
  faviconPreview.value = assetUrl(data.favicon_url)
  navbarBrandForm.mode = publicSettings.navbarBrandMode
  navbarBrandForm.icon = publicSettings.navbarBrandIcon
  navbarBrandForm.title = publicSettings.navbarBrandTitle
  navbarBrandForm.subtitle = publicSettings.navbarBrandSubtitle
  navbarBrandLogoPreview.value = publicSettings.navbarBrandLogoUrl
  footerContactForm.email = publicSettings.footerContactEmail
  footerContactForm.phone = publicSettings.footerContactPhone
  footerContactForm.location = publicSettings.footerContactLocation
  footerContactForm.mapsUrl = publicSettings.footerContactMapsUrl
  landingHeroImagePreview.value = publicSettings.landingHeroImageUrl
  loginBackgroundImagePreview.value = publicSettings.loginBackgroundImageUrl
  applyAccentColor(selectedAccent.value)
  applyFavicon(faviconPreview.value)
}

function chooseAccent(value) {
  selectedAccent.value = value
  applyAccentColor(value)
}

function handleAccentInput() {
  selectedAccent.value = selectedAccent.value.trim()
  applyAccentColor(selectedAccent.value)
}

function handleFaviconChange(event) {
  const file = event.target.files?.[0]
  if (!file) return

  faviconFile.value = file
  faviconPreview.value = URL.createObjectURL(file)
}

function handleNavbarBrandLogoChange(event) {
  const file = event.target.files?.[0]
  if (!file) return

  navbarBrandLogoFile.value = file
  navbarBrandLogoPreview.value = URL.createObjectURL(file)
}

function handleLandingHeroImageChange(event) {
  const file = event.target.files?.[0]
  if (!file) return

  landingHeroImageFile.value = file
  landingHeroImagePreview.value = URL.createObjectURL(file)
}

function handleLoginBackgroundImageChange(event) {
  const file = event.target.files?.[0]
  if (!file) return

  loginBackgroundImageFile.value = file
  loginBackgroundImagePreview.value = URL.createObjectURL(file)
}

async function loadGeneralSettings() {
  try {
    const { data } = await api.get('/settings/general')
    fillGeneralForm(data)
  } catch {
    generalMessage.type = 'error'
    generalMessage.text = 'Gagal memuat pengaturan umum.'
  }
}

async function saveGeneralSettings() {
  if (!isHexColor(selectedAccent.value)) {
    generalMessage.type = 'error'
    generalMessage.text = 'Warna aksen harus memakai format hex, contoh #2563eb.'
    return false
  }

  savingGeneral.value = true
  generalMessage.type = ''
  generalMessage.text = ''

  const formData = new FormData()
  formData.append('system_name', systemName.value)
  formData.append('accent_color', selectedAccent.value)
  formData.append('navbar_brand_mode', navbarBrandForm.mode)
  formData.append('navbar_brand_icon', navbarBrandForm.icon)
  formData.append('navbar_brand_title', navbarBrandForm.title)
  formData.append('navbar_brand_subtitle', navbarBrandForm.subtitle)
  formData.append('footer_contact_email', footerContactForm.email)
  formData.append('footer_contact_phone', footerContactForm.phone)
  formData.append('footer_contact_location', footerContactForm.location)
  formData.append('footer_contact_maps_url', footerContactForm.mapsUrl)
  if (faviconFile.value) {
    formData.append('favicon', faviconFile.value)
  }
  if (landingHeroImageFile.value) {
    formData.append('landing_hero_image', landingHeroImageFile.value)
  }
  if (loginBackgroundImageFile.value) {
    formData.append('login_background_image', loginBackgroundImageFile.value)
  }
  if (navbarBrandLogoFile.value) {
    formData.append('navbar_brand_logo', navbarBrandLogoFile.value)
  }

  try {
    const { data } = await api.post('/settings/general', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    })
    faviconFile.value = null
    navbarBrandLogoFile.value = null
    landingHeroImageFile.value = null
    loginBackgroundImageFile.value = null
    fillGeneralForm(data)
    generalMessage.type = 'success'
    generalMessage.text = 'Pengaturan umum berhasil disimpan.'
    return true
  } catch (error) {
    const errors = error.response?.data?.errors || {}
    generalMessage.type = 'error'
    generalMessage.text = Object.values(errors)?.[0]?.[0] || 'Gagal menyimpan pengaturan umum.'
    return false
  } finally {
    savingGeneral.value = false
  }
}

function fillGoogleForm(data) {
  googleForm.google_login_enabled = Boolean(data.google_login_enabled)
  googleForm.google_client_id = data.google_client_id || ''
  googleForm.google_client_secret = data.google_client_secret || ''
  googleForm.google_hosted_domain = data.google_hosted_domain || ''
  googleForm.google_auto_create_users = Boolean(data.google_auto_create_users)
  googleForm.google_default_role_id = data.google_default_role_id ? Number(data.google_default_role_id) : null
  googleRoles.value = data.roles || googleRoles.value
}

function fillEmailForm(data) {
  emailForm.smtp_enabled = Boolean(data.smtp_enabled)
  emailForm.smtp_host = data.smtp_host || ''
  emailForm.smtp_port = data.smtp_port || 587
  emailForm.smtp_username = data.smtp_username || ''
  emailForm.smtp_password = data.smtp_password || ''
  emailForm.smtp_encryption = data.smtp_encryption ?? 'tls'
  emailForm.smtp_from_address = data.smtp_from_address || ''
  emailForm.smtp_from_name = data.smtp_from_name || systemName.value
  emailForm.email_registration_default_role_id = data.email_registration_default_role_id ? Number(data.email_registration_default_role_id) : null
  emailRoles.value = data.roles || emailRoles.value
}

async function loadEmailSettings() {
  try {
    const { data } = await api.get('/settings/email')
    fillEmailForm(data)
  } catch {
    emailMessage.type = 'error'
    emailMessage.text = 'Gagal memuat pengaturan email.'
  }
}

async function saveEmailSettings() {
  savingEmail.value = true
  emailMessage.type = ''
  emailMessage.text = ''

  try {
    const { data } = await api.put('/settings/email', {
      ...emailForm,
      email_registration_default_role_id: emailForm.email_registration_default_role_id || null,
    })
    fillEmailForm(data)
    emailMessage.type = 'success'
    emailMessage.text = 'Pengaturan email berhasil disimpan.'
    return true
  } catch (error) {
    const errors = error.response?.data?.errors || {}
    emailMessage.type = 'error'
    emailMessage.text = Object.values(errors)?.[0]?.[0] || 'Gagal menyimpan pengaturan email.'
    return false
  } finally {
    savingEmail.value = false
  }
}

async function loadGoogleSettings() {
  try {
    const { data } = await api.get('/settings/google-login')
    fillGoogleForm(data)
  } catch {
    googleMessage.type = 'error'
    googleMessage.text = 'Gagal memuat pengaturan Login Google.'
  }
}

async function saveGoogleSettings() {
  savingGoogle.value = true
  googleMessage.type = ''
  googleMessage.text = ''

  try {
    const { data } = await api.put('/settings/google-login', {
      ...googleForm,
      google_default_role_id: googleForm.google_default_role_id || null,
    })
    fillGoogleForm(data)
    googleMessage.type = 'success'
    googleMessage.text = 'Pengaturan Login Google berhasil disimpan.'
  } catch (error) {
    const errors = error.response?.data?.errors || {}
    googleMessage.type = 'error'
    googleMessage.text = Object.values(errors)?.[0]?.[0] || 'Gagal menyimpan pengaturan Login Google.'
  } finally {
    savingGoogle.value = false
  }
}

async function saveAllSettings() {
  const generalSaved = await saveGeneralSettings()
  if (generalSaved) {
    await saveEmailSettings()
    await saveGoogleSettings()
  }
}

onMounted(() => {
  loadGeneralSettings()
  loadEmailSettings()
  loadGoogleSettings()
})
</script>

<style scoped>
.settings-card { background: var(--bg-card); border: 1px solid var(--border); }
.card-header { border-bottom: 1px solid var(--border); }
.guide-panel {
  border: 1px solid var(--border);
  border-radius: 1rem;
  background: color-mix(in srgb, var(--bg-input) 72%, transparent);
  padding: 1rem;
}
.guide-head {
  display: flex;
  gap: 0.8rem;
  align-items: flex-start;
  color: var(--text-heading);
}
.guide-head > span {
  display: inline-flex;
  width: 2.25rem;
  height: 2.25rem;
  align-items: center;
  justify-content: center;
  border-radius: 0.75rem;
  background: rgba(37, 99, 235, 0.12);
  color: var(--color-accent);
}
.guide-head h4 {
  font-size: 0.95rem;
  font-weight: 900;
}
.guide-head p,
.guide-step p,
.guide-notes p {
  color: var(--text-muted);
}
.guide-head p {
  margin-top: 0.15rem;
  font-size: 0.78rem;
  line-height: 1.5;
}
.guide-grid {
  display: grid;
  gap: 0.75rem;
  margin-top: 1rem;
}
.guide-step {
  display: grid;
  grid-template-columns: auto 1fr;
  gap: 0.7rem;
  border: 1px solid var(--border);
  border-radius: 0.85rem;
  background: var(--bg-card);
  padding: 0.85rem;
}
.guide-step-icon {
  color: var(--color-accent);
  font-size: 1.25rem;
}
.guide-step h5,
.guide-notes h5 {
  color: var(--text-heading);
  font-size: 0.8rem;
  font-weight: 900;
}
.guide-step p,
.guide-notes p {
  margin-top: 0.25rem;
  font-size: 0.76rem;
  line-height: 1.55;
}
.guide-notes {
  display: grid;
  gap: 0.75rem;
  margin-top: 1rem;
}
.guide-notes > div {
  border-left: 3px solid var(--color-accent);
  padding-left: 0.8rem;
}
.guide-notes code {
  border: 1px solid var(--border);
  border-radius: 0.35rem;
  background: var(--bg-card);
  color: var(--text-heading);
  padding: 0.08rem 0.28rem;
  font-size: 0.72rem;
}
.guide-link {
  display: inline-flex;
  width: fit-content;
  align-items: center;
  gap: 0.4rem;
  margin-top: 1rem;
  color: var(--color-accent);
  font-size: 0.78rem;
  font-weight: 900;
  transition: opacity 0.2s ease;
}
.guide-link:hover {
  opacity: 0.76;
}
.setting-row { display: flex; flex-direction: column; gap: 0.75rem; }
@media (min-width: 768px) {
  .setting-row { flex-direction: row; align-items: flex-start; }
  .setting-label { width: 280px; flex-shrink: 0; padding-top: 0.5rem; }
  .setting-control { flex: 1; }
  .guide-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
  .guide-notes {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }
}
.setting-input {
  background: var(--bg-input);
  border: 1px solid var(--border);
  color: var(--text-heading);
}
.setting-input:focus { border-color: var(--color-accent); box-shadow: 0 0 12px rgba(37, 99, 235, 0.3); }
.color-control {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.8rem;
}
.color-picker-shell {
  position: relative;
  display: inline-flex;
  width: 3rem;
  height: 3rem;
  cursor: pointer;
  overflow: hidden;
  border-radius: 0.9rem;
  border: 2px solid var(--border);
  background: var(--picked-color);
  box-shadow: 0 12px 26px rgba(0, 0, 0, 0.12);
}
.color-picker-shell input {
  position: absolute;
  inset: -0.5rem;
  width: 4rem;
  height: 4rem;
  cursor: pointer;
  opacity: 0;
}
.color-code {
  width: min(12rem, 100%);
}
.upload-btn {
  background: var(--bg-input);
  color: var(--text-heading);
  border: 1px solid var(--border);
}
.upload-btn:hover { border-color: var(--color-accent); color: var(--color-accent); }
.brand-settings-panel,
.brand-fields,
.brand-logo-editor {
  display: grid;
  gap: 1rem;
}
.brand-mode-switch {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 0.4rem;
  width: min(28rem, 100%);
  border: 1px solid var(--border);
  border-radius: 0.9rem;
  background: var(--bg-input);
  padding: 0.35rem;
}
.brand-mode-switch button {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.45rem;
  border-radius: 0.65rem;
  padding: 0.65rem 0.8rem;
  color: var(--text-muted);
  font-size: 0.82rem;
  font-weight: 900;
  transition: background 0.2s ease, color 0.2s ease, box-shadow 0.2s ease;
}
.brand-mode-switch button.active {
  background: var(--color-accent);
  color: var(--text-btn);
  box-shadow: 0 10px 24px rgba(37, 99, 235, 0.2);
}
.brand-mode-switch .material-symbols-outlined {
  font-size: 1.15rem;
}
.brand-field {
  display: grid;
  gap: 0.45rem;
}
.brand-field > span {
  color: var(--text-heading);
  font-size: 0.8rem;
  font-weight: 900;
}
.brand-field .setting-input {
  width: 100%;
  border-radius: 0.75rem;
  padding: 0.75rem 0.9rem;
}
.brand-icon-select {
  position: relative;
}
.brand-icon-select > .material-symbols-outlined {
  position: absolute;
  z-index: 1;
  left: 0.9rem;
  top: 50%;
  color: var(--color-accent);
  transform: translateY(-50%);
}
.brand-icon-select select {
  appearance: none;
  padding-left: 2.8rem !important;
}
.brand-text-preview {
  display: inline-flex;
  width: fit-content;
  min-width: 13rem;
  align-items: center;
  gap: 0.65rem;
  border-radius: 1rem;
  background: #0b1020;
  padding: 0.65rem 1rem 0.65rem 0.7rem;
  color: white;
}
.brand-text-preview-icon {
  display: inline-flex;
  width: 2.25rem;
  height: 2.25rem;
  align-items: center;
  justify-content: center;
  border: 1px solid rgba(125, 211, 252, 0.38);
  border-radius: 999px;
  background: rgba(56, 189, 248, 0.16);
  color: rgb(125 211 252);
}
.brand-text-preview strong,
.brand-text-preview small {
  display: block;
}
.brand-text-preview strong {
  font-size: 0.95rem;
  line-height: 1.2;
}
.brand-text-preview small {
  margin-top: 0.15rem;
  color: rgb(125 211 252);
  font-size: 0.64rem;
  font-weight: 900;
  letter-spacing: 0.04em;
  text-transform: uppercase;
}
.brand-logo-preview {
  display: flex;
  width: min(28rem, 100%);
  min-height: 7rem;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  border: 2px dashed var(--border);
  border-radius: 1rem;
  background: var(--bg-input);
  color: var(--text-muted);
}
.brand-logo-preview img {
  max-width: 100%;
  max-height: 6rem;
  object-fit: contain;
}
.footer-contact-settings {
  display: grid;
  gap: 0.9rem;
}
.contact-setting-field {
  position: relative;
}
.contact-setting-field > .material-symbols-outlined {
  position: absolute;
  z-index: 1;
  left: 0.9rem;
  top: 50%;
  color: var(--color-accent);
  font-size: 1.2rem;
  transform: translateY(-50%);
}
.contact-setting-field .setting-input {
  padding-left: 2.8rem;
}
.image-upload-control {
  display: grid;
  gap: 0.8rem;
}
.image-preview-shell {
  width: min(26rem, 100%);
  aspect-ratio: 16 / 9;
  overflow: hidden;
  border: 1px solid var(--border);
  border-radius: 0.9rem;
  background: var(--bg-input);
}
.image-preview-shell img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
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
.toggle-switch {
  width: 3rem;
  height: 1.65rem;
  border-radius: 999px;
  padding: 0.2rem;
  background: var(--bg-input);
  border: 1px solid var(--border);
  transition: background 0.2s ease, border-color 0.2s ease;
}
.toggle-switch span {
  display: block;
  width: 1.15rem;
  height: 1.15rem;
  border-radius: 999px;
  background: var(--text-muted);
  transition: transform 0.2s ease, background 0.2s ease;
}
.toggle-switch.active {
  background: rgba(37, 99, 235, 0.16);
  border-color: var(--color-accent);
}
.toggle-switch.active span {
  transform: translateX(1.32rem);
  background: var(--color-accent);
}
.checkbox-box {
  display: inline-flex;
  width: 1.25rem;
  height: 1.25rem;
  align-items: center;
  justify-content: center;
  border-radius: 0.35rem;
  border: 1px solid var(--border);
  background: var(--bg-input);
  color: transparent;
  transition: all 0.2s ease;
}
input:checked + .checkbox-box {
  border-color: var(--color-accent);
  background: var(--color-accent);
  color: var(--text-btn);
}
.message-box {
  display: flex;
  align-items: flex-start;
  gap: 0.65rem;
  border-radius: 0.75rem;
  padding: 0.9rem 1rem;
  font-size: 0.86rem;
  font-weight: 700;
}
.message-box.success {
  border: 1px solid rgba(34, 197, 94, 0.28);
  background: rgba(34, 197, 94, 0.1);
  color: #22c55e;
}
.message-box.error {
  border: 1px solid rgba(239, 68, 68, 0.28);
  background: rgba(239, 68, 68, 0.1);
  color: #ef4444;
}
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: translateY(-4px);
}
</style>
