<template>
  <form class="relative" @submit.prevent="updateUserData">
    <BaseSettingCard
      :title="$t('settings.account_settings.account_settings')"
      :description="$t('settings.account_settings.section_description')"
    >
      <BaseInputGrid>
        <BaseInputGroup
          :label="$t('settings.account_settings.profile_picture')"
        >
          <BaseFileUploader
            v-model="imgFiles"
            :avatar="true"
            accept="image/*"
            @change="onFileInputChange"
            @remove="onFileInputRemove"
          />
        </BaseInputGroup>

        <!-- Empty Column -->
        <span></span>

        <BaseInputGroup
          :label="$t('settings.account_settings.name')"
          :error="v$.name.$error && v$.name.$errors[0].$message"
          required
        >
          <BaseInput
            v-model="userForm.name"
            :invalid="v$.name.$error"
            @input="v$.name.$touch()"
          />
        </BaseInputGroup>

        <BaseInputGroup
          :label="$t('settings.account_settings.email')"
          :error="v$.email.$error && v$.email.$errors[0].$message"
          required
        >
          <BaseInput
            v-model="userForm.email"
            :invalid="v$.email.$error"
            @input="v$.email.$touch()"
          />
        </BaseInputGroup>

        <BaseInputGroup
          :error="v$.password.$error && v$.password.$errors[0].$message"
          :label="$t('settings.account_settings.password')"
        >
          <BaseInput
            v-model="userForm.password"
            type="password"
            @input="v$.password.$touch()"
          />
        </BaseInputGroup>

        <BaseInputGroup
          :label="$t('settings.account_settings.confirm_password')"
          :error="
            v$.confirm_password.$error &&
            v$.confirm_password.$errors[0].$message
          "
        >
          <BaseInput
            v-model="userForm.confirm_password"
            type="password"
            @input="v$.confirm_password.$touch()"
          />
        </BaseInputGroup>

        <BaseInputGroup :label="$t('settings.language')">
          <BaseMultiselect
            v-model="userForm.language"
            :options="globalStore.config.languages"
            label="name"
            value-prop="code"
            track-by="name"
            :searchable="true"
            open-direction="top"
          />
        </BaseInputGroup>
      </BaseInputGrid>

      <BaseButton :loading="isSaving" :disabled="isSaving" class="mt-6">
        <template #left="slotProps">
          <BaseIcon
            v-if="!isSaving"
            name="ArrowDownOnSquareIcon"
            :class="slotProps.class"
          ></BaseIcon>
        </template>
        {{ $t('settings.company_info.save') }}
      </BaseButton>
    </BaseSettingCard>
  </form>

  <!-- Two-Factor Authentication Section -->
  <div class="mt-6">
    <BaseSettingCard
      title="Two-Factor Authentication"
      description="Add additional security to your account using two-factor authentication."
    >
      <!-- 2FA is enabled -->
      <div v-if="userStore.currentUser && userStore.currentUser.two_factor_enabled">
        <p class="text-sm text-green-600 mb-4">
          Two-factor authentication is enabled.
        </p>
        <div>
          <BaseInputGroup
            label="Password"
            :error="disableError"
            class="mb-4 max-w-md"
          >
            <BaseInput
              v-model="disablePassword"
              type="password"
              placeholder="Enter your password to disable 2FA"
            />
          </BaseInputGroup>
          <BaseButton
            variant="danger"
            :loading="isDisabling"
            @click="disableTwoFactor"
          >
            Disable Two-Factor Authentication
          </BaseButton>
        </div>
      </div>

      <!-- 2FA setup flow -->
      <div v-else>
        <!-- Step 1: Not started -->
        <div v-if="setupStep === 'idle'">
          <p class="text-sm text-gray-600 mb-4">
            When two-factor authentication is enabled, you will be prompted for a secure, random token during authentication.
            You can use your phone's authenticator app (like Apple Passwords, Google Authenticator, or Authy).
          </p>
          <BaseButton :loading="isEnabling" @click="enableTwoFactor">
            Enable Two-Factor Authentication
          </BaseButton>
        </div>

        <!-- Step 2: Show QR Code -->
        <div v-if="setupStep === 'qr'">
          <p class="text-sm text-gray-600 mb-4">
            Scan this QR code with your authenticator app, then enter the 6-digit code below to confirm.
          </p>
          <div class="flex flex-col items-center mb-6">
            <div
              class="p-4 bg-white border rounded-lg mb-4"
              v-html="qrCodeSvg"
            ></div>
            <p class="text-xs text-gray-500 text-center">
              Or enter this code manually:
              <code class="block mt-1 text-sm font-mono bg-gray-100 px-2 py-1 rounded select-all">{{ secret }}</code>
            </p>
          </div>
          <BaseInputGroup
            label="Verification Code"
            :error="confirmError"
            class="mb-4 max-w-md"
          >
            <BaseInput
              v-model="confirmCode"
              type="text"
              inputmode="numeric"
              autocomplete="one-time-code"
              placeholder="000000"
              maxlength="6"
            />
          </BaseInputGroup>
          <BaseButton :loading="isConfirming" @click="confirmTwoFactor">
            Confirm
          </BaseButton>
        </div>

        <!-- Step 3: Show Recovery Codes -->
        <div v-if="setupStep === 'recovery'">
          <p class="text-sm text-green-600 mb-2">
            Two-factor authentication has been enabled!
          </p>
          <p class="text-sm text-gray-600 mb-4">
            Store these recovery codes in a secure place. They can be used to access your account if you lose your authenticator device.
            Each code can only be used once.
          </p>
          <div class="bg-gray-100 rounded-lg p-4 mb-4 max-w-md">
            <code
              v-for="code in recoveryCodes"
              :key="code"
              class="block text-sm font-mono py-0.5"
            >
              {{ code }}
            </code>
          </div>
          <BaseButton @click="finishSetup">
            Done
          </BaseButton>
        </div>
      </div>
    </BaseSettingCard>
  </div>
</template>

<script setup>
import axios from 'axios'
import { ref, computed, reactive } from 'vue'
import { useGlobalStore } from '@/scripts/admin/stores/global'
import { useUserStore } from '@/scripts/admin/stores/user'
import { useNotificationStore } from '@/scripts/stores/notification'
import { useI18n } from 'vue-i18n'
import {
  helpers,
  sameAs,
  email,
  required,
  minLength,
} from '@vuelidate/validators'
import { useVuelidate } from '@vuelidate/core'
import { useCompanyStore } from '@/scripts/admin/stores/company'
import { handleError } from '@/scripts/helpers/error-handling'

const userStore = useUserStore()
const globalStore = useGlobalStore()
const companyStore = useCompanyStore()
const notificationStore = useNotificationStore()
const { t } = useI18n()

let isSaving = ref(false)
let avatarFileBlob = ref(null)
let imgFiles = ref([])
const isAdminAvatarRemoved = ref(false)

// 2FA state
const setupStep = ref('idle')
const qrCodeSvg = ref('')
const secret = ref('')
const confirmCode = ref('')
const confirmError = ref('')
const recoveryCodes = ref([])
const isEnabling = ref(false)
const isConfirming = ref(false)
const isDisabling = ref(false)
const disablePassword = ref('')
const disableError = ref('')

if (userStore.currentUser.avatar) {
  imgFiles.value.push({
    image: userStore.currentUser.avatar,
  })
}

const rules = computed(() => {
  return {
    name: {
      required: helpers.withMessage(t('validation.required'), required),
    },
    email: {
      required: helpers.withMessage(t('validation.required'), required),
      email: helpers.withMessage(t('validation.email_incorrect'), email),
    },
    password: {
      minLength: helpers.withMessage(
        t('validation.password_length', { count: 8 }),
        minLength(8)
      ),
    },
    confirm_password: {
      sameAsPassword: helpers.withMessage(
        t('validation.password_incorrect'),
        sameAs(userForm.password)
      ),
    },
  }
})

const userForm = reactive({
  name: userStore.currentUser.name,
  email: userStore.currentUser.email,
  language:
    userStore.currentUserSettings.language ||
    companyStore.selectedCompanySettings.language,
  password: '',
  confirm_password: '',
})

const v$ = useVuelidate(
  rules,
  computed(() => userForm)
)

function onFileInputChange(fileName, file) {
  avatarFileBlob.value = file
}

function onFileInputRemove() {
  avatarFileBlob.value = null
  isAdminAvatarRemoved.value = true
}

async function updateUserData() {
  v$.value.$touch()

  if (v$.value.$invalid) {
    return true
  }

  isSaving.value = true

  let data = {
    name: userForm.name,
    email: userForm.email,
  }

  try {
    if (
      userForm.password != null &&
      userForm.password !== undefined &&
      userForm.password !== ''
    ) {
      data = { ...data, password: userForm.password }
    }
    // Update Language if changed

    if (userStore.currentUserSettings.language !== userForm.language) {
      // Load the new language dynamically before updating settings
      await window.loadLanguage(userForm.language)

      await userStore.updateUserSettings({
        settings: {
          language: userForm.language,
        },
      })
    }

    let response = await userStore.updateCurrentUser(data)

    if (response.data.data) {
      isSaving.value = false

      if (avatarFileBlob.value || isAdminAvatarRemoved.value) {
        let avatarData = new FormData()

        if (avatarFileBlob.value) {
          avatarData.append('admin_avatar', avatarFileBlob.value)
        }
        avatarData.append('is_admin_avatar_removed', isAdminAvatarRemoved.value)

        await userStore.uploadAvatar(avatarData)
        avatarFileBlob.value = null
        isAdminAvatarRemoved.value = false
      }

      userForm.password = ''
      userForm.confirm_password = ''
    }
  } catch (error) {
    isSaving.value = false
    return true
  }
}

// 2FA methods
async function enableTwoFactor() {
  isEnabling.value = true
  try {
    const response = await axios.post('/api/v1/me/two-factor/enable')
    qrCodeSvg.value = response.data.qr_code_svg
    secret.value = response.data.secret
    setupStep.value = 'qr'
  } catch (err) {
    handleError(err)
  }
  isEnabling.value = false
}

async function confirmTwoFactor() {
  confirmError.value = ''
  isConfirming.value = true
  try {
    const response = await axios.post('/api/v1/me/two-factor/confirm', {
      code: confirmCode.value,
    })
    recoveryCodes.value = response.data.recovery_codes
    setupStep.value = 'recovery'
    await userStore.fetchCurrentUser()
  } catch (err) {
    confirmError.value = 'The provided code is invalid.'
  }
  isConfirming.value = false
}

async function disableTwoFactor() {
  disableError.value = ''
  isDisabling.value = true
  try {
    await axios.post('/api/v1/me/two-factor/disable', {
      password: disablePassword.value,
    })
    disablePassword.value = ''
    await userStore.fetchCurrentUser()
    notificationStore.showNotification({
      type: 'success',
      message: 'Two-factor authentication has been disabled.',
    })
  } catch (err) {
    disableError.value = 'The provided password is incorrect.'
  }
  isDisabling.value = false
}

function finishSetup() {
  setupStep.value = 'idle'
  confirmCode.value = ''
  notificationStore.showNotification({
    type: 'success',
    message: 'Two-factor authentication has been enabled.',
  })
}
</script>
