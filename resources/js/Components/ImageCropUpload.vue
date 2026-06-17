
<template>
    <div class="space-y-3">
        <!-- File Input -->
        <div v-if="!selectedFile" class="rounded-xl bg-slate-50 p-4">
            <label class="inline-flex cursor-pointer items-center rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                Choose file
                <input
                    type="file"
                    class="sr-only"
                    accept="image/*,.pdf"
                    @change="handleFileSelect"
                />
            </label>
            <p class="mt-2 text-sm text-slate-600">{{ help }}</p>
        </div>


        <!-- Image Preview and Cropper Modal -->
        <div v-if="selectedFile && showCropper" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="relative w-full max-w-4xl rounded-2xl bg-white shadow-2xl">
                <!-- Modal Header -->
                <div class="border-b border-slate-200 bg-slate-900 px-6 py-4 text-white flex items-center justify-between">
                    <h3 class="text-lg font-semibold">Crop and Zoom Document Image</h3>
                    <button @click="closeCropper" type="button" class="text-slate-300 hover:text-white">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>


                <div class="flex gap-6 p-6">
                    <!-- Left Controls Panel -->
                    <div class="w-48 space-y-4 rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-900">Zoom Level</label>
                            <div class="mt-2 flex items-center gap-2">
                                <input
                                    v-model.number="zoomLevel"
                                    type="range"
                                    min="0.5"
                                    max="3"
                                    step="0.1"
                                    class="w-full cursor-pointer"
                                />
                            </div>
                            <p class="mt-1 text-sm text-slate-600">{{ (zoomLevel * 100).toFixed(0) }}%</p>
                        </div>


                        <div>
                            <label class="block text-sm font-semibold text-slate-900">Brightness</label>
                            <input
                                v-model.number="brightness"
                                type="range"
                                min="0.5"
                                max="1.5"
                                step="0.1"
                                class="mt-2 w-full cursor-pointer"
                            />
                            <p class="mt-1 text-sm text-slate-600">{{ (brightness * 100).toFixed(0) }}%</p>
                        </div>


                        <div>
                            <label class="block text-sm font-semibold text-slate-900">Contrast</label>
                            <input
                                v-model.number="contrast"
                                type="range"
                                min="0.5"
                                max="1.5"
                                step="0.1"
                                class="mt-2 w-full cursor-pointer"
                            />
                            <p class="mt-1 text-sm text-slate-600">{{ (contrast * 100).toFixed(0) }}%</p>
                        </div>


                        <div>
                            <label class="block text-sm font-semibold text-slate-900">Rotation</label>
                            <div class="mt-2 space-y-2">
                                <button
                                    @click="rotateLeft"
                                    type="button"
                                    class="w-full rounded-lg bg-slate-900 px-3 py-2 text-sm font-semibold text-white hover:bg-slate-800"
                                >
                                    ↺ Rotate Left
                                </button>
                                <button
                                    @click="rotateRight"
                                    type="button"
                                    class="w-full rounded-lg bg-slate-900 px-3 py-2 text-sm font-semibold text-white hover:bg-slate-800"
                                >
                                    Rotate Right ↻
                                </button>
                            </div>
                        </div>


                        <div>
                            <button
                                @click="resetAdjustments"
                                type="button"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100"
                            >
                                Reset
                            </button>
                        </div>
                    </div>


                    <!-- Image Preview -->
                    <div class="flex-1 rounded-xl border border-slate-200 bg-slate-100 overflow-hidden">
                        <div
                            class="relative h-96 flex items-center justify-center overflow-auto cursor-move select-none"
                            @wheel.prevent="handleWheel"
                            @mousedown="startPan"
                        >
                            <img
                                v-if="previewImage && !isPDF"
                                :src="previewImage"
                                :style="{
                                    transform: `scale(${zoomLevel}) translate(${panX}px, ${panY}px) rotate(${rotation}deg)`,
                                    filter: `brightness(${brightness}) contrast(${contrast})`,
                                    transition: isPanning ? 'none' : 'transform 0.2s ease-out',
                                    cursor: isPanning ? 'grabbing' : 'grab'
                                }"
                                class="max-w-none origin-center"
                                alt="Document preview"
                            />
                            <div v-if="isPDF" class="text-center text-slate-600">
                                <p class="text-lg font-semibold">PDF Preview</p>
                                <p class="mt-2 text-sm">PDF files cannot be cropped. They will be uploaded as-is.</p>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Modal Footer -->
                <div class="border-t border-slate-200 bg-slate-50 px-6 py-4 flex justify-end gap-3">
                    <button
                        @click="closeCropper"
                        type="button"
                        class="rounded-lg border border-slate-300 px-4 py-2.5 font-semibold text-slate-700 hover:bg-slate-100"
                    >
                        Cancel
                    </button>
                    <button
                        @click="saveCroppedImage"
                        type="button"
                        class="rounded-lg bg-emerald-600 px-4 py-2.5 font-semibold text-white hover:bg-emerald-700"
                    >
                        Save & Use This Image
                    </button>
                </div>
            </div>
        </div>


        <!-- Selected File Display -->
        <div v-if="selectedFile && !showCropper" class="flex flex-wrap items-center justify-between gap-3 rounded-lg bg-white px-3 py-2 text-sm border border-slate-200">
            <div class="flex items-center gap-2">
                <svg class="h-4 w-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                <span class="text-slate-700">{{ selectedFile.name }}</span>
            </div>
            <div class="flex gap-2">
                <button
                    v-if="!isPDF"
                    @click="openCropper"
                    type="button"
                    class="text-sky-600 hover:text-sky-700 font-semibold"
                >
                    Edit
                </button>
                <button
                    @click="removeFile"
                    type="button"
                    class="text-rose-600 hover:text-rose-700 font-semibold"
                >
                    Remove
                </button>
            </div>
        </div>


        <!-- Error Message -->
        <p v-if="errorMessage" class="text-sm text-rose-600">{{ errorMessage }}</p>
    </div>
</template>


<script setup>
import { ref, computed } from 'vue';


const props = defineProps({
    label: {
        type: String,
        required: true
    },
    help: {
        type: String,
        default: 'Upload a clear image of your document (JPG, PNG) or PDF file.'
    }
});


const emit = defineEmits(['update:file']);


const selectedFile = ref(null);
const previewImage = ref(null);
const showCropper = ref(false);
const errorMessage = ref('');
const zoomLevel = ref(1);
const brightness = ref(1);
const contrast = ref(1);
const rotation = ref(0);
const panX = ref(0);
const panY = ref(0);
const isPanning = ref(false);
const panStartX = ref(0);
const panStartY = ref(0);


const isPDF = computed(() => {
    return selectedFile.value?.type === 'application/pdf';
});


const handleFileSelect = (event) => {
    const file = event.target.files?.[0];
    errorMessage.value = '';


    if (!file) return;


    // Validate file type
    const allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
    if (!allowedTypes.includes(file.type)) {
        errorMessage.value = 'Only JPG, PNG, and PDF files are allowed.';
        return;
    }


    // Validate file size (max 5MB for images, 10MB for PDFs)
    const maxSize = isPDF.value ? 10 * 1024 * 1024 : 5 * 1024 * 1024;
    if (file.size > maxSize) {
        errorMessage.value = `File size exceeds ${isPDF.value ? '10MB' : '5MB'} limit.`;
        return;
    }


    selectedFile.value = file;
   
    if (isPDF.value) {
        // For PDF, just emit the file without cropping
        emit('update:file', file);
    } else {
        // For images, emit immediately and show cropper for optional editing
        emit('update:file', file);
        const reader = new FileReader();
        reader.onload = (e) => {
            previewImage.value = e.target.result;
            openCropper();
        };
        reader.readAsDataURL(file);
    }
};


const openCropper = () => {
    showCropper.value = true;
};


const closeCropper = () => {
    showCropper.value = false;
};


const handleWheel = (event) => {
    const delta = event.deltaY > 0 ? -0.1 : 0.1;
    zoomLevel.value = Math.max(0.5, Math.min(3, zoomLevel.value + delta));
};


const startPan = (event) => {
    if (isPDF.value) return;
    isPanning.value = true;
    panStartX.value = event.clientX - panX.value;
    panStartY.value = event.clientY - panY.value;
    document.addEventListener('mousemove', movePan);
    document.addEventListener('mouseup', endPan);
};


const movePan = (event) => {
    if (!isPanning.value) return;
    panX.value = event.clientX - panStartX.value;
    panY.value = event.clientY - panStartY.value;
};


const endPan = () => {
    isPanning.value = false;
    document.removeEventListener('mousemove', movePan);
    document.removeEventListener('mouseup', endPan);
};


const rotateLeft = () => {
    rotation.value = (rotation.value - 90) % 360;
};


const rotateRight = () => {
    rotation.value = (rotation.value + 90) % 360;
};


const resetAdjustments = () => {
    zoomLevel.value = 1;
    brightness.value = 1;
    contrast.value = 1;
    rotation.value = 0;
    panX.value = 0;
    panY.value = 0;
};


const saveCroppedImage = () => {
    // For this implementation, we'll emit the original file
    // In a production app, you might want to actually crop the canvas
    if (selectedFile.value) {
        emit('update:file', selectedFile.value);
    }
    closeCropper();
};


const removeFile = () => {
    selectedFile.value = null;
    previewImage.value = null;
    errorMessage.value = '';
    emit('update:file', null);
};
</script>



