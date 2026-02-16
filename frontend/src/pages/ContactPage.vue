<template>
  <v-container class="py-8">
    <v-row>
      <v-col cols="12">
        <h1 class="text-h3 mb-6">Contact Us</h1>
      </v-col>
    </v-row>
    
    <v-row>
      <v-col cols="12" md="6">
        <v-card class="pa-6">
          <h2 class="text-h5 mb-4">Get in Touch</h2>
          <v-form @submit.prevent="submitForm">
            <v-text-field
              v-model="form.name"
              label="Name"
              required
              class="mb-4"
            ></v-text-field>
            
            <v-text-field
              v-model="form.email"
              label="Email"
              type="email"
              required
              class="mb-4"
            ></v-text-field>
            
            <v-text-field
              v-model="form.subject"
              label="Subject"
              required
              class="mb-4"
            ></v-text-field>
            
            <v-textarea
              v-model="form.message"
              label="Message"
              required
              class="mb-4"
            ></v-textarea>
            
            <v-btn type="submit" color="primary">Send Message</v-btn>
          </v-form>
        </v-card>
      </v-col>
      
      <v-col cols="12" md="6">
        <v-card class="pa-6">
          <h2 class="text-h5 mb-4">Contact Information</h2>
          <div class="mb-4">
            <p class="font-weight-bold">Email:</p>
            <p>info@catlendar.com</p>
          </div>
          <div class="mb-4">
            <p class="font-weight-bold">Phone:</p>
            <p>+1 (555) 123-4567</p>
          </div>
          <div>
            <p class="font-weight-bold">Address:</p>
            <p>123 Event Street<br>New York, NY 10001</p>
          </div>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
import { reactive } from 'vue'

const form = reactive({
  name: '',
  email: '',
  subject: '',
  message: '',
})

const submitForm = async () => {
  // Submit form to backend
  console.log('Form submitted:', form)
  // Reset form
  form.name = ''
  form.email = ''
  form.subject = ''
  form.message = ''
}
</script>

<style scoped>
</style>
