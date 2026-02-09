<template>
  <!-- Two-Factor Challenge -->
  <form
    v-if="authStore.requiresTwoFactor"
    id="twoFactorForm"
    class="mt-12 text-left"
    @submit.prevent="onTwoFactorSubmit"
  >
    <div class="mb-4 text-sm text-gray-600">
      {{ useRecoveryCode
        ? 'Please enter one of your recovery codes.'
        : 'Please enter the authentication code from your authenticator app.'
      }}
    </div>

    <BaseInputGroup
      v-if="!useRecoveryCode"
      :error="twoFactorError"
      label="Authentication Code"
      class="mb-4"
      required
    >
      <BaseInput
        v-model="twoFactorCode"
        focus
        type="text"
        inputmode="numeric"
        autocomplete="one-time-code"
        name="code"
        placeholder="000000"
        maxlength="6"
      />
    </BaseInputGroup>

    <BaseInputGroup
      v-else
      :error="twoFactorError"
      label="Recovery Code"
      class="mb-4"
      required
    >
      <BaseInput
        v-model="recoveryCode"
        focus
        type="text"
        name="recovery_code"
      />
    </BaseInputGroup>

    <div class="mt-3 mb-6">
      <a
        href="#"
        class="text-sm text-primary-400 hover:text-gray-700"
        @click.prevent="useRecoveryCode = !useRecoveryCode"
      >
        {{ useRecoveryCode ? 'Use authentication code' : 'Use a recovery code' }}
      </a>
    </div>

    <BaseButton :loading="isLoading" type="submit">
      Verify
    </BaseButton>
  </form>

  <!-- Standard Login -->
  <form v-else id="loginForm" class="mt-12 text-left" @submit.prevent="onSubmit">
    <BaseInputGroup
      :error="v$.email.$error && v$.email.$errors[0].$message"
      :label="$t('login.email')"
      class="mb-4"
      required
    >
      <BaseInput
        v-model="authStore.loginData.email"
        :invalid="v$.email.$error"
        focus
        type="email"
        name="email"
        @input="v$.email.$touch()"
      />
    </BaseInputGroup>

    <BaseInputGroup
      :error="v$.password.$error && v$.password.$errors[0].$message"
      :label="$t('login.password')"
      class="mb-4"
      required
    >
      <BaseInput
        v-model="authStore.loginData.password"
        :invalid="v$.password.$error"
        :type="getInputType"
        name="password"
        @input="v$.password.$touch()"
      >
        <template #right>
          <BaseIcon
            :name="isShowPassword ? 'EyeIcon' : 'EyeSlashIcon'"
            class="mr-1 text-gray-500 cursor-pointer"
            @click="isShowPassword = !isShowPassword"
          />
        </template>
      </BaseInput>
    </BaseInputGroup>

    <div class="mt-5 mb-8">
      <div class="mb-4">
        <router-link
          to="forgot-password"
          class="text-sm text-primary-400 hover:text-gray-700"
        >
          {{ $t('login.forgot_password') }}
        </router-link>
      </div>
    </div>
    <BaseButton :loading="isLoading" type="submit">
      {{ $t('login.login') }}
    </BaseButton>
  </form>
</template>

<script setup>
import axios from 'axios'
import { ref, computed, onMounted } from 'vue'
import { useNotificationStore } from '@/scripts/stores/notification'
import { useRouter } from 'vue-router'
import { required, email, helpers } from '@vuelidate/validators'
import { useVuelidate } from '@vuelidate/core'
import { useI18n } from 'vue-i18n'
import { useAuthStore } from '@/scripts/admin/stores/auth'
import { handleError } from '@/scripts/helpers/error-handling'

const notificationStore = useNotificationStore()
const authStore = useAuthStore()
const { t } = useI18n()
const router = useRouter()
const isLoading = ref(false)
let isShowPassword = ref(false)

const twoFactorCode = ref('')
const recoveryCode = ref('')
const useRecoveryCode = ref(false)
const twoFactorError = ref('')

const rules = {
  email: {
    required: helpers.withMessage(t('validation.required'), required),
    email: helpers.withMessage(t('validation.email_incorrect'), email),
  },
  password: {
    required: helpers.withMessage(t('validation.required'), required),
  },
}

const v$ = useVuelidate(
  rules,
  computed(() => authStore.loginData)
)

const getInputType = computed(() => {
  if (isShowPassword.value) {
    return 'text'
  }
  return 'password'
})

async function onSubmit() {
  axios.defaults.withCredentials = true

  v$.value.$touch()

  if (v$.value.$invalid) {
    return true
  }

  isLoading.value = true

  try {
    isLoading.value = true
    await authStore.login(authStore.loginData)

    if (authStore.requiresTwoFactor) {
      isLoading.value = false
      return
    }

    router.push('/admin/dashboard')

    notificationStore.showNotification({
      type: 'success',
      message: 'Logged in successfully.',
    })
  } catch (error) {
    isLoading.value = false
  }
}

async function onTwoFactorSubmit() {
  twoFactorError.value = ''
  isLoading.value = true

  const data = useRecoveryCode.value
    ? { recovery_code: recoveryCode.value }
    : { code: twoFactorCode.value }

  try {
    await authStore.verifyTwoFactor(data)

    router.push('/admin/dashboard')

    notificationStore.showNotification({
      type: 'success',
      message: 'Logged in successfully.',
    })
  } catch (error) {
    twoFactorError.value = 'The provided code is invalid.'
    isLoading.value = false
  }
}

// Pre-fill demo credentials if in demo environment
onMounted(() => {
  if (window.demo_mode) {
    authStore.loginData.email = 'demo@invoiceshelf.com'
    authStore.loginData.password = 'demo'
  }
})
</script>
