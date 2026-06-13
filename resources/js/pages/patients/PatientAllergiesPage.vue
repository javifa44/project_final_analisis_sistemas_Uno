<template>
    <AppLayout>
        <main class="allergies-page">
            <section class="page-header">
                <div>
                    <p class="eyebrow">Módulo 15</p>
                    <h1>Alergias del paciente</h1>
                    <p>
                        Registro, consulta, edición y desactivación de alergias asociadas a un paciente.
                    </p>
                </div>

                <button class="secondary-button" @click="loadAllergies">
                    Recargar
                </button>
            </section>

            <section v-if="activeAllergies.length > 0" class="warning-card">
                <div class="warning-icon">⚠️</div>
                <div>
                    <h2>Paciente con alergias activas</h2>
                    <p>
                        Este paciente tiene {{ activeAllergies.length }} alergia(s) activa(s).
                        Revisar antes de indicar medicamentos, alimentos o procedimientos.
                    </p>

                    <div class="warning-tags">
                        <span
                            v-for="allergy in activeAllergies"
                            :key="allergy.id"
                            class="warning-tag"
                        >
                            {{ allergy.allergy_name }} - {{ severityLabel(allergy.severity) }}
                        </span>
                    </div>
                </div>
            </section>

            <section class="patient-card">
                <h2>Paciente seleccionado</h2>

                <div class="patient-grid">
                    <div>
                        <span>ID paciente</span>
                        <strong>{{ patientId }}</strong>
                    </div>

                    <div>
                        <span>Expediente</span>
                        <strong>EXP-{{ String(patientId).padStart(4, '0') }}</strong>
                    </div>

                    <div>
                        <span>Estado del módulo</span>
                        <strong>Funcional</strong>
                    </div>
                </div>
            </section>

            <section class="content-grid">
                <article class="form-card">
                    <h2>{{ editingId ? 'Editar alergia' : 'Registrar alergia' }}</h2>

                    <form @submit.prevent="saveAllergy">
                        <label>
                            Nombre de la alergia
                            <input
                                v-model="form.allergy_name"
                                type="text"
                                placeholder="Ejemplo: Penicilina"
                            />
                        </label>

                        <label>
                            Tipo de alergia
                            <select v-model="form.allergy_type">
                                <option value="medication">Medicamento</option>
                                <option value="food">Alimento</option>
                                <option value="environmental">Ambiental</option>
                                <option value="other">Otra</option>
                            </select>
                        </label>

                        <label>
                            Severidad
                            <select v-model="form.severity">
                                <option value="mild">Leve</option>
                                <option value="moderate">Moderada</option>
                                <option value="severe">Grave</option>
                            </select>
                        </label>

                        <label>
                            Reacción
                            <textarea
                                v-model="form.reaction"
                                placeholder="Describa la reacción presentada"
                            ></textarea>
                        </label>

                        <label>
                            Observaciones
                            <textarea
                                v-model="form.notes"
                                placeholder="Observaciones adicionales"
                            ></textarea>
                        </label>

                        <label class="checkbox-row">
                            <input v-model="form.active" type="checkbox" />
                            Alergia activa
                        </label>

                        <div v-if="errorMessage" class="error-message">
                            {{ errorMessage }}
                        </div>

                        <div v-if="successMessage" class="success-message">
                            {{ successMessage }}
                        </div>

                        <div class="form-actions">
                            <button type="submit" :disabled="loading">
                                {{ loading ? 'Guardando...' : editingId ? 'Actualizar' : 'Guardar' }}
                            </button>

                            <button
                                v-if="editingId"
                                type="button"
                                class="cancel-button"
                                @click="resetForm"
                            >
                                Cancelar edición
                            </button>
                        </div>
                    </form>
                </article>

                <article class="list-card">
                    <div class="list-header">
                        <div>
                            <h2>Alergias registradas</h2>
                            <p>Listado asociado al paciente seleccionado.</p>
                        </div>

                        <span class="counter">{{ allergies.length }}</span>
                    </div>

                    <div v-if="loadingList" class="empty-card">
                        Cargando alergias...
                    </div>

                    <div v-else-if="allergies.length === 0" class="empty-card">
                        No hay alergias registradas para este paciente.
                    </div>

                    <div
                        v-for="allergy in allergies"
                        v-else
                        :key="allergy.id"
                        class="allergy-item"
                        :class="allergy.severity"
                    >
                        <div>
                            <div class="allergy-title-row">
                                <h3>{{ allergy.allergy_name }}</h3>
                                <span v-if="allergy.active" class="active-pill">Activa</span>
                                <span v-else class="inactive-pill">Inactiva</span>
                            </div>

                            <p>
                                <strong>Tipo:</strong> {{ typeLabel(allergy.allergy_type) }}
                            </p>

                            <p>
                                <strong>Severidad:</strong> {{ severityLabel(allergy.severity) }}
                            </p>

                            <p v-if="allergy.reaction">
                                <strong>Reacción:</strong> {{ allergy.reaction }}
                            </p>

                            <p v-if="allergy.notes">
                                <strong>Notas:</strong> {{ allergy.notes }}
                            </p>
                        </div>

                        <div class="item-actions">
                            <button type="button" @click="editAllergy(allergy)">
                                Editar
                            </button>

                            <button
                                type="button"
                                class="danger-button"
                                @click="deactivateAllergy(allergy)"
                                :disabled="!allergy.active"
                            >
                                Desactivar
                            </button>
                        </div>
                    </div>
                </article>
            </section>
        </main>
    </AppLayout>
</template>

<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import AppLayout from '../../shared/components/AppLayout.vue'
import axios from '../../plugins/axios'

const route = useRoute()
const patientId = computed(() => route.params.patientId)

const allergies = ref([])
const loading = ref(false)
const loadingList = ref(false)
const errorMessage = ref('')
const successMessage = ref('')
const editingId = ref(null)

const form = reactive({
    allergy_name: '',
    allergy_type: 'medication',
    severity: 'mild',
    reaction: '',
    notes: '',
    active: true,
})

const activeAllergies = computed(() => {
    return allergies.value.filter((allergy) => allergy.active)
})

const resetMessages = () => {
    errorMessage.value = ''
    successMessage.value = ''
}

const resetForm = () => {
    editingId.value = null
    form.allergy_name = ''
    form.allergy_type = 'medication'
    form.severity = 'mild'
    form.reaction = ''
    form.notes = ''
    form.active = true
    resetMessages()
}

const loadAllergies = async () => {
    loadingList.value = true
    resetMessages()

    try {
        const response = await axios.get(`/patients/${patientId.value}/allergies`)
        allergies.value = response.data.data ?? []
    } catch (error) {
        errorMessage.value = 'No se pudieron cargar las alergias del paciente.'
    } finally {
        loadingList.value = false
    }
}

const saveAllergy = async () => {
    resetMessages()

    if (!form.allergy_name.trim()) {
        errorMessage.value = 'El nombre de la alergia es obligatorio.'
        return
    }

    loading.value = true

    const payload = {
        allergy_name: form.allergy_name,
        allergy_type: form.allergy_type,
        severity: form.severity,
        reaction: form.reaction,
        notes: form.notes,
        active: form.active,
    }

    try {
        if (editingId.value) {
            await axios.put(`/patient-allergies/${editingId.value}`, payload)
            successMessage.value = 'Alergia actualizada correctamente.'
        } else {
            await axios.post(`/patients/${patientId.value}/allergies`, payload)
            successMessage.value = 'Alergia registrada correctamente.'
        }

        resetForm()
        await loadAllergies()
    } catch (error) {
        errorMessage.value = 'No se pudo guardar la alergia. Revise los datos ingresados.'
    } finally {
        loading.value = false
    }
}

const editAllergy = (allergy) => {
    editingId.value = allergy.id
    form.allergy_name = allergy.allergy_name
    form.allergy_type = allergy.allergy_type
    form.severity = allergy.severity
    form.reaction = allergy.reaction ?? ''
    form.notes = allergy.notes ?? ''
    form.active = Boolean(allergy.active)
    resetMessages()
}

const deactivateAllergy = async (allergy) => {
    resetMessages()

    if (!confirm(`¿Desea desactivar la alergia ${allergy.allergy_name}?`)) {
        return
    }

    try {
        await axios.delete(`/patient-allergies/${allergy.id}`)
        successMessage.value = 'Alergia desactivada correctamente.'
        await loadAllergies()
    } catch (error) {
        errorMessage.value = 'No se pudo desactivar la alergia.'
    }
}

const typeLabel = (type) => {
    const labels = {
        medication: 'Medicamento',
        food: 'Alimento',
        environmental: 'Ambiental',
        other: 'Otra',
    }

    return labels[type] ?? 'Otra'
}

const severityLabel = (severity) => {
    const labels = {
        mild: 'Leve',
        moderate: 'Moderada',
        severe: 'Grave',
    }

    return labels[severity] ?? 'Leve'
}

onMounted(() => {
    loadAllergies()
})

watch(
    () => route.params.patientId,
    () => {
        resetForm()
        loadAllergies()
    }
)
</script>

<style scoped>
.allergies-page {
    min-height: 100vh;
    padding: 32px;
    background: #f4f7fb;
    color: #1f2937;
}

.page-header,
.patient-card,
.form-card,
.list-card,
.warning-card {
    background: #ffffff;
    border-radius: 22px;
    padding: 24px;
    box-shadow: 0 18px 45px rgba(15, 23, 42, 0.08);
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 22px;
    margin-bottom: 22px;
    background: linear-gradient(135deg, #0f766e, #155e75);
    color: #ffffff;
}

.eyebrow {
    margin: 0 0 8px;
    text-transform: uppercase;
    letter-spacing: 0.14em;
    font-size: 13px;
    opacity: 0.85;
}

.page-header h1 {
    margin: 0;
    font-size: 36px;
}

.page-header p {
    margin-bottom: 0;
    opacity: 0.9;
}

.secondary-button {
    background: rgba(255, 255, 255, 0.16);
    border: 1px solid rgba(255, 255, 255, 0.38);
    color: #ffffff;
    border-radius: 14px;
    padding: 12px 18px;
    font-weight: 800;
    cursor: pointer;
}

.warning-card {
    display: flex;
    gap: 18px;
    margin-bottom: 22px;
    background: #fffbeb;
    border: 2px solid #f59e0b;
}

.warning-icon {
    font-size: 36px;
}

.warning-card h2 {
    margin: 0 0 8px;
}

.warning-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 12px;
}

.warning-tag {
    background: #fee2e2;
    color: #991b1b;
    padding: 7px 12px;
    border-radius: 999px;
    font-weight: 800;
}

.patient-card {
    margin-bottom: 22px;
}

.patient-card h2,
.form-card h2,
.list-card h2 {
    margin-top: 0;
}

.patient-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 18px;
}

.patient-grid div {
    background: #f8fafc;
    padding: 16px;
    border-radius: 16px;
}

.patient-grid span {
    display: block;
    color: #64748b;
    margin-bottom: 6px;
    font-size: 14px;
}

.content-grid {
    display: grid;
    grid-template-columns: 0.9fr 1.1fr;
    gap: 22px;
}

form {
    display: grid;
    gap: 14px;
}

label {
    display: grid;
    gap: 7px;
    color: #334155;
    font-weight: 700;
}

input,
select,
textarea {
    width: 100%;
    border: 1px solid #cbd5e1;
    border-radius: 14px;
    padding: 12px 14px;
    font: inherit;
    background: #f8fafc;
    box-sizing: border-box;
}

textarea {
    min-height: 88px;
}

.checkbox-row {
    display: flex;
    align-items: center;
    gap: 10px;
}

.checkbox-row input {
    width: auto;
}

.form-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

button {
    border: none;
    border-radius: 14px;
    padding: 12px 16px;
    background: #0f766e;
    color: #ffffff;
    font-weight: 800;
    cursor: pointer;
}

button:disabled {
    opacity: 0.55;
    cursor: not-allowed;
}

.cancel-button {
    background: #64748b;
}

.danger-button {
    background: #dc2626;
}

.error-message,
.success-message {
    border-radius: 14px;
    padding: 12px 14px;
    font-weight: 700;
}

.error-message {
    background: #fee2e2;
    color: #991b1b;
}

.success-message {
    background: #dcfce7;
    color: #166534;
}

.list-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 14px;
}

.list-header p {
    margin: 0;
    color: #64748b;
}

.counter {
    background: #e0f2fe;
    color: #075985;
    border-radius: 999px;
    padding: 8px 13px;
    font-weight: 900;
}

.empty-card {
    margin-top: 16px;
    background: #f8fafc;
    border: 1px dashed #cbd5e1;
    border-radius: 16px;
    padding: 18px;
    color: #64748b;
    text-align: center;
}

.allergy-item {
    display: flex;
    justify-content: space-between;
    gap: 16px;
    margin-top: 16px;
    border-radius: 18px;
    padding: 18px;
    background: #f8fafc;
    border-left: 8px solid #94a3b8;
}

.allergy-item.mild {
    border-left-color: #22c55e;
}

.allergy-item.moderate {
    border-left-color: #f59e0b;
}

.allergy-item.severe {
    border-left-color: #dc2626;
}

.allergy-title-row {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.allergy-item h3 {
    margin: 0;
}

.allergy-item p {
    margin: 6px 0;
}

.active-pill,
.inactive-pill {
    border-radius: 999px;
    padding: 5px 10px;
    font-size: 12px;
    font-weight: 900;
}

.active-pill {
    background: #fee2e2;
    color: #991b1b;
}

.inactive-pill {
    background: #e2e8f0;
    color: #475569;
}

.item-actions {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

@media (max-width: 900px) {
    .page-header,
    .content-grid {
        display: grid;
        grid-template-columns: 1fr;
    }

    .patient-grid {
        grid-template-columns: 1fr;
    }

    .allergy-item {
        display: grid;
    }

    .item-actions {
        flex-direction: row;
    }
}
</style>
