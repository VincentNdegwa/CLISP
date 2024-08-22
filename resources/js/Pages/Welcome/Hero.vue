<script>
import NavButton from '@/Components/NavButton.vue'
import PrimaryRoseButton from '@/Components/PrimaryRoseButton.vue';
import PrimaryTransparentButton from '@/Components/PrimaryTransparentButton.vue';
export default {
  components: {
    NavButton,
    PrimaryRoseButton,
    PrimaryTransparentButton
  }
}
</script>
<template>
      <section class="relative h-dvh w-100vh">
      <img src="images/warehouse 1.jpg" class="absolute top-0 left-0 w-full h-full z-0 object-fill opacity-30"
        alt="Warehouse">
      <div class="w-full h-full z-10 bg-slate-950 top-0 left-0 text-white">

        <div class="w-full h-full relative lg:pr-40 lg:pl-40">
            <!-- header -->
          <header class=" flex md:flex-row flex-col justify-between md:items-center items-start w-full md:h-fit h-dvh p-3">
            <div class="flex md:flex-row flex-col h-full items-center md:gap-20">

              <div class="logo avatar ">
                <img src="images/CLISP-logo.png" class="rounded-full h-[70px]" alt="logo">
              </div>

              <nav>
                <ul class=" list-none flex md:flex-row flex-col gap-2 lg:gap-16 ">
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