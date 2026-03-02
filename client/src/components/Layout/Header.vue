<template>
    <div>
      <h1>
        <RouterLink to="/" ><img src="../../../public/Home_Logo.png" class="ms-2" alt=""></RouterLink>
       
      </h1>
    </div>
</template>

<script>
export default {

}
</script>

<style scoped>
h1 img {
  height: 80px;
  width: auto;
  padding: none !important;
}

/* Alapállapot: Világoskék szín */
.nav-link[to="/"] {
  color: #64b5f6 !important;
  transition: color 0.3s ease; /* Szép folyamatos átmenet a színek között */
}

/* Amikor rajta van a kurzor (Hover): Szürkés szín */
.nav-link[to="/"]:hover {
  color: #6c757d !important; /* Ez a Bootstrap standard szürkéje (gray-600) */
  text-decoration: none !important; /* Biztos ami biztos, ne legyen aláhúzva */
}
</style>
