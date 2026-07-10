<template>
  <div class="relative w-full select-none" ref="selectRef">
    <!-- Trigger Button -->
    <div
      @click="toggleDropdown"
      :class="[
        'w-full p-2.5 bg-slate-900/40 border border-white/5 rounded-xl text-slate-200 text-sm flex items-center justify-between cursor-pointer transition-all duration-300',
        isOpen ? 'border-amber-500 ring-1 ring-amber-500/30 bg-slate-900/60' : 'hover:border-white/10 hover:bg-slate-900/50',
        disabled && 'opacity-50 cursor-not-allowed pointer-events-none'
      ]"
    >
      <span :class="[!selectedOption && 'text-slate-500']">
        {{ selectedOption ? selectedOption.label : placeholder }}
      </span>
      <i
        :class="[
          'fas fa-chevron-down text-xs text-slate-400 transition-transform duration-300',
          isOpen && 'transform rotate-180 text-amber-500'
        ]"
      ></i>
    </div>

    <!-- Dropdown Options List -->
    <transition
      enter-active-class="transition duration-150 ease-out"
      enter-from-class="transform scale-95 opacity-0 -translate-y-2"
      enter-to-class="transform scale-100 opacity-100 translate-y-0"
      leave-active-class="transition duration-100 ease-in"
      leave-from-class="transform scale-100 opacity-100 translate-y-0"
      leave-to-class="transform scale-95 opacity-0 -translate-y-2"
    >
      <div
        v-if="isOpen"
        class="absolute left-0 right-0 mt-2 z-50 max-h-60 overflow-y-auto bg-slate-950/95 backdrop-blur-xl border border-white/10 rounded-xl shadow-[0_10px_30px_rgba(0,0,0,0.5)] scrollbar-thin"
      >
        <div class="py-1.5">
          <div
            v-for="opt in normalizedOptions"
            :key="opt.value"
            @click="selectOption(opt)"
            :class="[
              'px-4 py-2.5 text-sm text-slate-300 cursor-pointer transition-all duration-150 flex items-center justify-between',
              modelValue === opt.value
                ? 'bg-amber-500/20 text-amber-400 font-bold border-l-4 border-amber-500'
                : 'hover:bg-slate-900 hover:text-slate-100'
            ]"
          >
            <span>{{ opt.label }}</span>
            <i v-if="modelValue === opt.value" class="fas fa-check text-xs text-amber-400"></i>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script>
import { ref, computed, onMounted, onUnmounted } from 'vue';

export default {
  name: 'CustomSelect',
  props: {
    modelValue: {
      type: [String, Number, Boolean],
      default: ''
    },
    options: {
      type: Array,
      default: () => []
    },
    placeholder: {
      type: String,
      default: 'Selecciona una opción'
    },
    disabled: {
      type: Boolean,
      default: false
    }
  },
  emits: ['update:modelValue', 'change'],
  setup(props, { emit }) {
    const isOpen = ref(false);
    const selectRef = ref(null);

    const normalizedOptions = computed(() => {
      return props.options.map(opt => {
        if (typeof opt === 'object' && opt !== null) {
          // Si el objeto tiene una estructura personalizada
          let val = opt.value !== undefined ? opt.value : opt.id;
          let lbl = opt.label !== undefined ? opt.label : opt.nombre || opt.modelo || String(opt);
          return { value: val, label: lbl };
        }
        return { value: opt, label: opt };
      });
    });

    const selectedOption = computed(() => {
      return normalizedOptions.value.find(opt => opt.value === props.modelValue);
    });

    const toggleDropdown = () => {
      if (!props.disabled) {
        isOpen.value = !isOpen.value;
      }
    };

    const selectOption = (opt) => {
      emit('update:modelValue', opt.value);
      emit('change', opt.value);
      isOpen.value = false;
    };

    const handleClickOutside = (e) => {
      if (selectRef.value && !selectRef.value.contains(e.target)) {
        isOpen.value = false;
      }
    };

    onMounted(() => {
      window.addEventListener('click', handleClickOutside);
    });

    onUnmounted(() => {
      window.removeEventListener('click', handleClickOutside);
    });

    return {
      isOpen,
      selectRef,
      normalizedOptions,
      selectedOption,
      toggleDropdown,
      selectOption
    };
  }
};
</script>

<style scoped>
/* Custom Scrollbar for premium aesthetics */
.scrollbar-thin::-webkit-scrollbar {
  width: 6px;
}
.scrollbar-thin::-webkit-scrollbar-track {
  background: transparent;
}
.scrollbar-thin::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.1);
  border-radius: 9999px;
}
.scrollbar-thin::-webkit-scrollbar-thumb:hover {
  background: rgba(255, 255, 255, 0.2);
}
</style>
