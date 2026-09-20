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
import { Camera, CameraOff, ScanLine, X } from '@lucide/vue';
import { onMounted, onUnmounted, ref, watch } from 'vue';

type BarcodeDetectorLike = {
    detect: (source: HTMLVideoElement) => Promise<Array<{ rawValue: string }>>;
};

type BarcodeDetectorConstructor = new (options?: {
    formats?: string[];
}) => BarcodeDetectorLike;

declare global {
    interface Window {
        BarcodeDetector?: BarcodeDetectorConstructor;
    }
}

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

const video = ref<HTMLVideoElement | null>(null);
const stream = ref<MediaStream | null>(null);
const cameraError = ref('');
const cameraActive = ref(false);
let scanTimer: number | undefined;
let hidBuffer = '';
let hidTimer: number | undefined;

const stopCamera = (): void => {
    if (scanTimer !== undefined) {
        window.clearInterval(scanTimer);
        scanTimer = undefined;
    }

    stream.value?.getTracks().forEach((track) => track.stop());
    stream.value = null;
    cameraActive.value = false;
};

const startCamera = async (): Promise<void> => {
    cameraError.value = '';

    if (!window.BarcodeDetector) {
        cameraError.value =
            'Camera scanning is not supported in this browser. Use a USB scanner or type the SKU.';
        return;
    }

    try {
        stream.value = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: { ideal: 'environment' } },
            audio: false,
        });
        cameraActive.value = true;

        if (video.value) {
            video.value.srcObject = stream.value;
            await video.value.play();
        }

        const detector = new window.BarcodeDetector({
            formats: [
                'ean_13',
                'ean_8',
                'code_128',
                'code_39',
                'upc_a',
                'upc_e',
            ],
        });

        scanTimer = window.setInterval(async () => {
            if (!video.value || video.value.readyState < 2) {
                return;
            }

            const results = await detector.detect(video.value);
            const code = results[0]?.rawValue?.trim();

            if (code) {
                emit('detected', code);
                stopCamera();
            }
        }, 250);
    } catch {
        cameraError.value =
            'Camera access was unavailable. Check browser permissions or use a USB scanner.';
        stopCamera();
    }
};

const handleHidKeydown = (event: KeyboardEvent): void => {
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
    (isOpen) => {
        if (!isOpen) {
            stopCamera();
        }
    },
);

onMounted(() => {
    window.addEventListener('keydown', handleHidKeydown);
});

onUnmounted(() => {
    stopCamera();
    window.removeEventListener('keydown', handleHidKeydown);
});
</script>

<template>
    <div
        v-if="open"
        class="fixed inset-0 z-[60] flex items-center justify-center bg-neutral-900/60 p-4"
    >
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
                Use the camera or scan with a connected USB barcode reader.
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
</template>
