<script>
import NavButton from '@/Components/NavButton.vue'
import PrimaryRoseButton from '@/Components/PrimaryRoseButton.vue';
import PrimaryTransparentButton from '@/Components/PrimaryTransparentButton.vue';

export default {
  components: {
    NavButton,
    PrimaryRoseButton,
    PrimaryTransparentButton
  },
  data() {
    return {
      isHeaderVisible: true
    }
  },
  methods: {
    toggleHeader() {
      this.isHeaderVisible = !this.isHeaderVisible;
    },
    handleResize() {
      // Ensure header is visible on larger screens
      if (window.innerWidth >= 768) {
        this.isHeaderVisible = true;
      }
      if (window.innerWidth <= 767) {
        this.isHeaderVisible = false
      }
    }
  },
  mounted() {
    window.addEventListener('resize', this.handleResize);
  },
  beforeDestroy() {
    window.removeEventListener('resize', this.handleResize);
  }
}
</script>

<template>
  <section class="relative h-dvh w-100vh">
    <img src="images/warehouse 1.jpg" class="absolute top-0 left-0 w-full h-full z-0 object-fill opacity-30" alt="Warehouse">
    <div class="w-full h-full z-10 bg-slate-950 top-0 left-0 text-white">

      <div class="w-full h-full relative lg:pr-40 lg:pl-40">
        <!-- Mobile Toggle Button -->
        <div class="md:hidden p-3 bg-slate-950 opacity-90 w-full">
          <button @click="toggleHeader" class="text-white">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16m-7 6h7" />
            </svg>
          </button>
        </div>

        <!-- header -->
        <header :class="['transition-all duration-1000 ease-in-out transform', isHeaderVisible ? 'max-h-[60vh] translate-y-0 absolute md:relative top-10 md:top-0 left-0 bg-slate-950 md:bg-transparent opacity-70' : 'opacity-0 md:opacity-100 overflow-hidden -translate-y-[100vh]', 'md:max-h-fit md:flex md:flex-row flex-col md:items-center items-start w-full md:h-fit p-3 md:justify-between']">
          
          <div class="flex md:flex-row flex-col h-full items-center md:gap-20">

            <div class="logo avatar">
              <img src="images/CLISP-logo.png" class="rounded-full h-[70px]" alt="logo">
            </div>

            <nav>
              <ul class="list-none flex md:flex-row flex-col gap-2 lg:gap-10">
                <NavButton>
                  <a href="#">Home</a>
                </NavButton>
                <NavButton>
                  <a href="#">Home</a>
                </NavButton>
                <NavButton>
                  <a href="#">Home</a>
                </NavButton>
                <NavButton>
                  <a href="#">Home</a>
                </NavButton>
                <NavButton>
                  <a href="#">Home</a>
                </NavButton>
              </ul>
            </nav>
          </div>

          <div class="button-group flex md:flex-row flex-col gap-3">
            <PrimaryRoseButton>
              Register
            </PrimaryRoseButton>
            <PrimaryTransparentButton>
              Login
            </PrimaryTransparentButton>
          </div>

        </header>
      </div>

    </div>
  </section>
</template>
