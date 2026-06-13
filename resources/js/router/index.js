import { createRouter, createWebHistory } from 'vue-router';
import HomePage from '@/pages/HomePage.vue';
import LoginPage from '@/modules/auth/pages/LoginPage.vue';
import PatientAllergiesPage from '@/pages/patients/PatientAllergiesPage.vue';
import { authGuard } from '@/router/guards';

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/',
            name: 'home',
            component: HomePage,
        },
        {
            path: '/login',
            name: 'login',
            component: LoginPage,
            meta: { guest: true },
        },
        {
            path: '/patients/:patientId/allergies',
            name: 'patient-allergies',
            component: PatientAllergiesPage,
        },
    ],
});

router.beforeEach(authGuard);

export default router;
