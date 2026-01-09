<template>
  <div
    v-if="isOpen"
    class="fixed inset-0 bg-black/30 backdrop-blur-sm flex items-center justify-center z-50"
    @click.self="closeModal"
  >
    <div class="bg-white rounded-xl shadow-2xl p-8 max-w-md w-full mx-4">
      <h3 class="text-2xl font-bold text-gray-900 mb-4">Confirm Delete</h3>
      <p class="text-gray-600 mb-6">
        Are you sure you want to delete this booking? This action cannot be undone.
      </p>
      <div class="flex justify-end gap-3">
        <button
          @click="closeModal"
          class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-medium"
        >
          Cancel
        </button>
        <button
          @click="handleConfirm"
          class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50 font-medium"
          :disabled="deleting"
        >
          {{ deleting ? 'Deleting...' : 'Delete' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
interface Props {
  isOpen: boolean
  deleting?: boolean
}

withDefaults(defineProps<Props>(), {
  deleting: false,
})

const emit = defineEmits<{
  close: []
  confirm: []
}>()

function closeModal() {
  emit('close')
}

function handleConfirm() {
  emit('confirm')
}
</script>
