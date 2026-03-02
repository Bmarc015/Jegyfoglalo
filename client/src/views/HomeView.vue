<template>
  <div class="home-view">
    <!-- Carousel Background -->
    <div id="homeCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="3000">
      <!-- Carousel Indicators -->
      <div class="carousel-indicators">
        <button type="button" data-bs-target="#homeCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
        <button type="button" data-bs-target="#homeCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
        <button type="button" data-bs-target="#homeCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
      </div>
      
      <!-- Carousel Slides -->
      <div class="carousel-inner">
        <div class="carousel-item active">
          <img src="/carousel_santiago.jpg" class="d-block w-100 carousel-img" alt="Football 1">
        </div>
        <div class="carousel-item">
          <img src="/carousel2_santiago.jpg" class="d-block w-100 carousel-img" alt="Football 2">
        </div>
        <div class="carousel-item">
          <img src="/carousel3_santiago.jpg" class="d-block w-100 carousel-img" alt="Football 3">
        </div>
      </div>
    </div>
    
    <!-- Content Overlay -->
    <div class="content-overlay flex-grow-0">
      <h1 class="text-center mt-5">Welcome to TicketMaster!</h1>
      <p class="text-center mt-3">
        Discover the best football events and get your tickets now. 
      </p>
      <p>
        Don't miss out on the action!
      </p>
      <div class="d-flex justify-content-center mt-4">
        <RouterLink to="/matches" class="btn btn-primary btn-lg">
          Featured Matches
        </RouterLink>
      </div>
    </div>
    
    <!-- Match Calendar -->
    <div class="match-calendar container mt-auto pt-5 mb-4">
      <h5 class="text-center mb-3">Select a Day</h5>
      <div class="d-flex justify-content-center flex-wrap gap-2">
        <div 
          v-for="(day, index) in weekDays" 
          :key="index"
        >
          <div 
            class="calendar-day text-center" 
            :class="{ 'active': selectedDay === index }"
            @click="selectDay(index)"
          >
            <div class="day-name">{{ day.name }}</div>
            <div class="day-date">{{ day.date }}</div>
          </div>
        </div>
      </div>
      
      <!-- Matches for Selected Day -->
      <div class="matches-section mt-4">
        <h5 class="mb-3">Matches on {{ weekDays[selectedDay]?.name }} {{ weekDays[selectedDay]?.date }}</h5>
        <div class="row justify-content-center">
          <div 
            v-for="match in matches" 
            :key="match.id"
            class="col-12 col-md-6 col-lg-4 mb-3"
          >
            <div class="card match-card h-100">
              <div class="card-body text-center">
                <div class="match-time">{{ match.time }}</div>
                <div class="match-teams">
                  <span class="team-name">{{ match.homeTeam }}</span>
                  <span class="vs">vs</span>
                  <span class="team-name">{{ match.awayTeam }}</span>
                </div>
                <div class="match-venue">{{ match.venue }}</div>
                <button class="btn btn-sm btn-outline-primary mt-2">Buy Tickets</button>
              </div>
            </div>
          </div>
        </div>
        <div v-if="matches.length === 0" class="text-center text-muted">
          <p>No matches scheduled for this day.</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      selectedDay: 0,
      weekDays: [],
      matches: []
    };
  },
  mounted() {
    this.generateWeekDays();
    this.loadMatches();
  },
  methods: {
    generateWeekDays() {
      const days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
      const today = new Date();
      
      for (let i = 0; i < 7; i++) {
        const date = new Date(today);
        date.setDate(today.getDate() + i);
        
        this.weekDays.push({
          name: days[date.getDay()],
          date: date.getDate(),
          fullDate: date.toISOString().split('T')[0]
        });
      }
    },
    selectDay(index) {
      this.selectedDay = index;
      this.loadMatches();
    },
    loadMatches() {
      // Sample matches data - in real app, this would fetch from API based on selected date
      const sampleMatches = [
        { id: 1, time: '15:00', homeTeam: 'Real Madrid', awayTeam: 'Barcelona', venue: 'Santiago Bernabéu' },
        { id: 2, time: '18:00', homeTeam: 'Manchester United', awayTeam: 'Liverpool', venue: 'Old Trafford' },
        { id: 3, time: '20:00', homeTeam: 'Bayern Munich', awayTeam: 'Dortmund', venue: 'Allianz Arena' }
      ];
      
      // For demo, show same matches for all days
      this.matches = sampleMatches;
    }
  }
};
</script>

<style scoped>
.home-view {
  display: flex;
  flex-direction: column;
  min-height: 80vh;
}

.carousel-img {
  height: 80vh;
  object-fit: cover;
  filter: brightness(0.5);
}

.content-overlay {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 100%;
  text-align: center;
  color: white;
  z-index: 10;
  padding: 20px;
}

.content-overlay h1 {
  font-size: 3rem;
  font-weight: bold;
  text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7);
}

.content-overlay p {
  font-size: 1.5rem;
  text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.7);
}

.calendar-day {
  cursor: pointer;
  transition: all 0.2s ease;
  border: 2px solid #dee2e6;
  background-color: white;
  padding: 8px 16px;
  border-radius: 8px;
  min-width: 70px;
}

.calendar-day:hover {
  border-color: #0d6efd;
  background-color: #f8f9fa;
}

.calendar-day.active {
  background-color: #0d6efd;
  color: white;
  border-color: #0d6efd;
}

.day-name {
  font-size: 0.75rem;
  font-weight: bold;
  text-transform: uppercase;
}

.day-date {
  font-size: 1.1rem;
  font-weight: bold;
}

.match-card {
  border: 1px solid #dee2e6;
  border-radius: 10px;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.match-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.match-time {
  font-size: 0.9rem;
  color: #6c757d;
  font-weight: bold;
}

.match-teams {
  font-size: 1rem;
  font-weight: bold;
  margin: 10px 0;
}

.team-name {
  color: #212529;
}

.vs {
  color: #6c757d;
  margin: 0 8px;
  font-size: 0.85rem;
}

.match-venue {
  font-size: 0.85rem;
  color: #6c757d;
}

.gap-2 {
  gap: 0.5rem;
}
</style>
