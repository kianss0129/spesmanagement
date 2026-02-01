# Quick Reference - Document Upload System

## 🚀 Quick Start (2 minutes)

### For Backend (PHP/Laravel)
✅ **ALREADY COMPLETE** - No additional code needed

Files that are ready:
- `app/Http/Controllers/Beneficiary/OnboardingController.php` - upload() & submit()
- `app/Http/Controllers/PESO/PESOController.php` - document normalization
- `config/filesystems.php` - storage configured
- Routes: `POST /onboarding/upload` and `POST /onboarding/submit`

### For Frontend (Vue)

**Simple version (copy-paste):**

```vue
<!-- Step 3: Documents upload section -->
<div v-else-if="currentStep === 3">
  <h2 class="font-semibold mb-4">Step 3: Upload Documents</h2>
  
  <!-- File input -->
  <input type="file" @change="handleFileSelect" class="mb-3" />
  
  <!-- Upload button -->
  <button @click="uploadDoc" :disabled="!file || uploading">
    {{ uploading ? 'Uploading...' : 'Upload' }}
  </button>
  
  <!-- Show uploaded docs -->
  <ul v-if="form.documents.length" class="mt-4">
    <li v-for="d in form.documents" :key="d.path">
      {{ d.name }} - {{ new Date(d.uploaded_at).toLocaleDateString() }}
    </li>
  </ul>
  
  <p v-if="error" class="text-red-600 mt-2">{{ error }}</p>
</div>

<script setup>
import { ref, reactive } from 'vue'

const form = reactive({ documents: [] })
const file = ref(null)
const uploading = ref(false)
const error = ref('')

function handleFileSelect(e) {
  file.value = e.target.files[0]
}

async function uploadDoc() {
  const formData = new FormData()
  formData.append('file', file.value)
  
  uploading.value = true
  error.value = ''
  
  try {
    const res = await fetch('/onboarding/upload', {
      method: 'POST',
      body: formData,
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    const data = await res.json()
    if (data.success) {
      form.documents = data.documents
      file.value = null
    } else {
      error.value = data.message || 'Upload failed'
    }
  } catch (e) {
    error.value = 'Network error'
  } finally {
    uploading.value = false
  }
}
</script>
```

## 🗂️ File Locations

| Component | File | Purpose |
|-----------|------|---------|
| Upload Handler | `OnboardingController.php:111-155` | Save files + metadata |
| Submit Handler | `OnboardingController.php:162-244` | Finalize onboarding |
| PESO View (Beneficiary) | `PESOController.php:160-235` | Load & normalize documents |
| PESO View (Employer) | `PESOController.php:237-335` | Load & normalize documents |
| Display (Beneficiary) | `Applications.vue` | Show document list |
| Display (Employer) | `Applications.vue` | Show document list |

## 🔄 Flow Diagram

```
User uploads file
        ↓
POST /onboarding/upload
        ↓
OnboardingController::upload()
- Validate file
- Store to storage/app/public/documents/{users|employers}/{id}/
- Create {path, name, uploaded_at} object
- Save to beneficiaries.documents JSON
- Return documents array
        ↓
Frontend shows uploaded document
        ↓
User submits onboarding form
        ↓
POST /onboarding/submit
        ↓
OnboardingController::submit()
- Set status = pending
- Redirect to /onboarding/pending
        ↓
PESO Admin views pending
        ↓
Clicks "View Onboarding"
        ↓
GET /peso/beneficiaries/{id}/applications
        ↓
PESOController::viewBeneficiaryApplications()
- Load documents from DB
- Check file exists (Storage::disk('public')->exists())
- Generate URL (Storage::disk('public')->url())
- Add exists flag
- Return normalized documents
        ↓
Vue renders document list
- Shows "View" button if exists && url
- Shows "❌ File missing" warning if !exists
        ↓
PESO Admin clicks "View"
        ↓
Browser fetches /storage/documents/{users|employers}/{id}/file.pdf
        ↓
File opens/downloads
```

## 🎯 Endpoints

### Upload Document
```
POST /onboarding/upload
Content-Type: multipart/form-data
Body: { file: <binary> }
Returns: { success: true, documents: [...] }
```

### Submit Onboarding
```
POST /onboarding/submit
Content-Type: application/json
Body: { phone: "...", school: "..." } (beneficiary)
      { company_name: "...", phone: "...", ... } (employer)
Returns: 302 Redirect to /onboarding/pending
```

### View Beneficiary Documents
```
GET /peso/beneficiaries/{id}/applications
Returns: Inertia page with normalized documents array
```

### View Employer Documents
```
GET /peso/employers/{id}/applications
Returns: Inertia page with normalized documents array
```

## 📊 Database Structure

### Beneficiaries Table
```sql
documents: JSON
```

Example value:
```json
[
  {
    "path": "documents/users/1/resume.pdf",
    "name": "resume.pdf",
    "uploaded_at": "2025-01-02T10:30:00Z"
  }
]
```

### Employers Table
```sql
documents: JSON
```

Example value:
```json
[
  {
    "path": "documents/employers/1/dti.pdf",
    "name": "dti.pdf",
    "uploaded_at": "2025-01-02T11:00:00Z"
  }
]
```

## ✅ Implementation Checklist

- [x] OnboardingController::upload() - Saves files + metadata
- [x] OnboardingController::submit() - Finalizes onboarding
- [x] PESOController::viewBeneficiaryApplications() - Normalizes & returns documents
- [x] PESOController::viewEmployerApplications() - Normalizes & returns documents
- [x] Vue component - Conditionally shows View button
- [x] Storage link - `public/storage` → `storage/app/public`
- [x] Routes - `/onboarding/upload` and `/onboarding/submit` configured

**To do:**
- [ ] Update onboarding form Vue component with upload handler
- [ ] Test end-to-end workflow
- [ ] Verify files appear in PESO verification pages

## 🐛 Debugging Commands

```bash
# Check if storage symlink exists
ls -la public/storage

# Check if documents are in database
mysql> SELECT id, JSON_ARRAY_LENGTH(documents) as doc_count FROM beneficiaries;

# Check if files exist on disk
ls -la storage/app/public/documents/users/

# Test Storage facade in tinker
php artisan tinker
>>> Storage::disk('public')->exists('documents/users/1/file.pdf')
>>> Storage::disk('public')->url('documents/users/1/file.pdf')
```

## 🔒 File Validation

- **Max size:** 5 MB
- **Allowed types:** PDF, DOC, DOCX, JPG, JPEG, PNG
- **Storage location:** Public disk (accessible via `/storage/` URL)
- **Directory structure:** `documents/{users|employers}/{id}/`

## 📱 Vue Component Template

Copy this into your onboarding form's Step 3:

```vue
<template>
  <div v-if="currentStep === 3">
    <h2 class="font-semibold mb-4">Step 3: Upload Documents</h2>
    
    <input 
      type="file" 
      @change="(e) => file = e.target.files[0]"
      accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
      class="border p-2 w-full mb-3"
    />
    
    <button 
      @click="uploadDocument" 
      :disabled="!file || uploading"
      class="w-full bg-blue-600 text-white p-2 rounded mb-4"
    >
      {{ uploading ? 'Uploading...' : 'Upload' }}
    </button>
    
    <div v-if="form.documents.length" class="bg-gray-50 p-4 rounded">
      <h3 class="font-semibold mb-2">Uploaded Documents:</h3>
      <ul class="space-y-2">
        <li v-for="d in form.documents" :key="d.path" class="p-2 bg-white rounded">
          <p class="font-semibold">{{ d.name }}</p>
          <p class="text-xs text-gray-500">{{ formatDate(d.uploaded_at) }}</p>
        </li>
      </ul>
    </div>
    
    <p v-if="error" class="text-red-600 mt-4">{{ error }}</p>
    <p v-if="success" class="text-green-600 mt-4">{{ success }}</p>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'

const form = reactive({ documents: [] })
const file = ref(null)
const uploading = ref(false)
const error = ref('')
const success = ref('')

async function uploadDocument() {
  const fd = new FormData()
  fd.append('file', file.value)
  
  uploading.value = true
  error.value = ''
  success.value = ''
  
  try {
    const res = await fetch('/onboarding/upload', {
      method: 'POST',
      body: fd,
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    const data = await res.json()
    if (data.success) {
      form.documents = data.documents
      file.value = null
      success.value = 'Document uploaded successfully'
      setTimeout(() => success.value = '', 3000)
    } else {
      error.value = data.message || 'Upload failed'
    }
  } catch (e) {
    error.value = 'Error: ' + e.message
  } finally {
    uploading.value = false
  }
}

function formatDate(d) {
  return new Date(d).toLocaleDateString()
}
</script>
```

## 🌐 URL Format

Files are accessible at:
```
/storage/documents/users/{beneficiary_id}/{filename}
/storage/documents/employers/{employer_id}/{filename}
```

Example:
```
/storage/documents/users/1/resume.pdf
/storage/documents/employers/5/business-permit.pdf
```

## 📝 Notes

- Documents are saved **immediately** when uploaded (not on form submit)
- Submit endpoint only finalizes the onboarding status
- Both beneficiary and employer use the **same upload endpoint**
- Documents persist even if user closes browser
- PESO admin sees documents in verification page

## 🔗 Related Documentation

- [DOCUMENT-FLOW-COMPLETE.md](DOCUMENT-FLOW-COMPLETE.md) - Complete system overview
- [TEST-DOCUMENT-FLOW.md](TEST-DOCUMENT-FLOW.md) - Testing guide
- [FRONTEND-INTEGRATION-GUIDE.md](FRONTEND-INTEGRATION-GUIDE.md) - Detailed frontend guide

