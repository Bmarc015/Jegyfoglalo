<template>
  <div class=" my-container  ">
    <!-- Head -->

    <!-- Menü -->
    <Menu/>
    <!-- <Breadcrumb class="my-border"/> -->

    <!-- Ide töltődnek be az oldalak -->
    <RouterView class="my-border my-content-height"/>

    <ToastContanier/>
    <Footer class="my-border"/>
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

</style>
