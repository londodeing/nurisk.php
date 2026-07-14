<div
    x-data="mediaUploader({
        entityType: '{{ $entityType }}',
        entityId: {{ $entityId }},
        visibility: '{{ $visibility ?? 'PUBLIC' }}',
        uploadUrl: '{{ $uploadUrl ?? '/api/media' }}',
    })"
    class="media-uploader"
>
    <div
        x-on:dragover.prevent="$el.classList.add('dragover')"
        x-on:dragleave.prevent="$el.classList.remove('dragover')"
        x-on:drop.prevent="handleDrop($event)"
        class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center cursor-pointer hover:border-blue-400 transition-colors"
        x-on:click="document.getElementById('fileInput').click()"
    >
        <template x-if="!uploading && !preview">
            <div>
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                </svg>
                <p class="mt-2 text-sm text-gray-600">Drop file here or click to upload</p>
            </div>
        </template>

        <template x-if="uploading">
            <div class="space-y-2">
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-blue-500 h-2 rounded-full transition-all" x-bind:style="'width: ' + progress + '%'"></div>
                </div>
                <p class="text-sm text-gray-500" x-text="progress + '%'"></p>
            </div>
        </template>

        <template x-if="preview && !uploading">
            <div class="relative">
                <img x-bind:src="preview" class="max-h-48 mx-auto rounded">
                <button
                    x-on:click="reset()"
                    class="absolute top-0 right-0 bg-red-500 text-white rounded-full w-6 h-6 text-sm flex items-center justify-center"
                >&times;</button>
            </div>
        </template>

        <template x-if="uploaded">
            <div class="text-green-600">
                <p class="font-semibold">Upload successful</p>
                <p class="text-sm" x-text="'ID: ' + uploaded.id"></p>
            </div>
        </template>

        <template x-if="error">
            <div class="text-red-600 text-sm" x-text="error"></div>
        </template>
    </div>

    <input
        id="fileInput"
        type="file"
        class="hidden"
        x-on:change="handleFileSelect($event)"
        accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.csv"
    >
</div>

@pushOnce('scripts')
<script>
    function mediaUploader(config) {
        return {
            uploading: false,
            progress: 0,
            preview: null,
            uploaded: null,
            error: null,

            handleFileSelect(event) {
                const file = event.target.files[0];
                if (file) this.upload(file);
            },

            handleDrop(event) {
                const file = event.dataTransfer.files[0];
                if (file) this.upload(file);
            },

            upload(file) {
                this.uploading = true;
                this.progress = 0;
                this.error = null;
                this.uploaded = null;

                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = (e) => { this.preview = e.target.result; };
                    reader.readAsDataURL(file);
                }

                const formData = new FormData();
                formData.append('file', file);
                formData.append('entity_type', config.entityType);
                formData.append('entity_id', config.entityId);
                formData.append('visibility', config.visibility);

                const xhr = new XMLHttpRequest();
                xhr.open('POST', config.uploadUrl);
                xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name=csrf-token]')?.content);
                xhr.setRequestHeader('Accept', 'application/json');

                const token = document.querySelector('meta[name=api-token]')?.content;
                if (token) xhr.setRequestHeader('Authorization', 'Bearer ' + token);

                xhr.upload.onprogress = (e) => {
                    if (e.lengthComputable) {
                        this.progress = Math.round((e.loaded / e.total) * 100);
                    }
                };

                xhr.onload = () => {
                    this.uploading = false;
                    if (xhr.status === 201) {
                        this.uploaded = JSON.parse(xhr.responseText).data;
                        this.$dispatch('media-uploaded', this.uploaded);
                    } else {
                        this.error = 'Upload failed';
                        this.preview = null;
                    }
                };

                xhr.onerror = () => {
                    this.uploading = false;
                    this.error = 'Network error';
                    this.preview = null;
                };

                xhr.send(formData);
            },

            reset() {
                this.uploading = false;
                this.progress = 0;
                this.preview = null;
                this.uploaded = null;
                this.error = null;
                document.getElementById('fileInput').value = '';
            },
        };
    }
</script>
@endPushOnce

@once
    @push('styles')
    <style>
        .media-uploader .dragover {
            border-color: #3b82f6;
            background-color: #eff6ff;
        }
    </style>
    @endpush
@endonce
