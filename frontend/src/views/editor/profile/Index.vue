<template>
  <div class="flex flex-col gap-6">
    <div><h2 class="text-xl font-black" style="color: var(--text-heading)">Profile Editor</h2><p class="text-sm" style="color: var(--text-muted)">Data akun editor yang sedang login.</p></div>
    <section class="grid gap-4 rounded-2xl p-6 sm:grid-cols-2" style="background: var(--bg-card); border: 1px solid var(--border)">
      <Info label="Nama" :value="user.name || '-'" />
      <Info label="Username" :value="user.username || '-'" />
      <Info label="Email" :value="user.email || '-'" />
      <Info label="Role" :value="user.role?.name || '-'" />
    </section>
  </div>
</template>
<script setup>
import { defineComponent, h, onMounted, ref } from 'vue'
import api from '../../../axios'
const user = ref({})
const Info = defineComponent({ props:{label:String,value:String}, setup(props){ return () => h('div',{class:'rounded-xl border p-4',style:'border-color: var(--border); background: var(--bg-input)'},[h('p',{class:'text-xs font-bold uppercase tracking-wider',style:'color: var(--text-muted)'},props.label),h('p',{class:'mt-1 text-sm font-black',style:'color: var(--text-heading)'},props.value)]) }})
onMounted(async () => { user.value = (await api.get('/editor/profile')).data })
</script>
