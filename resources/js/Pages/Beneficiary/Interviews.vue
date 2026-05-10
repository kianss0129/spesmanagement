<template>
  <div class="page">

    <!-- 🔙 MODERN BACK BUTTON -->
    <button class="back-btn" @click="goBack">
      <span class="arrow">←</span>
      <span>Back</span>
    </button>

    <div class="card">
      <div class="header">
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
              {{ interview.job_title }} — {{ interview.employer_name }}
            </option>
          </select>
        </div>

        <div v-if="selectedInterviewData" class="interview-card">
          <div class="info">
            <h3>{{ selectedInterviewData.job_title }}</h3>
            <span class="employer">{{ selectedInterviewData.employer_name }}</span>
            <span class="date">{{ formatDate(selectedInterviewData.scheduled_at) }}</span>
          </div>

          <button
            class="join-btn"
            :disabled="!selectedInterviewData?.meet_link"
            @click="joinInterview(selectedInterviewData.id)"
          >
            <span v-if="canJoin">Join Interview</span>
            <span v-else>Join available in {{ countdown }}</span>
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
    const countdown = ref("");

    /* 🔙 BACK */
    const goBack = () => {
      window.history.back();
    };

    const fetchInterviews = async () => {
      loading.value = true;
      try {
        const res = await axios.get("/api/beneficiary/interviews");
        interviews.value = res.data;
      } catch (err) {
        console.error(err);
      } finally {
        loading.value = false;
      }
    };

    const selectedInterviewData = computed(() =>
      interviews.value.find(i => i.id === selectedInterview.value)
    );

    const canJoin = computed(() => {
      if (!selectedInterviewData.value || !selectedInterviewData.value.meet_link) return false;

      const startTime = new Date(selectedInterviewData.value.scheduled_at);
      const now = new Date();

      const startWindow = new Date(startTime.getTime() - 10 * 60000);
      const endWindow = new Date(startTime.getTime() + 60 * 60000);

      return now >= startWindow && now <= endWindow;
    });

    const updateCountdown = () => {
      if (!selectedInterviewData.value) {
        countdown.value = "";
        return;
      }

      const startTime = new Date(selectedInterviewData.value.scheduled_at);
      const now = new Date();
      const startWindow = new Date(startTime.getTime() - 10 * 60000);

      if (now < startWindow) {
        const diff = startWindow - now;
        const minutes = Math.floor(diff / 60000);
        const seconds = Math.floor((diff % 60000) / 1000);
        countdown.value = `${minutes}m ${seconds}s`;
      } else {
        countdown.value = "0m 0s";
      }
    };

    setInterval(updateCountdown, 1000);

    const joinInterview = (id) => {
      const interview = interviews.value.find(i => i.id === id);
      if (interview?.meet_link) {
        window.open(interview.meet_link, "_blank");
      } else {
        alert("Meet link not available yet.");
      }
    };

    const formatDate = (date) => new Date(date).toLocaleString();

    onMounted(fetchInterviews);

    return {
      interviews,
      selectedInterview,
      selectedInterviewData,
      joinInterview,
      loading,
      formatDate,
      canJoin,
      countdown,
      goBack
    };
  }
};
</script>

<style scoped>

/* Background */
.page {
  min-height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  background: #f8fafc;
  padding: 20px;
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
  position: relative;
}

/* 🔙 BACK BUTTON */
.back-btn {
  position: absolute;
  top: 20px;
  left: 20px;
  display: flex;
  align-items: center;
  gap: 6px;
  background: white;
  border: none;
  padding: 10px 14px;
  border-radius: 12px;
  font-size: 14px;
  color: #334155;
  box-shadow: 0 4px 20px rgba(0,0,0,0.08);
  cursor: pointer;
  transition: all 0.2s ease;
}

.back-btn:hover {
  transform: translateY(-1px);
  background: #eef2ff;
  color: #3b82f6;
}

.arrow {
  font-size: 16px;
  transition: transform 0.2s ease;
}

.back-btn:hover .arrow {
  transform: translateX(-3px);
}

/* Card */
.card {
  width: 100%;
  max-width: 520px;
  background: #ffffff;
  border-radius: 16px;
  padding: 40px;
  box-shadow: 0 10px 40px rgba(15, 23, 42, 0.08);
}

/* Header */
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

/* Select */
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

/* Interview Card */
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

/* Button */
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

/* States */
.state {
  margin-top: 30px;
  text-align: center;
  color: #64748b;
}
</style>