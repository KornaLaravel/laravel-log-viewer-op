<template>
  <table class="table-fixed min-w-full max-w-full border-separate" style="border-spacing: 0">
    <thead class="bg-gray-50">
    <tr>
      <th class="hidden lg:table-cell"><span class="sr-only">Expand/Collapse</span></th>
      <th v-for="(column) in logViewerStore.columns" scope="col">
        <div>{{ column.label }}</div>
      </th>
      <th scope="col" class="hidden lg:table-cell"><span class="sr-only">Log index</span></th>
    </tr>
    </thead>

    <template v-if="logViewerStore.logs && logViewerStore.logs.length > 0">
      <tbody v-for="(log, index) in logViewerStore.logs" :key="index"
             :class="[index === 0 ? 'first' : '', 'log-group']"
             :id="`tbody-${index}`" :data-index="index"
      >
      <tr @click="logViewerStore.toggle(index)"
          :class="['log-item group', log.level_class, logViewerStore.isOpen(index) ? 'active' : '', logViewerStore.shouldBeSticky(index) ? 'sticky z-2' : '']"
          :style="{ top: logViewerStore.stackTops[index] || 0 }"
      >
        <td class="hidden lg:table-cell">
          <div class="flex items-center lg:pl-2">
            <button :aria-expanded="logViewerStore.isOpen(index)"
                    @keydown="handleLogToggleKeyboardNavigation"
                    class="log-level-icon opacity-75 w-5 h-5 hidden lg:block group focus:opacity-100 focus:outline-none focus:ring-2 focus:ring-brand-500 rounded-md"
            >
              <span class="sr-only" v-if="!logViewerStore.isOpen(index)">Expand log entry</span>
              <span class="sr-only" v-if="logViewerStore.isOpen(index)">Collapse log entry</span>
              <span class="w-full h-full group-hover:hidden group-focus:hidden">

              </span>
              <span class="w-full h-full inline-block">
                <ChevronRightIcon :class="[logViewerStore.isOpen(index) ? 'rotate-90' : '', 'transition duration-100']" />
              </span>
            </button>
          </div>
        </td>

        <template v-for="(column, colIndex) in logViewerStore.columns">
          <!-- Severity -->
          <td :key="`${log.index}-column-${colIndex}`" v-if="column.data_path === 'level'" class="log-level truncate">
            <span class="font-semibold">{{ log.level_name }}</span>
          </td>
          <!-- /Severity -->

          <!-- Datetime -->
          <td :key="`${log.index}-column-${colIndex}`" v-else-if="column.data_path === 'datetime'" class="whitespace-nowrap text-gray-900 dark:text-gray-200">
            <span class="hidden lg:inline" v-html="highlightSearchResult(log.datetime, searchStore.query)"></span>
            <span class="lg:hidden">{{ log.time }}</span>
          </td>
          <!-- /Datetime -->

          <!-- Message -->
          <td :key="`${log.index}-column-${colIndex}`" v-else-if="column.data_path === 'message'" class="max-w-[1px] w-full truncate text-gray-500 dark:text-gray-300 dark:opacity-90">
            <span v-html="highlightSearchResult(`${log.message}`, searchStore.query)"></span>
          </td>
          <!-- /Message -->

          <td :key="`${log.index}-column-${colIndex}`" v-else class="max-w-[1px] text-gray-500 dark:text-gray-300 dark:opacity-90">
            <span v-html="highlightSearchResult(log[column.data_path], searchStore.query)"></span>
          </td>
        </template>

        <td class="whitespace-nowrap text-gray-500 dark:text-gray-300 dark:opacity-90 text-xs hidden lg:table-cell">
          <LogCopyButton :log="log" class="pr-2 large-screen" />
        </td>
      </tr>
      <tr v-show="logViewerStore.isOpen(index)">
        <td colspan="6">
          <pre class="log-stack" v-html="highlightSearchResult(log.full_text, searchStore.query)"></pre>
          <p class="mx-2 lg:mx-8 pt-2 border-t font-semibold text-gray-700 dark:text-gray-400 text-xs lg:text-sm">Context:</p>
          <pre class="log-stack" v-html="highlightSearchResult(JSON.stringify(log.context, null, 2), searchStore.query)"></pre>
        </td>
      </tr>
      </tbody>
    </template>

    <tbody v-else class="log-group">
    <tr>
      <td colspan="6">
        <div class="bg-white text-gray-600 dark:bg-gray-800 dark:text-gray-200 p-12">
          <div class="text-center font-semibold">No results</div>
          <div class="text-center mt-6">
            <button v-if="searchStore.query?.length > 0"
              class="px-3 py-2 border dark:border-gray-700 text-gray-800 dark:text-gray-200 hover:border-brand-600 dark:hover:border-brand-700 rounded-md"
              @click="clearQuery">Clear search query
            </button>
            <button v-if="searchStore.query?.length > 0 && fileStore.selectedFile"
              class="px-3 ml-3 py-2 border dark:border-gray-700 text-gray-800 dark:text-gray-200 hover:border-brand-600 dark:hover:border-brand-700 rounded-md"
              @click.prevent="clearSelectedFile">Search all files
            </button>
            <button
              v-if="severityStore.levelsFound.length > 0 && severityStore.levelsSelected.length === 0"
              class="px-3 ml-3 py-2 border dark:border-gray-700 text-gray-800 dark:text-gray-200 hover:border-brand-600 dark:hover:border-brand-700 rounded-md"
              @click="severityStore.selectAllLevels">Select all severities
            </button>
          </div>
        </div>
      </td>
    </tr>
    </tbody>
  </table>
</template>

<script setup>
import { highlightSearchResult } from '../helpers.js';
import { ChevronRightIcon } from '@heroicons/vue/24/solid';
import { useLogViewerStore } from '../stores/logViewer.js';
import { useSearchStore } from '../stores/search.js';
import { useFileStore } from '../stores/files.js';
import LogCopyButton from './LogCopyButton.vue';
import { handleLogToggleKeyboardNavigation } from '../keyboardNavigation';
import { useSeverityStore } from '../stores/severity.js';

const fileStore = useFileStore();
const logViewerStore = useLogViewerStore();
const searchStore = useSearchStore();
const severityStore = useSeverityStore();
const emit = defineEmits(['clearSelectedFile', 'clearQuery']);

const clearSelectedFile = () => {
  emit('clearSelectedFile');
}

const clearQuery = () => {
  emit('clearQuery');
}
</script>