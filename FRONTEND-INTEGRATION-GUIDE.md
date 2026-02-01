# Frontend Integration Guide - Document Upload

This guide explains how to integrate the document upload functionality into your Vue/Inertia onboarding forms.

## Overview

The backend is now ready to accept document uploads via `POST /onboarding/upload` and store them with metadata. You need to integrate this into your frontend onboarding forms (both beneficiary and employer).

## API Endpoints

### Upload Endpoint
```
POST /onboarding/upload
Content-Type: multipart/form-data

Request:
- file: File (binary) - Single file to upload

Response (200 OK):
{
  "success": true,
  "documents": [
    {
      "path": "documents/users/1/filename.pdf",
      "name": "filename.pdf",
      "uploaded_at": "2025-01-02T10:30:00Z"
    },
    ...
  ],
  "message": "Document uploaded successfully"
}

Response (422 Validation Error):
{
  "message": "The file field is required.",
  "errors": {
    "file": ["The file field is required."]
  }
}

Response (413 File Too Large):
{
  "message": "File size exceeds 5MB limit"
}
```

### Submit Endpoint
```
POST /onboarding/submit
Content-Type: application/x-www-form-urlencoded

Request (Beneficiary):
{
  "phone": "09991234567",
  "school": "University of the Philippines"
}

Request (Employer):
{
  "company_name": "Acme Corp",
  "email": "contact@acme.com",
  "contact_person": "John Doe",
  "phone": "09991234567",
  "address": "123 Business St"
}

Response (302 Redirect):
Location: /onboarding/pending
X-Inertia-Location: /onboarding/pending
```

## Vue Component Integration

### Basic Implementation (Simple)

```vue
<template>
  <div>
    <!-- File Input -->
    <input 
      type="file" 
      @change="handleFileSelect"
      accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
    />
    
    <!-- Upload Button -->
    <button 
      @click="uploadDocument"
      :disabled="!selectedFile || uploading"
    >
      {{ uploading ? 'Uploading...' : 'Upload Document' }}
    </button>
    
    <!-- Uploaded Documents List -->
    <div v-if="uploadedDocuments.length" class="mt-4">
      <h3>Uploaded Documents:</h3>
      <ul>
        <li v-for="(doc, i) in uploadedDocuments" :key="i">
          <span>{{ doc.name }}</span>
          <span class="text-sm text-gray-500">
            ({{ new Date(doc.uploaded_at).toLocaleDateString() }})
          </span>
          <button @click="removeDocument(i)">Remove</button>
        </li>
      </ul>
    </div>
    
    <!-- Error Messages -->
    <div v-if="uploadError" class="text-red-600">
      {{ uploadError }}
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'

const selectedFile = ref(null)
const uploading = ref(false)
const uploadedDocuments = ref([])
const uploadError = ref('')

function handleFileSelect(event) {
  selectedFile.value = event.target.files[0] || null
}

async function uploadDocument() {
  if (!selectedFile.value) return
  
  uploading.value = true
  uploadError.value = ''
  
  const formData = new FormData()
  formData.append('file', selectedFile.value)
  
  try {
    const response = await axios.post('/onboarding/upload', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    
    // Update documents list from server response
    uploadedDocuments.value = response.data.documents
    selectedFile.value = null
    
    // Reset file input
    event.target.value = ''
    
  } catch (error) {
    uploadError.value = error.response?.data?.message || 'Upload failed'
  } finally {
    uploading.value = false
  }
}

function removeDocument(index) {
  uploadedDocuments.value.splice(index, 1)
}
</script>
```

### Advanced Implementation (Full Integration)

For a complete onboarding form with proper state management:

```vue
<template>
  <div class="max-w-2xl mx-auto">
    <!-- Step 3: Document Upload -->
    <div v-if="currentStep === 3" class="bg-white p-6 rounded shadow">
      <h2 class="text-xl font-bold mb-4">Step 3: Upload Documents</h2>
      
      <!-- Required Documents Info -->
      <div class="bg-blue-50 p-4 rounded mb-4">
        <h3 class="font-semibold mb-2">Required Documents:</h3>
        <ul class="list-disc list-inside space-y-1">
          <li v-for="doc in requiredDocuments" :key="doc">{{ doc }}</li>
        </ul>
      </div>
      
      <!-- Upload Section -->
      <div class="border-2 border-dashed border-gray-300 p-6 rounded mb-4">
        <label class="cursor-pointer">
          <div class="text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
              <path d="M28 8H12a4 4 0 00-4 4v20a4 4 0 004 4h24a4 4 0 004-4V20m-14-2l3 3m0 0l3-3m-3 3V2" stroke-width="2" stroke-linecap="round"/>
            </svg>
            <p class="mt-2 text-sm text-gray-600">
              <span class="font-semibold">Click to upload</span> or drag and drop
            </p>
            <p class="text-xs text-gray-500">PDF, DOC, DOCX, JPG, JPEG, PNG up to 5MB</p>
          </div>
          <input 
            type="file" 
            hidden 
            @change="handleFileSelect"
            accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
          />
        </label>
      </div>
      
      <!-- Upload Button -->
      <button
        v-if="selectedFile"
        @click="uploadDocument"
        :disabled="uploading"
        class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 disabled:opacity-50"
      >
        {{ uploading ? `Uploading ${Math.round(uploadProgress)}%...` : 'Upload Document' }}
      </button>
      
      <!-- Error Display -->
      <div v-if="uploadError" class="mt-4 p-4 bg-red-50 border border-red-200 rounded text-red-700">
        {{ uploadError }}
      </div>
      
      <!-- Success Message -->
      <div v-if="lastUploadSuccess" class="mt-4 p-4 bg-green-50 border border-green-200 rounded text-green-700">
        Document uploaded successfully!
      </div>
      
      <!-- Uploaded Documents List -->
      <div v-if="form.documents.length" class="mt-6">
        <h3 class="font-semibold mb-3">Uploaded Documents ({{ form.documents.length }})</h3>
        <div class="space-y-2">
          <div 
            v-for="(doc, index) in form.documents"
            :key="index"
            class="flex items-center justify-between p-3 bg-gray-50 rounded border border-gray-200"
          >
            <div class="flex-1">
              <p class="font-semibold text-gray-900">{{ doc.name }}</p>
              <p class="text-xs text-gray-500">
                Uploaded: {{ formatDate(doc.uploaded_at) }}
              </p>
            </div>
            <button
              @click="removeDocument(index)"
              type="button"
              class="ml-4 px-3 py-1 text-red-600 hover:bg-red-50 rounded"
            >
              Remove
            </button>
          </div>
        </div>
      </div>
      
      <!-- Continue Button -->
      <div class="flex justify-between mt-6">
        <button @click="previousStep" class="px-4 py-2 border rounded hover:bg-gray-50">
          Previous
        </button>
        <button 
          @click="nextStep" 
          :disabled="form.documents.length === 0"
          class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50"
        >
          Continue to Review
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import axios from 'axios'

const currentStep = ref(3)
const selectedFile = ref(null)
const uploading = ref(false)
const uploadProgress = ref(0)
const uploadError = ref('')
const lastUploadSuccess = ref(false)

const form = reactive({
  documents: []
})

const requiredDocuments = ['Birth Certificate', 'ITR/Certificate of Low Income', 'Form 138/137']

function handleFileSelect(event) {
  selectedFile.value = event.target.files[0] || null
  uploadError.value = ''
  lastUploadSuccess.value = false
}

async function uploadDocument() {
  if (!selectedFile.value) return
  
  // Validate file size
  if (selectedFile.value.size > 5 * 1024 * 1024) {
    uploadError.value = 'File size exceeds 5MB limit'
    return
  }
  
  uploading.value = true
  uploadError.value = ''
  uploadProgress.value = 0
  
  const formData = new FormData()
  formData.append('file', selectedFile.value)
  
  try {
    const response = await axios.post('/onboarding/upload', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
      onUploadProgress: (progressEvent) => {
        uploadProgress.value = Math.round(
          (progressEvent.loaded * 100) / progressEvent.total
        )
      }
    })
    
    // Success: update documents from server response
    form.documents = response.data.documents || []
    lastUploadSuccess.value = true
    selectedFile.value = null
    
    // Reset after 3 seconds
    setTimeout(() => {
      lastUploadSuccess.value = false
    }, 3000)
    
  } catch (error) {
    if (error.response?.status === 422) {
      const errors = error.response.data.errors
      uploadError.value = errors.file?.[0] || 'Validation error'
    } else {
      uploadError.value = error.response?.data?.message || 'Upload failed. Please try again.'
    }
  } finally {
    uploading.value = false
    uploadProgress.value = 0
  }
}

function removeDocument(index) {
  // Note: Removing from frontend only
  // If needed, implement backend endpoint to remove specific document
  form.documents.splice(index, 1)
}

function formatDate(dateString) {
  return new Date(dateString).toLocaleString()
}

function nextStep() {
  if (form.documents.length > 0) {
    currentStep.value++
  }
}

function previousStep() {
  currentStep.value--
}
</script>
```

## Integration into Existing Onboarding Form

If you already have an onboarding form, add this to Step 3 (Documents):

### 1. Add File Upload Handler
```javascript
// In your script section
function handleFileSelect(event) {
  const file = event.target.files[0]
  if (file) {
    uploadDocument(file)
  }
}

async function uploadDocument(file) {
  const formData = new FormData()
  formData.append('file', file)
  
  try {
    const response = await fetch('/onboarding/upload', {
      method: 'POST',
      body: formData,
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
      }
    })
    
    const data = await response.json()
    if (data.success) {
      // Update your form's documents array
      form.documents = data.documents
    }
  } catch (error) {
    console.error('Upload failed:', error)
  }
}
```

### 2. Update Template
```vue
<!-- Replace your current document upload section with: -->
<div v-else-if="currentStep === 3">
  <h2 class="font-semibold mb-2">Step 3: Upload Documents</h2>
  
  <!-- File Input -->
  <input 
    type="file" 
    @change="handleFileSelect"
    accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
    class="border p-2 w-full mb-3 rounded"
  />
  
  <!-- Uploaded Documents List -->
  <div v-if="form.documents.length" class="mt-4 p-4 bg-gray-50 rounded">
    <h3 class="font-semibold mb-2">Uploaded Documents:</h3>
    <ul class="space-y-2">
      <li v-for="(doc, i) in form.documents" :key="i" class="flex justify-between items-center p-2 bg-white rounded">
        <div>
          <p class="font-semibold">{{ doc.name }}</p>
          <p class="text-xs text-gray-500">{{ new Date(doc.uploaded_at).toLocaleDateString() }}</p>
        </div>
        <button @click="removeDocument(i)" type="button" class="text-red-600 hover:text-red-800">
          Remove
        </button>
      </li>
    </ul>
  </div>
  <div v-else class="text-gray-500 mt-4">
    No documents uploaded yet.
  </div>
</div>
```

### 3. Update Form Submission
```javascript
// When submitting, documents are already on the server
// Just submit the final form with phone/school/company info
function submitForm() {
  const payload = {
    phone: form.phone,
    school: form.school, // for beneficiaries
    // OR for employers:
    company_name: form.companyName,
    email: form.email,
    contact_person: form.contactPerson,
    address: form.address
  }
  
  // Documents are already saved server-side via upload()
  // No need to send them again
  fetch('/onboarding/submit', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload)
  }).then(response => {
    if (response.ok) {
      window.location.href = '/onboarding/pending'
    }
  })
}
```

## Important Notes

### Document Persistence
- Documents are saved to the server immediately when uploaded
- They persist across page refreshes and browser closes
- They remain available even if the user doesn't submit the onboarding form
- Submitting the onboarding form just finalizes the process, doesn't re-upload documents

### File Size & Type Validation
- **Max file size:** 5MB
- **Allowed types:** PDF, DOC, DOCX, JPG, JPEG, PNG
- Validation happens on both frontend and backend

### Error Handling
```javascript
// Always wrap upload in try-catch
async function uploadDocument(file) {
  try {
    const response = await fetch('/onboarding/upload', {
      method: 'POST',
      body: formData
    })
    
    if (!response.ok) {
      const error = await response.json()
      console.error('Server error:', error.message)
      // Show error to user
      return
    }
    
    const data = await response.json()
    // Handle success
    
  } catch (error) {
    console.error('Network error:', error)
    // Show error to user
  }
}
```

## Testing the Integration

### Test Checklist
- [ ] Can upload a single file
- [ ] Can upload multiple files (one at a time)
- [ ] File appears in the list with correct name
- [ ] Upload timestamp displays correctly
- [ ] Can remove a file from the list
- [ ] Upload rejects files > 5MB with error
- [ ] Upload rejects wrong file types with error
- [ ] Form can be submitted without re-uploading
- [ ] Documents persist after page refresh
- [ ] Documents appear in PESO verification page

### Manual Test Steps
1. Open onboarding form
2. Fill out Steps 1-2
3. Reach Step 3: Documents
4. Select a PDF file
5. Click Upload
6. Verify file appears in list with correct name and date
7. Continue to Step 4 (Review)
8. Step 5 should show all entered info including uploaded documents
9. Click Submit
10. Login as PESO Admin
11. Navigate to pending beneficiaries
12. Click "View Onboarding"
13. Verify document appears with "View" button
14. Click "View" and confirm file opens

## API Response Examples

### Success Response
```json
{
  "success": true,
  "documents": [
    {
      "path": "documents/users/1/resume.pdf",
      "name": "resume.pdf",
      "uploaded_at": "2025-01-02T10:30:00+00:00"
    },
    {
      "path": "documents/users/1/transcript.pdf",
      "name": "transcript.pdf",
      "uploaded_at": "2025-01-02T10:35:00+00:00"
    }
  ],
  "message": "Document uploaded successfully"
}
```

### Validation Error Response
```json
{
  "message": "The file field is required.",
  "errors": {
    "file": [
      "The file field is required.",
      "The file must be a file.",
      "The file must be a string or file."
    ]
  }
}
```

### File Type Error Response
```json
{
  "message": "The file field must be a file of type: pdf, doc, docx, jpg, jpeg, png.",
  "errors": {
    "file": [
      "The file field must be a file of type: pdf, doc, docx, jpg, jpeg, png."
    ]
  }
}
```

## Employer Onboarding

The employer onboarding uses the same upload endpoint (`POST /onboarding/upload`) but:
- Store path: `documents/employers/{employer_id}/` (instead of `documents/users/{beneficiary_id}/`)
- Database column: `employers.documents` (instead of `beneficiaries.documents`)
- Display component: `Employers/Applications.vue` (instead of `Beneficiaries/Applications.vue`)

The frontend integration is identical - just use the same upload component for both.

## Troubleshooting

### Upload fails with 404
- Check route: `POST /onboarding/upload` should exist
- Check OnboardingController has `upload()` method
- Check Laravel is running and routes are cached

### Files not appearing in PESO view
- Check browser console for upload errors
- Check database: `SELECT documents FROM beneficiaries WHERE id = X;`
- Check files exist: `ls -la storage/app/public/documents/users/`

### "File missing or deleted" in PESO view
- File was deleted after upload
- Check symlink: `ls -la public/storage/`
- Run `php artisan storage:link` if missing

### Upload endpoint returns 422
- File size exceeds 5MB
- File type not in: PDF, DOC, DOCX, JPG, JPEG, PNG
- No file provided in request

