<template>
  <div class="mb-3">
    <label v-if="label" class="form-label" :for="labelId">
      {{ label }}
      <span v-if="required" class="required-mark">*</span>
    </label>
    <div class="input-group">
      <input
        :id="labelId"
        :ref="inputRef"
        :type="showPassword ? 'text' : 'password'"
        class="form-control"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
        required
      />
      <button
        class="btn btn-outline-secondary ms-1"
        type="button"
        @click="showPassword = !showPassword"
      >
        <i :class="showPassword ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
      </button>
      <div class="invalid-feedback">
        {{ passwordErrorMessage || "Password is required." }}
      </div>
      <div v-if="serverErrors?.password" class="invalid-feedback d-block">
        {{ serverErrors?.password[0] }}
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    modelValue: { type: String },
    label: { type: String, default: "Password" },
    required: { type: Boolean, default: false },
    inputRef: { type: String, default: "" },
    labelId: { type: String, default: "" },
    passwordErrorMessage: { type: String, default: "" },
    serverErrors: { type: Object, default: () => ({}) },
  },
  data() {
    return {
      showPassword: false,
    };
  },
};
</script>

<style scoped>
.required-mark {
  color: #d14343;
  font-weight: 700;
  margin-left: 0.2rem;
}
</style>
