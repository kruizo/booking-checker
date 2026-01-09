<template>
  <div class="p-8">
    <h1 class="text-2xl font-bold mb-6">Users</h1>
    <div class="mb-4 flex gap-4">
      <input
        v-model="search"
        @keyup.enter="doSearch(1)"
        type="text"
        placeholder="Search by name/email..."
        class="border rounded px-4 py-2 w-64"
      />
      <button @click="doSearch(1)" class="bg-indigo-600 text-white px-4 py-2 rounded">
        Search
      </button>
    </div>
    <div class="bg-white rounded-xl shadow p-4">
      <table v-if="users.length" class="w-full text-left">
        <thead>
          <tr class="bg-indigo-50">
            <th class="px-4 py-2">ID</th>
            <th class="px-4 py-2">Name</th>
            <th class="px-4 py-2">Email</th>
            <th class="px-4 py-2">Admin</th>
            <th class="px-4 py-2">Created</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="u in users" :key="u.id" class="border-b">
            <td class="px-4 py-2">{{ u.id }}</td>
            <td class="px-4 py-2">{{ u.name }}</td>
            <td class="px-4 py-2">{{ u.email }}</td>
            <td class="px-4 py-2">{{ u.is_admin ? 'Yes' : 'No' }}</td>
            <td class="px-4 py-2 text-xs text-gray-500">{{ formatDate(u.created_at) }}</td>
          </tr>
        </tbody>
      </table>
      <div v-else class="text-gray-400 text-center py-12">No users found.</div>
      <div class="flex justify-end gap-2 mt-4">
        <button
          v-if="pagination.has_prev_page"
          @click="doSearch(pagination.current_page - 1)"
          class="px-4 py-2 bg-indigo-100 text-indigo-700 rounded"
        >
          Prev
        </button>
        <span class="px-2 py-2 text-gray-600"
          >Page {{ pagination.current_page }} / {{ pagination.total_pages }}</span
        >
        <button
          v-if="pagination.has_next_page"
          @click="doSearch(pagination.current_page + 1)"
          class="px-4 py-2 bg-indigo-100 text-indigo-700 rounded"
        >
          Next
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useUser } from '@/composables/useUser'

const search = ref('')
const { users, pagination, loading, error, formatDate, fetchUsers } = useUser()

function doSearch(page = 1) {
  fetchUsers(page, search.value)
}

onMounted(() => {
  fetchUsers(1, '')
})
</script>
