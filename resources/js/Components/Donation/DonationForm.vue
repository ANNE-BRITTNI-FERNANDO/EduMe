<template>
  <div class="max-w-4xl mx-auto p-6">
    <div class="bg-white rounded-lg shadow-lg p-6">
      <h2 class="text-2xl font-bold mb-6">Make a Donation</h2>
      
      <!-- Donation Type Selection -->
      <div class="mb-6">
        <label class="block text-gray-700 text-sm font-bold mb-2">Donation Type</label>
        <div class="flex space-x-4">
          <button 
            @click="donationType = 'item'"
            :class="['px-4 py-2 rounded', donationType === 'item' ? 'bg-blue-500 text-white' : 'bg-gray-200']"
          >
            Educational Items
          </button>
          <button 
            @click="donationType = 'monetary'"
            :class="['px-4 py-2 rounded', donationType === 'monetary' ? 'bg-blue-500 text-white' : 'bg-gray-200']"
          >
            Monetary Donation
          </button>
        </div>
      </div>

      <!-- Item Donation Form -->
      <div v-if="donationType === 'item'" class="space-y-4">
        <div>
          <label class="block text-gray-700 text-sm font-bold mb-2">Category</label>
          <select v-model="itemForm.category" class="w-full border rounded px-3 py-2">
            <option value="textbooks">Textbooks</option>
            <option value="stationery">Stationery</option>
            <option value="devices">Electronic Devices</option>
            <option value="other">Other</option>
          </select>
        </div>

        <div>
          <label class="block text-gray-700 text-sm font-bold mb-2">Education Level</label>
          <select v-model="itemForm.educationLevel" class="w-full border rounded px-3 py-2">
            <option value="primary">Primary</option>
            <option value="secondary">Secondary</option>
            <option value="tertiary">Tertiary</option>
          </select>
        </div>

        <div>
          <label class="block text-gray-700 text-sm font-bold mb-2">Condition</label>
          <select v-model="itemForm.condition" class="w-full border rounded px-3 py-2">
            <option value="new">New</option>
            <option value="like_new">Like New</option>
            <option value="good">Good</option>
            <option value="fair">Fair</option>
          </select>
        </div>

        <div>
          <label class="block text-gray-700 text-sm font-bold mb-2">Description</label>
          <textarea 
            v-model="itemForm.description"
            class="w-full border rounded px-3 py-2"
            rows="3"
          ></textarea>
        </div>

        <div>
          <label class="block text-gray-700 text-sm font-bold mb-2">Quantity</label>
          <input 
            type="number"
            v-model="itemForm.quantity"
            class="w-full border rounded px-3 py-2"
            min="1"
          >
        </div>

        <div>
          <label class="block text-gray-700 text-sm font-bold mb-2">Images</label>
          <input 
            type="file"
            @change="handleImageUpload"
            multiple
            accept="image/*"
            class="w-full border rounded px-3 py-2"
          >
        </div>
      </div>

      <!-- Monetary Donation Form -->
      <div v-if="donationType === 'monetary'" class="space-y-4">
        <div>
          <label class="block text-gray-700 text-sm font-bold mb-2">Amount (LKR)</label>
          <input 
            type="number"
            v-model="monetaryForm.amount"
            class="w-full border rounded px-3 py-2"
            min="0"
          >
        </div>

        <div>
          <label class="block text-gray-700 text-sm font-bold mb-2">Purpose</label>
          <select v-model="monetaryForm.purpose" class="w-full border rounded px-3 py-2">
            <option value="books">Books</option>
            <option value="devices">Electronic Devices</option>
            <option value="general">General Education Support</option>
          </select>
        </div>

        <div>
          <label class="block text-gray-700 text-sm font-bold mb-2">Payment Method</label>
          <select v-model="monetaryForm.paymentMethod" class="w-full border rounded px-3 py-2">
            <option value="card">Credit/Debit Card</option>
            <option value="bank">Bank Transfer</option>
          </select>
        </div>

        <div>
          <label class="block text-gray-700 text-sm font-bold mb-2">Message (Optional)</label>
          <textarea 
            v-model="monetaryForm.message"
            class="w-full border rounded px-3 py-2"
            rows="3"
          ></textarea>
        </div>

        <div class="flex items-center">
          <input 
            type="checkbox"
            v-model="monetaryForm.isAnonymous"
            class="mr-2"
          >
          <label>Make this donation anonymous</label>
        </div>
      </div>

      <!-- Submit Button -->
      <div class="mt-6">
        <button 
          @click="submitDonation"
          class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600"
        >
          Submit Donation
        </button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      donationType: 'item',
      itemForm: {
        category: 'textbooks',
        educationLevel: 'primary',
        condition: 'new',
        description: '',
        quantity: 1,
        images: []
      },
      monetaryForm: {
        amount: 0,
        purpose: 'general',
        paymentMethod: 'card',
        message: '',
        isAnonymous: false
      }
    }
  },
  methods: {
    handleImageUpload(event) {
      this.itemForm.images = Array.from(event.target.files)
    },
    async submitDonation() {
      try {
        if (this.donationType === 'item') {
          const formData = new FormData()
          Object.keys(this.itemForm).forEach(key => {
            if (key === 'images') {
              this.itemForm.images.forEach(image => {
                formData.append('images[]', image)
              })
            } else {
              formData.append(key, this.itemForm[key])
            }
          })
          
          await axios.post('/api/donations', formData, {
            headers: {
              'Content-Type': 'multipart/form-data'
            }
          })
        } else {
          await axios.post('/api/donations/monetary', this.monetaryForm)
        }
        
        // Show success message
        this.$emit('donation-success')
      } catch (error) {
        console.error('Error submitting donation:', error)
        // Show error message
      }
    }
  }
}
</script>
