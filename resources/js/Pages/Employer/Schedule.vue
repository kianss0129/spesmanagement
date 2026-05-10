<template>
  <div class="page">

    <div class="card">

      <!-- HEADER -->
      <div class="header">

        <!-- 🔙 MODERN BACK BUTTON -->
        <button
          @click="goBack"
          class="flex items-center gap-2 mb-4 px-3 py-2 bg-gray-100 hover:bg-blue-100 rounded-xl transition group"
        >
          <svg xmlns="http://www.w3.org/2000/svg"
               class="w-5 h-5 text-gray-600 group-hover:text-blue-600 transform group-hover:-translate-x-1 transition"
               fill="none"
               viewBox="0 0 24 24"
               stroke="currentColor">
            <path stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M15 19l-7-7 7-7" />
          </svg>

          <span class="text-sm font-medium text-gray-700 group-hover:text-blue-600">
            Back
          </span>
        </button>

        <h1>Interviews</h1>
        <p>Select and join your scheduled interview</p>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="state">Loading interviews...</div>

      <!-- Empty -->
      <div v-else-if="!interviews.length" class="state">
        No scheduled interviews
      </div>

      <!-- Content -->
      <div v-else class="content">

        <div class="select-group">
          <select v-model="selectedInterview" class="modern-select">
            <option disabled value="">Choose interview</option>

            <option
              v-for="interview in interviews"
              :key="interview.id"
              :value="interview.id"
            >
              {{ interview.job_title }} — {{ interview.beneficiary_name }}
            </option>

          </select>
        </div>

        <div v-if="selectedInterviewData" class="interview-card">

          <div class="info">
            <h3>{{ selectedInterviewData.job_title }}</h3>

            <span class="employer">
              {{ selectedInterviewData.beneficiary_name }}
            </span>

            <span class="date">
              {{ formatDate(selectedInterviewData.scheduled_at) }}
            </span>
          </div>

          <button
            class="join-btn"
            :disabled="!selectedInterviewData?.meet_link || selectedInterviewData.meet_link.trim() === ''"
            @click="joinInterview(selectedInterviewData.id)"
          >
            Join Interview
          </button>

        </div>

      </div>
    </div>

  </div>
</template>

<script>
import { ref, onMounted, computed } from "vue";
import axios from "axios";

export default {
  setup() {

    const interviews = ref([]);
    const selectedInterview = ref("");
    const loading = ref(false);

    /* 🔙 BACK */
    function goBack(){
      window.history.back()
    }

    const fetchInterviews = async () => {
      loading.value = true;
      try {
        const res = await axios.get("/employer/interviews/data");
        interviews.value = res.data;
      } catch (err) {
        console.error("Error fetching interviews:", err);
      } finally {
        loading.value = false;
      }
    };

    const selectedInterviewData = computed(() =>
      interviews.value.find(i => i.id === Number(selectedInterview.value))
    );

    const joinInterview = (id) => {
      const interview = interviews.value.find(i => i.id === id);

      if (interview?.meet_link && interview.meet_link.trim() !== "") {
        window.open(interview.meet_link, "_blank");
      } else {
        alert("Meet link not available yet.");
      }
    };

    const formatDate = (date) => {
      if (!date) return "N/A";
      return new Date(date).toLocaleString();
    };

    onMounted(fetchInterviews);

    return {
      interviews,
      selectedInterview,
      selectedInterviewData,
      joinInterview,
      loading,
      formatDate,
      goBack
    };
  }
};
</script>

<style scoped>
.page {
  min-height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  background: #f8fafc;
  padding: 20px;
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
}

.card {
  width: 100%;
  max-width: 520px;
  background: #ffffff;
  border-radius: 16px;
  padding: 40px;
  box-shadow: 0 10px 40px rgba(15, 23, 42, 0.08);
}

.header h1 {
  margin: 0;
  font-size: 24px;
  font-weight: 600;
  color: #0f172a;
}

.header p {
  margin-top: 6px;
  font-size: 14px;
  color: #64748b;
}

.select-group {
  margin-top: 25px;
}

.modern-select {
  width: 100%;
  padding: 14px;
  border-radius: 12px;
  border: 1px solid #e2e8f0;
  background: #f9fafb;
  font-size: 14px;
  transition: all 0.2s ease;
}

.modern-select:focus {
  outline: none;
  border-color: #3b82f6;
  background: #ffffff;
  box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
}

.interview-card {
  margin-top: 25px;
  padding: 20px;
  border-radius: 14px;
  background: #f9fafb;
  border: 1px solid #e2e8f0;
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.info h3 {
  margin: 0;
  font-size: 16px;
  font-weight: 600;
  color: #0f172a;
}

.employer {
  display: block;
  font-size: 14px;
  color: #475569;
}

.date {
  display: block;
  font-size: 13px;
  color: #94a3b8;
}

.join-btn {
  padding: 12px;
  border-radius: 12px;
  border: none;
  background: #3b82f6;
  color: white;
  font-weight: 500;
  font-size: 14px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.join-btn:hover {
  background: #2563eb;
  transform: translateY(-1px);
  box-shadow: 0 6px 20px rgba(59, 130, 246, 0.25);
}

.join-btn:disabled {
  background: #cbd5e1;
  cursor: not-allowed;
}

.state {
  margin-top: 30px;
  text-align: center;
  color: #64748b;
}
</style>