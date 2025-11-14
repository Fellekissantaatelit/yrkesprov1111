<script setup>
    import Navbar from '@/components/NavbarComp.vue'
    import { ref } from 'vue'

const username = ref(localStorage.getItem('user') || '')
</script>

<template>
<Navbar/>
    <h2>Dashboard</h2>
    <p>Välkommen {{ username }}</p>
</template>