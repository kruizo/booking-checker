<template>
  <div
    v-if="isOpen"
    class="fixed inset-0 bg-black/30 backdrop-blur-sm flex items-center justify-center z-50"
    @click.self="closeModal"
  >
    <div class="bg-white rounded-xl shadow-2xl p-8 max-w-md w-full mx-4">
      <h3 class="text-2xl font-bold text-gray-900 mb-6">
        {{ isEditing ? 'Edit Booking' : 'Create Booking' }}
      </h3>
      <form @submit.prevent="handleSubmit" class="space-y-4">
        <div>
          <label class="block text-sm font-semibold text-gray-700 pb-2">Date</label>
          <input
            v-model="formData.date"
            type="date"
            class="w-full px-4 py-2 border-2 border-gray-200 rounded-lg focus:outline-none focus:border-indigo-500"
            required
          />
        </div>
        <div>
          <label class="block text-sm font-semibold text-gray-700 pb-2">Start Time</label>
          <input
            v-model="formData.start_time"
            type="time"
            class="w-full px-4 py-2 border-2 border-gray-200 rounded-lg focus:outline-none focus:border-indigo-500"
            required
          />
        </div>
        <div>
          <label class="block text-sm font-semibold text-gray-700 pb-2">End Time</label>
          <input
            v-model="formData.end_time"
            type="time"
            class="w-full px-4 py-2 border-2 border-gray-200 rounded-lg focus:outline-none focus:border-indigo-500"
            required
          />
        </div>
        <div
          v-if="formError"
          class="bg-red-50 border border-red-200 rounded-lg p-3 text-red-700 text-sm"
        >
          {{ formError }}
        </div>
        <div class="flex justify-end gap-3 mt-6">
          <button
            type="button"
            @click="closeModal"
            class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-medium"
          >
            Cancel
          </button>
          <button
            type="submit"
            class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium disabled:opacity-50"
            :disabled="submitting"
          >
            {{ isEditing ? 'Update' : 'Create' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, watch } from 'vue'

interface BookingFormData {
  date: string
  start_time: string
  end_time: string
}

interface Props {
  isOpen: boolean
  isEditing?: boolean
  initialData?: BookingFormData
  submitting?: boolean
  error?: string
}

const props = withDefaults(defineProps<Props>(), {
  isEditing: false,
  submitting: false,
  error: '',
})

const emit = defineEmits<{
  close: []
  submit: [data: BookingFormData]
}>()

const formData = reactive<BookingFormData>({
  date: '',
  start_time: '',
  end_time: '',
})

const formError = ref('')

watch(
  () => props.error,
  (newError) => {
    formError.value = newError || ''
  },
)

watch(
  () => props.initialData,
  (newData) => {
    if (newData) {
      formData.date = newData.date
      formData.start_time = newData.start_time
      formData.end_time = newData.end_time
    }
  },
  { deep: true },
)

watch(
  () => props.isOpen,
  (isOpen) => {
    if (!isOpen) {
      formError.value = ''
      formData.date = ''
      formData.start_time = ''
      formData.end_time = ''
    }
  },
)

function handleSubmit() {
  emit('submit', { ...formData })
}

function closeModal() {
  emit('close')
}
</script>
