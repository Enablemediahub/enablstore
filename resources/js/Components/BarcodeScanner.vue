<!--
Props:
- open: whether the camera scanner panel is visible

Emits:
- close: emitted when the scanner panel closes
- detected: emitted with a barcode or SKU value

Slots:
- none
-->
<script setup lang="ts">
import { BrowserMultiFormatReader, type IScannerControls } from '@zxing/browser';
import { Camera, CameraOff, ScanLine, X } from '@lucide/vue';
import { nextTick, onMounted, onUnmounted, ref, watch } from 'vue';

const props = withDefaults(
    defineProps<{
        open?: boolean;
    }>(),
    {
        open: false,
    },
);

const emit = defineEmits<{
    close: [];
    detected: [code: string];
}>();

const scannerDialog = ref<HTMLDialogElement | null>(null);
const video = ref<HTMLVideoElement | null>(null);
const cameraError = ref('');
const cameraActive = ref(false);
let scannerControls: IScannerControls | null = null;
let hidBuffer = '';
let hidTimer: number | undefined;

const stopCamera = (): void => {
    scannerControls?.stop();
    scannerControls = null;

    if (video.value) {
        video.value.srcObject = null;
    }
    cameraActive.value = false;
};

const startCamera = async (): Promise<void> => {
    cameraError.value = '';
    stopCamera();

    if (!navigator.mediaDevices?.getUserMedia) {
        cameraError.value = 'Camera scanning is not available in this browser. Use a USB scanner instead.';
        return;
    }

    try {
        await nextTick();
        if (!video.value) return;

        const reader = new BrowserMultiFormatReader();
        scannerControls = await reader.decodeFromConstraints(
            {
                video: {
                    facingMode: { ideal: 'environment' },
                    width: { ideal: 1280 },
                    height: { ideal: 720 },
                },
                audio: false,
            },
            video.value,
            (result) => {
                const code = result?.getText().trim();
                if (!code) return;

                emit('detected', code);
                stopCamera();
            },
        );
        cameraActive.value = true;
    } catch {
        cameraError.value =
            'Camera access was unavailable. Allow camera access, then try again, or use a USB scanner.';
        stopCamera();
    }
};

const handleHidKeydown = (event: KeyboardEvent): void => {
    if (!props.open) return;

    if (event.key === 'Enter') {
        if (hidBuffer.length >= 3) {
            emit('detected', hidBuffer);
        }
        hidBuffer = '';
        return;
    }

    if (event.key.length !== 1) {
        return;
    }

    hidBuffer += event.key;

    if (hidTimer !== undefined) {
        window.clearTimeout(hidTimer);
    }

    hidTimer = window.setTimeout(() => {
        hidBuffer = '';
    }, 80);
};

watch(
    () => props.open,
    async (isOpen) => {
        if (!isOpen) {
            stopCamera();
            hidBuffer = '';
            if (scannerDialog.value?.open) scannerDialog.value.close();
            return;
        }

        await nextTick();
        if (!scannerDialog.value?.open) scannerDialog.value?.showModal();
        await startCamera();
    },
);

onMounted(() => {
    window.addEventListener('keydown', handleHidKeydown);
});

onUnmounted(() => {
    stopCamera();
    if (scannerDialog.value?.open) scannerDialog.value.close();
    window.removeEventListener('keydown', handleHidKeydown);
});
</script>

<template>
    <dialog
        ref="scannerDialog"
        class="m-0 h-dvh w-screen max-w-none bg-transparent p-0 backdrop:bg-neutral-900/60"
        aria-labelledby="scanner-title"
        @cancel.prevent="emit('close')"
    >
        <div class="fixed inset-0 flex items-center justify-center p-4">
        <section
            class="w-full max-w-lg rounded-lg bg-white p-5 shadow-lg"
            role="dialog"
            aria-modal="true"
            aria-labelledby="scanner-title"
        >
            <div class="flex items-center justify-between">
                <div>
                    <p
                        class="text-primary-700 text-xs font-semibold tracking-wide uppercase"
                    >
                        Product lookup
                    </p>
                    <h2
                        id="scanner-title"
                        class="mt-1 text-xl font-semibold text-neutral-900"
                    >
                        Scan a barcode
                    </h2>
                </div>
                <button
                    type="button"
                    class="rounded-md p-2 text-neutral-500 hover:bg-neutral-100"
                    aria-label="Close barcode scanner"
                    @click="emit('close')"
                >
                    <X :size="20" aria-hidden="true" />
                </button>
            </div>

            <div class="mt-5 overflow-hidden rounded-md bg-neutral-900">
                <video
                    ref="video"
                    class="aspect-video w-full object-cover"
                    muted
                    playsinline
                    aria-label="Barcode camera preview"
                />
            </div>
            <p v-if="cameraError" class="text-danger mt-3 text-sm" role="alert">
                {{ cameraError }}
            </p>
            <p v-else class="mt-3 text-sm text-neutral-500">
                Point the camera at a barcode. A connected USB barcode scanner also works while this panel is open.
            </p>

            <div class="mt-5 flex flex-wrap justify-end gap-3">
                <button
                    v-if="cameraActive"
                    type="button"
                    class="inline-flex items-center gap-2 rounded-md border border-neutral-300 px-4 py-2 text-sm font-semibold text-neutral-700 hover:bg-neutral-50"
                    @click="stopCamera"
                >
                    <CameraOff :size="18" aria-hidden="true" />
                    Stop camera
                </button>
                <button
                    v-else
                    type="button"
                    class="bg-primary-600 hover:bg-primary-700 inline-flex items-center gap-2 rounded-md px-4 py-2 text-sm font-semibold text-white"
                    @click="startCamera"
                >
                    <Camera :size="18" aria-hidden="true" />
                    Start camera
                </button>
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-md border border-neutral-300 px-4 py-2 text-sm font-semibold text-neutral-700 hover:bg-neutral-50"
                    @click="emit('close')"
                >
                    <ScanLine :size="18" aria-hidden="true" />
                    Done
                </button>
            </div>
        </section>
        </div>
    </dialog>
</template>
