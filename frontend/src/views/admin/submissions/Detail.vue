<template>
  <div class="flex flex-col gap-6">
    <!-- Header Actions -->
    <div class="flex flex-wrap items-center justify-between gap-3">
      <router-link to="/administrator/submissions" class="inline-flex items-center gap-1 text-sm font-bold" style="color: var(--text-muted)">
        <span class="material-symbols-outlined text-[20px]">arrow_back</span>
        Kembali
      </router-link>
      <div class="flex items-center gap-2">
        <button
          v-if="submission && submission.status !== 'published' && (hasLayouterFile || submission.status === 'accepted')"
          @click="openPublishModal"
          :disabled="publishing"
          class="inline-flex items-center gap-1.5 rounded-xl px-4 py-2 text-sm font-black bg-emerald-500 text-white hover:bg-emerald-600 transition shadow-md cursor-pointer disabled:opacity-50"
        >
          <span v-if="publishing" class="material-symbols-outlined animate-spin text-[18px]">progress_activity</span>
          <span v-else class="material-symbols-outlined text-[18px]">publish</span>
          {{ publishing ? 'Menerbitkan...' : 'Terbitkan Menjadi Buku' }}
        </button>
        <router-link v-if="submission && submission.status === 'published'" to="/administrator/books" class="inline-flex items-center gap-1.5 rounded-xl px-4 py-2 text-sm font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 hover:bg-emerald-500/30 transition">
          <span class="material-symbols-outlined text-[18px]">menu_book</span>
          Lihat Data Buku
        </router-link>
        <router-link v-if="submission" :to="{ name: 'AdminReviewCycles', params: { id: submission.id } }" class="inline-flex items-center gap-1.5 rounded-xl px-4 py-2 text-sm font-bold border hover:opacity-90 transition" style="border-color: var(--border); color: var(--text-heading)">
          <span class="material-symbols-outlined text-[18px]">timeline</span>
          Review Cycles
        </router-link>
        <router-link v-if="submission" :to="{ name: 'AdminSubmissionEdit', params: { id: submission.id } }" class="inline-flex items-center gap-1.5 rounded-xl px-4 py-2 text-sm font-bold bg-accent text-white hover:opacity-90 transition">
          <span class="material-symbols-outlined text-[18px]">edit</span>
          Edit Submission
        </router-link>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center py-16">
      <span class="material-symbols-outlined animate-spin text-4xl text-accent">progress_activity</span>
    </div>

    <template v-else-if="submission">
      <!-- HORIZONTAL TIMELINE PROCESS BAR -->
      <div class="rounded-2xl p-4 md:p-6" style="background: var(--bg-card); border: 1px solid var(--border)">
        <h3 class="text-xs font-bold uppercase tracking-wider mb-4" style="color: var(--text-muted)">Alur Status Submission</h3>
        
        <div class="relative flex items-center justify-between">
          <div v-for="(step, idx) in horizontalTimelineSteps" :key="step.key" class="flex flex-1 items-center last:flex-initial min-w-0">
            <!-- Step Circle & Info -->
            <div class="flex flex-col items-center text-center relative z-10 shrink-0 w-full sm:w-auto">
              <div 
                class="w-9 h-9 sm:w-10 sm:h-10 md:w-12 md:h-12 rounded-full flex items-center justify-center font-black text-xs sm:text-sm md:text-base border-2 transition-all duration-300 shadow-md"
                :class="stepCircleClass(step.state)"
              >
                <span v-if="step.state === 'completed'" class="material-symbols-outlined text-[18px] sm:text-[20px] md:text-[24px]">check</span>
                <span v-else-if="step.state === 'rejected'" class="material-symbols-outlined text-[18px] sm:text-[20px] md:text-[24px]">close</span>
                <span v-else-if="step.state === 'active'" class="material-symbols-outlined text-[18px] sm:text-[20px] md:text-[24px] animate-pulse">{{ step.icon }}</span>
                <span v-else class="text-xs font-bold">{{ step.number }}</span>
              </div>
              <span class="mt-1.5 text-[11px] sm:text-xs md:text-sm font-black tracking-tight" :class="stepTitleClass(step.state)">{{ step.title }}</span>
              <span class="hidden sm:block text-[11px] font-medium mt-0.5" style="color: var(--text-muted)">{{ step.subtitle }}</span>
            </div>

            <!-- Connector Line -->
            <div v-if="idx < horizontalTimelineSteps.length - 1" class="flex-1 mx-1 sm:mx-2 md:mx-4 h-1 rounded-full transition-all self-center -mt-5 sm:-mt-6"
              :class="connectorLineClass(step.state, horizontalTimelineSteps[idx + 1].state)">
            </div>
          </div>
        </div>

        <div v-if="submission && submission.status !== 'published' && hasLayouterFile" class="mt-4 border-t pt-3 flex flex-wrap items-center justify-between gap-3" style="border-color: var(--border)">
          <span class="text-xs font-bold text-emerald-400 flex items-center gap-1">
            <span class="material-symbols-outlined text-[16px]">check_circle</span> Hasil layout telah diunggah! Naskah siap untuk diterbitkan menjadi data Buku.
          </span>
          <button
            @click="openPublishModal"
            :disabled="publishing"
            class="inline-flex items-center gap-1.5 rounded-xl px-4 py-2 text-xs font-black bg-emerald-500 text-white hover:bg-emerald-600 transition shadow-md cursor-pointer disabled:opacity-50 shrink-0"
          >
            <span v-if="publishing" class="material-symbols-outlined animate-spin text-[16px]">progress_activity</span>
            <span v-else class="material-symbols-outlined text-[16px]">publish</span>
            {{ publishing ? 'Menerbitkan...' : 'Terbitkan Menjadi Buku' }}
          </button>
        </div>

        <div v-if="submission && submission.status === 'published'" class="mt-4 border-t pt-3 flex flex-wrap items-center justify-between gap-3" style="border-color: var(--border)">
          <span class="text-xs font-bold text-emerald-400 flex items-center gap-1">
            <span class="material-symbols-outlined text-[16px]">verified</span> Naskah ini telah resmi diterbitkan sebagai Buku!
          </span>
          <router-link to="/administrator/books" class="inline-flex items-center gap-1.5 rounded-xl px-3 py-1.5 text-xs font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 hover:bg-emerald-500/30 transition">
            <span class="material-symbols-outlined text-[16px]">menu_book</span> Lihat Data Buku
          </router-link>
        </div>
      </div>

      <!-- 1. Detail Submission & MANUSCRIPT (Full Width) -->
      <article class="rounded-2xl p-6" style="background: var(--bg-card); border: 1px solid var(--border)">
        <div class="flex flex-wrap items-center justify-between gap-3">
          <div>
            <span :class="statusBadge(submission.status)">{{ statusLabel(submission.status) }}</span>
            <h1 class="mt-3 text-2xl font-black" style="color: var(--text-heading)">{{ submission.title }}</h1>
            <div class="mt-2 flex flex-wrap items-center gap-1.5 text-sm" style="color: var(--text-muted)">
              <span>Penulis / Author:</span>
              <div v-if="submission.submission_authors?.length" class="flex flex-wrap gap-1.5">
                <span v-for="sa in submission.submission_authors" :key="sa.id" class="inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-xs font-bold bg-blue-500/10 text-blue-400 border border-blue-500/20">
                  <span class="material-symbols-outlined text-[13px]">person</span>
                  {{ sa.name }}
                  <span v-if="sa.role" class="text-[10px] opacity-75">({{ sa.role }})</span>
                </span>
              </div>
              <strong v-else style="color: var(--text-heading)">{{ submission.author_name }}</strong>
            </div>
          </div>
        </div>

        <div class="mt-6 grid gap-4 sm:grid-cols-2 md:grid-cols-4">
          <Info label="Kategori Buku" :value="submission.category?.name || '-'" />
          <Info label="Email Pengirim" :value="submission.email || '-'" />
          <Info label="Nomor Telepon / HP" :value="submission.phone || '-'" />
          <Info label="Status Submisi" :value="statusLabel(submission.status)" />
          <Info label="Tanggal Masuk" :value="formatDate(submission.submitted_at || submission.created_at)" />
          <Info label="Tanggal Review" :value="formatDate(submission.reviewed_at)" />
          <Info label="Primary Editor" :value="primaryEditorName" />
          <Info label="Tim Layouter" :value="assignedLayoutersList" />
        </div>

        <div class="mt-6 grid gap-6 md:grid-cols-2">
          <div class="rounded-xl border p-4" style="border-color: var(--border); background: var(--bg-input)">
            <h3 class="font-black text-sm uppercase tracking-wider" style="color: var(--text-muted)">Deskripsi Naskah</h3>
            <p class="mt-2 whitespace-pre-line text-sm leading-7" style="color: var(--text-body)">{{ submission.description || 'Tidak ada deskripsi.' }}</p>
          </div>
          <div class="rounded-xl border p-4" style="border-color: var(--border); background: var(--bg-input)">
            <h3 class="font-black text-sm uppercase tracking-wider" style="color: var(--text-muted)">Catatan Pengirim</h3>
            <p class="mt-2 whitespace-pre-line text-sm leading-7" style="color: var(--text-body)">{{ submission.note || 'Tidak ada catatan tambahan.' }}</p>
          </div>
        </div>

        <!-- MANUSCRIPT & BERKAS NASKAH -->
        <div class="mt-6 border-t pt-5" style="border-color: var(--border)">
          <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
              <h3 class="font-black text-base" style="color: var(--text-heading)">Manuscript & Berkas Naskah</h3>
              <p class="text-xs" style="color: var(--text-muted)">Unduh berkas utama dan berkas revisi naskah.</p>
            </div>
          </div>
          
          <div class="mt-4 flex flex-wrap gap-3">
            <a v-if="submission.manuscript_file" :href="storageUrl(submission.manuscript_file)" target="_blank" class="file-btn">
              <span class="material-symbols-outlined text-[20px]">description</span>
              Download Manuscript Naskah Awal
            </a>

            <a v-if="submission.cover_file" :href="storageUrl(submission.cover_file)" target="_blank" class="file-btn">
              <span class="material-symbols-outlined text-[20px]">image</span>
              Download Sampul (Cover)
            </a>
          </div>

          <!-- RIWAYAT REVISI & BERKAS TERLACAK (FULL HISTORY) -->
          <div class="mt-6">
            <h4 class="text-xs font-bold uppercase tracking-wider mb-3" style="color: var(--text-muted)">Riwayat Berkas Revisi & Manuscript (Terlacak)</h4>
            
            <div v-if="combinedRevisionsHistory.length" class="grid gap-3">
              <div v-for="item in combinedRevisionsHistory" :key="item.id" class="flex flex-wrap items-center justify-between gap-3 rounded-xl border p-3.5" style="border-color: var(--border); background: var(--bg-input)">
                <div class="flex items-start gap-3">
                  <span class="material-symbols-outlined text-2xl mt-0.5" :class="item.is_layouter ? 'text-emerald-500' : (item.type === 'cover' ? 'text-amber-400' : 'text-accent')">
                    {{ item.is_layouter ? 'design_services' : (item.type === 'cover' ? 'image' : 'description') }}
                  </span>
                  <div>
                    <div class="flex items-center gap-2 flex-wrap">
                      <span class="font-black text-sm" :class="item.is_layouter ? 'text-emerald-500 font-black' : ''" :style="item.is_layouter ? '' : 'color: var(--text-heading)'">{{ item.title }}</span>
                    </div>
                    <p class="text-xs opacity-75 mt-0.5" style="color: var(--text-muted)">
                      Diunggah oleh: <strong style="color: var(--text-heading)">{{ item.user_name }}</strong> · Waktu Upload: {{ formatDateTime(item.created_at) }}
                    </p>
                    <p v-if="item.note" class="mt-1.5 text-xs whitespace-pre-line" style="color: var(--text-body)">Catatan: {{ item.note }}</p>
                  </div>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                  <a :href="storageUrl(item.file_path)" target="_blank" class="file-btn text-xs py-1.5 px-3.5">
                    <span class="material-symbols-outlined text-[16px]">download</span> Download File
                  </a>
                  <button @click="deleteRevisionFile(item)" class="rounded-lg bg-red-500/10 border border-red-500/30 text-red-400 hover:bg-red-500 hover:text-white px-3 py-1.5 text-xs font-extrabold transition flex items-center gap-1 cursor-pointer" title="Hapus Berkas">
                    <span class="material-symbols-outlined text-[16px]">delete</span> Hapus
                  </button>
                </div>
              </div>
            </div>
            <p v-else class="text-xs" style="color: var(--text-muted)">Belum ada riwayat revisi yang tersimpan.</p>
          </div>
        </div>
      </article>

      <!-- Assignment Cards Section (Editor, Layouter, Desainer Cover) -->
      <div class="grid gap-6 lg:grid-cols-3">
        <!-- Assignment Editor Card -->
        <section class="rounded-2xl p-6 flex flex-col justify-between" style="background: var(--bg-card); border: 1px solid var(--border)">
          <div>
            <div class="flex flex-col gap-1">
              <h2 class="text-lg font-black" style="color: var(--text-heading)">Assignment Editor</h2>
              <p class="text-sm" style="color: var(--text-muted)">Tugaskan tim editor naskah.</p>
            </div>
            <form class="mt-5 grid gap-4" @submit.prevent="assignEditors">
              <div class="field assignment-field">
                <span>Primary Editor *</span>
                <VueMultiselect
                  v-model="primaryEditorOption"
                  :options="editors"
                  :multiple="false"
                  :searchable="true"
                  :show-labels="false"
                  label="name"
                  track-by="id"
                  placeholder="Cari primary editor"
                >
                  <template #option="{ option }">
                    <div class="flex flex-col">
                      <span class="font-bold">{{ option.name }}</span>
                      <span class="text-xs opacity-70">{{ option.username }} · {{ option.email }}</span>
                    </div>
                  </template>
                  <template #singleLabel="{ option }">
                    <span>{{ option.name }} ({{ option.username }})</span>
                  </template>
                </VueMultiselect>
              </div>

              <div class="field assignment-field">
                <span>Co-Editor</span>
                <VueMultiselect
                  v-model="coEditorOptions"
                  :options="availableCoEditors"
                  :multiple="true"
                  :close-on-select="false"
                  :searchable="true"
                  :clear-on-select="false"
                  :show-labels="false"
                  label="name"
                  track-by="id"
                  placeholder="Cari co-editor"
                >
                  <template #option="{ option }">
                    <div class="flex items-start gap-3">
                      <span class="editor-check" :class="{ selected: isCoEditorSelected(option) }">
                        <span v-if="isCoEditorSelected(option)" class="material-symbols-outlined text-[14px]">check</span>
                      </span>
                      <span class="flex flex-col">
                        <span class="font-bold">{{ option.name }}</span>
                        <span class="text-xs opacity-70">{{ option.username }} · {{ option.email }}</span>
                      </span>
                    </div>
                  </template>
                  <template #tag="{ option, remove }">
                    <span class="editor-tag">
                      {{ option.name }}
                      <button type="button" @click.stop="remove(option)">×</button>
                    </span>
                  </template>
                </VueMultiselect>
              </div>

              <label class="field">
                <span>Catatan Assignment</span>
                <textarea v-model="assignment.note" class="input min-h-[75px]" placeholder="Catatan untuk editor..."></textarea>
              </label>

              <div class="flex flex-col gap-3 border-t pt-4 sm:flex-row sm:items-center sm:justify-between" style="border-color: var(--border)">
                <button
                  :disabled="assignmentSaving"
                  class="inline-flex items-center justify-center gap-2 rounded-xl bg-accent px-5 py-2.5 text-sm font-black disabled:opacity-60 text-white cursor-pointer"
                >
                  <span v-if="assignmentSaving" class="material-symbols-outlined animate-spin text-[18px]">progress_activity</span>
                  {{ assignmentSaving ? 'Menyimpan...' : 'Simpan Assignment Editor' }}
                </button>
                <span v-if="assignmentMessage" class="text-sm font-bold text-green-400">{{ assignmentMessage }}</span>
              </div>
            </form>
          </div>

          <div class="mt-5 border-t pt-4 grid gap-2" style="border-color: var(--border)">
            <p class="text-xs font-bold uppercase tracking-wider" style="color: var(--text-muted)">Tim Editor Terassign</p>
            
            <div v-for="assignmentItem in submission.editor_assignments" :key="assignmentItem.id" class="flex flex-wrap items-center justify-between gap-3 rounded-xl border p-3" style="border-color: var(--border); background: var(--bg-input)">
              <div>
                <p class="font-black text-sm" style="color: var(--text-heading)">{{ assignmentItem.editor?.name || '-' }}</p>
                <p class="text-xs" style="color: var(--text-muted)">
                  <span v-if="assignmentItem.role === 'primary'" class="text-amber-400 font-bold">★ Primary Editor</span>
                  <span v-else>Co-editor</span> · {{ assignmentItem.editor?.email || '-' }}
                </p>
              </div>
            </div>
            <p v-if="!submission.editor_assignments?.length" class="text-xs" style="color: var(--text-muted)">Belum ada editor yang diassign.</p>
          </div>
        </section>

        <!-- Assignment Layouter Card -->
        <section class="rounded-2xl p-6 flex flex-col justify-between" style="background: var(--bg-card); border: 1px solid var(--border)">
          <div>
            <div class="flex flex-col gap-1">
              <h2 class="text-lg font-black" style="color: var(--text-heading)">Assignment Layouter</h2>
              <p class="text-sm" style="color: var(--text-muted)">Tugaskan tim layouter naskah.</p>
            </div>
            <form class="mt-5 grid gap-4" @submit.prevent="assignLayouters">
              <div class="field assignment-field">
                <span>Pilih Layouter</span>
                <VueMultiselect
                  v-model="layouterOptions"
                  :options="layouters"
                  :multiple="true"
                  :close-on-select="false"
                  :searchable="true"
                  :clear-on-select="false"
                  :show-labels="false"
                  label="name"
                  track-by="id"
                  placeholder="Cari layouter..."
                >
                  <template #option="{ option }">
                    <div class="flex items-start gap-3">
                      <span class="editor-check" :class="{ selected: isLayouterSelected(option) }">
                        <span v-if="isLayouterSelected(option)" class="material-symbols-outlined text-[14px]">check</span>
                      </span>
                      <span class="flex flex-col">
                        <span class="font-bold">{{ option.name }}</span>
                        <span class="text-xs opacity-70">{{ option.username }} · {{ option.email }}</span>
                      </span>
                    </div>
                  </template>
                  <template #tag="{ option, remove }">
                    <span class="editor-tag">
                      {{ option.name }}
                      <button type="button" @click.stop="remove(option)">×</button>
                    </span>
                  </template>
                </VueMultiselect>
              </div>

              <label class="field">
                <span>Catatan untuk Layouter</span>
                <textarea v-model="layouterAssignment.note" class="input min-h-[75px]" placeholder="Instruksi untuk layouter..."></textarea>
              </label>

              <div class="flex flex-col gap-3 border-t pt-4 sm:flex-row sm:items-center sm:justify-between" style="border-color: var(--border)">
                <button
                  :disabled="layouterSaving"
                  class="inline-flex items-center justify-center gap-2 rounded-xl bg-accent px-5 py-2.5 text-sm font-black disabled:opacity-60 text-white cursor-pointer"
                >
                  <span v-if="layouterSaving" class="material-symbols-outlined animate-spin text-[18px]">progress_activity</span>
                  {{ layouterSaving ? 'Menyimpan...' : 'Simpan Assignment Layouter' }}
                </button>
                <span v-if="layouterMessage" class="text-sm font-bold text-green-400">{{ layouterMessage }}</span>
              </div>
            </form>
          </div>

          <div class="mt-5 border-t pt-4 grid gap-2" style="border-color: var(--border)">
            <p class="text-xs font-bold uppercase tracking-wider" style="color: var(--text-muted)">Tim Layouter Terassign</p>

            <div v-for="assignmentItem in submission.layouter_assignments" :key="assignmentItem.id" class="flex flex-wrap items-center justify-between gap-3 rounded-xl border p-3" style="border-color: var(--border); background: var(--bg-input)">
              <div>
                <p class="font-black text-sm" style="color: var(--text-heading)">{{ assignmentItem.layouter?.name || '-' }}</p>
                <p class="text-xs" style="color: var(--text-muted)">Layouter · {{ assignmentItem.layouter?.email || '-' }}</p>
              </div>
              <button @click="openLayouterModal(assignmentItem)" class="rounded-lg bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 hover:bg-emerald-500 hover:text-white px-3 py-1.5 text-xs font-extrabold transition flex items-center gap-1 cursor-pointer">
                <span class="material-symbols-outlined text-[16px]">upload_file</span>
                Isi Hasil Layout
              </button>
            </div>
            <p v-if="!submission.layouter_assignments?.length" class="text-xs" style="color: var(--text-muted)">Belum ada layouter yang diassign.</p>
          </div>
        </section>

        <!-- Assignment Desainer Sampul Card -->
        <section class="rounded-2xl p-6 flex flex-col justify-between" style="background: var(--bg-card); border: 1px solid var(--border)">
          <div>
            <div class="flex flex-col gap-1">
              <h2 class="text-lg font-black" style="color: var(--text-heading)">Assignment Desainer Sampul</h2>
              <p class="text-sm" style="color: var(--text-muted)">Tugaskan tim perancang sampul (cover).</p>
            </div>
            <form class="mt-5 grid gap-4" @submit.prevent="assignCoverDesigners">
              <div class="field assignment-field">
                <span>Pilih Desainer Sampul</span>
                <VueMultiselect
                  v-model="coverDesignerOptions"
                  :options="coverDesigners"
                  :multiple="true"
                  :close-on-select="false"
                  :searchable="true"
                  :clear-on-select="false"
                  :show-labels="false"
                  label="name"
                  track-by="id"
                  placeholder="Cari desainer sampul..."
                >
                  <template #option="{ option }">
                    <div class="flex items-start gap-3">
                      <span class="editor-check" :class="{ selected: isCoverDesignerSelected(option) }">
                        <span v-if="isCoverDesignerSelected(option)" class="material-symbols-outlined text-[14px]">check</span>
                      </span>
                      <span class="flex flex-col">
                        <span class="font-bold">{{ option.name }}</span>
                        <span class="text-xs opacity-70">{{ option.username }} · {{ option.email }}</span>
                      </span>
                    </div>
                  </template>
                  <template #tag="{ option, remove }">
                    <span class="editor-tag">
                      {{ option.name }}
                      <button type="button" @click.stop="remove(option)">×</button>
                    </span>
                  </template>
                </VueMultiselect>
              </div>

              <label class="field">
                <span>Catatan untuk Desainer Sampul</span>
                <textarea v-model="coverAssignment.note" class="input min-h-[75px]" placeholder="Instruksi desain sampul..."></textarea>
              </label>

              <div class="flex flex-col gap-3 border-t pt-4 sm:flex-row sm:items-center sm:justify-between" style="border-color: var(--border)">
                <button
                  :disabled="coverSaving"
                  class="inline-flex items-center justify-center gap-2 rounded-xl bg-accent px-5 py-2.5 text-sm font-black disabled:opacity-60 text-white cursor-pointer"
                >
                  <span v-if="coverSaving" class="material-symbols-outlined animate-spin text-[18px]">progress_activity</span>
                  {{ coverSaving ? 'Menyimpan...' : 'Simpan Assignment Desainer' }}
                </button>
                <span v-if="coverMessage" class="text-sm font-bold text-green-400">{{ coverMessage }}</span>
              </div>
            </form>
          </div>

          <div class="mt-5 border-t pt-4 grid gap-2" style="border-color: var(--border)">
            <p class="text-xs font-bold uppercase tracking-wider" style="color: var(--text-muted)">Tim Desainer Cover Terassign</p>

            <div v-for="assignmentItem in submission.cover_assignments" :key="assignmentItem.id" class="flex flex-wrap items-center justify-between gap-3 rounded-xl border p-3" style="border-color: var(--border); background: var(--bg-input)">
              <div>
                <p class="font-black text-sm" style="color: var(--text-heading)">{{ assignmentItem.designer?.name || '-' }}</p>
                <p class="text-xs" style="color: var(--text-muted)">Desainer Cover · {{ assignmentItem.designer?.email || '-' }}</p>
              </div>
              <button @click="openCoverModal(assignmentItem)" class="rounded-lg bg-sky-500/10 border border-sky-500/30 text-sky-400 hover:bg-sky-500 hover:text-white px-3 py-1.5 text-xs font-extrabold transition flex items-center gap-1 cursor-pointer">
                <span class="material-symbols-outlined text-[16px]">upload_file</span>
                Isi Hasil Cover
              </button>
            </div>
            <p v-if="!submission.cover_assignments?.length" class="text-xs" style="color: var(--text-muted)">Belum ada desainer cover yang diassign.</p>
          </div>
        </section>
      </div>
    </template>



    <!-- MODAL 2: ISI HASIL LAYOUT (ADMIN JOKI) -->
    <Teleport to="body">
      <div v-if="showLayouterModal" :data-theme="currentTheme" class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-black/75 backdrop-blur-md">
        <div class="w-full max-w-lg rounded-2xl p-6 shadow-2xl transition-colors duration-300 max-h-[85vh] overflow-y-auto custom-scrollbar" :class="modalCard">
          <div class="flex items-center justify-between border-b pb-3" :class="modalBorder">
            <div>
              <h3 class="text-lg font-black" :class="modalTitle">Upload Hasil Layout</h3>
              <p class="text-xs" :class="modalSub">Untuk Layouter: <strong class="text-emerald-500 font-bold">{{ activeLayouterName }}</strong></p>
            </div>
            <button @click="showLayouterModal = false" class="cursor-pointer transition" :class="modalClose"><span class="material-symbols-outlined">close</span></button>
          </div>

          <form class="mt-4 grid gap-4" @submit.prevent="submitModalLayouterLayout">
            <label class="field" :class="modalLabel">
              <span>File Hasil Layout (PDF/DOC/DOCX) *</span>
              <input type="file" ref="layoutFileInput" accept=".pdf,.doc,.docx" required class="input" :class="modalInput" />
            </label>

            <label class="field" :class="modalLabel">
              <span>Catatan Layout</span>
              <textarea v-model="adminLayouterForm.note" class="input min-h-[80px]" :class="modalInput" placeholder="Catatan hasil layout..."></textarea>
              
              <!-- Quick Hint Chips -->
              <div class="flex flex-wrap gap-1.5 mt-2">
                <span class="text-[11px] font-bold flex items-center gap-1 w-full" :class="modalSub">
                  <span class="material-symbols-outlined text-[14px] text-emerald-400">lightbulb</span> Opsi Catatan Cepat (Klik untuk isi):
                </span>
                <button
                  v-for="(hint, idx) in layouterHints"
                  :key="idx"
                  type="button"
                  @click="adminLayouterForm.note = hint"
                  class="text-[11px] font-medium rounded-lg px-2.5 py-1 border transition cursor-pointer hover:bg-emerald-500 hover:text-white text-left"
                  :class="modalChip"
                >
                  {{ hint }}
                </button>
              </div>
            </label>

            <div v-if="adminLayouterMsg" class="text-sm font-bold" :class="adminLayouterSuccess ? 'text-green-500' : 'text-red-500'">{{ adminLayouterMsg }}</div>

            <div class="flex justify-end gap-2 border-t pt-4" :class="modalBorder">
              <button type="button" @click="showLayouterModal = false" class="rounded-xl border px-4 py-2 text-sm font-bold cursor-pointer transition" :class="modalBtnCancel">Batal</button>
              <button type="submit" :disabled="adminLayouterSaving" class="rounded-xl bg-emerald-500 px-5 py-2 text-sm font-black text-white flex items-center gap-2 cursor-pointer hover:opacity-90">
                <span v-if="adminLayouterSaving" class="material-symbols-outlined animate-spin text-[18px]">progress_activity</span>
                Upload Hasil Layout
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>

    <!-- MODAL 3: ISI HASIL DESAIN COVER (ADMIN JOKI) -->
    <Teleport to="body">
      <div v-if="showCoverModal" :data-theme="currentTheme" class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-black/75 backdrop-blur-md">
        <div class="w-full max-w-lg rounded-2xl p-6 shadow-2xl transition-colors duration-300 max-h-[85vh] overflow-y-auto custom-scrollbar" :class="modalCard">
          <div class="flex items-center justify-between border-b pb-3" :class="modalBorder">
            <div>
              <h3 class="text-lg font-black" :class="modalTitle">Upload Hasil Desain Cover</h3>
              <p class="text-xs" :class="modalSub">Untuk Desainer Cover: <strong class="text-sky-400 font-bold">{{ activeCoverDesignerName }}</strong></p>
            </div>
            <button @click="showCoverModal = false" class="cursor-pointer transition" :class="modalClose"><span class="material-symbols-outlined">close</span></button>
          </div>

          <!-- Warning Banner if Initial Cover Exists -->
          <div
            v-if="submission?.cover_file"
            class="mt-4 rounded-xl border p-3.5 text-xs flex flex-col gap-2.5 transition-colors duration-200"
            :class="currentTheme === 'light' ? 'bg-amber-50 border-amber-300 text-amber-950' : 'bg-amber-500/10 border-amber-500/30 text-amber-200'"
          >
            <div class="flex items-center gap-2 font-black text-sm" :class="currentTheme === 'light' ? 'text-amber-900' : 'text-amber-300'">
              <span class="material-symbols-outlined text-[20px]">warning</span>
              Cover Awal Penulis Tersedia!
            </div>
            <p :class="currentTheme === 'light' ? 'text-amber-900 font-semibold' : 'text-amber-200'">
              Penulis telah menyertakan berkas cover awal saat pengajuan:
            </p>

            <!-- Inline Cover Preview -->
            <div class="rounded-xl border p-2 flex flex-col items-center gap-1.5" :class="currentTheme === 'light' ? 'bg-white border-amber-200' : 'bg-black/20 border-amber-500/20'">
              <img
                :src="storageUrl(submission.cover_file)"
                alt="Preview Cover Awal"
                class="max-h-32 rounded-lg object-contain shadow-md"
                @error="$event.target.style.display = 'none'"
              />
              <a
                :href="storageUrl(submission.cover_file)"
                target="_blank"
                class="inline-flex items-center gap-1 text-[11px] font-extrabold underline"
                :class="currentTheme === 'light' ? 'text-sky-700 hover:text-sky-900' : 'text-sky-300 hover:text-sky-200'"
              >
                <span class="material-symbols-outlined text-[14px]">open_in_new</span> Lihat / Download Berkas Cover
              </a>
            </div>

            <button
              type="button"
              @click="submitModalUseInitialCover"
              :disabled="adminCoverSaving"
              class="w-full rounded-xl bg-sky-500 hover:bg-sky-600 px-4 py-2.5 text-xs font-black text-white transition cursor-pointer flex items-center justify-center gap-1.5 shadow-md"
            >
              <span v-if="adminCoverSaving" class="material-symbols-outlined animate-spin text-[16px]">progress_activity</span>
              <span v-else class="material-symbols-outlined text-[16px]">file_copy</span>
              Gunakan Cover Awal Penulis
            </button>
          </div>

          <div v-if="submission?.cover_file" class="my-3 flex items-center gap-2 text-xs opacity-60">
            <div class="h-px flex-1 bg-current opacity-20"></div>
            <span>atau Upload File Baru</span>
            <div class="h-px flex-1 bg-current opacity-20"></div>
          </div>

          <form class="mt-2 grid gap-4" @submit.prevent="submitModalCoverLayout">
            <label class="field" :class="modalLabel">
              <span>File Hasil Desain Cover *</span>
              <input type="file" ref="coverFileInput" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.psd,.ai,.zip,.rar" required class="input" :class="modalInput" />
            </label>

            <label class="field" :class="modalLabel">
              <span>Catatan Desain Cover</span>
              <textarea v-model="adminCoverForm.note" class="input min-h-[80px]" :class="modalInput" placeholder="Catatan hasil desain cover..."></textarea>
            </label>

            <div v-if="adminCoverMsg" class="text-sm font-bold" :class="adminCoverSuccess ? 'text-green-500' : 'text-red-500'">{{ adminCoverMsg }}</div>

            <div class="flex justify-end gap-2 border-t pt-4" :class="modalBorder">
              <button type="button" @click="showCoverModal = false" class="rounded-xl border px-4 py-2 text-sm font-bold cursor-pointer transition" :class="modalBtnCancel">Batal</button>
              <button type="submit" :disabled="adminCoverSaving" class="rounded-xl bg-sky-500 px-5 py-2 text-sm font-black text-white flex items-center gap-2 cursor-pointer hover:opacity-90">
                <span v-if="adminCoverSaving" class="material-symbols-outlined animate-spin text-[18px]">progress_activity</span>
                Upload Hasil Cover
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>

    <!-- MODAL: TERBITKAN NASKAH MENJADI BUKU -->
    <Teleport to="body">
      <div v-if="showPublishModal" :data-theme="currentTheme" class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-black/75 backdrop-blur-md">
        <div class="w-full max-w-2xl rounded-2xl p-6 shadow-2xl transition-colors duration-300 max-h-[90vh] overflow-y-auto" :class="modalCard">
          <div class="flex items-center justify-between border-b pb-3" :class="modalBorder">
            <div>
              <h3 class="text-lg font-black text-emerald-400 flex items-center gap-2">
                <span class="material-symbols-outlined">auto_stories</span>
                Terbitkan Naskah Menjadi Buku
              </h3>
              <p class="text-xs" :class="modalSub">Lengkapi metadata buku sebelum diterbitkan ke katalog resmi.</p>
            </div>
            <button @click="showPublishModal = false" class="cursor-pointer transition" :class="modalClose"><span class="material-symbols-outlined">close</span></button>
          </div>

          <form class="mt-4 grid gap-4" @submit.prevent="submitPublishForm">
            <!-- Grid 2 Column for metadata -->
            <div class="grid gap-4 sm:grid-cols-2">
              <label class="field" :class="modalLabel">
                <span>Judul Buku *</span>
                <input v-model="publishForm.title" required class="input" :class="modalInput" placeholder="Judul buku" />
              </label>

              <label class="field" :class="modalLabel">
                <span>Kategori Buku *</span>
                <select v-model="publishForm.category_id" required class="input" :class="modalInput">
                  <option value="">— Pilih Kategori —</option>
                  <option v-for="cat in bookCategories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                </select>
              </label>

              <!-- Authors Multi-Author Section in Publish Modal -->
              <div class="rounded-xl p-3 border" style="border-color: var(--border); background: var(--bg-card)">
                <div class="flex items-center justify-between mb-2">
                  <span class="text-xs font-bold uppercase tracking-wider" style="color: var(--text-muted)">Penulis Buku (Multi-Author) *</span>
                  <button type="button" @click="addPublishAuthor" class="inline-flex items-center gap-1 text-xs font-bold text-accent hover:underline">
                    <span class="material-symbols-outlined text-[14px]">add</span>
                    Tambah Penulis
                  </button>
                </div>
                <div class="space-y-2">
                  <div v-for="(auth, idx) in publishAuthors" :key="idx" class="flex items-center gap-2">
                    <input v-model="auth.name" required class="input flex-1 py-1.5 text-xs" :class="modalInput" placeholder="Nama penulis" />
                    <select v-model="auth.role" class="input w-36 py-1.5 text-xs" :class="modalInput">
                      <option value="Penulis Utama">Penulis Utama</option>
                      <option value="Penulis Pendamping">Penulis Pendamping</option>
                      <option value="Editor / Penyusun">Editor / Penyusun</option>
                      <option value="Kontributor">Kontributor</option>
                    </select>
                    <button v-if="publishAuthors.length > 1" type="button" @click="removePublishAuthor(idx)" class="p-1 text-red-400 hover:text-red-300" title="Hapus">
                      <span class="material-symbols-outlined text-[16px]">close</span>
                    </button>
                  </div>
                </div>
              </div>

              <label class="field" :class="modalLabel">
                <span>Nomor ISBN</span>
                <input v-model="publishForm.isbn" class="input" :class="modalInput" placeholder="Contoh: 978-602-1234-56-7" />
              </label>

              <label class="field" :class="modalLabel">
                <span>Penerbit *</span>
                <input v-model="publishForm.publisher" required class="input" :class="modalInput" placeholder="Penerbit" />
              </label>

              <label class="field" :class="modalLabel">
                <span>Tahun Terbit *</span>
                <input v-model.number="publishForm.year" type="number" required class="input" :class="modalInput" placeholder="2026" />
              </label>

              <label class="field" :class="modalLabel">
                <span>Jumlah Halaman *</span>
                <input v-model="publishForm.pages" required class="input" :class="modalInput" placeholder="Contoh: xii + 250 hlm" />
              </label>

              <label class="field" :class="modalLabel">
                <span>Ukuran Buku (Dimensi) *</span>
                <input v-model="publishForm.size" required class="input" :class="modalInput" placeholder="Contoh: 14.8 x 21 cm (A5)" />
              </label>

              <label class="field" :class="modalLabel">
                <span>Bahasa *</span>
                <input v-model="publishForm.language" required class="input" :class="modalInput" placeholder="Contoh: Indonesia / Arab / Inggris" />
              </label>
            </div>

            <label class="field" :class="modalLabel">
              <span>Deskripsi / Sinopsis Buku</span>
              <textarea v-model="publishForm.description" class="input min-h-[90px]" :class="modalInput" placeholder="Sinopsis atau deskripsi singkat buku..."></textarea>
            </label>

            <div v-if="publishMsg" class="text-sm font-bold" :class="publishSuccess ? 'text-green-500' : 'text-red-500'">{{ publishMsg }}</div>

            <div class="flex justify-end gap-2 border-t pt-4" :class="modalBorder">
              <button type="button" @click="showPublishModal = false" class="rounded-xl border px-4 py-2 text-sm font-bold cursor-pointer transition" :class="modalBtnCancel">Batal</button>
              <button type="submit" :disabled="publishing" class="rounded-xl bg-emerald-500 px-6 py-2.5 text-sm font-black text-white flex items-center gap-2 cursor-pointer hover:bg-emerald-600 transition shadow-md">
                <span v-if="publishing" class="material-symbols-outlined animate-spin text-[18px]">progress_activity</span>
                <span v-else class="material-symbols-outlined text-[18px]">publish</span>
                {{ publishing ? 'Menerbitkan...' : 'Terbitkan Sekarang' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>

  </div>
</template>

<script setup>
import { computed, defineComponent, h, onMounted, onUnmounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import VueMultiselect from 'vue-multiselect'
import 'vue-multiselect/dist/vue-multiselect.css'
import api from '../../../axios'
import { storageUrl } from '../../../utils/asset'
import { readThemePreference, resolveThemePreference } from '../../../composables/useThemePreference'

const Info = defineComponent({
  props: { label: String, value: String },
  setup(props) {
    return () => h('div', { class: 'rounded-xl border p-4', style: 'border-color: var(--border); background: var(--bg-input)' }, [
      h('p', { class: 'text-xs font-bold uppercase tracking-wider', style: 'color: var(--text-muted)' }, props.label),
      h('p', { class: 'mt-1 text-sm font-black', style: 'color: var(--text-heading)' }, props.value),
    ])
  },
})

const route = useRoute()
const loading = ref(false)
const submission = ref(null)

const currentTheme = ref('dark')
let themeObserver = null

function syncThemeFromDOM() {
  const root = document.querySelector('.admin-root')
  if (root) {
    currentTheme.value = root.getAttribute('data-theme') || 'dark'
  } else {
    currentTheme.value = resolveThemePreference(readThemePreference())
  }
}

// Modal Theme Classes
const modalCard = computed(() => currentTheme.value === 'light' ? 'bg-white text-slate-800 border border-slate-200' : 'bg-[#141d38] text-slate-100 border border-[#1c2850]')
const modalBorder = computed(() => currentTheme.value === 'light' ? 'border-slate-200' : 'border-[#1c2850]')
const modalTitle = computed(() => currentTheme.value === 'light' ? 'text-slate-900' : 'text-white')
const modalSub = computed(() => currentTheme.value === 'light' ? 'text-slate-500' : 'text-slate-400')
const modalClose = computed(() => currentTheme.value === 'light' ? 'text-slate-400 hover:text-slate-800' : 'text-slate-400 hover:text-white')
const modalLabel = computed(() => currentTheme.value === 'light' ? 'text-slate-700' : 'text-slate-200')
const modalInput = computed(() => currentTheme.value === 'light' ? 'bg-white text-slate-900 border-slate-300' : 'bg-[#0f172a] text-white border-[#1c2850]')
const modalChip = computed(() => currentTheme.value === 'light' ? 'bg-slate-100 border-slate-200 text-slate-700' : 'bg-[#0f172a] border-[#1c2850] text-slate-300')
const modalBtnCancel = computed(() => currentTheme.value === 'light' ? 'bg-slate-100 border-slate-300 text-slate-700 hover:bg-slate-200' : 'bg-[#0f172a] border-[#1c2850] text-slate-300 hover:bg-slate-800')

// Preset Hint Options
const layouterHints = [
  'Hasil tata letak (layout) dan desain sampul telah diselesaikan. Silakan ditinjau.',
  'Layouting tahap awal selesai. Font dan margin telah disesuaikan dengan standar cetak Dalwa Press.',
  'Berkas layout siap untuk proses uji cetak dan finalisasi.',
]



// Editor Assignments
const editors = ref([])
const assignment = ref({ primary_editor_id: '', co_editor_ids: [], note: '' })
const assignmentSaving = ref(false)
const assignmentMessage = ref('')

// Layouter Assignments
const layouters = ref([])
const layouterAssignment = ref({ layouter_ids: [], note: '' })
const layouterSaving = ref(false)
const layouterMessage = ref('')

// Admin Layouter Modal
const showLayouterModal = ref(false)
const activeLayouterName = ref('')
const layoutFileInput = ref(null)
const adminLayouterForm = ref({ layouter_id: '', note: '' })
const adminLayouterSaving = ref(false)
const adminLayouterMsg = ref('')
const adminLayouterSuccess = ref(false)

// Cover Designer Assignments
const coverDesigners = ref([])
const coverAssignment = ref({ cover_designer_ids: [], note: '' })
const coverSaving = ref(false)
const coverMessage = ref('')

// Admin Cover Modal
const showCoverModal = ref(false)
const activeCoverDesignerName = ref('')
const coverFileInput = ref(null)
const adminCoverForm = ref({ cover_designer_id: '', note: '' })
const adminCoverSaving = ref(false)
const adminCoverMsg = ref('')
const adminCoverSuccess = ref(false)

const combinedRevisionsHistory = computed(() => {
  if (!submission.value) return []
  const list = []

  // 1. Initial Manuscript file
  if (submission.value.manuscript_file) {
    list.push({
      id: 'main-manuscript',
      title: 'Manuscript Naskah Awal',
      type: 'manuscript',
      file_path: submission.value.manuscript_file,
      user_name: submission.value.author_name || 'Author',
      role_name: 'Author',
      note: 'Berkas naskah utama yang diunggah saat pengajuan pertama.',
      created_at: submission.value.submitted_at || submission.value.created_at,
    })
  }

  // 2. Cover file
  if (submission.value.cover_file) {
    list.push({
      id: 'main-cover',
      title: 'Desain Sampul / Cover Naskah',
      type: 'cover',
      file_path: submission.value.cover_file,
      user_name: submission.value.author_name || 'Author',
      role_name: 'Author',
      note: 'File sampul / cover awal naskah.',
      created_at: submission.value.submitted_at || submission.value.created_at,
    })
  }

  // 3. Revisions (SubmissionRevision)
  const revisionsList = Array.isArray(submission.value.revisions)
    ? submission.value.revisions
    : (submission.value.revisions && typeof submission.value.revisions === 'object' ? Object.values(submission.value.revisions) : [])

  let layouterIdx = 0
  let coverIdx = 0
  let revisionIdx = 0

  revisionsList.forEach((rev) => {
    if (rev && rev.revision_file) {
      const roleName = (rev.user?.role?.name || '').toLowerCase()
      const userName = (rev.user?.name || '').toLowerCase()
      const noteText = (rev.note || '').toLowerCase()
      
      const isCoverFile = roleName.includes('cover') || roleName.includes('sampul') || userName.includes('cover') || noteText.includes('cover') || noteText.includes('sampul')
      const isLayouterFile = !isCoverFile && (roleName.includes('layouter') || userName.includes('layouter') || noteText.includes('layout'))

      let title = ''
      let type = 'revision'

      if (isCoverFile) {
        coverIdx++
        type = 'cover'
        title = coverIdx > 1 ? `Berkas Desain Sampul #${coverIdx}` : `Berkas Desain Sampul`
      } else if (isLayouterFile) {
        layouterIdx++
        type = 'layouter'
        title = layouterIdx > 1 ? `Berkas Naskah Layouter #${layouterIdx}` : `Berkas Naskah Layouter`
      } else {
        revisionIdx++
        type = 'revision'
        title = `Berkas Revisi Naskah #${revisionIdx}`
      }

      list.push({
        id: `rev-${rev.id}`,
        title,
        type,
        is_layouter: isLayouterFile,
        is_cover: isCoverFile,
        file_path: rev.revision_file,
        user_name: rev.user?.name || 'User',
        role_name: rev.user?.role?.name || (isCoverFile ? 'Desain Cover' : (isLayouterFile ? 'Layouter' : 'User')),
        note: rev.note || '-',
        created_at: rev.created_at,
      })
    }
  })

  // 4. Manuscript Versions (SubmissionManuscriptVersion)
  const manuscriptVersionsList = Array.isArray(submission.value.manuscript_versions)
    ? submission.value.manuscript_versions
    : (submission.value.manuscript_versions && typeof submission.value.manuscript_versions === 'object' ? Object.values(submission.value.manuscript_versions) : [])

  manuscriptVersionsList.forEach((ver) => {
    if (ver && ver.file_path && !list.some((item) => item.file_path === ver.file_path)) {
      list.push({
        id: `ver-${ver.id}`,
        title: `Manuscript Version v${ver.version_number}`,
        type: 'manuscript_version',
        file_path: ver.file_path,
        user_name: ver.user?.name || 'User',
        role_name: ver.user?.role?.name || 'User',
        note: ver.note || '-',
        created_at: ver.created_at,
      })
    }
  })

  return list.sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
})

const primaryEditorOption = computed({
  get: () => editors.value.find((editor) => editor.id === assignment.value.primary_editor_id) || null,
  set: (editor) => {
    assignment.value.primary_editor_id = editor?.id || ''
    assignment.value.co_editor_ids = assignment.value.co_editor_ids.filter((id) => id !== editor?.id)
  },
})

const coEditorOptions = computed({
  get: () => editors.value.filter((editor) => assignment.value.co_editor_ids.includes(editor.id)),
  set: (selectedEditors) => {
    assignment.value.co_editor_ids = (selectedEditors || []).map((editor) => editor.id)
  },
})

const availableCoEditors = computed(() => editors.value.filter((editor) => editor.id !== assignment.value.primary_editor_id))

function isCoEditorSelected(editor) {
  return assignment.value.co_editor_ids.includes(editor.id)
}

const layouterOptions = computed({
  get: () => layouters.value.filter((layouter) => layouterAssignment.value.layouter_ids.includes(layouter.id)),
  set: (selectedLayouters) => {
    layouterAssignment.value.layouter_ids = (selectedLayouters || []).map((layouter) => layouter.id)
  },
})

function isLayouterSelected(layouter) {
  return layouterAssignment.value.layouter_ids.includes(layouter.id)
}

const coverDesignerOptions = computed({
  get: () => coverDesigners.value.filter((designer) => coverAssignment.value.cover_designer_ids.includes(designer.id)),
  set: (selectedDesigners) => {
    coverAssignment.value.cover_designer_ids = (selectedDesigners || []).map((designer) => designer.id)
  },
})

function isCoverDesignerSelected(designer) {
  return coverAssignment.value.cover_designer_ids.includes(designer.id)
}

const primaryEditorName = computed(() => {
  const primary = submission.value?.editor_assignments?.find((item) => item.role === 'primary')
  return primary?.editor?.name || '-'
})

const assignedLayoutersList = computed(() => {
  if (!submission.value?.layouter_assignments?.length) return '-'
  return submission.value.layouter_assignments.map((a) => a.layouter?.name).filter(Boolean).join(', ') || '-'
})

async function loadData() {
  loading.value = true
  const { data } = await api.get(`/submissions/${route.params.id}`)
  submission.value = data
  
  const primary = data.editor_assignments?.find((item) => item.role === 'primary')
  assignment.value = {
    primary_editor_id: primary?.editor_id || '',
    co_editor_ids: (data.editor_assignments || []).filter((item) => item.role === 'co_editor').map((item) => item.editor_id),
    note: primary?.note || '',
  }

  layouterAssignment.value = {
    layouter_ids: (data.layouter_assignments || []).map((item) => item.layouter_id),
    note: data.layouter_assignments?.[0]?.note || '',
  }

  coverAssignment.value = {
    cover_designer_ids: (data.cover_assignments || []).map((item) => item.cover_designer_id),
    note: data.cover_assignments?.[0]?.note || '',
  }

  loading.value = false
}

async function loadEditors() {
  const { data } = await api.get('/editors')
  editors.value = data || []
}

async function loadLayouters() {
  const { data } = await api.get('/layouters')
  layouters.value = data || []
}

async function loadCoverDesigners() {
  try {
    const { data } = await api.get('/cover-designers')
    coverDesigners.value = data || []
  } catch (err) {
    console.error('Failed to load cover designers', err)
  }
}

async function assignEditors() {
  assignmentSaving.value = true
  assignmentMessage.value = ''
  try {
    const { data } = await api.post(`/submissions/${route.params.id}/assign-editors`, assignment.value)
    submission.value = data
    assignmentMessage.value = 'Assignment Editor berhasil disimpan.'
    setTimeout(() => { assignmentMessage.value = '' }, 2500)
  } finally {
    assignmentSaving.value = false
  }
}

async function assignLayouters() {
  layouterSaving.value = true
  layouterMessage.value = ''
  try {
    const { data } = await api.post(`/submissions/${route.params.id}/assign-layouters`, layouterAssignment.value)
    submission.value = data
    layouterMessage.value = 'Assignment Layouter berhasil disimpan.'
    setTimeout(() => { layouterMessage.value = '' }, 2500)
  } finally {
    layouterSaving.value = false
  }
}

async function assignCoverDesigners() {
  coverSaving.value = true
  coverMessage.value = ''
  try {
    const { data } = await api.post(`/submissions/${route.params.id}/assign-cover-designers`, coverAssignment.value)
    submission.value = data
    coverMessage.value = 'Assignment desainer cover berhasil disimpan.'
    setTimeout(() => {
      coverMessage.value = ''
    }, 3000)
  } catch (err) {
    alert(err.response?.data?.message || 'Gagal menyimpan assignment desainer cover.')
  } finally {
    coverSaving.value = false
  }
}

function openLayouterModal(item) {
  adminLayouterForm.value = {
    layouter_id: item.layouter_id,
    note: '',
  }
  activeLayouterName.value = item.layouter?.name || 'Layouter'
  adminLayouterMsg.value = ''
  showLayouterModal.value = true
}

async function submitModalLayouterLayout() {
  const file = layoutFileInput.value?.files?.[0]
  if (!file) {
    adminLayouterSuccess.value = false
    adminLayouterMsg.value = 'Silakan pilih berkas layout.'
    return
  }

  adminLayouterSaving.value = true
  adminLayouterMsg.value = ''

  const formData = new FormData()
  formData.append('revision_file', file)
  if (adminLayouterForm.value.note) formData.append('note', adminLayouterForm.value.note)
  if (adminLayouterForm.value.layouter_id) formData.append('layouter_id', adminLayouterForm.value.layouter_id)

  try {
    const { data } = await api.post(`/submissions/${route.params.id}/upload-layout`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    submission.value = data.submission
    adminLayouterSuccess.value = true
    adminLayouterMsg.value = 'Hasil layout berhasil diunggah!'
    setTimeout(() => {
      adminLayouterMsg.value = ''
      showLayouterModal.value = false
    }, 1500)
  } catch (err) {
    adminLayouterSuccess.value = false
    adminLayouterMsg.value = err.response?.data?.message || 'Gagal mengunggah hasil layout.'
  } finally {
    adminLayouterSaving.value = false
  }
}

function openCoverModal(item) {
  adminCoverForm.value = {
    cover_designer_id: item.cover_designer_id,
    note: '',
  }
  activeCoverDesignerName.value = item.designer?.name || 'Desainer Cover'
  adminCoverMsg.value = ''
  showCoverModal.value = true
}

async function submitModalUseInitialCover() {
  adminCoverSaving.value = true
  adminCoverMsg.value = ''
  try {
    const { data } = await api.post(`/submissions/${route.params.id}/upload-cover`, {
      use_initial_cover: true,
      note: adminCoverForm.value.note || 'Menggunakan berkas sampul / cover dari pengajuan awal penulis.',
      cover_designer_id: adminCoverForm.value.cover_designer_id,
    })
    submission.value = data.submission
    adminCoverSuccess.value = true
    adminCoverMsg.value = 'Cover awal penulis berhasil disetujui & digunakan!'
    setTimeout(() => {
      adminCoverMsg.value = ''
      showCoverModal.value = false
    }, 1500)
  } catch (err) {
    adminCoverSuccess.value = false
    adminCoverMsg.value = err.response?.data?.message || 'Gagal menyetujui cover awal penulis.'
  } finally {
    adminCoverSaving.value = false
  }
}

async function submitModalCoverLayout() {
  const file = coverFileInput.value?.files?.[0]
  if (!file) {
    adminCoverSuccess.value = false
    adminCoverMsg.value = 'Silakan pilih berkas hasil cover.'
    return
  }

  adminCoverSaving.value = true
  adminCoverMsg.value = ''

  const formData = new FormData()
  formData.append('revision_file', file)
  if (adminCoverForm.value.note) formData.append('note', adminCoverForm.value.note)
  if (adminCoverForm.value.cover_designer_id) formData.append('cover_designer_id', adminCoverForm.value.cover_designer_id)

  try {
    const { data } = await api.post(`/submissions/${route.params.id}/upload-cover`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    submission.value = data.submission
    adminCoverSuccess.value = true
    adminCoverMsg.value = 'Hasil desain cover berhasil diunggah!'
    setTimeout(() => {
      adminCoverMsg.value = ''
      showCoverModal.value = false
    }, 1500)
  } catch (err) {
    adminCoverSuccess.value = false
    adminCoverMsg.value = err.response?.data?.message || 'Gagal mengunggah hasil desain cover.'
  } finally {
    adminCoverSaving.value = false
  }
}

async function deleteRevisionFile(item) {
  if (!confirm(`Apakah Anda yakin ingin menghapus "${item.title}"?`)) return
  try {
    const { data } = await api.delete(`/submissions/${route.params.id}/revisions/${item.id}`)
    submission.value = data.submission
    alert('Berkas berhasil dihapus.')
  } catch (err) {
    alert(err.response?.data?.message || 'Gagal menghapus berkas.')
  }
}



function isEditorPrimary(editorId) {
  const assignment = submission.value?.editor_assignments?.find((a) => a.editor_id === editorId)
  return assignment?.role === 'primary'
}

function statusLabel(val) {
  return { submitted: 'Submitted', under_review: 'Under Review', revision: 'Revision', accepted: 'Accepted', rejected: 'Rejected', published: 'Published' }[val] || val
}

function statusBadge(val) {
  const base = 'app-badge'
  if (val === 'accepted' || val === 'published') return `${base} app-badge--success`
  if (val === 'rejected') return `${base} app-badge--red`
  if (val === 'revision') return `${base} app-badge--blue`
  return `${base} app-badge--sky`
}

function recommendationBadge(rec) {
  const base = 'app-badge text-xs uppercase font-extrabold'
  if (rec === 'accept') return `${base} app-badge--success`
  if (rec === 'reject') return `${base} app-badge--red`
  return `${base} app-badge--blue`
}

function roleBadgeClass(roleName) {
  const role = (roleName || '').toLowerCase()
  if (role.includes('admin')) return 'bg-amber-500/20 text-amber-500 border border-amber-500/30'
  if (role.includes('author') || role.includes('penulis')) return 'bg-blue-500/20 text-blue-500 border border-blue-500/30'
  if (role.includes('editor')) return 'bg-purple-500/20 text-purple-500 border border-purple-500/30'
  if (role.includes('layouter')) return 'bg-emerald-500/20 text-emerald-500 border border-emerald-500/30'
  return 'bg-slate-500/20 text-slate-400 border border-slate-500/30'
}

function decisionStatusToSubStatus(dec) {
  if (dec === 'accepted') return 'accepted'
  if (dec === 'rejected') return 'rejected'
  return 'revision'
}

function formatDate(date) {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
}

function formatDateTime(date) {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })
}

const hasLayouterFile = computed(() => {
  if (!submission.value) return false
  const revisionsList = Array.isArray(submission.value.revisions)
    ? submission.value.revisions
    : (submission.value.revisions && typeof submission.value.revisions === 'object' ? Object.values(submission.value.revisions) : [])
  return revisionsList.some((rev) => {
    const roleName = (rev.user?.role?.name || '').toLowerCase()
    const userName = (rev.user?.name || '').toLowerCase()
    const noteText = (rev.note || '').toLowerCase()
    return roleName.includes('layouter') || userName.includes('layouter') || noteText.includes('layout')
  })
})

const hasCoverFile = computed(() => {
  if (!submission.value) return false
  const revisionsList = Array.isArray(submission.value.revisions)
    ? submission.value.revisions
    : (submission.value.revisions && typeof submission.value.revisions === 'object' ? Object.values(submission.value.revisions) : [])
  return revisionsList.some((rev) => {
    const roleName = (rev.user?.role?.name || '').toLowerCase()
    const userName = (rev.user?.name || '').toLowerCase()
    const noteText = (rev.note || '').toLowerCase()
    return roleName.includes('cover') || roleName.includes('sampul') || userName.includes('cover') || noteText.includes('cover') || noteText.includes('sampul')
  })
})

const publishing = ref(false)
const showPublishModal = ref(false)
const bookCategories = ref([])
const publishMsg = ref('')
const publishSuccess = ref(false)

const publishAuthors = ref([
  { name: '', role: 'Penulis Utama' }
])

const publishForm = ref({
  title: '',
  category_id: '',
  author_name: '',
  isbn: '',
  year: new Date().getFullYear(),
  publisher: 'UII Dalwa Press',
  pages: '',
  size: '14.8 x 21 cm (A5)',
  language: 'Indonesia',
  description: '',
})

function addPublishAuthor() {
  publishAuthors.value.push({
    name: '',
    role: publishAuthors.value.length === 0 ? 'Penulis Utama' : 'Penulis Pendamping'
  })
}

function removePublishAuthor(index) {
  if (publishAuthors.value.length > 1) {
    publishAuthors.value.splice(index, 1)
  }
}

async function openPublishModal() {
  if (!submission.value) return

  if (submission.value.submission_authors && submission.value.submission_authors.length > 0) {
    publishAuthors.value = submission.value.submission_authors.map((sa, idx) => ({
      name: sa.name,
      author_id: sa.author_id || null,
      email: sa.email || '',
      phone: sa.phone || '',
      institution: sa.institution || '',
      role: sa.role || (idx === 0 ? 'Penulis Utama' : 'Penulis Pendamping')
    }))
  } else {
    publishAuthors.value = [
      {
        name: submission.value.author_name || '',
        email: submission.value.email || '',
        phone: submission.value.phone || '',
        role: 'Penulis Utama'
      }
    ]
  }

  publishForm.value = {
    title: submission.value.title || '',
    category_id: submission.value.category_id || '',
    author_name: submission.value.author_name || '',
    isbn: '',
    year: new Date().getFullYear(),
    publisher: 'UII Dalwa Press',
    pages: '',
    size: '14.8 x 21 cm (A5)',
    language: 'Indonesia',
    description: submission.value.description || '',
  }
  publishMsg.value = ''
  showPublishModal.value = true

  if (!bookCategories.value.length) {
    try {
      const { data } = await api.get('/book-categories', { params: { all: 1 } })
      bookCategories.value = data.data || data || []
    } catch (err) {
      console.error('Failed to load book categories', err)
    }
  }
}

async function submitPublishForm() {
  publishing.value = true
  publishMsg.value = ''
  try {
    const validAuthors = publishAuthors.value.filter(a => a.name.trim())
    const payload = {
      ...publishForm.value,
      authors: validAuthors,
      author_name: validAuthors.map(a => a.name.trim()).join(', ')
    }
    const { data } = await api.post(`/submissions/${route.params.id}/publish`, payload)
    submission.value = data.submission
    publishSuccess.value = true
    publishMsg.value = 'Sukses! Naskah berhasil diterbitkan menjadi data Buku.'
    setTimeout(() => {
      showPublishModal.value = false
    }, 1200)
  } catch (err) {
    publishSuccess.value = false
    publishMsg.value = err.response?.data?.message || 'Gagal menerbitkan naskah.'
  } finally {
    publishing.value = false
  }
}

// Horizontal Timeline Steps
const horizontalTimelineSteps = computed(() => {
  if (!submission.value) return []
  const status = submission.value.status

  // Step 1: Open
  const step1 = {
    number: 1,
    key: 'open',
    title: 'Open',
    subtitle: 'Naskah Masuk',
    state: 'completed',
    icon: 'folder_open',
  }

  // Step 2: Review
  let step2State = 'pending'
  let step2Sub = 'Menunggu Review'
  if (status === 'rejected') {
    step2State = 'rejected'
    step2Sub = 'Ditolak Editor'
  } else if (status === 'under_review' || status === 'revision') {
    step2State = 'active'
    step2Sub = status === 'revision' ? 'Perlu Revisi' : 'Proses Review'
  } else if (status === 'accepted' || status === 'published') {
    step2State = 'completed'
    step2Sub = 'Lolos Review'
  } else if (status === 'submitted') {
    step2State = 'active'
    step2Sub = 'Draft Masuk'
  }

  // Step 3: Layout & Cover
  let step3State = 'pending'
  let step3Sub = 'Layout & Sampul'
  if (status === 'rejected') {
    step3State = 'rejected'
    step3Sub = 'Dibatalkan'
  } else if (status === 'published' || hasLayouterFile.value || hasCoverFile.value) {
    step3State = 'completed'
    step3Sub = 'Layout & Sampul Selesai'
  } else if (status === 'accepted' || submission.value?.layouter_assignments?.length || submission.value?.cover_assignments?.length) {
    step3State = 'active'
    step3Sub = 'Proses Layout & Sampul'
  }

  // Step 4: Done
  let step4State = 'pending'
  let step4Sub = 'Belum Terbit'
  if (status === 'rejected') {
    step4State = 'rejected'
    step4Sub = 'Gagal Terbit'
  } else if (status === 'published') {
    step4State = 'completed'
    step4Sub = 'Telah Diterbitkan'
  } else if (hasLayouterFile.value || step3State === 'completed') {
    step4State = 'active'
    step4Sub = 'Siap Diterbitkan'
  }

  return [
    step1,
    { number: 2, key: 'review', title: 'Review', subtitle: step2Sub, state: step2State, icon: step2State === 'rejected' ? 'close' : 'rate_review' },
    { number: 3, key: 'layouter', title: 'Layout & Sampul', subtitle: step3Sub, state: step3State, icon: step3State === 'rejected' ? 'close' : 'palette' },
    { number: 4, key: 'done', title: 'Done', subtitle: step4Sub, state: step4State, icon: step4State === 'rejected' ? 'close' : (step4State === 'completed' ? 'check_circle' : 'flag') },
  ]
})

function stepCircleClass(state) {
  if (state === 'completed') return 'bg-emerald-500 text-white border-emerald-400 shadow-emerald-500/20'
  if (state === 'rejected') return 'bg-red-500 text-white border-red-400 shadow-red-500/20'
  if (state === 'active') return 'bg-accent text-white border-blue-400 ring-4 ring-accent/20'
  return 'bg-slate-800 text-slate-400 border-slate-700'
}

function stepTitleClass(state) {
  if (state === 'completed') return 'text-emerald-400 font-black'
  if (state === 'rejected') return 'text-red-400 font-black'
  if (state === 'active') return 'text-accent font-black'
  return 'opacity-60'
}

function connectorLineClass(currentState, nextState) {
  if (nextState === 'rejected') return 'bg-red-500/60'
  if (currentState === 'completed') return 'bg-emerald-500'
  if (currentState === 'active') return 'bg-accent/50'
  return 'bg-slate-700/50'
}

onMounted(async () => {
  syncThemeFromDOM()
  const root = document.querySelector('.admin-root')
  if (root) {
    themeObserver = new MutationObserver(() => syncThemeFromDOM())
    themeObserver.observe(root, { attributes: true, attributeFilter: ['data-theme'] })
  }
  await Promise.all([loadData(), loadEditors(), loadLayouters(), loadCoverDesigners()])
})

onUnmounted(() => {
  if (themeObserver) {
    themeObserver.disconnect()
    themeObserver = null
  }
})
</script>

<style scoped>
.field { display: flex; flex-direction: column; gap: 0.4rem; color: var(--text-body); font-size: 0.875rem; font-weight: 700; }
.assignment-field { min-width: 0; }
.assignment-field :deep(.multiselect) { min-height: 44px; }
.assignment-field :deep(.multiselect__tags) { min-height: 44px; padding-top: 8px; }
.assignment-field :deep(.multiselect__content-wrapper) { z-index: 80; }
.input { border-radius: 0.85rem; background: var(--bg-input); border: 1px solid var(--border); color: var(--text-heading); padding: 0.7rem 0.9rem; outline: none; }
.file-btn { display: inline-flex; align-items: center; gap: 0.5rem; border-radius: 999px; background: var(--bg-input); border: 1px solid var(--border); color: var(--text-heading); padding: 0.7rem 1rem; font-weight: 800; text-decoration: none; transition: background 0.2s; }
.file-btn:hover { background: rgba(255, 255, 255, 0.08); }
.editor-tag { display: inline-flex; align-items: center; gap: 0.35rem; margin: 0 0.25rem 0.25rem 0; border-radius: 999px; background: rgba(37, 99, 235, 0.16); color: var(--color-accent); padding: 0.25rem 0.55rem; font-size: 0.75rem; font-weight: 900; }
.editor-tag button { color: inherit; font-weight: 900; line-height: 1; }
.editor-check { display: inline-flex; align-items: center; justify-content: center; width: 1.1rem; height: 1.1rem; margin-top: 0.1rem; border-radius: 0.3rem; border: 1px solid var(--border-light); color: var(--text-btn); background: var(--bg-card); flex-shrink: 0; }
.editor-check.selected { border-color: var(--color-accent); background: var(--color-accent); }
</style>
