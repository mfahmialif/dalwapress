<template>
  <main class="books-page pb-24">
    <!-- Top Navigation Bar -->
    <header class="book-top-bar">
      <div class="books-shell">
        <div class="book-nav-bar">
          <!-- Left: Desktop Breadcrumbs -->
          <nav aria-label="Breadcrumb" class="book-breadcrumbs hidden md:flex items-center gap-2 text-xs font-bold text-slate-500 dark:text-slate-400 min-w-0">
            <router-link to="/" class="hover:text-sky-600 transition-colors flex items-center gap-1 shrink-0">
              <span class="material-symbols-outlined text-[15px]">home</span>
              Beranda
            </router-link>
            <span class="text-slate-300 dark:text-slate-700">/</span>
            <router-link to="/books" class="hover:text-sky-600 transition-colors shrink-0">Katalog Buku</router-link>
            <span class="text-slate-300 dark:text-slate-700">/</span>
            <span class="font-black text-slate-800 dark:text-slate-200 max-w-[280px] truncate">{{ bookCategory }}</span>
          </nav>

          <!-- Right: Back Button -->
          <div class="book-nav-right flex items-center">
            <button
              @click="goBack"
              class="book-back-pill"
              type="button"
              title="Kembali ke Katalog"
              aria-label="Kembali ke Katalog"
            >
              <span class="material-symbols-outlined text-[20px] transition-transform duration-200">arrow_back</span>
              <span class="book-back-text font-bold text-xs">Kembali ke Katalog</span>
            </button>
          </div>
        </div>
      </div>
    </header>

    <!-- Hero Showcase Section -->
    <section class="book-detail-hero">
      <div class="books-shell">
        <!-- Loading State -->
        <div v-if="loading" class="flex flex-col items-center justify-center py-24">
          <span class="material-symbols-outlined animate-spin text-5xl text-sky-600">progress_activity</span>
          <p class="mt-4 font-bold text-slate-500">Memuat detail buku Dalwa Press...</p>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="book-empty-state" data-aos="fade-up">
          <span class="material-symbols-outlined">menu_book</span>
          <h1>Buku tidak ditemukan</h1>
          <p>{{ error }}</p>
          <router-link to="/books" class="book-btn secondary">Kembali ke Katalog</router-link>
        </div>

        <!-- Main Book Hero Card -->
        <div v-else-if="book" class="book-hero-card" data-aos="fade-up">
          <!-- Left: Realistic Physical Hardcover Presentation & Pedestal -->
          <div class="book-hero-cover-col">
            <div class="book-cover-stage" @click="openImageModal(bookCoverDisplayUrl, book.title)">
              <!-- Soft ambient backdrop light -->
              <div class="book-cover-ambient"></div>

              <!-- Physical Hardcover Book Wrapper -->
              <div class="book-hardcover-wrap">
                <div class="book-hardcover-spine-edge"></div>

                <img
                  v-if="bookCoverDisplayUrl"
                  :src="bookCoverDisplayUrl"
                  :alt="book.title"
                  class="book-hardcover-img"
                  loading="eager"
                  @error="handleCoverError"
                />

                <div v-else class="book-cover-fallback">
                  <span class="material-symbols-outlined text-5xl text-sky-300">menu_book</span>
                  <p class="text-xs font-black uppercase tracking-wider text-sky-200 mt-2">UII Dalwa Press</p>
                  <p class="text-xs text-white/90 font-bold mt-1 line-clamp-2 px-2">{{ book.title }}</p>
                </div>

                <!-- Realistic Book Shine/Glare overlay -->
                <div class="book-hardcover-glare"></div>

                <!-- Hover zoom pill -->
                <div class="book-cover-hover-hint">
                  <span class="material-symbols-outlined text-base">zoom_in</span>
                  <span class="text-[11px] font-bold">Perbesar</span>
                </div>
              </div>
            </div>

            <!-- Snug Cover Pedestal with Key Metrics -->
            <div class="book-cover-pedestal">
              <div class="book-pedestal-stat">
                <span class="book-pedestal-num">{{ book.pages || '-' }}</span>
                <span class="book-pedestal-lbl">Halaman</span>
              </div>
              <div class="book-pedestal-sep"></div>
              <div class="book-pedestal-stat">
                <span class="book-pedestal-num">{{ book.year || '-' }}</span>
                <span class="book-pedestal-lbl">Tahun</span>
              </div>
              <div class="book-pedestal-sep"></div>
              <div class="book-pedestal-stat">
                <span class="book-pedestal-num">{{ book.views || 0 }}</span>
                <span class="book-pedestal-lbl">Dilihat</span>
              </div>
            </div>
          </div>

          <!-- Right: Book Title, Metadata, Author & Action Buttons -->
          <div class="book-hero-info-col">
            <!-- Badges Eyebrow -->
            <div class="book-eyebrow-row">
              <span class="book-eyebrow-pill category">
                <span class="material-symbols-outlined text-[14px]">auto_stories</span>
                <span>{{ bookCategory }}</span>
              </span>
              <span class="book-eyebrow-pill press">
                <span class="material-symbols-outlined text-[14px]">verified</span>
                <span>UII Dalwa Press</span>
              </span>
              <div class="book-eyebrow-break"></div>
              <span v-if="book.language" class="book-eyebrow-pill language">
                <span>{{ book.language }}</span>
              </span>
            </div>

            <!-- Book Title -->
            <h1 class="book-hero-title">
              {{ book.title }}
            </h1>

            <!-- Author Byline Card (High Contrast, Authoritative) -->
            <div class="book-hero-author-card">
              <div class="book-author-avatar-wrap">
                <img
                  v-if="authorPhotoUrl"
                  :src="authorPhotoUrl"
                  :alt="bookAuthor"
                  class="size-full object-cover"
                  @error="handleAuthorError"
                />
                <span v-else class="book-author-avatar-initials">
                  {{ authorInitials }}
                </span>
              </div>
              <div class="book-hero-author-text">
                <span class="book-author-kicker">Penulis Utama</span>
                <h2 class="book-author-name">{{ bookAuthor }}</h2>
                <p class="book-author-institution">{{ authorInstitution }}</p>
              </div>
            </div>

            <!-- Publishing Specs Strip (Non-Redundant) -->
            <div class="book-specs-strip">
              <div class="book-spec-cell">
                <span class="book-spec-cell-label">ISBN</span>
                <span class="book-spec-cell-value font-mono">{{ book.isbn || 'Dalam Proses' }}</span>
              </div>
              <div class="book-spec-cell">
                <span class="book-spec-cell-label">Format / Ukuran</span>
                <span class="book-spec-cell-value">{{ book.size || '15,5 x 23 cm' }}</span>
              </div>
              <div class="book-spec-cell">
                <span class="book-spec-cell-label">Edisi Terbitan</span>
                <span class="book-spec-cell-value">Cetakan I, {{ book.year || '2026' }}</span>
              </div>
              <div class="book-spec-cell">
                <span class="book-spec-cell-label">Penerbit Resmi</span>
                <span class="book-spec-cell-value">{{ book.publisher || 'UII Dalwa Press' }}</span>
              </div>
            </div>

            <!-- Action Toolbar: Unified, Sleek, Academic -->
            <div class="book-action-toolbar">
              <!-- Download E-Book if available -->
              <a
                v-if="book.full_file"
                :href="`/api/books/${book.id}/download`"
                class="book-toolbar-btn download"
                download
              >
                <span class="material-symbols-outlined text-[18px]">download</span>
                <span>Unduh E-Book</span>
              </a>

              <!-- Preview sample if available -->
              <a
                v-if="book.preview_file"
                :href="fileUrl(book.preview_file)"
                target="_blank"
                rel="noopener noreferrer"
                class="book-toolbar-btn preview"
              >
                <span class="material-symbols-outlined text-[18px]">menu_book</span>
                <span>Baca Sampel</span>
              </a>

              <!-- Citation Modal Trigger -->
              <button
                @click="showCiteModal = true"
                class="book-toolbar-btn cite"
                type="button"
                title="Format Sitasi Ilmiah"
              >
                <span class="material-symbols-outlined text-[18px] text-sky-600">format_quote</span>
                <span>Sitasi Buku</span>
              </button>

              <!-- Share Trigger -->
              <button
                @click="handleShare"
                class="book-toolbar-btn share"
                type="button"
                title="Bagikan Tautan Buku"
              >
                <span class="material-symbols-outlined text-[17px] text-slate-500">share</span>
                <span>Bagikan</span>
              </button>

              <!-- WhatsApp Share Pill (Placed below / after Bagikan) -->
              <a
                v-if="shareWhatsAppUrl"
                :href="shareWhatsAppUrl"
                target="_blank"
                rel="noopener noreferrer"
                class="book-toolbar-btn whatsapp"
                title="Bagikan tautan buku via WhatsApp"
              >
                <svg class="w-4 h-4 fill-current shrink-0" viewBox="0 0 24 24">
                  <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.652-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                </svg>
                <span>Bagikan ke WhatsApp</span>
              </a>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Main Interactive Editorial Tabs Section -->
    <section v-if="book" id="book-content-tabs" class="book-tabs-section mt-8">
      <div class="books-shell">
        <div class="book-tabs-container">
          <!-- Horizontal Tabs Navigation with Scroll Indicators -->
          <div class="book-tabs-bar-wrap">
            <div class="book-tabs-bar">
              <!-- Left Scroll Fade & Button -->
              <div v-show="canScrollTabsLeft" class="book-tabs-scroll-fade left">
                <button
                  @click="scrollTabs('left')"
                  type="button"
                  class="book-tabs-scroll-btn left"
                  title="Geser tab ke kiri"
                  aria-label="Geser tab ke kiri"
                >
                  <span class="material-symbols-outlined text-[18px]">chevron_left</span>
                </button>
              </div>

              <nav
                ref="tabsNavRef"
                @scroll="checkTabsScroll"
                class="book-tabs-nav"
                aria-label="Book Information Tabs"
              >
                <button
                  v-for="tab in bookTabs"
                  :key="tab.id"
                  :ref="(el) => setTabRef(el, tab.id)"
                  @click="selectTab(tab.id)"
                  type="button"
                  class="book-tab-btn"
                  :class="{ active: activeTab === tab.id }"
                >
                  <span class="material-symbols-outlined text-[19px] shrink-0">{{ tab.icon }}</span>
                  <span class="book-tab-label">{{ tab.label }}</span>
                  <span v-if="tab.count !== undefined" class="book-tab-badge">
                    {{ tab.count }}
                  </span>
                </button>
              </nav>

              <!-- Right Scroll Fade & Button -->
              <div v-show="canScrollTabsRight" class="book-tabs-scroll-fade right">
                <button
                  @click="scrollTabs('right')"
                  type="button"
                  class="book-tabs-scroll-btn right"
                  title="Geser tab ke kanan"
                  aria-label="Geser tab ke kanan"
                >
                  <span class="material-symbols-outlined text-[18px] animate-pulse">chevron_right</span>
                </button>
              </div>
            </div>

            <!-- Mobile Swipe Hint Under Tabs Bar -->
            <div
              v-if="canScrollTabsRight"
              class="book-tabs-mobile-hint"
              @click="scrollTabs('right')"
              role="button"
              tabindex="0"
            >
              <span class="book-tabs-hint-text">Geser untuk tab lainnya</span>
              <span class="material-symbols-outlined text-[15px] animate-bounce-x">arrow_forward</span>
            </div>
          </div>

          <!-- Tab Panes Content Card -->
          <div class="book-tab-card">
            <!-- 1. PANE: SINOPSIS BUKU -->
            <div v-show="activeTab === 'synopsis'" class="book-tab-pane" role="tabpanel">
              <div class="book-tab-header">
                <div class="flex items-center gap-2.5">
                  <span class="material-symbols-outlined text-sky-600 text-[24px]">menu_book</span>
                  <h3 class="book-tab-title">Sinopsis & Pembahasan Buku</h3>
                </div>
                <div class="book-read-time-badge">
                  <span class="material-symbols-outlined text-[16px]">timer</span>
                  <span>~ {{ readingTime }} Menit Membaca</span>
                </div>
              </div>

              <!-- Prose Paragraphs with Enhanced Typography -->
              <div v-if="parsedDescription.storyParagraphs.length" class="book-prose-content mt-6">
                <p
                  v-for="(para, idx) in parsedDescription.storyParagraphs"
                  :key="idx"
                  class="book-prose-para"
                >
                  {{ para }}
                </p>
              </div>
              <div v-else class="py-10 text-center text-slate-500 text-sm">
                Deskripsi lengkap buku ini sedang disusun oleh tim kurasi redaksi UII Dalwa Press.
              </div>

              <!-- Meta Notes inside description (if any) -->
              <div v-if="parsedDescription.metaNotes.length" class="book-editorial-meta-box mt-6">
                <p class="book-meta-box-title">Catatan Naskah & Spesifikasi Tambahan:</p>
                <div class="grid gap-1.5 sm:grid-cols-2">
                  <div v-for="(note, idx) in parsedDescription.metaNotes" :key="idx" class="book-meta-box-item">
                    • {{ note }}
                  </div>
                </div>
              </div>

              <!-- Topic Keywords / Tags -->
              <div class="book-editorial-tags mt-7 pt-6 border-t border-slate-100 dark:border-slate-800">
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400">Topik & Kata Kunci:</span>
                <div class="flex flex-wrap gap-2 mt-2">
                  <span v-for="tag in bookTags" :key="tag" class="book-tag-chip">
                    # {{ tag }}
                  </span>
                </div>
              </div>
            </div>

            <!-- 2. PANE: DAFTAR ISI & SISTEMATIKA (If populated) -->
            <div v-if="tocItems.length" v-show="activeTab === 'toc'" class="book-tab-pane" role="tabpanel">
              <div class="book-tab-header">
                <div class="flex items-center gap-2.5">
                  <span class="material-symbols-outlined text-sky-600 text-[24px]">format_list_numbered</span>
                  <h3 class="book-tab-title">Daftar Isi & Sistematika Buku</h3>
                </div>
                <span class="text-xs font-bold px-3 py-1 rounded-full bg-sky-50 dark:bg-sky-950/60 text-sky-700 dark:text-sky-300 border border-sky-100 dark:border-sky-900">
                  Total {{ tocItems.length }} Bagian
                </span>
              </div>

              <div class="book-toc-list mt-6">
                <div v-for="(item, idx) in tocItems" :key="idx" class="book-toc-row">
                  <div class="book-toc-num">{{ String(idx + 1).padStart(2, '0') }}</div>
                  <div class="book-toc-text">{{ item }}</div>
                </div>
              </div>
            </div>

            <!-- 3. PANE: DATA BIBLIOGRAFI (KDT) -->
            <div v-show="activeTab === 'biblio'" class="book-tab-pane" role="tabpanel">
              <div class="book-tab-header">
                <div class="flex items-center gap-2.5">
                  <span class="material-symbols-outlined text-sky-600 text-[24px]">account_tree</span>
                  <div>
                    <h3 class="book-tab-title">Data Bibliografi (Katalog Dalam Terbitan)</h3>
                    <p class="book-tab-subtitle">Standar Katalogisasi Perpustakaan Nasional RI & UII Dalwa</p>
                  </div>
                </div>
                <button
                  @click="copyBiblioText"
                  class="book-kdt-copy-btn"
                  type="button"
                  title="Salin data bibliografi"
                >
                  <span class="material-symbols-outlined text-[17px]">content_copy</span>
                  <span>Salin Format KDT</span>
                </button>
              </div>

              <!-- High-End 2-Column Bibliographic Grid -->
              <div class="book-kdt-grid mt-6">
                <div class="book-kdt-cell">
                  <span class="book-kdt-cell-label">Judul Lengkap</span>
                  <span class="book-kdt-cell-val book-title-val">{{ book.title }}</span>
                </div>
                <div class="book-kdt-cell">
                  <span class="book-kdt-cell-label">Penulis / Pengarang</span>
                  <span class="book-kdt-cell-val">{{ bookAuthor }}</span>
                </div>
                <div class="book-kdt-cell">
                  <span class="book-kdt-cell-label">Penerbit Resmi</span>
                  <span class="book-kdt-cell-val">{{ book.publisher || 'UII Dalwa Press' }}</span>
                </div>
                <div class="book-kdt-cell">
                  <span class="book-kdt-cell-label">Tahun Publikasi</span>
                  <span class="book-kdt-cell-val">{{ book.year || '-' }}</span>
                </div>
                <div class="book-kdt-cell">
                  <span class="book-kdt-cell-label">Nomor Baku ISBN</span>
                  <span class="book-kdt-cell-val font-mono font-bold text-sky-700 dark:text-sky-300">{{ book.isbn || 'Dalam Proses Verifikasi' }}</span>
                </div>
                <div class="book-kdt-cell">
                  <span class="book-kdt-cell-label">Ketebalan / Halaman</span>
                  <span class="book-kdt-cell-val">{{ book.pages ? `${book.pages} Halaman` : '-' }}</span>
                </div>
                <div class="book-kdt-cell">
                  <span class="book-kdt-cell-label">Dimensi / Ukuran Fisik</span>
                  <span class="book-kdt-cell-val">{{ book.size || '15,5 x 23 cm (Standar UNESCO)' }}</span>
                </div>
                <div class="book-kdt-cell">
                  <span class="book-kdt-cell-label">Bahasa Pengantar</span>
                  <span class="book-kdt-cell-val">{{ book.language || 'Bahasa Indonesia' }}</span>
                </div>
                <div class="book-kdt-cell">
                  <span class="book-kdt-cell-label">Kategori / Klasifikasi</span>
                  <span class="book-kdt-cell-val">{{ bookCategory }}</span>
                </div>
                <div class="book-kdt-cell">
                  <span class="book-kdt-cell-label">Kota Terbit</span>
                  <span class="book-kdt-cell-val">Pasuruan, Jawa Timur, Indonesia</span>
                </div>
              </div>

              <!-- Official Library & Archive Guarantee Notice -->
              <div class="book-kdt-notice-bar mt-6">
                <span class="material-symbols-outlined text-sky-600 text-xl shrink-0">verified</span>
                <p class="text-xs text-slate-700 dark:text-slate-300 leading-relaxed">
                  <strong>Katalogisasi Dalam Terbitan (KDT):</strong> Hak cipta dilindungi undang-undang. Naskah ini telah melalui kurasi ilmiah Dewan Redaksi UII Dalwa Press serta pengarsipan repositori resmi kampus. Dilarang memperbanyak atau menerbitkan ulang sebagian atau seluruh isi buku tanpa izin tertulis dari penerbit.
                </p>
              </div>
            </div>

            <!-- 4. PANE: TENTANG PENULIS -->
            <div v-show="activeTab === 'author'" class="book-tab-pane" role="tabpanel">
              <div class="book-tab-header">
                <div class="flex items-center gap-2.5">
                  <span class="material-symbols-outlined text-sky-600 text-[24px]">person</span>
                  <h3 class="book-tab-title">Tentang Penulis</h3>
                </div>
              </div>

              <div class="book-author-profile mt-6">
                <div class="book-author-profile-left">
                  <div class="book-author-big-avatar">
                    <img
                      v-if="authorPhotoUrl"
                      :src="authorPhotoUrl"
                      :alt="bookAuthor"
                      class="size-full object-cover"
                      @error="handleAuthorError"
                    />
                    <span v-else class="text-2xl font-black text-sky-700 dark:text-sky-300">
                      {{ authorInitials }}
                    </span>
                  </div>
                </div>
                <div class="book-author-profile-right">
                  <div class="flex flex-wrap items-center gap-2">
                    <h4 class="book-author-profile-name">{{ bookAuthor }}</h4>
                    <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-800">
                      <span class="material-symbols-outlined text-[13px]">check_circle</span>
                      Penulis Terverifikasi
                    </span>
                  </div>
                  <p class="text-xs sm:text-sm font-bold text-sky-600 dark:text-sky-400 mt-1">{{ authorInstitution }}</p>
                  <p class="text-sm text-slate-700 dark:text-slate-300 mt-4 leading-relaxed book-prose-para">
                    {{ authorBio }}
                  </p>

                  <div v-if="book.author?.email || book.author?.phone" class="flex flex-wrap gap-3 mt-5 pt-4 border-t border-slate-100 dark:border-slate-800">
                    <a v-if="book.author?.email" :href="`mailto:${book.author.email}`" class="book-author-contact-item">
                      <span class="material-symbols-outlined text-[16px]">mail</span>
                      <span>{{ book.author.email }}</span>
                    </a>
                    <a v-if="book.author?.phone" :href="`tel:${book.author.phone}`" class="book-author-contact-item">
                      <span class="material-symbols-outlined text-[16px]">phone</span>
                      <span>{{ book.author.phone }}</span>
                    </a>
                  </div>
                </div>
              </div>
            </div>

            <!-- 5. PANE: GALERI & CUPLIKAN NASKAH (If galleries exist) -->
            <div v-if="book.galleries && book.galleries.length" v-show="activeTab === 'gallery'" class="book-tab-pane" role="tabpanel">
              <div class="book-tab-header">
                <div class="flex items-center gap-2.5">
                  <span class="material-symbols-outlined text-sky-600 text-[24px]">photo_library</span>
                  <h3 class="book-tab-title">Galeri & Cuplikan Halaman Naskah</h3>
                </div>
                <span class="text-xs font-bold text-slate-500">{{ book.galleries.length }} Cuplikan Foto</span>
              </div>

              <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 mt-6">
                <div
                  v-for="(gal, gIdx) in book.galleries"
                  :key="gIdx"
                  @click="openImageModal(fileUrl(gal.image), gal.caption || book.title)"
                  class="book-gallery-item group"
                >
                  <img :src="fileUrl(gal.image)" :alt="gal.caption || book.title" class="book-gallery-img" />
                  <div class="book-gallery-overlay">
                    <span class="material-symbols-outlined text-white text-2xl">zoom_in</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Bottom Banner: Call for Manuscripts (Clean Full-Width Strip) -->
          <div class="book-submission-cta mt-6">
            <div class="p-6 sm:p-7 rounded-2xl bg-gradient-to-r from-sky-900 via-indigo-950 to-slate-900 text-white shadow-xl relative overflow-hidden flex flex-col sm:flex-row items-start sm:items-center justify-between gap-5">
              <div class="relative z-10 max-w-2xl">
                <div class="flex items-center gap-2 text-sky-400 text-xs font-black uppercase tracking-wider mb-1">
                  <span class="material-symbols-outlined text-[18px]">publish</span>
                  <span>Penerbitan Karya Ilmiah & Buku Ajar</span>
                </div>
                <h4 class="text-base sm:text-lg font-black text-white">Ingin Menerbitkan Karya Anda Bersama UII Dalwa Press?</h4>
                <p class="text-xs sm:text-sm text-sky-200 mt-1 leading-relaxed">
                  Kami memfasilitasi publikasi buku ber-ISBN, monograf, buku ajar perguruan tinggi, serta prosiding ilmiah dengan pendampingan kurasi editorial profesional.
                </p>
              </div>
              <router-link to="/submissions" class="book-cta-white-btn shrink-0 text-xs font-black">
                <span>Ajukan Naskah Sekarang</span>
                <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Related Books Section -->
    <section v-if="relatedBooks.length" class="book-related-section mt-16">
      <div class="books-shell">
        <div class="flex items-end justify-between mb-7">
          <div>
            <div class="inline-flex items-center gap-1.5 text-xs font-bold text-sky-600 dark:text-sky-400 uppercase tracking-widest">
              <span class="material-symbols-outlined text-[16px]">recommend</span>
              <span>Rekomendasi Lainnya</span>
            </div>
            <h3 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white mt-1">Buku Terkait dalam Kategori Ini</h3>
          </div>
          <router-link to="/books" class="hidden sm:inline-flex items-center gap-1 text-xs font-black text-sky-600 hover:text-sky-700">
            <span>Lihat Semua Katalog</span>
            <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
          </router-link>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6">
          <router-link
            v-for="rel in relatedBooks"
            :key="rel.id"
            :to="`/books/${rel.id}`"
            class="book-card-mini group"
          >
            <div class="book-card-mini-cover">
              <img :src="getBookCoverUrl(rel)" :alt="rel.title" class="book-card-mini-img" />
              <div class="book-card-mini-badge">{{ rel.category?.name || 'Buku' }}</div>
            </div>
            <div class="p-3.5">
              <h4 class="font-bold text-xs sm:text-sm text-slate-900 dark:text-slate-100 line-clamp-2 group-hover:text-sky-600 transition-colors">
                {{ rel.title }}
              </h4>
              <p class="text-xs text-slate-500 line-clamp-1 mt-1 font-medium">
                {{ rel.author?.name || 'Penulis Dalwa' }}
              </p>
              <div class="flex items-center justify-between text-[11px] text-slate-400 mt-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                <span>{{ rel.year || '2026' }}</span>
                <span class="text-sky-600 font-bold group-hover:translate-x-0.5 transition-transform">Detail →</span>
              </div>
            </div>
          </router-link>
        </div>
      </div>
    </section>

    <!-- Modals (Citation, Image Lightbox, Toast) -->
    <!-- Academic Citation Modal -->
    <Teleport to="body">
      <div
        v-if="showCiteModal"
        class="book-modal-backdrop"
        @click.self="showCiteModal = false"
      >
        <div class="book-modal-card" role="dialog" aria-modal="true">
          <div class="book-modal-header">
            <div>
              <h4 class="text-base font-black text-slate-900 dark:text-white">Sitasi Akademik</h4>
              <p class="text-xs text-slate-500">Pilih format kutipan ilmiah standar untuk karya ini:</p>
            </div>
            <button
              @click="showCiteModal = false"
              class="book-modal-close-btn"
              type="button"
              title="Tutup"
            >
              <span class="material-symbols-outlined">close</span>
            </button>
          </div>

          <div class="book-cite-style-tabs mt-4">
            <button
              v-for="fmt in citationFormats"
              :key="fmt.id"
              @click="citeTab = fmt.id"
              :class="['book-cite-pill', { active: citeTab === fmt.id }]"
              type="button"
            >
              {{ fmt.label }}
            </button>
          </div>

          <div class="book-cite-box mt-3">
            <p class="book-cite-text font-mono text-xs">{{ currentCitationText }}</p>
          </div>

          <div class="flex items-center justify-end gap-2.5 mt-5">
            <button
              @click="showCiteModal = false"
              class="book-sub-btn"
              type="button"
            >
              Tutup
            </button>
            <button
              @click="copyCitation(currentCitationText, currentCitationLabel)"
              class="book-action-primary !py-2 !px-4 !text-xs"
              type="button"
            >
              <span class="material-symbols-outlined text-[17px]">content_copy</span>
              <span>Salin Sitasi ({{ currentCitationLabel }})</span>
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Image Lightbox Modal -->
    <Teleport to="body">
      <div
        v-if="showImageModal"
        class="book-modal-backdrop"
        @click.self="showImageModal = false"
      >
        <div class="book-image-modal-content">
          <button
            @click="showImageModal = false"
            class="book-lightbox-close"
            type="button"
            title="Tutup"
          >
            <span class="material-symbols-outlined">close</span>
          </button>
          <img :src="modalImageUrl" :alt="modalImageAlt" class="book-lightbox-img" />
          <p class="text-center text-white/90 text-sm font-bold mt-3">{{ modalImageAlt }}</p>
        </div>
      </div>
    </Teleport>

    <!-- Floating Toast Notification -->
    <Teleport to="body">
      <Transition name="toast-slide">
        <div v-if="toast.show" class="book-toast-alert" role="alert">
          <span class="material-symbols-outlined text-emerald-400 text-[20px]">check_circle</span>
          <span class="text-sm font-bold text-white">{{ toast.message }}</span>
        </div>
      </Transition>
    </Teleport>
  </main>
</template>

<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import AOS from 'aos'
import api from '../../../axios'
import { assetUrl, storageUrl } from '../../../utils/asset'
import './styles.css'

const route = useRoute()
const router = useRouter()
const book = ref(null)
const loading = ref(false)
const error = ref('')

// Interactive tabs state & scroll detection
const activeTab = ref('synopsis')
const tabsNavRef = ref(null)
const canScrollTabsLeft = ref(false)
const canScrollTabsRight = ref(false)
const tabElementRefs = new Map()

function setTabRef(el, id) {
  if (el) {
    tabElementRefs.set(id, el)
  } else {
    tabElementRefs.delete(id)
  }
}

function checkTabsScroll() {
  const el = tabsNavRef.value
  if (!el) return
  canScrollTabsLeft.value = el.scrollLeft > 6
  canScrollTabsRight.value = el.scrollWidth - el.clientWidth - el.scrollLeft > 6
}

function scrollTabs(direction) {
  const el = tabsNavRef.value
  if (!el) return
  const amount = direction === 'left' ? -200 : 200
  el.scrollBy({ left: amount, behavior: 'smooth' })
  setTimeout(checkTabsScroll, 320)
}

function selectTab(tabId) {
  activeTab.value = tabId
  nextTick(() => {
    const el = tabElementRefs.get(tabId)
    if (el && typeof el.scrollIntoView === 'function') {
      el.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' })
    }
    setTimeout(checkTabsScroll, 350)
  })
}

// Citation modal state
const showCiteModal = ref(false)
const citeTab = ref('apa')

// Image lightbox state
const showImageModal = ref(false)
const modalImageUrl = ref('')
const modalImageAlt = ref('')

// Related books state
const relatedBooks = ref([])
const loadingRelated = ref(false)

// Toast notification state
const toast = ref({ show: false, message: '' })
let toastTimer = null

const citationFormats = [
  { id: 'apa', label: 'APA (7th)' },
  { id: 'mla', label: 'MLA (9th)' },
  { id: 'chicago', label: 'Chicago' },
  { id: 'bibtex', label: 'BibTeX' },
]

const fallbackCovers = [
  '/img/thumb1.jpg',
  '/img/thumb2.jpg',
  '/img/hero-bg.jpg',
  '/img/galeri-bg.jpg',
  '/img/agenda-bg.jpg',
]

function fileUrl(path) {
  if (!path) return ''
  if (path.startsWith('http') || path.startsWith('/')) return assetUrl(path)
  return storageUrl(path)
}

const coverImgError = ref(false)
const authorImgError = ref(false)

function handleCoverError() {
  coverImgError.value = true
}

function handleAuthorError() {
  authorImgError.value = true
}

const fallbackCoverUrl = computed(() => {
  const idx = Number(book.value?.id || 0) % fallbackCovers.length
  return fallbackCovers[idx] || '/img/thumb1.jpg'
})

const bookCoverDisplayUrl = computed(() => {
  if (coverImgError.value) return fallbackCoverUrl.value
  if (book.value?.cover_url) return book.value.cover_url
  if (book.value?.cover) return fileUrl(book.value.cover)
  return fallbackCoverUrl.value
})

const bookCoverUrl = computed(() => bookCoverDisplayUrl.value)

function getBookCoverUrl(item) {
  if (item?.cover_url) return item.cover_url
  if (item?.cover) return fileUrl(item.cover)
  const idx = Number(item?.id || 0) % fallbackCovers.length
  return fallbackCovers[idx]
}

const bookCategory = computed(() => {
  const category = book.value?.category
  return typeof category === 'string' ? category : category?.name || 'Buku Referensi'
})

const bookAuthor = computed(() => {
  const author = book.value?.author
  return typeof author === 'string' ? author : author?.name || '-'
})

const authorInstitution = computed(() => {
  return book.value?.author?.institution || 'Universitas Islam Internasional Darullughah Wadda\'wah'
})

const authorPhotoUrl = computed(() => {
  if (authorImgError.value) return ''
  const photo = book.value?.author?.photo
  if (!photo) return ''
  return fileUrl(photo)
})

const authorInitials = computed(() => {
  const name = (bookAuthor.value || 'DP').trim()
  const parts = name.split(/\s+/)
  if (parts.length >= 2) {
    return `${parts[0][0]}${parts[parts.length - 1][0]}`.toUpperCase()
  }
  return name.slice(0, 2).toUpperCase()
})

const authorBio = computed(() => {
  return book.value?.author?.bio ||
    `Penulis dan akademisi aktif yang telah berkontribusi dalam penerbitan karya ilmiah, buku referensi, dan kajian literasi di lingkungan ${authorInstitution.value}.`
})


// Parsed description separating story paragraphs and editorial meta notes
const parsedDescription = computed(() => {
  if (!book.value?.description) return { storyParagraphs: [], metaNotes: [] }
  const rawParagraphs = book.value.description
    .split(/\r?\n\r?\n+/)
    .map((p) => p.trim())
    .filter(Boolean)

  const storyParagraphs = []
  const metaNotes = []

  for (const para of rawParagraphs) {
    const lower = para.toLowerCase()
    if (
      lower.startsWith('penulis :') ||
      lower.startsWith('penulis:') ||
      lower.startsWith('jumlah halaman :') ||
      lower.startsWith('jumlah halaman:') ||
      lower.startsWith('ukuran :') ||
      lower.startsWith('ukuran:')
    ) {
      metaNotes.push(para)
    } else {
      storyParagraphs.push(para)
    }
  }

  if (!storyParagraphs.length && metaNotes.length) {
    return { storyParagraphs: metaNotes, metaNotes: [] }
  }

  return { storyParagraphs, metaNotes }
})

// Table of Contents itemized lines
const tocItems = computed(() => {
  if (!book.value?.table_of_contents) return []
  return book.value.table_of_contents
    .split(/\r?\n+/)
    .map((line) => line.trim())
    .filter((line) => line.length > 0)
})

// Dynamic book tabs navigation
const bookTabs = computed(() => {
  const tabs = [
    { id: 'synopsis', label: 'Sinopsis Buku', icon: 'menu_book' },
  ]
  if (tocItems.value.length > 0) {
    tabs.push({ id: 'toc', label: 'Daftar Isi', icon: 'format_list_numbered', count: tocItems.value.length })
  }
  tabs.push({ id: 'biblio', label: 'Data Bibliografi (KDT)', icon: 'account_tree' })
  tabs.push({ id: 'author', label: 'Tentang Penulis', icon: 'person' })
  if (book.value?.galleries && book.value.galleries.length > 0) {
    tabs.push({ id: 'gallery', label: 'Galeri Cuplikan', icon: 'photo_library', count: book.value.galleries.length })
  }
  return tabs
})

// Reading time estimate
const readingTime = computed(() => {
  if (!book.value?.description) return 1
  const words = book.value.description.trim().split(/\s+/).length
  return Math.max(1, Math.ceil(words / 150))
})

// Tags / keywords
const bookTags = computed(() => {
  if (book.value?.tags) {
    return book.value.tags.split(',').map((t) => t.trim()).filter(Boolean)
  }
  const tags = []
  if (bookCategory.value && bookCategory.value !== 'Buku') tags.push(bookCategory.value)
  tags.push('UII Dalwa Press', 'Buku Akademik', 'Kajian Ilmiah', 'Publikasi Kampus')
  return tags
})

// Share links
const shareWhatsAppUrl = computed(() => {
  const currentUrl = typeof window !== 'undefined' ? window.location.href : ''
  const text = `Lihat buku "${book.value?.title || 'Buku'}" karya ${bookAuthor.value} di UII Dalwa Press:\n${currentUrl}`
  return `https://wa.me/?text=${encodeURIComponent(text)}`
})

// Citations parsing
const authorNames = computed(() => {
  const full = (bookAuthor.value || '').trim()
  if (!full || full === '-') return { first: '', last: 'Anonim', initials: 'A.', full: 'Anonim' }
  const parts = full.split(/\s+/)
  if (parts.length === 1) {
    return { first: '', last: parts[0], initials: `${parts[0][0]}.`, full: parts[0] }
  }
  const last = parts[parts.length - 1]
  const rest = parts.slice(0, -1)
  const initials = rest.map((p) => `${p[0]}.`).join(' ')
  return { first: rest.join(' '), last, initials, full }
})

const currentCitationLabel = computed(() => {
  const found = citationFormats.find((f) => f.id === citeTab.value)
  return found ? found.label : 'Sitasi'
})

const currentCitationText = computed(() => {
  const { last, first, initials, full } = authorNames.value
  const authorStr = first ? `${last}, ${first}` : last
  const y = book.value?.year || new Date().getFullYear()
  const t = book.value?.title || 'Judul Buku'
  const pub = book.value?.publisher || 'UII Dalwa Press'

  switch (citeTab.value) {
    case 'apa':
      return `${last}, ${initials} (${y}). ${t}. ${pub}.`
    case 'mla':
      return `${authorStr}. ${t}. ${pub}, ${y}.`
    case 'chicago':
      return `${authorStr}. ${y}. ${t}. Pasuruan: ${pub}.`
    case 'bibtex': {
      const key = `${(last || 'book').toLowerCase().replace(/[^a-z0-9]/g, '')}${y}`
      return `@book{${key},
  title = {${t}},
  author = {${full}},
  year = {${y}},
  publisher = {${pub}},
  address = {Pasuruan}
}`
    }
    default:
      return `${last}, ${initials} (${y}). ${t}. ${pub}.`
  }
})

function showToast(message) {
  toast.value = { show: true, message }
  clearTimeout(toastTimer)
  toastTimer = setTimeout(() => {
    toast.value.show = false
  }, 2600)
}

async function handleShare() {
  const shareData = {
    title: book.value?.title ? `${book.value.title} - UII Dalwa Press` : 'UII Dalwa Press',
    text: `Lihat buku "${book.value?.title || 'Buku'}" karya ${bookAuthor.value} di UII Dalwa Press`,
    url: window.location.href,
  }

  if (typeof navigator !== 'undefined' && navigator.share && navigator.canShare && navigator.canShare(shareData)) {
    try {
      await navigator.share(shareData)
      return
    } catch (err) {
      if (err.name === 'AbortError') return
    }
  }

  await copyPageLink()
}

async function copyPageLink() {
  try {
    await navigator.clipboard.writeText(window.location.href)
    showToast('Tautan buku berhasil disalin ke clipboard!')
  } catch {
    showToast('Gagal menyalin tautan.')
  }
}

function goBack() {
  if (window.history.length > 1 && window.history.state?.back) {
    router.back()
  } else {
    router.push('/books')
  }
}

async function copyCitation(text, formatLabel) {
  try {
    await navigator.clipboard.writeText(text)
    showToast(`Sitasi format ${formatLabel} berhasil disalin!`)
  } catch {
    showToast('Gagal menyalin sitasi.')
  }
}

async function copyBiblioText() {
  if (!book.value) return
  const lines = [
    `Judul: ${book.value.title}`,
    `Penulis: ${bookAuthor.value}`,
    `Penerbit: ${book.value.publisher || 'UII Dalwa Press'}`,
    `Tahun: ${book.value.year || '-'}`,
    `ISBN: ${book.value.isbn || '-'}`,
    `Halaman: ${book.value.pages || '-'}`,
    `Ukuran: ${book.value.size || '15,5 x 23 cm'}`,
    `Bahasa: ${book.value.language || 'Bahasa Indonesia'}`,
  ].join('\n')

  try {
    await navigator.clipboard.writeText(lines)
    showToast('Data bibliografi KDT berhasil disalin!')
  } catch {
    showToast('Gagal menyalin data bibliografi.')
  }
}

function openImageModal(url, alt) {
  if (!url) return
  modalImageUrl.value = url
  modalImageAlt.value = alt || 'Pratinjau Gambar'
  showImageModal.value = true
}

function jumpToTab(tabName) {
  selectTab(tabName)
  nextTick(() => {
    const el = document.getElementById('book-content-tabs')
    if (el) {
      el.scrollIntoView({ behavior: 'smooth', block: 'start' })
    }
  })
}

async function loadBook() {
  loading.value = true
  error.value = ''
  coverImgError.value = false
  authorImgError.value = false
  try {
    const { data } = await api.get(`/books/${route.params.id}`)
    book.value = data
    await loadRelatedBooks()
  } catch {
    book.value = null
    error.value = 'Buku tidak ditemukan atau telah diarsipkan.'
  } finally {
    loading.value = false
    await nextTick()
    AOS.refreshHard()
    checkTabsScroll()
  }
}

async function loadRelatedBooks() {
  loadingRelated.value = true
  try {
    const params = { status: 'published', per_page: 6 }
    if (book.value?.category_id) {
      params.category_id = book.value.category_id
    }
    const { data } = await api.get('/books', { params })
    const all = data.data || []
    relatedBooks.value = all.filter((b) => String(b.id) !== String(route.params.id)).slice(0, 4)

    if (relatedBooks.value.length < 2) {
      const { data: generalData } = await api.get('/books', { params: { status: 'published', per_page: 6 } })
      const general = (generalData.data || []).filter((b) => String(b.id) !== String(route.params.id))
      const combined = [...relatedBooks.value]
      for (const item of general) {
        if (!combined.some((x) => x.id === item.id) && combined.length < 4) {
          combined.push(item)
        }
      }
      relatedBooks.value = combined
    }
  } catch (err) {
    console.error('Error loading related books:', err)
    relatedBooks.value = []
  } finally {
    loadingRelated.value = false
  }
}

onMounted(async () => {
  AOS.init({ duration: 780, easing: 'ease-out-cubic', once: true, offset: 80 })
  window.addEventListener('resize', checkTabsScroll)
  await loadBook()
  await nextTick()
  checkTabsScroll()
})

onBeforeUnmount(() => {
  window.removeEventListener('resize', checkTabsScroll)
})

watch(bookTabs, () => {
  nextTick(() => {
    checkTabsScroll()
  })
})

watch(
  () => route.params.id,
  () => {
    window.scrollTo({ top: 0, behavior: 'smooth' })
    loadBook()
  }
)
</script>
