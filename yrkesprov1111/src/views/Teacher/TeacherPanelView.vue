<script setup>
    import Navbar from '@/components/NavbarComp.vue'
    import TeacherDashboard from '@/components/teacher-dashboardComp.vue'
    import TeacherStandalone from '@/components/TeacherStandaloneExercises.vue'
    import { ref } from 'vue'

</script>

<template>
<Navbar/>
    <TeacherDashboard/>
    <TeacherStandalone/>
</template>