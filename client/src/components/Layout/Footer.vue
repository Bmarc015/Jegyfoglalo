<template>
  <footer class="footer bg-dark text-white py-4 mt-auto">
    <div class="container">
      <div class="row">
        <!-- About Section -->
        <div class="col-md-4 mb-3 mb-md-0">
          <h5>TicketMaster</h5>
          <p class="small text-light mb-0">
            Your one-stop destination for football tickets. 
            Experience the thrill of live matches in Estadio Santiago Bernabéu!
          </p>
        </div>
        
        <!-- Quick Links -->
        <div class="col-md-4 mb-3 mb-md-0">
          <h5>Quick Links</h5>
          <ul class="list-unstyled mb-0">
            <li><RouterLink to="/about" class="text-light text-decoration-none small">About Us</RouterLink></li>
            <li><RouterLink to="/matches" class="text-light text-decoration-none small">Matches</RouterLink></li>
            <li><RouterLink to="/login" class="text-light text-decoration-none small">Login</RouterLink></li>
          </ul>
        </div>
        
        <!-- Contact Section -->
        <div class="col-md-4">
          <h5>Contact</h5>
          <p class="small text-light mb-1">
            <i class="bi bi-envelope me-2"></i>support@ticketmaster.com
          </p>
          <p class="small text-light mb-0">
            <i class="bi bi-phone me-2"></i>+1-800-TICKETS
          </p>
        </div>
      </div>
      
      <hr class="my-3">
      
      <!-- Copyright -->
      <div class="row">
        <div class="col-12 text-center">
          <p class="small text-light mb-0">
            &copy; {{ currentYear }} TicketMaster. All rights reserved.
          </p>
        </div>
      </div>
    </div>
  </footer>
</template>

<script>
export default {
  computed: {
    currentYear() {
      return new Date().getFullYear();
    }
  }
};
</script>

<style scoped>
.footer {
  position: relative;
  width: 100%;
  border-top: 1px solid #2b3a47;
}

.footer a:hover {
  color: #0d6efd !important;
}

ul li {
  margin-bottom: 0.25rem;
}
</style>
