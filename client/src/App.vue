<template>
  <div class="my-container app-shell">
    <!-- Head -->

    <!-- Menü -->
    <Menu/>
    <!-- <Breadcrumb class="my-border"/> -->

    <!-- Ide töltődnek be az oldalak -->
    <main class="app-content-offset">
      <RouterView class="my-border my-content-height" />
    </main>

    <ToastContanier/>
    <Footer/>
  </div>
</template>

<script>
import Menu from './components/Layout/Menu.vue';
import Header from './components/Layout/Header.vue';
import Footer from './components/Layout/Footer.vue';
import Breadcrumb from './components/Layout/Breadcrumb.vue';
import ToastContanier from './components/Message/ToastContanier.vue';
export default {
  components:{
    Menu,
    Header,
    Footer,
    Breadcrumb,
    ToastContanier,
  },
  data() {
    return {
    }
  }
};
</script>

<style>
:root {
  --app-gutter: 18px;
}

.app-content-offset {
  padding-top: var(--app-menu-height, 92px);
  flex: 1 0 auto;
}

.app-shell {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  margin: 0 var(--app-gutter);
  border: 1px solid #d7dde6;
  border-radius: 12px;
  overflow: hidden;
  background: #fdfefe;
}

@media (max-width: 768px) {
  :root {
    --app-gutter: 10px;
  }
}
</style>
