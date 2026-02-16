<template>
  <div>
    <!-- Hero Section -->
    <v-img
      src="https://images.unsplash.com/photo-1552664730-d307ca884978?w=1200&h=600&fit=crop"
      height="600"
      class="d-flex align-center justify-center"
    >
      <v-container class="text-center">
        <v-row>
          <v-col cols="12">
            <h1 class="text-h2 font-weight-bold text-white mb-4">
              Catlendar
            </h1>
            <p class="text-h5 text-white mb-6">
              Organize Events. Connect with Friends. Create Memories.
            </p>
            <v-btn
              size="large"
              color="accent"
              @click="scrollToFeatures"
              class="px-8"
            >
              Learn More
            </v-btn>
          </v-col>
        </v-row>
      </v-container>
    </v-img>

    <!-- Features Section -->
    <v-container class="py-16">
      <h2 class="text-h3 font-weight-bold text-center mb-12">Our Features</h2>
      <v-row>
        <v-col cols="12" md="4" class="mb-8">
          <v-card elevation="0">
            <v-card-item>
              <v-icon size="48" color="primary" class="mb-4">
                mdi-calendar
              </v-icon>
              <h3 class="text-h5 font-weight-bold mb-2">Event Planning</h3>
              <p class="text-body1">
                Create and manage events with ease. Set dates, times, and
                locations all in one place.
              </p>
            </v-card-item>
          </v-card>
        </v-col>

        <v-col cols="12" md="4" class="mb-8">
          <v-card elevation="0">
            <v-card-item>
              <v-icon size="48" color="primary" class="mb-4">
                mdi-people
              </v-icon>
              <h3 class="text-h5 font-weight-bold mb-2">Team Collaboration</h3>
              <p class="text-body1">
                Invite friends and collaborate on event planning. Share ideas
                and stay synchronized.
              </p>
            </v-card-item>
          </v-card>
        </v-col>

        <v-col cols="12" md="4" class="mb-8">
          <v-card elevation="0">
            <v-card-item>
              <v-icon size="48" color="primary" class="mb-4">
                mdi-shield-check
              </v-icon>
              <h3 class="text-h5 font-weight-bold mb-2">Secure & Reliable</h3>
              <p class="text-body1">
                Your data is protected with industry-standard security
                measures. Trust us to keep your info safe.
              </p>
            </v-card-item>
          </v-card>
        </v-col>
      </v-row>
    </v-container>

    <!-- CTA Section -->
    <v-container class="py-16 bg-primary text-white">
      <v-row>
        <v-col cols="12" class="text-center">
          <h2 class="text-h3 font-weight-bold mb-4">
            Ready to Plan Your Next Event?
          </h2>
          <p class="text-h6 mb-8">
            Join thousands of users organizing amazing events
          </p>
          <v-btn size="large" color="accent" class="px-8"> Get Started </v-btn>
        </v-col>
      </v-row>
    </v-container>

    <!-- Stats Section -->
    <v-container class="py-16">
      <v-row class="text-center">
        <v-col cols="12" md="3" class="mb-8">
          <h3 class="text-h3 font-weight-bold text-primary">10K+</h3>
          <p class="text-body1">Active Users</p>
        </v-col>
        <v-col cols="12" md="3" class="mb-8">
          <h3 class="text-h3 font-weight-bold text-primary">50K+</h3>
          <p class="text-body1">Events Created</p>
        </v-col>
        <v-col cols="12" md="3" class="mb-8">
          <h3 class="text-h3 font-weight-bold text-primary">100K+</h3>
          <p class="text-body1">Connections Made</p>
        </v-col>
        <v-col cols="12" md="3" class="mb-8">
          <h3 class="text-h3 font-weight-bold text-primary">99.9%</h3>
          <p class="text-body1">Uptime</p>
        </v-col>
      </v-row>
    </v-container>
  </div>
</template>

<script setup>
const scrollToFeatures = () => {
  window.scrollTo({
    top: window.innerHeight,
    behavior: 'smooth',
  })
}
</script>

<style scoped>
.bg-primary {
  background-color: #1976d2;
}
</style>
