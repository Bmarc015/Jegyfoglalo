<template>
  <div class="home-view">
    <!-- Carousel -->
    <div
      id="homeCarousel"
      class="carousel slide carousel-fade"
      data-bs-ride="carousel"
      data-bs-interval="3000"
    >
      <div class="carousel-indicators">
        <button
          type="button"
          data-bs-target="#homeCarousel"
          data-bs-slide-to="0"
          class="active"
          aria-current="true"
          aria-label="Slide 1"
        ></button>
        <button
          type="button"
          data-bs-target="#homeCarousel"
          data-bs-slide-to="1"
          aria-label="Slide 2"
        ></button>
        <button
          type="button"
          data-bs-target="#homeCarousel"
          data-bs-slide-to="2"
          aria-label="Slide 3"
        ></button>
      </div>
      <!-- Carousel Pics -->
      <div class="carousel-inner">
        <div class="carousel-item active">
          <img
            src="/carousel_santiago.jpg"
            class="d-block w-100 carousel-img"
            alt="Football 1"
          />
        </div>
        <div class="carousel-item">
          <img
            src="/carousel2_santiago.jpg"
            class="d-block w-100 carousel-img"
            alt="Football 2"
          />
        </div>
        <div class="carousel-item">
          <img
            src="/carousel3_santiago.jpg"
            class="d-block w-100 carousel-img"
            alt="Football 3"
          />
        </div>
      </div>
    </div>
    <!-- Carousel Text -->
    <div class="content-overlay flex-grow-0">
      <h1 class="text-center mt-5">Welcome to TicketMaster!</h1>
      <p class="text-center mt-3">
        Discover the best football events and get your tickets now.
      </p>
      <p>Don't miss out on the action!</p>
      <div class="d-flex justify-content-center mt-4">
        <RouterLink to="/tickets" class="btn btn-primary btn-lg">
          Featured Matches
        </RouterLink>
      </div>
    </div>
    <!-- Calendar -->
    <HomeMatchCalendar />
  </div>
</template>

<script>
import HomeMatchCalendar from "@/components/Home/HomeMatchCalendar.vue";

export default {
  name: "HomeView",
  components: {
    HomeMatchCalendar,
  },
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
</style>
